<?php
/**
 * Prices editor: a price list grouped by category. Empty at first, so the
 * Prices section stays hidden until the first price is saved. Other lists can
 * say {from:Category} to show "from £12" from here.
 */

if (! defined('ABSPATH')) {
    exit;
}

function cornwall_price_label($price)
{
    $price = (string) $price;
    return '£' . (substr($price, -3) === '.00' ? substr($price, 0, -3) : $price);
}

/** "from £12" for the cheapest price in a category (any page), '' if there is none. */
function cornwall_price_from($category)
{
    $low = null;
    foreach (cornwall_content_items('prices') as $item) {
        if (strcasecmp(trim($item['category']), trim((string) $category)) === 0 && ($low === null || (float) $item['price'] < (float) $low)) {
            $low = $item['price'];
        }
    }
    return $low === null ? '' : 'from ' . cornwall_price_label($low);
}

function cornwall_price_text($item)
{
    return ($item['kind'] === 'from' ? 'from ' : '') . cornwall_price_label($item['price']);
}

/** Category => items, in the order categories first appear. */
function cornwall_prices_grouped($items)
{
    $groups = array();
    foreach ($items as $item) {
        $groups[$item['category']][] = $item;
    }
    return $groups;
}

function cornwall_prices_render($items, $page, $flags)
{
    $html = '';
    foreach (cornwall_prices_grouped($items) as $category => $rows) {
        $body = '';
        foreach ($rows as $item) {
            try {
                $note  = $item['note'] !== '' ? '<span class="price-note">' . cornwall_content_text($item['note']) . '</span>' : '';
                $body .= '<tr data-id="' . esc_attr($item['id']) . '"><th scope="row">' . cornwall_content_text($item['name']) . $note . '</th><td>' . esc_html(cornwall_price_text($item)) . '</td></tr>';
            } catch (Throwable $e) {
                continue;
            }
        }
        if ($body !== '') {
            $html .= '<div class="card price-card"><h3>' . esc_html($category) . '</h3><table class="price-table"><tbody>' . $body . '</tbody></table></div>' . "\n";
        }
    }
    return $html;
}

cornwall_register_editor('prices', array(
    'title'       => 'Prices',
    'singular'    => 'price',
    'title_field' => 'name',
    'guide'       => 'A price list, grouped by category, on the homepage (after Services) and optionally on the wedding page. Prices also go into Google’s structured data. With no prices the section stays hidden. Elsewhere, type {from:Dry cleaning} to show “from £…” using the cheapest price in that category.',
    'fields'      => array(
        'category' => array('type' => 'text', 'label' => 'Category', 'max' => 40, 'required' => true, 'list' => true, 'help' => 'e.g. Dry cleaning, Laundry, Repairs'),
        'name'     => array('type' => 'text', 'label' => 'Item', 'max' => 80, 'required' => true, 'help' => 'e.g. Two-piece suit'),
        'price'    => array('type' => 'price', 'label' => 'Price', 'required' => true),
        'kind'     => array('type' => 'select', 'label' => 'Shown as', 'options' => array('exact' => 'The price (£12)', 'from' => 'A starting price (from £12)')),
        'note'     => array('type' => 'text', 'label' => 'Note (optional)', 'max' => 80, 'help' => 'Small print under the item, e.g. “per kilo”'),
    ),
    'pages'       => array('home', 'wedding'),
    'render'      => 'cornwall_prices_render',
    'anchor'      => array('home' => array('id' => 'prices', 'flags' => array())),
    'counts'      => array(
        'home'    => array('what' => 'prices', 'hides' => 'the Prices section is hidden'),
        'wedding' => array('what' => 'prices', 'hides' => 'no price list there'),
    ),
));
