<?php
/**
 * Services editor: the homepage service cards (and extra cards on the wedding
 * page). Also the Offer list in Google's structured data.
 */

if (! defined('ABSPATH')) {
    exit;
}

function cornwall_services_defaults()
{
    $items = array();
    foreach (cornwall_services() as $i => $service) {
        $items[] = array('id' => 'service-' . ($i + 1), 'show' => array('home'), 'title' => $service[0], 'text' => $service[1], 'link' => '', 'image' => 0);
    }
    return $items;
}

function cornwall_services_render_item($item, $n, $page, $flags)
{
    $start = isset($flags['start']) ? max(1, (int) $flags['start']) : 1;
    $num   = sprintf('%02d', $start + $n);
    $name  = cornwall_content_text($item['title']);
    if ($item['link'] !== '') {
        $name = '<a href="' . esc_url($item['link']) . '">' . $name . '</a>';
    }
    $image = $item['image'] ? wp_get_attachment_image((int) $item['image'], 'medium_large', false, array('class' => 'service-img', 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '')) : '';
    $text  = cornwall_content_multiline($item['text']);
    $id    = esc_attr($item['id']);

    if ($page === 'home') {
        return "\n" . '<!-- wp:group {"className":"card service-card","layout":{"type":"default"}} -->' . "\n"
            . '<div class="wp-block-group card service-card" data-id="' . $id . '">' . $image . "\n"
            . '<!-- wp:heading {"level":3} -->' . "\n"
            . '<h3 class="wp-block-heading" data-num="' . $num . '">' . $name . '</h3>' . "\n"
            . '<!-- /wp:heading -->' . "\n"
            . '<!-- wp:paragraph {"className":"muted"} -->' . "\n"
            . '<p class="muted">' . $text . '</p>' . "\n"
            . '<!-- /wp:paragraph -->' . "\n"
            . '</div>' . "\n"
            . '<!-- /wp:group -->' . "\n";
    }
    return '<div class="card service-card" data-id="' . $id . '">' . $image
        . '<h3 data-num="' . $num . '">' . $name . '</h3>'
        . '<p class="muted">' . $text . '</p></div>' . "\n";
}

cornwall_register_editor('services', array(
    'title'       => 'Services',
    'singular'    => 'service',
    'title_field' => 'title',
    'guide'       => 'The cards under “Services” on the homepage, and the list of services Google sees. Tick “Wedding page” to add a card after the wedding page’s own cards. With no services, the Services section and its menu link disappear.',
    'fields'      => array(
        'title' => array('type' => 'text', 'label' => 'Name', 'max' => 60, 'required' => true),
        'text'  => array('type' => 'textarea', 'label' => 'Description', 'max' => 240, 'required' => true, 'help' => 'Placeholders work here, e.g. “{from:Dry cleaning}”.'),
        'link'  => array('type' => 'link', 'label' => 'Link (optional)', 'max' => 200, 'help' => 'Makes the name a link: a page on this site (/…) or https://…'),
        'image' => array('type' => 'media', 'label' => 'Image (optional)'),
    ),
    'pages'       => array('home', 'wedding'),
    'defaults'    => 'cornwall_services_defaults',
    'render_item' => 'cornwall_services_render_item',
    'anchor'      => array('home' => array('id' => 'services', 'flags' => array())),
    'counts'      => array(
        'home'    => array('what' => 'services', 'hides' => 'the Services section and its menu link are hidden'),
        'wedding' => array('what' => 'extra cards'),
    ),
));
