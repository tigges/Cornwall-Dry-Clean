<?php
/**
 * Cornwall Dry Cleaners block theme.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('cornwall_review_source_for_path')) {
    require_once __DIR__ . '/inc/review-fallback.php';
}
require_once __DIR__ . '/inc/business.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/inc/settings.php';

function cornwall_theme_setup()
{
    add_editor_style('assets/fonts.css');
    add_editor_style('assets/site.css');
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

    cornwall_migrate_options();
    cornwall_ensure_legal_pages();
    cornwall_ensure_wedding_mockup();
    cornwall_cleanup_default_content();
    cornwall_write_root_files();
    cornwall_reset_customized_templates();
    wp_schedule_single_event(time(), 'cornwall_bank_holidays_refresh');
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

/** 1.5.8 "closed today" checkbox → closure dates (it never switched itself off). */
function cornwall_migrate_options()
{
    if (get_option('cornwall_holiday_closed_today', null) !== null) {
        if (get_option('cornwall_holiday_closed_today') && cornwall_closed_range() === null) {
            update_option('cornwall_closed_from', wp_date('Y-m-d'));
        }
        delete_option('cornwall_holiday_closed_today');
    }
}

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
    $ver = wp_get_theme()->get('Version');
    wp_enqueue_style('cornwall-fonts', get_theme_file_uri('assets/fonts.css'), array(), $ver);
    wp_enqueue_style('cornwall-site', get_theme_file_uri('assets/site.css'), array(), $ver);
    wp_enqueue_script('cornwall-site', get_theme_file_uri('assets/script.js'), array(), $ver, true);
    wp_add_inline_script('cornwall-site', 'window.cornwallShop=' . wp_json_encode(cornwall_shop_data_for_js()) . ';', 'before');
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

function cornwall_body_class($classes)
{
    $classes[] = 'cornwall-theme';
    if (is_page(array('privacy', 'terms'))) {
        $classes[] = 'legal-page';
    }
    return $classes;
}
add_filter('body_class', 'cornwall_body_class');

function cornwall_language_attributes($output)
{
    if (preg_match('/\blang=/', $output)) {
        return preg_replace('/\blang="[^"]*"/', 'lang="en-GB"', $output, 1);
    }
    return trim($output . ' lang="en-GB"');
}
add_filter('language_attributes', 'cornwall_language_attributes');

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

function cornwall_wedding_slug()
{
    return 'wedding-dress-cleaning-alterations';
}

/**
 * Mockup only: a draft page (never public) built from the cornwall/wedding
 * pattern, for review in Pages → Drafts → Preview. Created once; left alone
 * after that so edits and a later Publish are not undone.
 */
function cornwall_ensure_wedding_mockup()
{
    $existing = get_posts(array(
        'name'        => cornwall_wedding_slug(),
        'post_type'   => 'page',
        'post_status' => array('draft', 'pending', 'publish', 'private', 'future', 'trash'),
        'numberposts' => 1,
    ));
    if (! empty($existing)) {
        return;
    }
    $id = wp_insert_post(array(
        'post_title'   => 'Wedding dress cleaning & alterations (mockup)',
        'post_name'    => cornwall_wedding_slug(),
        'post_status'  => 'draft',
        'post_type'    => 'page',
        'post_content' => '<!-- wp:pattern {"slug":"cornwall/wedding"} /-->',
    ));
    if (! is_wp_error($id) && $id) {
        update_post_meta((int) $id, '_wp_page_template', 'landing');
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

function cornwall_indexnow_key()
{
    return 'ffd0a129ae9340fcb4c5fc8e11373b45';
}

function cornwall_soe_indexnow_key()
{
    return '8d0cd194ad314bd3b47ea6d695dd0dd4';
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

function cornwall_serve_root_files()
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

    $keys = array(cornwall_indexnow_key(), cornwall_soe_indexnow_key());
    foreach ($keys as $key) {
        if ($path === '/' . $key . '.txt' || ($key === cornwall_indexnow_key() && preg_match('#^/indexnow\.txt/?$#', $path))) {
            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: public, max-age=86400');
            echo $key;
            exit;
        }
    }
}
add_action('template_redirect', 'cornwall_serve_root_files', 0);

function cornwall_llms_txt()
{
    $services = array();
    foreach (cornwall_services() as $service) {
        if ($service[0] !== 'Express service') {
            $services[] = strtolower(str_replace(' & ', ' and ', $service[0]));
        }
    }
    $lines = array(
        '# ' . cornwall_biz('name'),
        '',
        '> Independent dry cleaners at ' . cornwall_address_line() . '. Since ' . cornwall_biz('founded') . '.',
        '',
        'Phone: ' . cornwall_biz('phone_intl'),
        'Hours: ' . cornwall_hours_summary() . '. Closed on bank holidays.',
    );
    $holiday = cornwall_holiday_notice();
    if ($holiday !== '') {
        $lines[] = 'Special hours notice: ' . $holiday;
    }
    $lines[] = 'Services: ' . implode(', ', $services) . '. Often next day if dropped in by 10am.';
    $lines[] = 'Payment: cash, card and contactless at the counter. No online booking, collection or delivery — drop in at the shop.';
    $lines[] = 'Reviews: ' . cornwall_rating_value() . ' out of 5 from ' . cornwall_review_count_total() . ' Google reviews.';
    $lines[] = 'Website: ' . home_url('/');
    $lines[] = 'Maps: ' . cornwall_biz('maps_url');
    $lines[] = '';
    return implode("\n", $lines);
}
