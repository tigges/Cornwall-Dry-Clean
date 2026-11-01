<?php
/**
 * FAQs editor: the Questions box next to the opening hours (and extra
 * questions on the wedding page). Also Google's FAQPage structured data.
 */

if (! defined('ABSPATH')) {
    exit;
}

function cornwall_faqs_defaults()
{
    $items = array();
    foreach (cornwall_faqs() as $i => $faq) {
        $items[] = array('id' => 'faq-' . ($i + 1), 'show' => array('home'), 'q' => $faq[0], 'a' => $faq[1]);
    }
    return $items;
}

function cornwall_faqs_render_item($item, $n, $page, $flags)
{
    $open = $page === 'home' ? '' : ' open';
    return '<details' . $open . ' id="q-' . esc_attr($item['id']) . '" data-id="' . esc_attr($item['id']) . '">' . "\n"
        . '            <summary>' . cornwall_content_text($item['q']) . '</summary>' . "\n"
        . '            <p>' . cornwall_content_multiline($item['a']) . '</p>' . "\n"
        . '          </details>' . "\n";
}

cornwall_register_editor('faqs', array(
    'title'       => 'FAQs',
    'singular'    => 'question',
    'title_field' => 'q',
    'guide'       => 'The Questions box next to the opening hours on the homepage, and the questions Google sees. Tick “Wedding page” to add a question after the wedding page’s own questions. With no questions, the box disappears.',
    'fields'      => array(
        'q' => array('type' => 'text', 'label' => 'Question', 'max' => 140, 'required' => true),
        'a' => array('type' => 'textarea', 'label' => 'Answer', 'max' => 700, 'required' => true, 'help' => 'Placeholders work here, e.g. “We are open {hours}.”'),
    ),
    'pages'       => array('home', 'wedding'),
    'defaults'    => 'cornwall_faqs_defaults',
    'render_item' => 'cornwall_faqs_render_item',
    'anchor'      => array('home' => array('id' => 'faq', 'flags' => array())),
    'counts'      => array(
        'home'    => array('what' => 'questions', 'hides' => 'the Questions box is hidden'),
        'wedding' => array('what' => 'extra questions'),
    ),
));
