<?php
/**
 * Short review links, scan counts, the weekly scan email and the shop
 * settings under Settings → General.
 *
 * /review, /review/qr, /review/nfc, /review/receipt, /review/web (plus /qr and
 * /nfc) 302 to the Google review form — never cached, never indexed — so the
 * destination can change without reprinting. Each source keeps a daily
 * count. No cookies, IPs or other personal data.
 *
 * Three-layer safety net (cornwall_write_review_fallbacks):
 * 1. Theme template_redirect at priority 0 (normal path + counter).
 * 2. wp-content/mu-plugins/cornwall-review-fallback.php, a copy of
 *    inc/review-fallback.php (theme rollbacks, plugin/theme fatal errors).
 * 3. wp-content/db-error.php + .htaccess ErrorDocument (database outages and
 *    server 500/502/503/404 responses on /review*, /qr and /nfc).
 */

if (! defined('ABSPATH')) {
    exit;
}

function cornwall_review_sources()
{
    return array(
        'qr'      => 'QR code at the till',
        'nfc'     => 'NFC card at the till',
        'receipt' => 'QR code on receipt',
        'web'     => 'Website “Write a review” button',
        'typed'   => 'Typed in (receipt text, word of mouth)',
    );
}

function cornwall_review_url()
{
    $url = (string) get_option('cornwall_review_url', '');
    return $url !== '' ? $url : CORNWALL_REVIEW_URL;
}

function cornwall_review_should_count()
{
    $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper((string) $_SERVER['REQUEST_METHOD']) : 'GET';
    if ($method !== 'GET' || is_user_logged_in()) {
        return false;
    }
    $purpose = strtolower((string) ($_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? ''));
    if (strpos($purpose, 'prefetch') !== false) {
        return false;
    }
    $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower((string) wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
    if ($agent === '') {
        return false;
    }
    $not_people = array('bot', 'crawl', 'spider', 'slurp', 'preview', 'facebookexternalhit', 'whatsapp', 'curl', 'wget', 'python', 'headless', 'lighthouse');
    foreach ($not_people as $needle) {
        if (strpos($agent, $needle) !== false) {
            return false;
        }
    }
    return true;
}

function cornwall_review_count($source)
{
    $hits = get_option('cornwall_review_hits');
    if (! is_array($hits) || ! isset($hits['days']) || ! is_array($hits['days'])) {
        $hits = array('since' => wp_date('Y-m-d'), 'days' => array());
    }
    $day = wp_date('Y-m-d');
    if (! isset($hits['days'][$day][$source])) {
        $hits['days'][$day][$source] = 0;
    }
    $hits['days'][$day][$source]++;
    $hits['last'] = array('time' => time(), 'source' => $source);
    update_option('cornwall_review_hits', $hits, false);
}

function cornwall_review_redirect()
{
    if (cornwall_review_fallback_source() === null) {
        return;
    }
    $source = cornwall_review_fallback_source();

    try {
        if (cornwall_review_should_count()) {
            cornwall_review_count($source);
        }
    } catch (\Throwable $e) {
        // Never block the redirect if the database counter fails.
    }

    $target = cornwall_review_url();
    $GLOBALS['cornwall_review_redirected'] = true;
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    nocache_headers();
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
    header('X-Robots-Tag: noindex, nofollow');
    if (! wp_redirect($target, 302, 'Cornwall review link')) {
        header('X-Redirect-By: Cornwall review link');
        header('Location: ' . $target, true, 302);
    }
    exit;
}
add_action('template_redirect', 'cornwall_review_redirect', 0);

function cornwall_write_review_fallbacks($override_url = null)
{
    $url = is_string($override_url) && $override_url !== '' ? $override_url : cornwall_review_url();
    $exported_url = var_export($url, true);

    if (defined('WP_CONTENT_DIR') && is_dir(WP_CONTENT_DIR) && is_writable(WP_CONTENT_DIR)) {
        $mu_dir = WP_CONTENT_DIR . '/mu-plugins';
        if (! is_dir($mu_dir)) {
            @wp_mkdir_p($mu_dir);
        }
        $source = get_theme_file_path('inc/review-fallback.php');
        $mu_file = $mu_dir . '/cornwall-review-fallback.php';
        if (is_dir($mu_dir) && is_writable($mu_dir) && is_readable($source)) {
            $code = (string) file_get_contents($source);
            if (! file_exists($mu_file) || (string) @file_get_contents($mu_file) !== $code) {
                @file_put_contents($mu_file, $code);
            }
        }

        $db_err_file = WP_CONTENT_DIR . '/db-error.php';
        $can_write_db = ! file_exists($db_err_file)
            || strpos((string) @file_get_contents($db_err_file), 'Cornwall review fallback') !== false;
        if ($can_write_db) {
            $db_code = <<<PHP
<?php
/**
 * Cornwall review fallback for database errors.
 * Redirects /review*, /qr and /nfc to the Google review form if the DB is unavailable.
 */
define('CORNWALL_REVIEW_FALLBACK_URL', {$exported_url});
define('CORNWALL_REVIEW_FALLBACK_NO_HOOKS', true);
\$cornwall_fallback = __DIR__ . '/mu-plugins/cornwall-review-fallback.php';
if (is_readable(\$cornwall_fallback)) {
    require_once \$cornwall_fallback;
    if (function_exists('cornwall_review_fallback_source') && cornwall_review_fallback_source() !== null) {
        cornwall_review_fallback_send('Cornwall review fallback (db-error)', false);
    }
}
if (! headers_sent()) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
    header('Cache-Control: no-cache, must-revalidate, max-age=0');
    header('Content-Type: text/html; charset=utf-8');
}
echo '<!DOCTYPE html><html lang="en-GB"><head><meta charset="utf-8"><title>Database Error</title></head><body><h1>Error establishing a database connection</h1></body></html>';
die();
PHP;
            if ((string) @file_get_contents($db_err_file) !== $db_code . "\n") {
                @file_put_contents($db_err_file, $db_code . "\n");
            }
        }
    }

    if (defined('ABSPATH')) {
        $htaccess = ABSPATH . '.htaccess';
        if ((file_exists($htaccess) && is_writable($htaccess)) || (! file_exists($htaccess) && is_writable(ABSPATH))) {
            if (! function_exists('insert_with_markers')) {
                require_once ABSPATH . 'wp-admin/includes/misc.php';
            }
            if (function_exists('insert_with_markers')) {
                $rules = array(
                    '<If "%{REQUEST_URI} =~ m#^/(review(/.*)?|qr|nfc)/?$#i">',
                    '    ErrorDocument 404 ' . $url,
                    '    ErrorDocument 500 ' . $url,
                    '    ErrorDocument 502 ' . $url,
                    '    ErrorDocument 503 ' . $url,
                    '</If>',
                );
                @insert_with_markers($htaccess, 'CornwallReviewFallback', $rules);
            }
        }
    }
}
add_action('update_option_cornwall_review_url', function ($old_value, $value) {
    cornwall_write_review_fallbacks(is_string($value) && $value !== '' ? $value : CORNWALL_REVIEW_URL);
}, 10, 2);
add_action('add_option_cornwall_review_url', function ($option, $value) {
    cornwall_write_review_fallbacks(is_string($value) && $value !== '' ? $value : CORNWALL_REVIEW_URL);
}, 10, 2);

/* ---- Scan totals (Settings table and weekly email) ---- */

/**
 * Per-source and total counts for windows ending today (Europe/London).
 * $windows: key => number of days (0 = all time).
 */
function cornwall_review_hits_summary($windows, $end_offset_days = 0)
{
    $hits = get_option('cornwall_review_hits');
    $days = (is_array($hits) && isset($hits['days']) && is_array($hits['days'])) ? $hits['days'] : array();
    $end  = (new DateTimeImmutable('now', wp_timezone()))->modify('-' . (int) $end_offset_days . ' days');
    $ranges = array();
    foreach ($windows as $key => $length) {
        $ranges[$key] = array(
            'from' => $length > 0 ? $end->modify('-' . ($length - 1) . ' days')->format('Y-m-d') : '0000-00-00',
            'to'   => $end->format('Y-m-d'),
        );
    }
    $rows = array();
    $total = array_fill_keys(array_keys($windows), 0);
    foreach (cornwall_review_sources() as $key => $label) {
        $rows[$key] = array_fill_keys(array_keys($windows), 0);
    }
    foreach ($days as $day => $counts) {
        foreach ((array) $counts as $key => $n) {
            if (! isset($rows[$key])) {
                continue;
            }
            foreach ($ranges as $window => $range) {
                if (strcmp((string) $day, $range['from']) >= 0 && strcmp((string) $day, $range['to']) <= 0) {
                    $rows[$key][$window] += (int) $n;
                    $total[$window] += (int) $n;
                }
            }
        }
    }
    return array('rows' => $rows, 'total' => $total, 'ranges' => $ranges, 'hits' => $hits);
}

/* ---- Weekly scan email ---- */

function cornwall_scan_email_to()
{
    return (string) get_option('cornwall_scan_email', cornwall_biz('email'));
}

function cornwall_scan_email_content()
{
    $week = cornwall_review_hits_summary(array('week' => 7, 'all' => 0));
    $prev = cornwall_review_hits_summary(array('week' => 7), 7);
    $from = DateTimeImmutable::createFromFormat('!Y-m-d', $week['ranges']['week']['from'], wp_timezone());
    $to   = DateTimeImmutable::createFromFormat('!Y-m-d', $week['ranges']['week']['to'], wp_timezone());
    $span = $from->format('j M') . ' – ' . $to->format('j M Y');

    $subject = sprintf('Review scans: %d this week (%s)', $week['total']['week'], $span);
    $lines = array(
        'Review link scans for ' . $span . ' (Europe/London).',
        '',
        sprintf('This week: %d · Week before: %d · All time: %d', $week['total']['week'], $prev['total']['week'], $week['total']['all']),
        '',
    );
    foreach (cornwall_review_sources() as $key => $label) {
        $lines[] = sprintf('%s: %d this week (%d all time)', $label, $week['rows'][$key]['week'], $week['rows'][$key]['all']);
    }
    $lines[] = '';
    $lines[] = 'A scan is someone opening the review link. Google shows the reviews they actually leave.';
    $lines[] = 'Rating shown on the website: ' . cornwall_rating_value() . ' from ' . cornwall_review_count_total() . ' Google reviews. Update it under Settings → General when Google changes.';
    $lines[] = '';
    $lines[] = admin_url('options-general.php#cornwall_review_url');
    $lines[] = '';
    $lines[] = 'Sent every Monday morning by the website. Change the address or leave it blank to stop these emails under Settings → General.';
    return array('subject' => $subject, 'body' => implode("\n", $lines));
}

function cornwall_send_scan_email()
{
    $to = cornwall_scan_email_to();
    if (! is_email($to)) {
        return false;
    }
    $mail = cornwall_scan_email_content();
    return wp_mail($to, $mail['subject'], $mail['body'], array('Content-Type: text/plain; charset=UTF-8'));
}
add_action('cornwall_weekly_scan_email', 'cornwall_send_scan_email');

/** Next Monday 08:00 Europe/London as a Unix timestamp. */
function cornwall_next_monday_8am()
{
    $now = new DateTimeImmutable('now', wp_timezone());
    $next = $now->setTime(8, 0);
    if ((int) $now->format('N') !== 1 || $now >= $next) {
        $next = $now->modify('next monday')->setTime(8, 0);
    }
    return $next->getTimestamp();
}

/**
 * The scan email is a single event re-armed for the next Monday 8am after
 * each send, so it stays at 8am London time across clock changes.
 */
function cornwall_schedule_events()
{
    if (! wp_next_scheduled('cornwall_weekly_scan_email')) {
        wp_schedule_single_event(cornwall_next_monday_8am(), 'cornwall_weekly_scan_email');
    }
    if (! wp_next_scheduled('cornwall_bank_holidays_refresh')) {
        wp_schedule_event(time() + 60, 'weekly', 'cornwall_bank_holidays_refresh');
    }
}
add_action('init', 'cornwall_schedule_events', 30);

function cornwall_scan_email_test()
{
    if (! current_user_can('manage_options')) {
        wp_die('Not allowed.');
    }
    check_admin_referer('cornwall_scan_email_test');
    $sent = cornwall_send_scan_email();
    wp_safe_redirect(add_query_arg('cornwall_scan_test', $sent ? 'sent' : 'failed', admin_url('options-general.php')));
    exit;
}
add_action('admin_post_cornwall_scan_email_test', 'cornwall_scan_email_test');

function cornwall_scan_email_notice()
{
    if (! isset($_GET['cornwall_scan_test'])) {
        return;
    }
    $sent = $_GET['cornwall_scan_test'] === 'sent';
    printf(
        '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
        $sent ? 'success' : 'error',
        $sent
            ? esc_html('Test scan email sent to ' . cornwall_scan_email_to() . '. If it does not arrive, check spam or set up SMTP (Cloudways → Application → SMTP).')
            : esc_html('The test scan email could not be sent. Check the address, or set up SMTP for the site.')
    );
}
add_action('admin_notices', 'cornwall_scan_email_notice');

/* ---- Settings → General ---- */

function cornwall_review_settings()
{
    register_setting('general', 'cornwall_review_url', array(
        'type'              => 'string',
        'sanitize_callback' => 'cornwall_review_sanitize_url',
        'default'           => '',
    ));
    register_setting('general', 'cornwall_scan_email', array(
        'type'              => 'string',
        'sanitize_callback' => 'cornwall_sanitize_scan_email',
        'default'           => cornwall_biz('email'),
    ));
    register_setting('general', 'cornwall_rating_value', array(
        'type'              => 'string',
        'sanitize_callback' => 'cornwall_sanitize_rating_value',
        'default'           => '4.9',
    ));
    register_setting('general', 'cornwall_review_count_total', array(
        'type'              => 'integer',
        'sanitize_callback' => 'cornwall_sanitize_review_count_total',
        'default'           => 45,
    ));
    register_setting('general', 'cornwall_holiday_notice', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ));
    foreach (array('cornwall_closed_from', 'cornwall_closed_to') as $opt) {
        register_setting('general', $opt, array(
            'type'              => 'string',
            'sanitize_callback' => 'cornwall_valid_date',
            'default'           => '',
        ));
    }
    add_settings_field('cornwall_review_url', 'Review link', 'cornwall_review_url_field', 'general', 'default', array('label_for' => 'cornwall_review_url'));
    add_settings_field('cornwall_review_hits', 'Review scans', 'cornwall_review_hits_field', 'general');
    add_settings_field('cornwall_scan_email', 'Weekly scan email', 'cornwall_scan_email_field', 'general', 'default', array('label_for' => 'cornwall_scan_email'));
    add_settings_field('cornwall_rating_summary', 'Google rating & count', 'cornwall_rating_summary_field', 'general');
    add_settings_field('cornwall_holiday_notice', 'Holiday / special hours', 'cornwall_holiday_notice_field', 'general', 'default', array('label_for' => 'cornwall_holiday_notice'));
}
add_action('admin_init', 'cornwall_review_settings');

function cornwall_sanitize_scan_email($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (! is_email($value)) {
        add_settings_error('cornwall_scan_email', 'cornwall_scan_email', 'The weekly scan email address is not valid. The previous address was kept.');
        return cornwall_scan_email_to();
    }
    return sanitize_email($value);
}

function cornwall_sanitize_rating_value($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '4.9';
    }
    if (! preg_match('/^[1-5](\.[0-9])?$/', $value)) {
        add_settings_error('cornwall_rating_value', 'cornwall_rating_value', 'Google rating must be a number between 1.0 and 5.0 (for example 4.9).');
        return cornwall_rating_value();
    }
    return $value;
}

function cornwall_sanitize_review_count_total($value)
{
    $n = (int) $value;
    return $n > 0 ? $n : 45;
}

function cornwall_review_sanitize_url($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    $clean = esc_url_raw($value, array('https'));
    if ($clean === '') {
        add_settings_error('cornwall_review_url', 'cornwall_review_url', 'The review link must start with https://. The previous link was kept.');
        return (string) get_option('cornwall_review_url', '');
    }
    return $clean;
}

function cornwall_review_url_field()
{
    printf(
        '<input name="cornwall_review_url" id="cornwall_review_url" type="url" class="large-text code" value="%s" placeholder="%s" />',
        esc_attr((string) get_option('cornwall_review_url', '')),
        esc_attr(CORNWALL_REVIEW_URL)
    );
    echo '<p class="description">Where <code>' . esc_html(wp_parse_url(home_url('/review'), PHP_URL_HOST) . '/review') . '</code> sends people (302, not indexed). Leave blank to use the built-in Google review form. Must start with https://</p>';
}

function cornwall_scan_email_field()
{
    printf(
        '<input name="cornwall_scan_email" id="cornwall_scan_email" type="email" class="regular-text" value="%s" />',
        esc_attr(cornwall_scan_email_to())
    );
    $next = wp_next_scheduled('cornwall_weekly_scan_email');
    $test = wp_nonce_url(admin_url('admin-post.php?action=cornwall_scan_email_test'), 'cornwall_scan_email_test');
    echo '<p class="description">A short summary of the review scans, every Monday at 8am. Leave blank to stop.'
        . ($next ? ' Next email: ' . esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $next)) . '.' : '')
        . ' <a href="' . esc_url($test) . '">Send a test now</a></p>';
}

function cornwall_rating_summary_field()
{
    printf(
        '<label for="cornwall_rating_value">Rating </label><input name="cornwall_rating_value" id="cornwall_rating_value" type="text" class="small-text" value="%s" /> &nbsp; <label for="cornwall_review_count_total">Reviews </label><input name="cornwall_review_count_total" id="cornwall_review_count_total" type="number" min="1" step="1" class="small-text" value="%s" />',
        esc_attr(cornwall_rating_value()),
        esc_attr(cornwall_review_count_total())
    );
    echo '<p class="description">Shown on the homepage hero card, the Reviews heading, Google structured data, and <code>/llms.txt</code>.</p>';
}

function cornwall_holiday_notice_field()
{
    printf(
        '<input name="cornwall_holiday_notice" id="cornwall_holiday_notice" type="text" class="regular-text" value="%s" placeholder="e.g. Closed 25–28 Dec for Christmas — back Tuesday 29 Dec at 8am" />',
        esc_attr(cornwall_holiday_notice_raw())
    );
    printf(
        '<p><label for="cornwall_closed_from">Closed from </label><input name="cornwall_closed_from" id="cornwall_closed_from" type="date" value="%s" /> <label for="cornwall_closed_to"> to </label><input name="cornwall_closed_to" id="cornwall_closed_to" type="date" value="%s" /></p>',
        esc_attr(cornwall_valid_date(get_option('cornwall_closed_from', ''))),
        esc_attr(cornwall_valid_date(get_option('cornwall_closed_to', '')))
    );
    echo '<p class="description">The status pill shows “Closed” on those days, and the notice disappears by itself after the last day. For a single day, fill in just the first date. Bank holidays in England and Wales are handled automatically.</p>';
}

function cornwall_review_hits_field()
{
    $summary = cornwall_review_hits_summary(array('today' => 1, 'week' => 7, 'month' => 30, 'all' => 0));
    $rows  = $summary['rows'];
    $total = $summary['total'];
    $hits  = $summary['hits'];
    $sources = cornwall_review_sources();

    // WordPress stacks every .form-table cell on small screens, including nested tables.
    echo '<style>.cornwall-review-scans{overflow-x:auto;max-width:760px}.cornwall-review-scans table{min-width:520px}.form-table .cornwall-review-scans th,.form-table .cornwall-review-scans td{display:table-cell;padding:8px 10px;width:auto}.cornwall-review-scans td:nth-child(n+3),.cornwall-review-scans th:nth-child(n+3){text-align:right}</style>';
    echo '<div class="cornwall-review-scans"><table class="widefat striped"><thead><tr><th>Where they came from</th><th>Link</th><th>Today</th><th>Last 7 days</th><th>Last 30 days</th><th>All time</th></tr></thead><tbody>';
    foreach ($sources as $key => $label) {
        if ($key === 'typed') {
            $path = '<code>/review</code>';
        } elseif ($key === 'qr' || $key === 'nfc') {
            $path = '<code>/review/' . esc_html($key) . '</code>, <code>/' . esc_html($key) . '</code>';
        } else {
            $path = '<code>/review/' . esc_html($key) . '</code>';
        }
        printf(
            '<tr><td>%s</td><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td></tr>',
            esc_html($label),
            $path,
            $rows[$key]['today'],
            $rows[$key]['week'],
            $rows[$key]['month'],
            $rows[$key]['all']
        );
    }
    printf(
        '</tbody><tfoot><tr><th>Total</th><th></th><th>%d</th><th>%d</th><th>%d</th><th>%d</th></tr></tfoot></table></div>',
        $total['today'],
        $total['week'],
        $total['month'],
        $total['all']
    );

    $notes = array();
    if (is_array($hits) && isset($hits['last']['time'])) {
        $last_source = isset($sources[$hits['last']['source']]) ? $sources[$hits['last']['source']] : '';
        $notes[] = 'Last scan ' . wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) $hits['last']['time']) . ($last_source !== '' ? ' (' . $last_source . ')' : '');
    }
    if (is_array($hits) && isset($hits['since'])) {
        $since = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $hits['since'], wp_timezone());
        if ($since) {
            $notes[] = 'Counting since ' . wp_date(get_option('date_format'), $since->getTimestamp());
        }
    }
    $notes[] = 'Link previews, bots and visits while logged in to WordPress are not counted.';
    echo '<p class="description">' . esc_html(implode(' · ', $notes)) . '<br>Counts only. No IP addresses, cookies or other personal data are stored.</p>';
}

function cornwall_refresh_after_shop_setting_change()
{
    cornwall_write_root_files();
    cornwall_purge_page_cache();
}
foreach (array('cornwall_rating_value', 'cornwall_review_count_total', 'cornwall_holiday_notice', 'cornwall_closed_from', 'cornwall_closed_to') as $cornwall_opt) {
    add_action('update_option_' . $cornwall_opt, 'cornwall_refresh_after_shop_setting_change');
    add_action('add_option_' . $cornwall_opt, 'cornwall_refresh_after_shop_setting_change');
}
