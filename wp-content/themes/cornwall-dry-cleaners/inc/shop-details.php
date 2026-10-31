<?php
/**
 * Shop details admin page: weekly hours, holidays and special days, the
 * notice, the Google rating, review links, the weekly scan email and Plausible
 * analytics. Saving rewrites llms.txt and the review fallbacks, then clears the
 * page cache so every page shows the new details.
 */

if (! defined('ABSPATH')) {
    exit;
}

/* ---- Analytics (Plausible, cookie-free) ---- */

function cornwall_analytics_settings()
{
    $saved = get_option('cornwall_analytics');
    $on    = is_array($saved) && array_key_exists('on', $saved) ? (bool) $saved['on'] : true;
    $site  = is_array($saved) && isset($saved['site']) ? (string) $saved['site'] : cornwall_biz('domain');
    return array('on' => $on, 'site' => $site);
}

/** A domain ("cornwalldrycleaners.com") or Plausible's site script URL (https://plausible.io/js/pa-….js). */
function cornwall_sanitize_analytics_site($value)
{
    $value = trim((string) $value);
    if (preg_match('#^https://plausible\.io/js/pa-[A-Za-z0-9_-]+\.js$#', $value)) {
        return $value;
    }
    $value = strtolower(preg_replace('#^https?://#i', '', $value));
    $value = rtrim($value, '/');
    return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $value) ? $value : '';
}

/** Visitors on the live site only; never staging, local copies or logged-in staff. */
function cornwall_analytics_tag()
{
    $settings = cornwall_analytics_settings();
    $site     = cornwall_sanitize_analytics_site($settings['site']);
    if (! $settings['on'] || $site === '' || is_admin() || is_user_logged_in() || ! cornwall_is_live() || is_preview()) {
        return;
    }
    if (strpos($site, 'https://') === 0) {
        echo '<script async src="' . esc_url($site) . '"></script>' . "\n";
        echo '<script>window.plausible=window.plausible||function(){(plausible.q=plausible.q||[]).push(arguments)},plausible.init=plausible.init||function(i){plausible.o=i||{}};plausible.init()</script>' . "\n";
        return;
    }
    echo '<script defer data-domain="' . esc_attr($site) . '" src="https://plausible.io/js/script.js"></script>' . "\n";
    echo '<script>window.plausible=window.plausible||function(){(window.plausible.q=window.plausible.q||[]).push(arguments)}</script>' . "\n";
}
add_action('wp_head', 'cornwall_analytics_tag', 5);

/* ---- Moving 1.5.x settings over ---- */

/** "Closed from / to" (1.5.9–1.5.12) becomes a special day; the notice keeps ending on its last day. */
function cornwall_migrate_shop_details()
{
    if (get_option('cornwall_closed_from', null) === null && get_option('cornwall_closed_to', null) === null) {
        return;
    }
    $from = cornwall_valid_date(get_option('cornwall_closed_from', ''));
    $to   = cornwall_valid_date(get_option('cornwall_closed_to', ''));
    if ($from === '') {
        $from = $to;
    }
    if ($from !== '') {
        if ($to === '' || strcmp($to, $from) < 0) {
            $to = $from;
        }
        if (strcmp($to, wp_date('Y-m-d')) >= 0) {
            $days   = (array) get_option('cornwall_special_days', array());
            $days[] = array('from' => $from, 'to' => $to, 'closed' => true, 'opens' => '', 'closes' => '', 'name' => '');
            update_option('cornwall_special_days', array_values($days), false);
        }
        if (cornwall_holiday_notice_raw() !== '' && get_option('cornwall_notice_to', '') === '') {
            update_option('cornwall_notice_to', $to, false);
        }
    }
    delete_option('cornwall_closed_from');
    delete_option('cornwall_closed_to');
}

/* ---- Admin page ---- */

function cornwall_shop_details_url($args = array())
{
    return add_query_arg(array_merge(array('page' => 'cornwall-shop'), $args), admin_url('admin.php'));
}

function cornwall_shop_details_menu()
{
    add_menu_page('Shop details', 'Shop details', 'manage_options', 'cornwall-shop', 'cornwall_shop_details_page', 'dashicons-store', 3);
}
add_action('admin_menu', 'cornwall_shop_details_menu');

function cornwall_shop_details_general_notice()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ($screen && $screen->id === 'options-general') {
        echo '<div class="notice notice-info"><p>Opening hours, holidays, the notice, the Google rating, review links and the weekly scan email are now under <a href="' . esc_url(cornwall_shop_details_url()) . '">Shop details</a>.</p></div>';
    }
}
add_action('admin_notices', 'cornwall_shop_details_general_notice');

function cornwall_shop_time_input($name, $value, $label)
{
    return sprintf('<input type="time" name="%s" value="%s" step="900" aria-label="%s" />', esc_attr($name), esc_attr($value), esc_attr($label));
}

function cornwall_special_day_row($i, $row)
{
    $row = wp_parse_args($row, array('from' => '', 'to' => '', 'closed' => true, 'opens' => '', 'closes' => '', 'name' => ''));
    $n   = 'special[' . $i . ']';
    return '<tr>'
        . '<td><input type="date" name="' . esc_attr($n . '[from]') . '" value="' . esc_attr($row['from']) . '" aria-label="From" /></td>'
        . '<td><input type="date" name="' . esc_attr($n . '[to]') . '" value="' . esc_attr($row['to'] === $row['from'] ? '' : $row['to']) . '" aria-label="Until" /></td>'
        . '<td class="cornwall-center"><input type="checkbox" name="' . esc_attr($n . '[closed]') . '" value="1"' . checked($row['closed'], true, false) . ' aria-label="Closed" /></td>'
        . '<td>' . cornwall_shop_time_input($n . '[opens]', $row['opens'], 'Opens') . '</td>'
        . '<td>' . cornwall_shop_time_input($n . '[closes]', $row['closes'], 'Closes') . '</td>'
        . '<td><input type="text" name="' . esc_attr($n . '[name]') . '" value="' . esc_attr($row['name']) . '" placeholder="e.g. Christmas" maxlength="60" aria-label="Name" /></td>'
        . '<td><button type="button" class="button-link-delete" data-remove-row>Remove</button></td>'
        . '</tr>';
}

function cornwall_shop_details_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }
    $names    = cornwall_day_names();
    $hours    = cornwall_opening_hours();
    $defaults = cornwall_default_hours();
    $special  = cornwall_special_days();
    $window   = cornwall_notice_window();
    $analytics = cornwall_analytics_settings();
    $errors   = get_transient('cornwall_shop_errors_' . get_current_user_id());
    delete_transient('cornwall_shop_errors_' . get_current_user_id());

    $today = wp_date('Y-m-d');
    $bank  = array();
    foreach (cornwall_bank_holidays() as $date) {
        if (strcmp($date, $today) >= 0 && count($bank) < 8) {
            $d = DateTimeImmutable::createFromFormat('!Y-m-d', $date, wp_timezone());
            $bank[] = $d ? $d->format('D j M Y') : $date;
        }
    }
    ?>
<div class="wrap cornwall-shop">
  <h1>Shop details</h1>
  <style>
    .cornwall-shop .card{max-width:980px;padding:8px 20px 16px;margin-top:16px}
    .cornwall-shop .card h2{margin:12px 0 4px}
    .cornwall-shop table.widefat{max-width:940px}
    .cornwall-shop table.widefat td,.cornwall-shop table.widefat th{vertical-align:middle}
    .cornwall-shop .cornwall-center{text-align:center}
    .cornwall-shop [data-special-table] input[type=text]{width:100%;min-width:8rem}
    .cornwall-shop [data-special-table] input[type=date]{max-width:9.5rem}
    .cornwall-shop .cornwall-row{display:flex;gap:24px;flex-wrap:wrap;align-items:flex-end}
    .cornwall-shop .cornwall-row label{display:block;font-weight:600;margin-bottom:4px}
    .cornwall-review-scans{overflow-x:auto;max-width:760px}.cornwall-review-scans table{min-width:520px}.cornwall-review-scans td:nth-child(n+3),.cornwall-review-scans th:nth-child(n+3){text-align:right}
  </style>

  <?php if (isset($_GET['saved'])) : ?>
    <div class="notice notice-success is-dismissible"><p>Saved. Every page now uses these details, and the page cache was cleared.</p></div>
  <?php endif; ?>
  <?php if (is_array($errors)) : foreach ($errors as $message) : ?>
    <div class="notice notice-error is-dismissible"><p><?php echo esc_html($message); ?></p></div>
  <?php endforeach; endif; ?>

  <p>These feed the homepage hours table and “Open now” pill, the footer, the wedding page, Google’s structured data and <code>/llms.txt</code>. Name, address and phone stay fixed in the theme so they always match the Google listing.</p>

  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="cornwall_shop_save" />
    <?php wp_nonce_field('cornwall_shop_save'); ?>

    <div class="card">
      <h2>Opening hours</h2>
      <p class="description">In sentences these read: <strong><?php echo esc_html(cornwall_hours_summary()); ?></strong>.</p>
      <table class="widefat striped">
        <thead><tr><th>Day</th><th>Open</th><th>From</th><th>Until</th></tr></thead>
        <tbody>
        <?php foreach ($hours as $day => $span) : $fallback = $span ? $span : ($defaults[$day] ? $defaults[$day] : array('09:00', '17:00')); ?>
          <tr>
            <th scope="row"><?php echo esc_html($names[$day]); ?></th>
            <td><label><input type="checkbox" name="hours[<?php echo (int) $day; ?>][open]" value="1" <?php checked($span !== null); ?> /> Open</label></td>
            <td><?php echo cornwall_shop_time_input('hours[' . (int) $day . '][from]', $fallback[0], $names[$day] . ' from'); ?></td>
            <td><?php echo cornwall_shop_time_input('hours[' . (int) $day . '][to]', $fallback[1], $names[$day] . ' until'); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <h2>Holidays and special days</h2>
      <p class="description">Christmas, closures and one-off late or short days. Leave “Until” empty for a single day. Untick Closed and add times for different hours. Past dates drop off by themselves.</p>
      <table class="widefat striped" data-special-table>
        <thead><tr><th>From</th><th>Until</th><th>Closed</th><th>Opens</th><th>Closes</th><th>Name (optional)</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($special as $i => $row) { echo cornwall_special_day_row($i, $row); } ?>
        </tbody>
      </table>
      <template data-special-template><?php echo cornwall_special_day_row('__i__', array()); ?></template>
      <p><button type="button" class="button" data-add-row>Add a day</button></p>
      <p class="description">Bank holidays in England and Wales are closed automatically (updated weekly from GOV.UK)<?php echo $bank ? ': ' . esc_html(implode(', ', $bank)) . '.' : '.'; ?> To open on one, add it here with times.</p>
    </div>

    <div class="card">
      <h2>Notice</h2>
      <p class="description">Shown in the Visit section under the hours. On a closed special day without a name, the “Open now” pill shows it too if it starts with “Closed”.</p>
      <p><label for="cornwall_holiday_notice"><strong>Message</strong></label><br>
        <input name="cornwall_holiday_notice" id="cornwall_holiday_notice" type="text" class="large-text" value="<?php echo esc_attr(cornwall_holiday_notice_raw()); ?>" placeholder="e.g. Closed 25–28 Dec for Christmas — back Tuesday 29 Dec at 8am" /></p>
      <div class="cornwall-row">
        <p><label for="cornwall_notice_from">Show from</label><input type="date" id="cornwall_notice_from" name="cornwall_notice_from" value="<?php echo esc_attr($window['from']); ?>" /></p>
        <p><label for="cornwall_notice_to">Show until</label><input type="date" id="cornwall_notice_to" name="cornwall_notice_to" value="<?php echo esc_attr($window['to']); ?>" /></p>
      </div>
      <p class="description">Leave both dates empty to show it until you clear the message.</p>
    </div>

    <div class="card">
      <h2>Google rating</h2>
      <p class="description">Shown on the homepage rating card and Reviews heading, in Google structured data and <code>/llms.txt</code>. Copy the figures from the Google Business Profile.</p>
      <div class="cornwall-row">
        <p><label for="cornwall_rating_value">Rating (out of 5)</label><input name="cornwall_rating_value" id="cornwall_rating_value" type="text" class="small-text" value="<?php echo esc_attr(cornwall_rating_value()); ?>" /></p>
        <p><label for="cornwall_review_count_total">Number of reviews</label><input name="cornwall_review_count_total" id="cornwall_review_count_total" type="number" min="1" step="1" class="small-text" value="<?php echo esc_attr(cornwall_review_count_total()); ?>" /></p>
      </div>
    </div>

    <div class="card">
      <h2>Review links</h2>
      <div><label for="cornwall_review_url"><strong>Review link</strong></label><br><?php cornwall_review_url_field(); ?></div>
      <div><label for="cornwall_scan_email"><strong>Weekly scan email</strong></label><br><?php cornwall_scan_email_field(); ?></div>
    </div>

    <div class="card">
      <h2>Visitor analytics</h2>
      <p class="description">Plausible: cookie-free, no personal data, no cookie banner needed. Counts visits, pages, where visitors came from, and taps on Call, Directions, the map and “Write a review”. Loads only on <?php echo esc_html(cornwall_biz('domain')); ?> for visitors who are not logged in to WordPress.</p>
      <p><label><input type="checkbox" name="analytics_on" value="1" <?php checked($analytics['on']); ?> /> Use Plausible</label></p>
      <p><label for="cornwall_analytics_site"><strong>Plausible site</strong></label><br>
        <input name="analytics_site" id="cornwall_analytics_site" type="text" class="regular-text code" value="<?php echo esc_attr($analytics['site']); ?>" placeholder="cornwalldrycleaners.com" /></p>
      <p class="description">The domain as added in Plausible, or the script address Plausible gives you (<code>https://plausible.io/js/pa-….js</code>). Dashboard: <a href="https://plausible.io/<?php echo esc_attr(cornwall_biz('domain')); ?>" target="_blank" rel="noreferrer">plausible.io/<?php echo esc_html(cornwall_biz('domain')); ?></a>. Add goals named Call, Directions, Map, Write a review and Email to see the taps.</p>
    </div>

    <?php submit_button('Save shop details'); ?>
  </form>

  <div class="card">
    <h2>Review scans</h2>
    <?php cornwall_review_hits_field(); ?>
  </div>
</div>
<script>
(function () {
  var table = document.querySelector('[data-special-table] tbody');
  var template = document.querySelector('[data-special-template]');
  var next = table.querySelectorAll('tr').length;
  document.querySelector('[data-add-row]').addEventListener('click', function () {
    table.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__i__/g, String(next++)));
    table.lastElementChild.querySelector('input[type=date]').focus();
  });
  table.addEventListener('click', function (event) {
    if (event.target.matches('[data-remove-row]')) event.target.closest('tr').remove();
  });
  if (!table.querySelector('tr')) document.querySelector('[data-add-row]').click();
})();
</script>
    <?php
}

/* ---- Saving ---- */

function cornwall_shop_details_save()
{
    if (! current_user_can('manage_options')) {
        wp_die('Not allowed.');
    }
    check_admin_referer('cornwall_shop_save');
    $post   = wp_unslash($_POST);
    $errors = array();
    $names  = cornwall_day_names();

    $current = cornwall_opening_hours();
    $hours   = array();
    foreach (cornwall_default_hours() as $day => $unused) {
        $in = isset($post['hours'][$day]) && is_array($post['hours'][$day]) ? $post['hours'][$day] : array();
        if (empty($in['open'])) {
            $hours[$day] = null;
            continue;
        }
        $from = cornwall_valid_time(isset($in['from']) ? $in['from'] : '');
        $to   = cornwall_valid_time(isset($in['to']) ? $in['to'] : '');
        if ($from === '' || $to === '' || strcmp($to, $from) <= 0) {
            $errors[] = $names[$day] . ': the closing time must be after the opening time. The previous hours were kept.';
            $hours[$day] = $current[$day];
            continue;
        }
        $hours[$day] = array($from, $to);
    }
    update_option('cornwall_hours', $hours, false);

    $today   = wp_date('Y-m-d');
    $special = array();
    foreach ((isset($post['special']) && is_array($post['special'])) ? $post['special'] : array() as $row) {
        if (! is_array($row)) {
            continue;
        }
        $from = cornwall_valid_date(isset($row['from']) ? $row['from'] : '');
        $to   = cornwall_valid_date(isset($row['to']) ? $row['to'] : '');
        $name = sanitize_text_field(isset($row['name']) ? (string) $row['name'] : '');
        if ($from === '' && $to === '' && $name === '') {
            continue;
        }
        if ($from === '') {
            $errors[] = 'A special day without a “From” date was left out' . ($name !== '' ? ' (' . $name . ')' : '') . '.';
            continue;
        }
        if ($to === '' || strcmp($to, $from) < 0) {
            $to = $from;
        }
        if (strcmp($to, $today) < 0) {
            continue;
        }
        $closed = ! empty($row['closed']);
        $opens  = cornwall_valid_time(isset($row['opens']) ? $row['opens'] : '');
        $closes = cornwall_valid_time(isset($row['closes']) ? $row['closes'] : '');
        if (! $closed && ($opens === '' || $closes === '' || strcmp($closes, $opens) <= 0)) {
            $errors[] = 'Special day from ' . $from . ': add an opening and a later closing time, or tick Closed. It was saved as closed.';
            $closed = true;
        }
        $special[] = array(
            'from'   => $from,
            'to'     => $to,
            'closed' => $closed,
            'opens'  => $closed ? '' : $opens,
            'closes' => $closed ? '' : $closes,
            'name'   => mb_substr($name, 0, 60),
        );
    }
    usort($special, function ($a, $b) {
        return strcmp($a['from'], $b['from']);
    });
    update_option('cornwall_special_days', $special, false);

    update_option('cornwall_holiday_notice', sanitize_text_field(isset($post['cornwall_holiday_notice']) ? (string) $post['cornwall_holiday_notice'] : ''), false);
    $notice_from = cornwall_valid_date(isset($post['cornwall_notice_from']) ? $post['cornwall_notice_from'] : '');
    $notice_to   = cornwall_valid_date(isset($post['cornwall_notice_to']) ? $post['cornwall_notice_to'] : '');
    if ($notice_from !== '' && $notice_to !== '' && strcmp($notice_to, $notice_from) < 0) {
        $errors[] = 'The notice “Show until” date was before “Show from”, so it was set to the same day.';
        $notice_to = $notice_from;
    }
    update_option('cornwall_notice_from', $notice_from, false);
    update_option('cornwall_notice_to', $notice_to, false);

    update_option('cornwall_rating_value', cornwall_sanitize_rating_value(isset($post['cornwall_rating_value']) ? $post['cornwall_rating_value'] : ''), false);
    update_option('cornwall_review_count_total', cornwall_sanitize_review_count_total(isset($post['cornwall_review_count_total']) ? $post['cornwall_review_count_total'] : 0), false);
    update_option('cornwall_review_url', cornwall_review_sanitize_url(isset($post['cornwall_review_url']) ? $post['cornwall_review_url'] : ''));
    update_option('cornwall_scan_email', cornwall_sanitize_scan_email(isset($post['cornwall_scan_email']) ? $post['cornwall_scan_email'] : ''), false);

    $site = cornwall_sanitize_analytics_site(isset($post['analytics_site']) ? $post['analytics_site'] : '');
    if ($site === '' && trim((string) (isset($post['analytics_site']) ? $post['analytics_site'] : '')) !== '') {
        $errors[] = 'The Plausible site must be a domain (cornwalldrycleaners.com) or a https://plausible.io/js/pa-….js address. The previous value was kept.';
        $site = cornwall_analytics_settings()['site'];
    }
    update_option('cornwall_analytics', array('on' => ! empty($post['analytics_on']), 'site' => $site), false);

    foreach (get_settings_errors() as $error) {
        $errors[] = wp_strip_all_tags((string) $error['message']);
    }
    if ($errors) {
        set_transient('cornwall_shop_errors_' . get_current_user_id(), array_values(array_unique($errors)), 300);
    }

    cornwall_write_root_files();
    cornwall_purge_page_cache();
    wp_safe_redirect(cornwall_shop_details_url(array('saved' => 1)));
    exit;
}
add_action('admin_post_cornwall_shop_save', 'cornwall_shop_details_save');
