<?php
/**
 * <head> output, structured data, security headers and the pages this
 * one-page shop site does not use.
 */

if (! defined('ABSPATH')) {
    exit;
}

/* ---- Trim WordPress head output ---- */

function cornwall_trim_head()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_enqueue_scripts', 'wp_enqueue_emoji_styles');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wp_shortlink_wp_head', 10);
    remove_action('wp_head', 'rest_output_link_wp_head', 10);
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_discovery_links', 4);
    remove_action('wp_head', 'wp_oembed_add_host_js');
    remove_action('template_redirect', 'rest_output_link_header', 11);
    remove_action('template_redirect', 'wp_shortlink_header', 11);
    // The theme prints its own favicon set; a Customizer Site Icon would compete with it.
    remove_action('wp_head', 'wp_site_icon', 99);
}
add_action('after_setup_theme', 'cornwall_trim_head');

/**
 * Core still prints every default palette, gradient, duotone, font size,
 * spacing, shadow and aspect-ratio variable even when theme.json turns them
 * off in the editor. None are used; layout and element defaults stay.
 */
function cornwall_strip_default_presets($theme_json)
{
    $data = $theme_json->get_data();
    unset(
        $data['settings']['color']['palette'],
        $data['settings']['color']['gradients'],
        $data['settings']['color']['duotone'],
        $data['settings']['typography']['fontSizes'],
        $data['settings']['spacing']['spacingSizes'],
        $data['settings']['shadow']['presets'],
        $data['settings']['dimensions']['aspectRatios']
    );
    return new WP_Theme_JSON_Data($data, 'default');
}
add_filter('wp_theme_json_data_default', 'cornwall_strip_default_presets');

function cornwall_dequeue_extras()
{
    // WordPress core already prefetches/prerenders links with speculation rules.
    wp_dequeue_script('breeze-prefetch');
}
add_action('wp_enqueue_scripts', 'cornwall_dequeue_extras', 100);

/* ---- Security headers ---- */

function cornwall_security_headers()
{
    if (headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    if (is_ssl() && cornwall_is_live()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}
add_action('send_headers', 'cornwall_security_headers');

/* ---- Pages the site does not use: feeds, search, archives, posts, embeds ---- */

function cornwall_redirect_unused_views()
{
    if (is_admin() || is_preview() || is_customize_preview()) {
        return;
    }
    if (is_feed() || is_search() || is_author() || is_date() || is_category() || is_tag()
        || is_attachment() || is_embed() || is_singular('post') || (is_home() && ! is_front_page())) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}
add_action('template_redirect', 'cornwall_redirect_unused_views', 1);

/** Hide the user list (it exposes login names) from logged-out visitors. */
function cornwall_rest_endpoints($endpoints)
{
    if (is_user_logged_in()) {
        return $endpoints;
    }
    foreach (array_keys($endpoints) as $route) {
        if (strpos($route, '/wp/v2/users') === 0) {
            unset($endpoints[$route]);
        }
    }
    return $endpoints;
}
add_filter('rest_endpoints', 'cornwall_rest_endpoints');

add_filter('xmlrpc_enabled', '__return_false');

/* ---- Titles, robots, sitemaps ---- */

function cornwall_document_title($parts)
{
    if (is_front_page()) {
        $parts['title'] = 'Cornwall Dry Cleaners';
        $parts['tagline'] = 'Ealing';
        unset($parts['site']);
    } elseif (is_page()) {
        unset($parts['tagline']);
    }
    return $parts;
}
add_filter('document_title_parts', 'cornwall_document_title');

function cornwall_robots($robots)
{
    if (is_404()) {
        $robots['noindex'] = true;
    }
    return $robots;
}
add_filter('wp_robots', 'cornwall_robots');

add_filter('wp_sitemaps_stylesheet_url', '__return_false');
add_filter('wp_sitemaps_stylesheet_index_url', '__return_false');

function cornwall_sitemap_providers($provider, $name)
{
    return $name === 'users' ? false : $provider;
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

/* ---- Meta tags ---- */

/** Title, description and URL for pages that get social/meta tags. */
function cornwall_page_meta()
{
    if (is_front_page()) {
        return array(
            'title' => 'Cornwall Dry Cleaners – Ealing',
            'desc'  => cornwall_site_description(),
            'url'   => home_url('/'),
        );
    }
    if (is_page('privacy')) {
        return array(
            'title' => 'Privacy – Cornwall Dry Cleaners',
            'desc'  => 'How Cornwall Dry Cleaners on Haven Lane, Ealing uses the information you give us in the shop, by phone or on this website.',
            'url'   => home_url('/privacy/'),
        );
    }
    if (is_page('terms')) {
        return array(
            'title' => 'Terms – Cornwall Dry Cleaners',
            'desc'  => 'Simple terms for using the Cornwall Dry Cleaners website and dropping garments in at 23 Haven Lane, Ealing.',
            'url'   => home_url('/terms/'),
        );
    }
    if (is_page(cornwall_wedding_slug())) {
        return array(
            'title' => 'Wedding dress cleaning & alterations – Cornwall Dry Cleaners, Ealing',
            'desc'  => 'Wedding dress cleaning, occasion wear and alterations on Haven Lane, Ealing. Bring it in — we inspect first and tell you honestly what we can do.',
            'url'   => get_permalink(),
        );
    }
    return null;
}

function cornwall_head_meta()
{
    $icon  = esc_url(get_theme_file_uri('assets/favicon.svg'));
    $png   = esc_url(get_theme_file_uri('assets/favicon.png'));
    $touch = esc_url(get_theme_file_uri('assets/apple-touch-icon.png'));

    echo '<link rel="icon" href="' . $icon . '" type="image/svg+xml" />' . "\n";
    echo '<link rel="icon" href="' . $png . '" sizes="32x32" type="image/png" />' . "\n";
    echo '<link rel="apple-touch-icon" href="' . $touch . '" />' . "\n";
    echo '<meta name="theme-color" content="#003868" />' . "\n";

    if (is_front_page()) {
        $hero     = esc_url(get_theme_file_uri('assets/shop-front.webp'));
        $hero_640 = esc_url(get_theme_file_uri('assets/shop-front-640.webp'));
        $hero_960 = esc_url(get_theme_file_uri('assets/shop-front-960.webp'));
        echo '<link rel="preload" as="image" href="' . $hero . '" imagesrcset="' . $hero_640 . ' 640w, ' . $hero_960 . ' 960w, ' . $hero . ' 1600w" imagesizes="(max-width: 720px) calc(100vw - 32px), (max-width: 1180px) calc(100vw - 48px), 1120px" type="image/webp" fetchpriority="high" />' . "\n";
    }

    $meta = cornwall_page_meta();
    if ($meta === null) {
        return;
    }
    $share = esc_url(get_theme_file_uri('assets/og-share.jpg'));
    $tags = array(
        array('name', 'description', $meta['desc']),
        array('property', 'og:type', 'website'),
        array('property', 'og:locale', 'en_GB'),
        array('property', 'og:site_name', cornwall_biz('name')),
        array('property', 'og:title', $meta['title']),
        array('property', 'og:description', $meta['desc']),
        array('property', 'og:url', $meta['url']),
        array('property', 'og:image', $share),
        array('property', 'og:image:width', '1200'),
        array('property', 'og:image:height', '630'),
        array('property', 'og:image:alt', 'Cornwall Dry Cleaners on Haven Lane, Ealing'),
        array('name', 'twitter:card', 'summary_large_image'),
        array('name', 'twitter:title', $meta['title']),
        array('name', 'twitter:description', $meta['desc']),
        array('name', 'twitter:image', $share),
    );
    foreach ($tags as $tag) {
        $value = in_array($tag[1], array('og:url', 'og:image', 'twitter:image'), true) ? esc_url($tag[2]) : esc_attr($tag[2]);
        echo '<meta ' . $tag[0] . '="' . esc_attr($tag[1]) . '" content="' . $value . '" />' . "\n";
    }
}
add_action('wp_head', 'cornwall_head_meta', 1);

/* ---- Structured data ---- */

function cornwall_opening_hours_spec()
{
    $names = cornwall_day_names();
    $spec = array();
    foreach (cornwall_hours_groups() as $group) {
        if ($group['hours'] === null) {
            continue;
        }
        $days = array();
        foreach ($group['days'] as $d) {
            $days[] = $names[$d];
        }
        $spec[] = array(
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => count($days) === 1 ? $days[0] : $days,
            'opens'     => $group['hours'][0],
            'closes'    => $group['hours'][1],
        );
    }
    return $spec;
}

/**
 * Bank holidays in the next year as closed days, plus each special day from
 * Shop details (closed, or its own hours). A special day replaces a bank holiday.
 */
function cornwall_special_hours_spec()
{
    $today   = wp_date('Y-m-d');
    $until   = wp_date('Y-m-d', strtotime('+1 year'));
    $special = cornwall_special_dates();
    $spec    = array();
    foreach (cornwall_bank_holidays() as $date) {
        if (strcmp($date, $today) >= 0 && strcmp($date, $until) <= 0 && ! isset($special[$date])) {
            $spec[] = array(
                '@type'        => 'OpeningHoursSpecification',
                'opens'        => '00:00',
                'closes'       => '00:00',
                'validFrom'    => $date,
                'validThrough' => $date,
            );
        }
    }
    foreach (cornwall_special_days() as $row) {
        if (strcmp($row['from'], $until) > 0) {
            continue;
        }
        $spec[] = array(
            '@type'        => 'OpeningHoursSpecification',
            'opens'        => $row['closed'] ? '00:00' : $row['opens'],
            'closes'       => $row['closed'] ? '00:00' : $row['closes'],
            'validFrom'    => strcmp($row['from'], $today) < 0 ? $today : $row['from'],
            'validThrough' => $row['to'],
        );
    }
    usort($spec, function ($a, $b) {
        return strcmp($a['validFrom'], $b['validFrom']);
    });
    return $spec;
}

function cornwall_business_schema()
{
    $home = home_url('/');
    $offers = array();
    foreach (cornwall_services() as $service) {
        $offers[] = array(
            '@type'       => 'Offer',
            'itemOffered' => array(
                '@type'       => 'Service',
                'name'        => $service[0],
                'description' => $service[1],
            ),
        );
    }
    $data = array(
        '@type'       => array('DryCleaningOrLaundry', 'LocalBusiness'),
        '@id'         => $home . '#business',
        'name'        => cornwall_biz('name'),
        'legalName'   => cornwall_biz('legal_name'),
        'description' => cornwall_site_description(),
        'foundingDate' => cornwall_biz('founded'),
        'image'       => array(
            get_theme_file_uri('assets/shop-front.jpg'),
            get_theme_file_uri('assets/og-share.jpg'),
        ),
        'logo'        => get_theme_file_uri('assets/apple-touch-icon.png'),
        'url'         => $home,
        'telephone'   => cornwall_biz('phone'),
        'email'       => cornwall_biz('email'),
        'priceRange'  => '£',
        'currenciesAccepted' => 'GBP',
        'paymentAccepted'    => 'Cash, Credit Card, Debit Card, Contactless',
        'address'     => array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => cornwall_biz('street'),
            'addressLocality' => cornwall_biz('postal_town'),
            'postalCode'      => cornwall_biz('postcode'),
            'addressCountry'  => cornwall_biz('country'),
        ),
        'geo'         => array(
            '@type'     => 'GeoCoordinates',
            'latitude'  => cornwall_biz('lat'),
            'longitude' => cornwall_biz('lng'),
        ),
        'areaServed'  => array(
            array('@type' => 'Place', 'name' => 'Ealing'),
            array('@type' => 'Place', 'name' => 'Ealing Broadway'),
            array('@type' => 'Place', 'name' => 'London W5'),
        ),
        'hasMap'      => cornwall_biz('maps_url'),
        'sameAs'      => array(cornwall_biz('maps_url'), cornwall_google_place_url()),
        'openingHoursSpecification' => cornwall_opening_hours_spec(),
        'hasOfferCatalog' => array(
            '@type'           => 'OfferCatalog',
            'name'            => 'Dry cleaning, laundry and repairs',
            'itemListElement' => $offers,
        ),
        'aggregateRating' => array(
            '@type'       => 'AggregateRating',
            'ratingValue' => cornwall_rating_value(),
            'reviewCount' => cornwall_review_count_total(),
            'bestRating'  => '5',
        ),
    );
    $special = cornwall_special_hours_spec();
    if (! empty($special)) {
        $data['specialOpeningHoursSpecification'] = $special;
    }
    return $data;
}

function cornwall_json_ld()
{
    $meta = cornwall_page_meta();
    if ($meta === null) {
        return;
    }
    $home = home_url('/');
    $graph = array(
        cornwall_business_schema(),
        array(
            '@type'      => 'WebSite',
            '@id'        => $home . '#website',
            'url'        => $home,
            'name'       => cornwall_biz('name'),
            'inLanguage' => 'en-GB',
            'publisher'  => array('@id' => $home . '#business'),
        ),
        array(
            '@type'      => 'WebPage',
            '@id'        => $meta['url'] . '#webpage',
            'url'        => $meta['url'],
            'name'       => $meta['title'],
            'description' => $meta['desc'],
            'inLanguage' => 'en-GB',
            'isPartOf'   => array('@id' => $home . '#website'),
            'about'      => array('@id' => $home . '#business'),
        ),
    );
    if (is_front_page()) {
        $questions = array();
        foreach (cornwall_faqs() as $faq) {
            $questions[] = array(
                '@type'          => 'Question',
                'name'           => $faq[0],
                'acceptedAnswer' => array('@type' => 'Answer', 'text' => $faq[1]),
            );
        }
        $graph[] = array(
            '@type'      => 'FAQPage',
            '@id'        => $home . '#faq',
            'mainEntity' => $questions,
        );
    }
    $data = array('@context' => 'https://schema.org', '@graph' => $graph);
    echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head', 'cornwall_json_ld', 20);
