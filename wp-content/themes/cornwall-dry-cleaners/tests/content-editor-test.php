<?php
/**
 * Page content editors: renders the homepage, wedding page and header with
 * default, edited, empty and broken data and checks the HTML and JSON-LD.
 *
 *   wp eval-file wp-content/themes/cornwall-dry-cleaners/tests/content-editor-test.php
 *
 * Saves and restores the four list options, so it is safe on a local or staging copy.
 */

if (! defined('WP_CLI') || ! WP_CLI) {
    exit;
}

$lists  = array('services', 'prices', 'reviews', 'faqs');
$backup = array();
foreach ($lists as $key) {
    $backup[$key] = get_option(cornwall_content_editor($key)['option'], null);
}
$GLOBALS['cct'] = array('pass' => 0, 'fail' => 0);

function cct_check($ok, $label)
{
    $GLOBALS['cct'][$ok ? 'pass' : 'fail']++;
    WP_CLI::log(($ok ? '  ok   ' : '  FAIL ') . $label);
}

/** Rendered HTML with runs of whitespace collapsed to one space. */
function cct_render($slug, $filtered = true)
{
    if (! $filtered) {
        remove_filter('render_block_core/pattern', 'cornwall_content_render_pattern', 10);
    }
    $html = do_blocks('<!-- wp:pattern {"slug":"' . $slug . '"} /-->');
    if (! $filtered) {
        add_filter('render_block_core/pattern', 'cornwall_content_render_pattern', 10, 2);
    }
    return trim(preg_replace('/\s+/', ' ', $html));
}

/** The template without the new Prices sections, which stay hidden until a price is saved. */
function cct_fallback($slug)
{
    return preg_replace('#<section class="section prices"[^>]*>.*?</section>#s', '', cct_render($slug, false));
}

/** Visible markup only: no comments, editor ids or whitespace differences. */
function cct_normal($html)
{
    $html = preg_replace('/<!--.*?-->/s', '', $html);
    $html = preg_replace('/\s(data-id|id)="(q-)?(faq|service|review)[^"]*"/', '', $html);
    return trim(preg_replace('/\s+/', ' ', preg_replace('/>\s+</', '><', $html)));
}

function cct_set($key, $rows)
{
    $editor = cornwall_content_editor($key);
    $items  = array();
    foreach ($rows as $row) {
        $clean = cornwall_content_clean_item($editor, $row);
        if ($clean['item'] && ! $clean['blank'] && $clean['missing'] === '') {
            $items[] = $clean['item'];
        }
    }
    update_option($editor['option'], $items, false);
}

function cct_order($html, $needles)
{
    $last = -1;
    foreach ($needles as $needle) {
        $pos = strpos($html, $needle);
        if ($pos === false || $pos < $last) {
            return false;
        }
        $last = $pos;
    }
    return true;
}

try {
    /* ---- Default: never saved, so the lists come from the theme defaults ---- */
    WP_CLI::log('Default data');
    foreach ($lists as $key) {
        delete_option(cornwall_content_editor($key)['option']);
    }
    foreach (array('cornwall/homepage' => 'Homepage', 'cornwall/wedding' => 'Wedding page', 'cornwall/header' => 'Header') as $slug => $label) {
        cct_check(cct_normal(cct_render($slug)) === cct_normal(cct_fallback($slug)), $label . ' renders exactly like the template fallback');
    }
    $home = cct_render('cornwall/homepage');
    cct_check(substr_count($home, 'class="wp-block-group card service-card') === 6, '6 service cards');
    cct_check(substr_count($home, '<details id="q-') === 5, '5 questions');
    cct_check(strpos($home, 'id="prices"') === false, 'Prices section hidden while the list is empty');
    cct_check(strpos($home, 'Gina is the rock star of cleaning. I had a cocktail spilt over my suit trousers… they came back perfectly clean and pressed. Legendary service.') !== false, 'Review text verbatim');
    cct_check(count(cornwall_offer_schema('home')) === 6, 'JSON-LD: 6 offers');
    $faq = cornwall_faq_schema('home');
    cct_check($faq && count($faq['mainEntity']) === 5 && $faq['mainEntity'][0]['name'] === 'How long does it take?', 'JSON-LD: FAQPage with the 5 questions');
    cct_check(cornwall_review_schema('home') === array(), 'JSON-LD: no Review without reviewer names');
    cornwall_content_seed();
    cct_check(count(get_option('cornwall_services_list')) === 6 && get_option('cornwall_prices_list') === array(), 'Seeding writes the defaults once');

    /* ---- Edited ---- */
    WP_CLI::log('Edited data');
    $defaults = cornwall_services_defaults();
    cct_set('services', array(
        $defaults[2],
        $defaults[0],
        array('id' => 'shirts', 'show' => array('home', 'wedding'), 'title' => '<script>alert(1)</script>Shirts', 'text' => "Washed and pressed, {from:Shirts}.\nOn hangers.", 'link' => 'javascript:alert(1)'),
        array('id' => 'bad-link', 'show' => array('home'), 'title' => 'Curtains', 'text' => 'Ask at the counter.', 'link' => '/#visit'),
    ));
    cct_set('prices', array(
        array('id' => 'p1', 'show' => array('home'), 'category' => 'Shirts', 'name' => 'Shirt on a hanger', 'price' => '£3.5', 'kind' => 'exact'),
        array('id' => 'p2', 'show' => array('home', 'wedding'), 'category' => 'Shirts', 'name' => 'Shirt, folded', 'price' => '4', 'kind' => 'from', 'note' => 'Boxed on request'),
        array('id' => 'p3', 'show' => array('wedding'), 'category' => 'Wedding', 'name' => 'Wedding dress', 'price' => '150', 'kind' => 'from'),
        array('id' => 'p4', 'show' => array('home'), 'category' => 'Shirts', 'name' => 'Not a price', 'price' => 'cheap'),
    ));
    cct_set('reviews', array(
        array('id' => 'r1', 'show' => array('home'), 'text' => 'Fourth-star review.', 'tag' => 'Shirts', 'stars' => '4', 'style' => 'card', 'source' => 'google', 'name' => 'Sam'),
        array('id' => 'r2', 'show' => array('home'), 'text' => 'Second.', 'tag' => '', 'stars' => '5', 'style' => 'card'),
        array('id' => 'r3', 'show' => array('home'), 'text' => 'Third.', 'stars' => '9', 'style' => 'card'),
        array('id' => 'r4', 'show' => array('home'), 'text' => 'Only first 3 show.', 'style' => 'card'),
        array('id' => 'rq', 'show' => array('home', 'wedding'), 'text' => 'New shop quote.', 'style' => 'quote', 'source' => 'customer'),
    ));
    cct_set('faqs', array(
        array('id' => 'open', 'show' => array('home', 'wedding'), 'q' => 'When are you open?', 'a' => "We are open {hours}.\nCall {phone}. {nonsense}"),
        array('id' => 'cards', 'show' => array('home'), 'q' => 'Cards or cash?', 'a' => 'Both.'),
        array('id' => 'missing', 'show' => array('home'), 'q' => 'No answer'),
    ));
    $home    = cct_render('cornwall/homepage');
    $wedding = cct_render('cornwall/wedding');
    $hours   = cornwall_hours_summary();

    cct_check(cct_order($home, array('data-num="01">Laundry', 'data-num="02">Dry cleaning', 'data-num="03">Shirts', 'data-num="04"><a href="/#visit">Curtains')), 'Services in saved order, numbered, with a safe link');
    cct_check(strpos($home, '<script>alert') === false && strpos($home, 'javascript:') === false, 'No script tags or javascript: links stored or shown');
    cct_check(strpos($home, 'Washed and pressed, from £3.50.<br> On hangers.') !== false, '{from:Shirts} fills in and newlines become <br>');
    cct_check(strpos($home, 'id="prices"') !== false && strpos($home, '<h3>Shirts</h3>') !== false, 'Prices section appears with its category');
    cct_check(strpos($home, '<td>£3.50</td>') !== false && strpos($home, '<td>from £4</td>') !== false && strpos($home, 'Boxed on request') !== false, 'Prices formatted (£3.50, from £4, note)');
    cct_check(strpos($home, 'Not a price') === false && strpos($home, 'Wedding dress') === false, 'Invalid price dropped; wedding-only price not on the homepage');
    cct_check(substr_count($home, '<div class="wp-block-group card is-layout-flow') === 3 && strpos($home, 'Only first 3 show.') === false, 'Only the first 3 review cards show');
    cct_check(strpos($home, 'aria-label="4 out of 5 stars">★★★★☆') !== false && strpos($home, 'Sam · Google review') !== false, '4-star card with reviewer name');
    cct_check(strpos($home, '★★★★★</p>') !== false, 'Out-of-range stars fall back to 5');
    cct_check(strpos($home, '“New shop quote.”') !== false && strpos($home, '">Customer</p>') !== false, 'Shop quote and source updated');
    cct_check(strpos($home, 'We are open ' . esc_html($hours) . '.<br> Call ' . cornwall_biz('phone_display') . '. {nonsense}') !== false, '{hours} and {phone} fill in; unknown placeholders stay as typed');
    cct_check(strpos($home, 'No answer') === false && substr_count($home, '<details id="q-') === 2, 'Question without an answer is left out');
    cct_check(strpos($wedding, 'data-num="07">Shirts') !== false, 'Wedding page: extra service card after its own six (07)');
    cct_check(strpos($wedding, 'id="wedding-prices"') !== false && strpos($wedding, '<td>from £150</td>') !== false, 'Wedding page: its own prices');
    cct_check(substr_count($wedding, '<details open') === 5 && strpos($wedding, 'When are you open?') !== false, 'Wedding page: mockup questions plus the ticked one');
    cct_check(strpos($wedding, '“New shop quote.”') !== false && strpos($wedding, '[Confirm') !== false, 'Wedding page: new quote, mockup notes untouched');

    $offers = cornwall_offer_schema('home');
    $named  = wp_list_pluck(wp_list_pluck($offers, 'itemOffered'), 'name');
    cct_check(in_array('Shirts', $named, true) && in_array('Shirt on a hanger', $named, true) && ! in_array('Wedding dress', $named, true), 'JSON-LD offers match the homepage services and prices');
    $prices = array_values(array_filter($offers, function ($o) { return isset($o['price']) || isset($o['priceSpecification']); }));
    cct_check($prices[0]['price'] === '3.50' && $prices[1]['priceSpecification']['minPrice'] === '4.00', 'JSON-LD prices: price 3.50, minPrice 4.00 for "from"');
    $faq = cornwall_faq_schema('home');
    cct_check($faq && count($faq['mainEntity']) === 2 && strpos($faq['mainEntity'][0]['acceptedAnswer']['text'], $hours) !== false, 'JSON-LD FAQPage matches the 2 questions, placeholders filled');
    $reviews = cornwall_review_schema('home');
    cct_check(count($reviews) === 1 && $reviews[0]['author']['name'] === 'Sam' && $reviews[0]['reviewRating']['ratingValue'] === '4', 'JSON-LD Review only for the named review');
    $header = cct_render('cornwall/header');
    cct_check(substr_count($header, 'href="/#services"') === 2 && substr_count($header, 'href="/#reviews"') === 2, 'Header keeps Services and Reviews links');

    /* ---- Empty ---- */
    WP_CLI::log('Empty lists');
    foreach ($lists as $key) {
        update_option(cornwall_content_editor($key)['option'], array(), false);
    }
    $home    = cct_render('cornwall/homepage');
    $wedding = cct_render('cornwall/wedding');
    $header  = cct_render('cornwall/header');
    foreach (array('services', 'prices', 'reviews', 'faq') as $id) {
        cct_check(strpos($home, 'id="' . $id . '"') === false, 'Homepage: #' . $id . ' section removed');
    }
    cct_check(substr_count($home, '<section') === substr_count($home, '</section>'), 'Homepage: sections still balanced');
    cct_check(strpos($home, 'id="story"') !== false && strpos($home, 'quote-panel') === false, 'Story section stays, its quote goes');
    cct_check(strpos($home, 'id="visit"') !== false && strpos($home, 'Opening hours') !== false, 'Visit section and hours stay');
    cct_check(strpos($header, '#services') === false && strpos($header, '#reviews') === false && substr_count($header, 'href="/#story"') === 2 && substr_count($header, 'href="/#visit"') === 2, 'Header: Services and Reviews links removed, others kept');
    cct_check(strpos($wedding, 'id="wedding-services"') !== false && strpos($wedding, 'id="wedding-prices"') === false && strpos($wedding, 'quote-panel') === false, 'Wedding page: own cards stay, prices and quote go');
    cct_check(cornwall_offer_schema('home') === array() && cornwall_faq_schema('home') === null, 'JSON-LD: no offers, no FAQPage');
    $business = cornwall_business_schema();
    cct_check(! isset($business['hasOfferCatalog']), 'JSON-LD: OfferCatalog left out when empty');

    /* ---- Broken records ---- */
    WP_CLI::log('Broken records');
    update_option('cornwall_services_list', array('not a row', 42, array('title' => array('x'), 'text' => array()), array('id' => 'ok', 'show' => array('home'), 'title' => 'Still here', 'text' => 'Fine.')), false);
    update_option('cornwall_faqs_list', 'not an array', false);
    update_option('cornwall_reviews_list', array(array('show' => 'home', 'text' => 'Show is not a list')), false);
    $home = cct_render('cornwall/homepage');
    cct_check(strpos($home, 'data-num="01">Still here') !== false && substr_count($home, 'service-card') === 1, 'Bad rows skipped, good row shown');
    cct_check(strpos($home, 'id="faq"') !== false && substr_count($home, '<details') === 5, 'Unreadable FAQ option falls back to the defaults');
    cct_check(strpos($home, 'id="reviews"') === false, 'Review with no valid page is not shown');

    /* ---- Sitemap ---- */
    WP_CLI::log('Sitemap');
    $front = get_post((int) get_option('page_on_front'));
    if ($front) {
        cornwall_content_touch(array('home'));
        $entry = apply_filters('wp_sitemaps_posts_entry', array('loc' => home_url('/'), 'lastmod' => '2020-01-01T00:00:00+00:00'), $front, 'page');
        cct_check(strtotime($entry['lastmod']) >= time() - 5, 'Homepage lastmod moves to the save time');
    }
} catch (Throwable $e) {
    $GLOBALS['cct']['fail']++;
    WP_CLI::log('  FAIL threw ' . get_class($e) . ': ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
}

foreach ($lists as $key) {
    $option = cornwall_content_editor($key)['option'];
    if ($backup[$key] === null) {
        delete_option($option);
    } else {
        update_option($option, $backup[$key], false);
    }
}

WP_CLI::log(sprintf('%d passed, %d failed', $GLOBALS['cct']['pass'], $GLOBALS['cct']['fail']));
if ($GLOBALS['cct']['fail']) {
    WP_CLI::halt(1);
}
