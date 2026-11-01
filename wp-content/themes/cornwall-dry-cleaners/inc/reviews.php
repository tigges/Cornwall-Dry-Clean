<?php
/**
 * Reviews editor: the three review cards on the homepage and the quote next
 * to the shop photo (homepage and wedding page). Reviews with a name are also
 * given to Google as Review structured data.
 */

if (! defined('ABSPATH')) {
    exit;
}

function cornwall_reviews_defaults()
{
    $cards = array(
        array('Wool coat', 'Tucked away on a residential street. I’m so glad I found it. Cornwall cleaned my wool coat with such care and really good value.'),
        array('Stain removal', 'Gina is the rock star of cleaning. I had a cocktail spilt over my suit trousers… they came back perfectly clean and pressed. Legendary service.'),
        array('Repairs', 'I’ve used both the repairs and dry cleaning services over the years. The staff always try to go the extra mile — fixing a loose button without me pointing it out.'),
    );
    $items = array();
    foreach ($cards as $i => $card) {
        $items[] = array('id' => 'review-' . ($i + 1), 'show' => array('home'), 'text' => $card[1], 'tag' => $card[0], 'stars' => '5', 'style' => 'card', 'source' => 'google', 'name' => '');
    }
    $items[] = array('id' => 'review-quote', 'show' => array('home', 'wedding'), 'text' => 'These guys really care about the quality of the service they provide and clearly check your goods before handing them back.', 'tag' => '', 'stars' => '5', 'style' => 'quote', 'source' => 'google', 'name' => '');
    return $items;
}

function cornwall_review_sources_list()
{
    return array('google' => 'Google review', 'customer' => 'Customer');
}

/** Cards: first 3 on the homepage, none elsewhere. Quote: the first one. */
function cornwall_reviews_select($items, $page, $flags)
{
    $style = isset($flags['quote']) ? 'quote' : 'card';
    $items = array_values(array_filter($items, function ($item) use ($style) {
        return $item['style'] === $style;
    }));
    if ($style === 'card') {
        return $page === 'home' ? array_slice($items, 0, 3) : array();
    }
    return array_slice($items, 0, 1);
}

function cornwall_review_byline($item)
{
    $sources = cornwall_review_sources_list();
    $source  = isset($sources[$item['source']]) ? $sources[$item['source']] : $sources['google'];
    return esc_html($item['name'] !== '' ? $item['name'] . ' · ' . $source : $source);
}

function cornwall_reviews_render_item($item, $n, $page, $flags)
{
    $quote  = '“' . cornwall_content_multiline($item['text']) . '”';
    $byline = cornwall_review_byline($item);
    $id     = esc_attr($item['id']);

    if (isset($flags['quote'])) {
        if ($page !== 'home') {
            return '<div class="card quote-panel" data-id="' . $id . '">' . "\n" . '      <blockquote><p>' . $quote . '</p></blockquote>' . "\n" . '      <p>' . $byline . '</p>' . "\n" . '    </div>';
        }
        return '<!-- wp:group {"className":"card quote-panel","layout":{"type":"default"}} -->' . "\n"
            . '<div class="wp-block-group card quote-panel" data-id="' . $id . '">' . "\n"
            . '<!-- wp:quote -->' . "\n"
            . '<blockquote class="wp-block-quote">' . "\n" . '<p>' . $quote . '</p>' . "\n" . '</blockquote>' . "\n"
            . '<!-- /wp:quote -->' . "\n"
            . '<!-- wp:paragraph -->' . "\n" . '<p>' . $byline . '</p>' . "\n" . '<!-- /wp:paragraph -->' . "\n"
            . '</div>' . "\n"
            . '<!-- /wp:group -->' . "\n";
    }

    $stars = (int) $item['stars'];
    $label = $stars < 5 ? ' aria-label="' . $stars . ' out of 5 stars"' : '';
    $tag   = $item['tag'] !== '' ? "\n" . '<!-- wp:paragraph -->' . "\n" . '<p>' . cornwall_content_text($item['tag']) . '</p>' . "\n" . '<!-- /wp:paragraph -->' : '';
    return "\n" . '<!-- wp:group {"className":"card","layout":{"type":"default"}} -->' . "\n"
        . '<div class="wp-block-group card" data-id="' . $id . '">' . "\n"
        . '<!-- wp:group {"className":"review-meta","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->' . "\n"
        . '<div class="wp-block-group review-meta">' . "\n"
        . '<!-- wp:paragraph {"className":"stars"} -->' . "\n"
        . '<p class="stars"' . $label . '>' . str_repeat('★', $stars) . str_repeat('☆', 5 - $stars) . '</p>' . "\n"
        . '<!-- /wp:paragraph -->' . $tag . "\n"
        . '</div>' . "\n"
        . '<!-- /wp:group -->' . "\n"
        . '<!-- wp:quote -->' . "\n"
        . '<blockquote class="wp-block-quote">' . "\n" . '<p>' . $quote . '</p>' . "\n" . '</blockquote>' . "\n"
        . '<!-- /wp:quote -->' . "\n"
        . '<!-- wp:paragraph {"className":"muted"} -->' . "\n"
        . '<p class="muted">' . $byline . '</p>' . "\n"
        . '<!-- /wp:paragraph -->' . "\n"
        . '</div>' . "\n"
        . '<!-- /wp:group -->' . "\n";
}

cornwall_register_editor('reviews', array(
    'title'       => 'Reviews',
    'singular'    => 'review',
    'title_field' => 'tag',
    'guide'       => 'Review cards for the homepage Reviews section (the first 3 show) and the shop quote next to the shop photo (the first quote shows; the wedding page uses it too). Copy reviews word for word from Google, without quote marks. With no review cards, the Reviews section and its menu link disappear.',
    'fields'      => array(
        'text'   => array('type' => 'textarea', 'label' => 'Review', 'max' => 600, 'required' => true),
        'tag'    => array('type' => 'text', 'label' => 'Label', 'max' => 30, 'help' => 'Short topic on the card, e.g. “Wool coat”'),
        'stars'  => array('type' => 'select', 'label' => 'Stars', 'options' => array('5' => '★★★★★ 5', '4' => '★★★★ 4', '3' => '★★★ 3', '2' => '★★ 2', '1' => '★ 1')),
        'style'  => array('type' => 'select', 'label' => 'Where', 'options' => array('card' => 'Review card (Reviews section)', 'quote' => 'Shop quote (next to the shop photo)')),
        'source' => array('type' => 'select', 'label' => 'Source', 'options' => cornwall_review_sources_list()),
        'name'   => array('type' => 'text', 'label' => 'Reviewer name (optional)', 'max' => 40, 'help' => 'Shown under the review and given to Google. Leave empty to keep it anonymous.'),
    ),
    'pages'       => array('home', 'wedding'),
    'defaults'    => 'cornwall_reviews_defaults',
    'select'      => 'cornwall_reviews_select',
    'render_item' => 'cornwall_reviews_render_item',
    'anchor'      => array('home' => array('id' => 'reviews', 'flags' => array('cards' => true))),
    'counts'      => array(
        'home'    => array('what' => 'review cards', 'field' => 'style', 'value' => 'card', 'max' => 3, 'hides' => 'the Reviews section and its menu link are hidden'),
        'wedding' => array('what' => 'shop quotes', 'field' => 'style', 'value' => 'quote', 'max' => 1),
    ),
));

/** Reviews shown on $page that have a reviewer name (Google needs an author for Review data). */
function cornwall_reviews_named($page)
{
    $shown = array_merge(
        cornwall_content_selected('reviews', $page, array('cards' => true)),
        cornwall_content_selected('reviews', $page, array('quote' => true))
    );
    return array_values(array_filter($shown, function ($item) {
        return $item['name'] !== '';
    }));
}
