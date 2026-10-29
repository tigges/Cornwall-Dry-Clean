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
    update_option('date_format', 'j F Y');
    update_option('time_format', 'g:ia');

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
        array('cornwall-fonts'),
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

function cornwall_maps_url()
{
    return 'https://maps.app.goo.gl/cw7sznF2TJq2SkL69';
}

function cornwall_head_meta()
{
    $icon   = esc_url(get_theme_file_uri('assets/favicon.svg'));
    $png    = esc_url(get_theme_file_uri('assets/favicon.png'));
    $touch  = esc_url(get_theme_file_uri('assets/apple-touch-icon.png'));
    $hero   = esc_url(get_theme_file_uri('assets/shop-front.webp'));
    $share  = esc_url(get_theme_file_uri('assets/og-share.jpg'));
    $url    = esc_url(home_url('/'));
    $title  = 'Cornwall Dry Cleaners – Ealing';
    $desc   = cornwall_site_description();

    echo '<link rel="icon" href="' . $icon . '" type="image/svg+xml" />' . "\n";
    echo '<link rel="icon" href="' . $png . '" sizes="32x32" type="image/png" />' . "\n";
    echo '<link rel="apple-touch-icon" href="' . $touch . '" />' . "\n";
    echo '<meta name="theme-color" content="#003868" />' . "\n";
    echo '<link rel="preload" as="image" href="' . $hero . '" type="image/webp" fetchpriority="high" />' . "\n";

    if (is_front_page()) {
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
    return $classes;
}
add_filter('body_class', 'cornwall_body_class');

function cornwall_document_title($parts)
{
    if (is_front_page()) {
        $parts['title'] = 'Cornwall Dry Cleaners';
        $parts['tagline'] = 'Ealing';
        unset($parts['site']);
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
