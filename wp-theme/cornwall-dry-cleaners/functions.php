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
    add_theme_support('editor-styles');
    add_theme_support('wp-block-styles');
    add_theme_support('title-tag');
}
add_action('after_setup_theme', 'cornwall_theme_setup');

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

function cornwall_head_meta()
{
    $icon = esc_url(get_theme_file_uri('assets/favicon.svg'));
    $png  = esc_url(get_theme_file_uri('assets/favicon.png'));
    $touch = esc_url(get_theme_file_uri('assets/apple-touch-icon.png'));
    echo '<link rel="icon" href="' . $icon . '" type="image/svg+xml" />' . "\n";
    echo '<link rel="icon" href="' . $png . '" sizes="32x32" type="image/png" />' . "\n";
    echo '<link rel="apple-touch-icon" href="' . $touch . '" />' . "\n";
    echo '<meta name="theme-color" content="#003868" />' . "\n";
}
add_action('wp_head', 'cornwall_head_meta', 1);

function cornwall_json_ld()
{
    if (! is_front_page()) {
        return;
    }
    $data = array(
        '@context' => 'https://schema.org',
        '@type'    => 'DryCleaningOrLaundry',
        'name'     => 'Cornwall Dry Cleaners',
        'image'    => get_theme_file_uri('assets/shop-front.jpg'),
        'url'      => home_url('/'),
        'telephone' => '+442089919413',
        'priceRange' => '£',
        'address'  => array(
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
}
add_action('wp_head', 'cornwall_json_ld', 20);

function cornwall_body_class($classes)
{
    $classes[] = 'cornwall-theme';
    return $classes;
}
add_filter('body_class', 'cornwall_body_class');
