<?php
/**
 * Cornwall Dry Cleaners block theme.
 */

if (! defined('ABSPATH')) {
    exit;
}

function cornwall_theme_setup()
{
    add_editor_style('assets/mockups.css');
    add_editor_style('assets/wp.css');
    add_editor_style('assets/ux.css');
    add_theme_support('editor-styles');
    add_theme_support('wp-block-styles');
    add_theme_support('title-tag');
}
add_action('after_setup_theme', 'cornwall_theme_setup');

/**
 * Make this theme the site homepage when it is activated.
 * front-page.html replaces the default Sample Page / blog index.
 */
function cornwall_replace_homepage()
{
    update_option('blogname', 'Cornwall Dry Cleaners');
    update_option('blogdescription', 'Ealing');
    update_option('timezone_string', 'Europe/London');
    update_option('date_format', 'd/m/Y');
    update_option('time_format', 'g:i a');
    update_option('start_of_week', '1');

    $home = get_page_by_path('home');
    if ($home instanceof WP_Post) {
        $home_id = (int) $home->ID;
        wp_update_post(array(
            'ID'           => $home_id,
            'post_status'  => 'publish',
            'post_content' => '<!-- wp:pattern {"slug":"cornwall/homepage"} /-->',
        ));
    } else {
        $created = wp_insert_post(array(
            'post_title'   => 'Home',
            'post_name'    => 'home',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '<!-- wp:pattern {"slug":"cornwall/homepage"} /-->',
        ));
        $home_id = is_wp_error($created) ? 0 : (int) $created;
    }

    if ($home_id > 0) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_id);
    }

    cornwall_ensure_legal_pages();
    cornwall_cleanup_default_content();
    cornwall_write_root_files();
    cornwall_reset_customized_templates();
    flush_rewrite_rules();
    cornwall_purge_page_cache();
}
add_action('after_switch_theme', 'cornwall_replace_homepage');

/**
 * A Cloudways git pull does not switch the theme, so Site Editor copies and
 * Breeze HTML would otherwise keep the old homepage. Refresh when Version
 * in style.css changes.
 */
function cornwall_maybe_refresh_homepage()
{
    $version = wp_get_theme()->get('Version');
    if (get_option('cornwall_homepage_ver') === $version) {
        return;
    }
    cornwall_replace_homepage();
    update_option('cornwall_homepage_ver', $version);
}
add_action('init', 'cornwall_maybe_refresh_homepage', 20);

function cornwall_reset_customized_templates()
{
    $query = new WP_Query(array(
        'post_type'      => array('wp_template', 'wp_template_part'),
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'no_found_rows'  => true,
        'tax_query'      => array(
            array(
                'taxonomy' => 'wp_theme',
                'field'    => 'name',
                'terms'    => get_stylesheet(),
            ),
        ),
    ));

    foreach ($query->posts as $post) {
        wp_delete_post((int) $post->ID, true);
    }
}

function cornwall_purge_page_cache()
{
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    do_action('breeze_clear_all_cache');
}

function cornwall_enqueue_assets()
{
    wp_enqueue_style(
        'cornwall-fonts',
        'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Great+Vibes&display=swap',
        array(),
        null
    );
    wp_enqueue_style(
        'cornwall-mockups',
        get_theme_file_uri('assets/mockups.css'),
        array(),
        wp_get_theme()->get('Version')
    );
    wp_enqueue_style(
        'cornwall-wp',
        get_theme_file_uri('assets/wp.css'),
        array('cornwall-mockups'),
        wp_get_theme()->get('Version')
    );
    wp_enqueue_style(
        'cornwall-ux',
        get_theme_file_uri('assets/ux.css'),
        array('cornwall-wp'),
        wp_get_theme()->get('Version')
    );
    wp_enqueue_script(
        'cornwall-site',
        get_theme_file_uri('assets/script.js'),
        array(),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('wp_enqueue_scripts', 'cornwall_enqueue_assets');

function cornwall_async_font_stylesheet($html, $handle, $href)
{
    if ($handle !== 'cornwall-fonts' || is_admin()) {
        return $html;
    }
    $esc = esc_url($href);
    return '<link rel="preload" as="style" href="' . $esc . '" onload="this.onload=null;this.rel=\'stylesheet\'" />' . "\n"
        . '<noscript><link rel="stylesheet" href="' . $esc . '" /></noscript>' . "\n";
}
add_filter('style_loader_tag', 'cornwall_async_font_stylesheet', 10, 3);

function cornwall_editor_assets()
{
    wp_enqueue_style(
        'cornwall-fonts',
        'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Great+Vibes&display=swap',
        array(),
        null
    );
}
add_action('enqueue_block_editor_assets', 'cornwall_editor_assets');

function cornwall_site_description()
{
    return 'Independent dry cleaners on Haven Lane, Ealing. Suits, coats, laundry and repairs — often next day if dropped in by 10am. Call 020 8991 9413.';
}

function cornwall_indexnow_key()
{
    return 'ffd0a129ae9340fcb4c5fc8e11373b45';
}

function cornwall_soe_indexnow_key()
{
    return '8d0cd194ad314bd3b47ea6d695dd0dd4';
}

function cornwall_maps_url()
{
    return 'https://maps.app.goo.gl/cw7sznF2TJq2SkL69';
}

function cornwall_head_meta()
{
    $icon     = esc_url(get_theme_file_uri('assets/favicon.svg'));
    $png      = esc_url(get_theme_file_uri('assets/favicon.png'));
    $touch    = esc_url(get_theme_file_uri('assets/apple-touch-icon.png'));
    $hero     = esc_url(get_theme_file_uri('assets/shop-front.webp'));
    $hero_640 = esc_url(get_theme_file_uri('assets/shop-front-640.webp'));
    $hero_960 = esc_url(get_theme_file_uri('assets/shop-front-960.webp'));
    $share    = esc_url(get_theme_file_uri('assets/og-share.jpg'));
    $url      = esc_url(home_url('/'));
    $title    = 'Cornwall Dry Cleaners – Ealing';
    $desc     = cornwall_site_description();

    echo '<link rel="icon" href="' . $icon . '" type="image/svg+xml" />' . "\n";
    echo '<link rel="icon" href="' . $png . '" sizes="32x32" type="image/png" />' . "\n";
    echo '<link rel="apple-touch-icon" href="' . $touch . '" />' . "\n";
    echo '<meta name="theme-color" content="#003868" />' . "\n";

    if (is_front_page()) {
        echo '<link rel="preload" as="image" href="' . $hero . '" imagesrcset="' . $hero_640 . ' 640w, ' . $hero_960 . ' 960w, ' . $hero . ' 1600w" imagesizes="(max-width: 720px) calc(100vw - 32px), (max-width: 1180px) calc(100vw - 48px), 1120px" type="image/webp" fetchpriority="high" />' . "\n";
        echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
        echo '<meta property="og:type" content="website" />' . "\n";
        echo '<meta property="og:locale" content="en_GB" />' . "\n";
        echo '<meta property="og:site_name" content="Cornwall Dry Cleaners" />' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($desc) . '" />' . "\n";
        echo '<meta property="og:url" content="' . $url . '" />' . "\n";
        echo '<meta property="og:image" content="' . $share . '" />' . "\n";
        echo '<meta property="og:image:width" content="1200" />' . "\n";
        echo '<meta property="og:image:height" content="630" />' . "\n";
        echo '<meta property="og:image:alt" content="Cornwall Dry Cleaners on Haven Lane, Ealing" />' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($desc) . '" />' . "\n";
        echo '<meta name="twitter:image" content="' . $share . '" />' . "\n";
    } elseif (is_page('privacy')) {
        $p_desc = 'How Cornwall Dry Cleaners on Haven Lane, Ealing uses the information you give us in the shop, by phone or on this website.';
        echo '<meta name="description" content="' . esc_attr($p_desc) . '" />' . "\n";
        echo '<meta property="og:type" content="website" />' . "\n";
        echo '<meta property="og:locale" content="en_GB" />' . "\n";
        echo '<meta property="og:site_name" content="Cornwall Dry Cleaners" />' . "\n";
        echo '<meta property="og:title" content="Privacy – Cornwall Dry Cleaners" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($p_desc) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url(home_url('/privacy/')) . '" />' . "\n";
        echo '<meta property="og:image" content="' . $share . '" />' . "\n";
        echo '<meta property="og:image:width" content="1200" />' . "\n";
        echo '<meta property="og:image:height" content="630" />' . "\n";
        echo '<meta property="og:image:alt" content="Cornwall Dry Cleaners on Haven Lane, Ealing" />' . "\n";
    } elseif (is_page('terms')) {
        $t_desc = 'Simple terms for using the Cornwall Dry Cleaners website and dropping garments in at 23 Haven Lane, Ealing.';
        echo '<meta name="description" content="' . esc_attr($t_desc) . '" />' . "\n";
        echo '<meta property="og:type" content="website" />' . "\n";
        echo '<meta property="og:locale" content="en_GB" />' . "\n";
        echo '<meta property="og:site_name" content="Cornwall Dry Cleaners" />' . "\n";
        echo '<meta property="og:title" content="Terms – Cornwall Dry Cleaners" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($t_desc) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url(home_url('/terms/')) . '" />' . "\n";
        echo '<meta property="og:image" content="' . $share . '" />' . "\n";
        echo '<meta property="og:image:width" content="1200" />' . "\n";
        echo '<meta property="og:image:height" content="630" />' . "\n";
        echo '<meta property="og:image:alt" content="Cornwall Dry Cleaners on Haven Lane, Ealing" />' . "\n";
    }
}
add_action('wp_head', 'cornwall_head_meta', 1);

function cornwall_json_ld()
{
    if (! is_front_page()) {
        return;
    }

    $home = home_url('/');
    $data = array(
        '@context'  => 'https://schema.org',
        '@type'     => array('DryCleaningOrLaundry', 'LocalBusiness'),
        '@id'       => $home . '#business',
        'name'      => 'Cornwall Dry Cleaners',
        'description' => cornwall_site_description(),
        'image'     => array(
            get_theme_file_uri('assets/shop-front.jpg'),
            get_theme_file_uri('assets/og-share.jpg'),
        ),
        'url'       => $home,
        'telephone' => '+442089919413',
        'priceRange' => '£',
        'address'   => array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => '23 Haven Lane',
            'addressLocality' => 'Ealing',
            'addressRegion'   => 'London',
            'postalCode'      => 'W5 2HZ',
            'addressCountry'  => 'GB',
        ),
        'geo' => array(
            '@type'     => 'GeoCoordinates',
            'latitude'  => 51.51661,
            'longitude' => -0.300303,
        ),
        'hasMap'    => cornwall_maps_url(),
        'sameAs'    => array(cornwall_maps_url()),
        'openingHoursSpecification' => array(
            array(
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
                'opens'     => '08:00',
                'closes'    => '18:00',
            ),
            array(
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => 'Saturday',
                'opens'     => '09:00',
                'closes'    => '17:00',
            ),
        ),
        'aggregateRating' => array(
            '@type'       => 'AggregateRating',
            'ratingValue' => '4.9',
            'reviewCount' => '45',
            'bestRating'  => '5',
        ),
    );
    echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";

    $faqs = array(
        array(
            'How long does it take?',
            'Often next day if dropped in by 10am. Repairs and wedding pieces can take longer — we will say when you drop off.',
        ),
        array(
            'Collect or deliver?',
            'No. Drop in on Haven Lane.',
        ),
        array(
            'Cards or cash?',
            'Both. Contactless at the counter.',
        ),
        array(
            'Bank holidays?',
            'Usually closed. Call the day before if you need to be sure.',
        ),
        array(
            'Not sure we can take it?',
            'Bring it in. We inspect first and say honestly what will come out.',
        ),
    );
    $entities = array();
    foreach ($faqs as $faq) {
        $entities[] = array(
            '@type' => 'Question',
            'name'  => $faq[0],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => $faq[1],
            ),
        );
    }
    $faq_data = array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $entities,
    );
    echo '<script type="application/ld+json">' . wp_json_encode($faq_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head', 'cornwall_json_ld', 20);

function cornwall_body_class($classes)
{
    $classes[] = 'cornwall-theme';
    if (is_page(array('privacy', 'terms'))) {
        $classes[] = 'legal-page';
    }
    return $classes;
}
add_filter('body_class', 'cornwall_body_class');

function cornwall_document_title($parts)
{
    if (is_front_page()) {
        $parts['title'] = 'Cornwall Dry Cleaners';
        $parts['tagline'] = 'Ealing';
        unset($parts['site']);
    } elseif (is_page('privacy') || is_page('terms')) {
        unset($parts['tagline']);
    }
    return $parts;
}
add_filter('document_title_parts', 'cornwall_document_title');

function cornwall_language_attributes($output)
{
    if (preg_match('/\blang=/', $output)) {
        return preg_replace('/\blang="[^"]*"/', 'lang="en-GB"', $output, 1);
    }
    return trim($output . ' lang="en-GB"');
}
add_filter('language_attributes', 'cornwall_language_attributes');

function cornwall_font_resource_hints($urls, $relation_type)
{
    if ($relation_type === 'preconnect') {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = array(
            'href'        => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        );
    }
    return $urls;
}
add_filter('wp_resource_hints', 'cornwall_font_resource_hints', 10, 2);

function cornwall_robots($robots)
{
    if (is_front_page()) {
        return $robots;
    }
    if (is_singular('post') || is_author() || is_category() || is_tag() || is_date() || is_search() || is_home()) {
        $robots['noindex'] = true;
        unset($robots['index']);
    }
    return $robots;
}
add_filter('wp_robots', 'cornwall_robots');

function cornwall_sitemap_providers($provider, $name)
{
    if ($name === 'users') {
        return false;
    }
    return $provider;
}
add_filter('wp_sitemaps_add_provider', 'cornwall_sitemap_providers', 10, 2);

function cornwall_sitemap_taxonomies($taxonomies)
{
    unset($taxonomies['category'], $taxonomies['post_tag']);
    return $taxonomies;
}
add_filter('wp_sitemaps_taxonomies', 'cornwall_sitemap_taxonomies');

function cornwall_sitemap_post_types($post_types)
{
    unset($post_types['post']);
    return $post_types;
}
add_filter('wp_sitemaps_post_types', 'cornwall_sitemap_post_types');

function cornwall_ensure_legal_pages()
{
    $pages = array(
        'privacy' => array('Privacy', 'cornwall/privacy'),
        'terms'   => array('Terms', 'cornwall/terms'),
    );
    foreach ($pages as $slug => $info) {
        $content = '<!-- wp:pattern {"slug":"' . $info[1] . '"} /-->';
        $page = get_page_by_path($slug);
        if ($page instanceof WP_Post) {
            wp_update_post(array(
                'ID'           => (int) $page->ID,
                'post_status'  => 'publish',
                'post_title'   => $info[0],
                'post_name'    => $slug,
                'post_content' => $content,
            ));
            continue;
        }
        wp_insert_post(array(
            'post_title'   => $info[0],
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => $content,
        ));
    }
}

function cornwall_cleanup_default_content()
{
    $posts = get_posts(array(
        'name'           => 'hello-world',
        'post_type'      => 'post',
        'post_status'    => array('publish', 'draft', 'pending', 'future', 'private'),
        'posts_per_page' => 5,
    ));
    foreach ($posts as $post) {
        wp_trash_post((int) $post->ID);
    }
}

function cornwall_write_root_files()
{
    if (! defined('ABSPATH') || ! is_dir(ABSPATH) || ! is_writable(ABSPATH)) {
        return;
    }
    file_put_contents(ABSPATH . 'llms.txt', cornwall_llms_txt());
    file_put_contents(ABSPATH . 'indexnow.txt', cornwall_indexnow_key());
    file_put_contents(ABSPATH . cornwall_indexnow_key() . '.txt', cornwall_indexnow_key());
    file_put_contents(ABSPATH . cornwall_soe_indexnow_key() . '.txt', cornwall_soe_indexnow_key());
    cornwall_write_review_fallbacks();
}

function cornwall_redirect_leftover_urls()
{
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
    $path = strtok($uri, '?');

    if (preg_match('#^/llms\.txt/?$#', $path)) {
        header('Content-Type: text/plain; charset=utf-8');
        header('X-Robots-Tag: noindex');
        echo cornwall_llms_txt();
        exit;
    }

    if (preg_match('#^/indexnow\.txt/?$#', $path) || $path === '/' . cornwall_indexnow_key() . '.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=86400');
        echo cornwall_indexnow_key();
        exit;
    }

    if ($path === '/' . cornwall_soe_indexnow_key() . '.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=86400');
        echo cornwall_soe_indexnow_key();
        exit;
    }

    if (is_singular('post') && get_query_var('name') === 'hello-world') {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}
add_action('template_redirect', 'cornwall_redirect_leftover_urls', 1);

function cornwall_llms_txt()
{
    return implode("\n", array(
        '# Cornwall Dry Cleaners',
        '',
        '> Independent dry cleaners at 23 Haven Lane, Ealing, London W5 2HZ.',
        '',
        'Phone: +44 20 8991 9413',
        'Hours: Monday–Friday 8am–6pm, Saturday 9am–5pm, Sunday closed. Closed on bank holidays.',
        'Services: dry cleaning, stain removal, laundry, repairs and alterations, formal and luxury pieces. Often next day if dropped in by 10am.',
        'Reviews: 4.9 out of 5 from 45 Google reviews.',
        'Website: https://cornwalldrycleaners.com/',
        'Maps: https://maps.app.goo.gl/cw7sznF2TJq2SkL69',
        '',
    ));
}

/**
 * Short review links for the QR code and NFC card at the till point and the
 * receipt: /review, /review/qr, /review/nfc, /review/receipt (plus /qr and
 * /nfc shorthand) all send people to the Google review form. A 302 (never
 * cached, never indexed) so the destination can be changed later from
 * Settings → General without reprinting. Each source keeps a daily count.
 * No cookies, IPs or other personal data.
 *
 * Three-layer safety net (cornwall_write_review_fallbacks):
 * 1. Theme template_redirect at priority 0 (normal path + counter).
 * 2. wp-content/mu-plugins/cornwall-review-fallback.php (survives theme
 *    rollbacks and plugin/theme fatal errors via wp_php_error_message,
 *    wp_die_handler, template_redirect 999 and register_shutdown_function).
 * 3. wp-content/db-error.php + .htaccess ErrorDocument (survives DB outages
 *    and server 500/502/503/404 responses on /review*, /qr and /nfc).
 */
if (! defined('CORNWALL_REVIEW_URL')) {
    define('CORNWALL_REVIEW_URL', 'https://search.google.com/local/writereview?placeid=ChIJ81BJSewTdkgR1Ru6ekdBmKw');
}

function cornwall_review_sources()
{
    return array(
        'qr'      => 'QR code at the till',
        'nfc'     => 'NFC card at the till',
        'receipt' => 'QR code on receipt',
        'typed'   => 'Typed in (receipt text, word of mouth)',
    );
}

function cornwall_review_url()
{
    $url = (string) get_option('cornwall_review_url', '');
    return $url !== '' ? $url : CORNWALL_REVIEW_URL;
}

function cornwall_review_source_for_path($path)
{
    $path = strtolower(trim((string) $path, '/'));
    if ($path === 'review') {
        return 'typed';
    }
    if ($path === 'qr') {
        return 'qr';
    }
    if ($path === 'nfc') {
        return 'nfc';
    }
    if (strpos($path, 'review/') !== 0) {
        return null;
    }
    $tag = substr($path, strlen('review/'));
    $sources = cornwall_review_sources();
    return isset($sources[$tag]) ? $tag : 'typed';
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
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
    $source = cornwall_review_source_for_path((string) strtok($uri, '?'));
    if ($source === null) {
        return;
    }

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
        if (is_dir($mu_dir) && is_writable($mu_dir)) {
            $mu_code = <<<PHP
<?php
/**
 * Plugin Name: Cornwall Review Link Fallback
 * Description: Keeps /review, /review/qr, /review/nfc, /review/receipt, /qr and /nfc redirecting to the Google review form even if the theme is rolled back or a plugin triggers a fatal error.
 * Version: 1.5.6
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! defined('CORNWALL_REVIEW_FALLBACK_URL')) {
    define('CORNWALL_REVIEW_FALLBACK_URL', {$exported_url});
}

function cornwall_review_fallback_source()
{
    if (function_exists('is_admin') && is_admin()) {
        return null;
    }
    \$uri = isset(\$_SERVER['REQUEST_URI']) ? (string) \$_SERVER['REQUEST_URI'] : '';
    \$path = strtolower(trim((string) strtok(\$uri, '?'), '/'));
    if (\$path === 'review') {
        return 'typed';
    }
    if (\$path === 'qr' || \$path === 'review/qr') {
        return 'qr';
    }
    if (\$path === 'nfc' || \$path === 'review/nfc') {
        return 'nfc';
    }
    if (\$path === 'review/receipt') {
        return 'receipt';
    }
    if (strpos(\$path, 'review/') === 0) {
        return 'typed';
    }
    return null;
}

function cornwall_review_fallback_target()
{
    if (function_exists('get_option')) {
        try {
            \$opt = (string) get_option('cornwall_review_url', '');
            if (\$opt !== '' && strpos(\$opt, 'https://') === 0) {
                return \$opt;
            }
        } catch (\\Throwable \$e) {
        }
    }
    return CORNWALL_REVIEW_FALLBACK_URL;
}

function cornwall_review_fallback_send(\$reason = 'Cornwall review fallback')
{
    if (! empty(\$GLOBALS['cornwall_review_redirected'])) {
        return;
    }
    \$GLOBALS['cornwall_review_redirected'] = true;
    \$target = cornwall_review_fallback_target();
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    if (! headers_sent()) {
        header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
        header('X-Robots-Tag: noindex, nofollow');
        header('X-Redirect-By: ' . \$reason);
        header('Location: ' . \$target, true, 302);
    } else {
        \$esc = htmlspecialchars(\$target, ENT_QUOTES, 'UTF-8');
        echo '<!doctype html><meta http-equiv="refresh" content="0;url=' . \$esc . '"><script>location.replace(' . json_encode(\$target) . ');</script>';
    }
    exit;
}

if (cornwall_review_fallback_source() !== null) {
    @ob_start();

    add_filter('wp_php_error_message', function (\$message) {
        cornwall_review_fallback_send('Cornwall review fallback (php-error)');
        return \$message;
    }, 1);

    add_filter('wp_die_handler', function () {
        return function () {
            cornwall_review_fallback_send('Cornwall review fallback (wp-die)');
        };
    }, 1);

    add_action('template_redirect', function () {
        if (! empty(\$GLOBALS['cornwall_review_redirected'])) {
            return;
        }
        \$src = cornwall_review_fallback_source();
        if (\$src !== null && function_exists('cornwall_review_should_count') && function_exists('cornwall_review_count')) {
            try {
                if (cornwall_review_should_count()) {
                    cornwall_review_count(\$src);
                }
            } catch (\\Throwable \$e) {
            }
        }
        cornwall_review_fallback_send('Cornwall review fallback (template)');
    }, 999);

    register_shutdown_function(function () {
        if (empty(\$GLOBALS['cornwall_review_redirected'])) {
            cornwall_review_fallback_send('Cornwall review fallback (shutdown)');
        }
    });
}
PHP;
            @file_put_contents($mu_dir . '/cornwall-review-fallback.php', $mu_code . "\n");
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
\$uri = isset(\$_SERVER['REQUEST_URI']) ? (string) \$_SERVER['REQUEST_URI'] : '';
\$path = strtolower(trim((string) strtok(\$uri, '?'), '/'));
if (\$path === 'review' || \$path === 'qr' || \$path === 'nfc' || strpos(\$path, 'review/') === 0) {
    \$GLOBALS['cornwall_review_redirected'] = true;
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    if (! headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
        header('X-Robots-Tag: noindex, nofollow');
        header('X-Redirect-By: Cornwall review fallback (db-error)');
        header('Location: ' . {$exported_url}, true, 302);
    }
    exit;
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
            @file_put_contents($db_err_file, $db_code . "\n");
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

function cornwall_review_settings()
{
    register_setting('general', 'cornwall_review_url', array(
        'type'              => 'string',
        'sanitize_callback' => 'cornwall_review_sanitize_url',
        'default'           => '',
    ));
    add_settings_field('cornwall_review_url', 'Review link', 'cornwall_review_url_field', 'general', 'default', array('label_for' => 'cornwall_review_url'));
    add_settings_field('cornwall_review_hits', 'Review scans', 'cornwall_review_hits_field', 'general');
}
add_action('admin_init', 'cornwall_review_settings');

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

function cornwall_review_hits_field()
{
    $hits = get_option('cornwall_review_hits');
    $days = (is_array($hits) && isset($hits['days']) && is_array($hits['days'])) ? $hits['days'] : array();
    $today = new DateTimeImmutable('now', wp_timezone());
    $windows = array(
        'today' => $today->format('Y-m-d'),
        'week'  => $today->modify('-6 days')->format('Y-m-d'),
        'month' => $today->modify('-29 days')->format('Y-m-d'),
    );
    $sources = cornwall_review_sources();
    $rows = array();
    $total = array('today' => 0, 'week' => 0, 'month' => 0, 'all' => 0);
    foreach ($sources as $key => $label) {
        $rows[$key] = array('today' => 0, 'week' => 0, 'month' => 0, 'all' => 0);
    }
    foreach ($days as $day => $counts) {
        foreach ((array) $counts as $key => $n) {
            if (! isset($rows[$key])) {
                continue;
            }
            $n = (int) $n;
            $rows[$key]['all'] += $n;
            $total['all'] += $n;
            foreach ($windows as $window => $from) {
                if (strcmp((string) $day, $from) >= 0) {
                    $rows[$key][$window] += $n;
                    $total[$window] += $n;
                }
            }
        }
    }

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
        $since = DateTimeImmutable::createFromFormat('Y-m-d', (string) $hits['since'], wp_timezone());
        if ($since) {
            $notes[] = 'Counting since ' . wp_date(get_option('date_format'), $since->getTimestamp());
        }
    }
    $notes[] = 'Link previews, bots and visits while logged in to WordPress are not counted.';
    echo '<p class="description">' . esc_html(implode(' · ', $notes)) . '<br>Counts only. No IP addresses, cookies or other personal data are stored.</p>';
}
