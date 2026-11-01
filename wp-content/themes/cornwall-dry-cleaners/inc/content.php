<?php
/**
 * Page content editors (wp-admin → Page content): Services, Prices, Reviews
 * and FAQs, one screen and one option per list.
 *
 * Patterns mark each editable block with
 *   <!-- site-{list}:{page} [flags] --> … <!-- /site-{list}:{page} -->
 * and keep the original HTML between the markers as the fallback. At render
 * time the block is rebuilt from the option. Flag "section": when nothing is
 * left to show, the enclosing <section> goes too, along with menu links to it.
 * JSON-LD reads the same lists (see inc/head.php).
 */

if (! defined('ABSPATH')) {
    exit;
}

$GLOBALS['cornwall_content_editors'] = array();

function cornwall_content_pages()
{
    return array('home' => 'Homepage', 'wedding' => 'Wedding page');
}

function cornwall_content_pattern_pages()
{
    return array('cornwall/homepage' => 'home', 'cornwall/wedding' => 'wedding');
}

/**
 * Field types: text, textarea (newline = line break), select (options), link,
 * price, media (attachment ID). Each field: label, max, required, help, list.
 */
function cornwall_register_editor($key, $def)
{
    $GLOBALS['cornwall_content_editors'][$key] = wp_parse_args($def, array(
        'option'      => 'cornwall_' . $key . '_list',
        'title'       => ucfirst($key),
        'singular'    => 'item',
        'title_field' => '',
        'guide'       => '',
        'fields'      => array(),
        'pages'       => array('home'),
        'new_show'    => array('home'),
        'defaults'    => '__return_empty_array',
        'select'      => null,
        'render_item' => null,
        'render'      => null,
        'anchor'      => array(),
        'counts'      => array(),
    ));
}

function cornwall_content_editors()
{
    return $GLOBALS['cornwall_content_editors'];
}

function cornwall_content_editor($key)
{
    return isset($GLOBALS['cornwall_content_editors'][$key]) ? $GLOBALS['cornwall_content_editors'][$key] : null;
}

function cornwall_content_new_id()
{
    return strtolower(wp_generate_password(8, false, false));
}

/* ---- Cleaning (used for saving and again for every read) ---- */

/** "12", "12.5", "£12.50" → "12.50"; '' if not a price. */
function cornwall_content_clean_price($value)
{
    $value = str_replace(array('£', ',', ' '), '', trim((string) $value));
    if (! preg_match('/^(\d{1,5})(?:\.(\d{1,2}))?$/', $value, $m)) {
        return '';
    }
    return $m[1] . '.' . str_pad(isset($m[2]) ? $m[2] : '0', 2, '0');
}

function cornwall_content_clean_value($field, $value)
{
    $max = isset($field['max']) ? (int) $field['max'] : 200;
    switch ($field['type']) {
        case 'textarea':
            $value = sanitize_textarea_field(str_replace("\r\n", "\n", is_scalar($value) ? (string) $value : ''));
            return mb_substr(trim(preg_replace("/\n{3,}/", "\n\n", $value)), 0, $max);
        case 'select':
            $value = is_scalar($value) ? (string) $value : '';
            return array_key_exists($value, $field['options']) ? $value : (string) key($field['options']);
        case 'link':
            return cornwall_sanitize_notice_link(is_scalar($value) ? $value : '');
        case 'price':
            return cornwall_content_clean_price(is_scalar($value) ? $value : '');
        case 'media':
            $id = absint(is_scalar($value) ? $value : 0);
            return ($id && wp_attachment_is_image($id)) ? $id : 0;
        default:
            return mb_substr(sanitize_text_field(is_scalar($value) ? (string) $value : ''), 0, $max);
    }
}

/**
 * array('item' => cleaned item, 'blank' => nothing typed, 'missing' => label of
 * the first empty required field or '').
 */
function cornwall_content_clean_item($editor, $raw)
{
    if (! is_array($raw)) {
        return array('item' => null, 'blank' => true, 'missing' => '');
    }
    $id   = isset($raw['id']) && is_scalar($raw['id']) && preg_match('/^[a-z0-9-]{1,24}$/', (string) $raw['id']) ? (string) $raw['id'] : cornwall_content_new_id();
    $show = isset($raw['show']) && is_array($raw['show']) ? array_values(array_intersect($editor['pages'], array_map('strval', $raw['show']))) : array();
    $item = array('id' => $id, 'show' => $show);
    $blank   = true;
    $missing = '';
    foreach ($editor['fields'] as $name => $field) {
        $value       = cornwall_content_clean_value($field, isset($raw[$name]) ? $raw[$name] : '');
        $item[$name] = $value;
        if ($field['type'] !== 'select' && $value !== '' && $value !== 0) {
            $blank = false;
        }
        if (! empty($field['required']) && ($value === '' || $value === 0) && $missing === '') {
            $missing = $field['label'];
        }
    }
    return array('item' => $item, 'blank' => $blank, 'missing' => $missing);
}

/** Every valid saved item in order (the theme defaults until the list is first saved). */
function cornwall_content_items($key)
{
    $editor = cornwall_content_editor($key);
    if (! $editor) {
        return array();
    }
    $saved = get_option($editor['option'], null);
    $rows  = is_array($saved) ? $saved : (array) call_user_func($editor['defaults']);
    $items = array();
    foreach ($rows as $row) {
        try {
            $clean = cornwall_content_clean_item($editor, $row);
            if ($clean['item'] && ! $clean['blank'] && $clean['missing'] === '') {
                $items[] = $clean['item'];
            }
        } catch (Throwable $e) {
            continue;
        }
    }
    return $items;
}

function cornwall_content_items_for($key, $page)
{
    return array_values(array_filter(cornwall_content_items($key), function ($item) use ($page) {
        return in_array($page, $item['show'], true);
    }));
}

/** Items a marker shows on a page (an editor's "select" can filter and limit). */
function cornwall_content_selected($key, $page, $flags)
{
    $editor = cornwall_content_editor($key);
    $items  = cornwall_content_items_for($key, $page);
    return $editor && is_callable($editor['select']) ? array_values((array) call_user_func($editor['select'], $items, $page, $flags)) : $items;
}

/** Write each list once, from the theme defaults, so the screens open with today's content. */
function cornwall_content_seed()
{
    foreach (cornwall_content_editors() as $editor) {
        if (get_option($editor['option'], null) === null) {
            add_option($editor['option'], array_values((array) call_user_func($editor['defaults'])), '', false);
        }
    }
}

/* ---- Placeholders ---- */

function cornwall_content_placeholder_values()
{
    return array(
        'hours'   => cornwall_hours_summary(),
        'closed'  => cornwall_closed_days_sentence(),
        'phone'   => cornwall_biz('phone_display'),
        'rating'  => cornwall_rating_value(),
        'reviews' => (string) cornwall_review_count_total(),
    );
}

/** {hours}, {closed}, {phone}, {rating}, {reviews} and {from:Category} ("from £12"). */
function cornwall_content_fill($text)
{
    if (strpos((string) $text, '{') === false) {
        return (string) $text;
    }
    $values = cornwall_content_placeholder_values();
    $filled = preg_replace_callback('/\{([a-z]+)(?::([^{}]{1,40}))?\}/i', function ($m) use ($values) {
        $name = strtolower($m[1]);
        if ($name === 'from' && isset($m[2])) {
            return cornwall_price_from($m[2]);
        }
        return (! isset($m[2]) && isset($values[$name])) ? $values[$name] : $m[0];
    }, (string) $text);
    return trim(preg_replace('/ {2,}/', ' ', $filled));
}

/** Placeholders in $text that will not fill in, for the save notice. */
function cornwall_content_unfilled($text)
{
    $bad = array();
    if (preg_match_all('/\{([a-z]+)(?::([^{}]{1,40}))?\}/i', (string) $text, $all, PREG_SET_ORDER)) {
        $values = cornwall_content_placeholder_values();
        foreach ($all as $m) {
            $name = strtolower($m[1]);
            $unfilled = ($name === 'from' && isset($m[2])) ? cornwall_price_from($m[2]) === '' : (! isset($values[$name]) || isset($m[2]));
            if ($unfilled) {
                $bad[] = $m[0];
            }
        }
    }
    return $bad;
}

function cornwall_content_text($text)
{
    return esc_html(cornwall_content_fill($text));
}

function cornwall_content_multiline($text)
{
    return nl2br(esc_html(cornwall_content_fill($text)), false);
}

/* ---- Rendering ---- */

function cornwall_content_parse_flags($flags)
{
    $out = array();
    foreach (preg_split('/\s+/', trim((string) $flags), -1, PREG_SPLIT_NO_EMPTY) as $flag) {
        $parts = explode('=', $flag, 2);
        $out[$parts[0]] = isset($parts[1]) ? $parts[1] : true;
    }
    return $out;
}

/** Markup for one marker, or null to keep the fallback between the markers. */
function cornwall_content_render_block($key, $page, $flags)
{
    $editor = cornwall_content_editor($key);
    if (! $editor) {
        return null;
    }
    $items = cornwall_content_selected($key, $page, $flags);
    if (is_callable($editor['render'])) {
        $markup = (string) call_user_func($editor['render'], $items, $page, $flags);
    } else {
        $markup = '';
        foreach ($items as $n => $item) {
            try {
                $markup .= (string) call_user_func($editor['render_item'], $item, $n, $page, $flags);
            } catch (Throwable $e) {
                continue;
            }
        }
    }
    // Block markup (same as the pattern) so WordPress adds the same layout classes and styles.
    return trim($markup) === '' ? '' : do_blocks($markup);
}

function cornwall_content_apply($html, $page)
{
    $html = preg_replace_callback(
        '#<!-- site-([a-z]+):([a-z]+)((?:\s+[a-z0-9=_-]+)*) -->(.*?)<!-- /site-\1:\2 -->#s',
        function ($m) use ($page) {
            if ($m[2] !== $page) {
                return $m[4];
            }
            $flags = cornwall_content_parse_flags($m[3]);
            try {
                $out = cornwall_content_render_block($m[1], $page, $flags);
            } catch (Throwable $e) {
                $out = null;
            }
            if ($out === null) {
                $out = trim($m[4]) === '' ? '' : $m[4];
            }
            if ($out === '' && isset($flags['section'])) {
                return '<!--cornwall-hide-section-->';
            }
            return $out;
        },
        $html
    );
    while (($pos = strpos($html, '<!--cornwall-hide-section-->')) !== false) {
        $html = cornwall_content_remove_section($html, $pos);
    }
    return $html;
}

/** Remove the innermost <section> around $pos (or just the placeholder if there is none). */
function cornwall_content_remove_section($html, $pos)
{
    $mark = strlen('<!--cornwall-hide-section-->');
    preg_match_all('#<(/?)section\b[^>]*>#i', substr($html, 0, $pos), $before, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    $open = array();
    foreach ($before as $tag) {
        if ($tag[1][0] === '') {
            $open[] = $tag[0][1];
        } else {
            array_pop($open);
        }
    }
    if ($open) {
        preg_match_all('#<(/?)section\b[^>]*>#i', $html, $after, PREG_SET_ORDER | PREG_OFFSET_CAPTURE, $pos);
        $depth = 1;
        foreach ($after as $tag) {
            $depth += $tag[1][0] === '' ? 1 : -1;
            if ($depth === 0) {
                return substr($html, 0, end($open)) . substr($html, $tag[0][1] + strlen($tag[0][0]));
            }
        }
    }
    return substr($html, 0, $pos) . substr($html, $pos + $mark);
}

/** Section ids on the homepage that are hidden because their list is empty. */
function cornwall_content_hidden_anchors()
{
    $hidden = array();
    foreach (cornwall_content_editors() as $key => $editor) {
        foreach ($editor['anchor'] as $page => $anchor) {
            try {
                if (! cornwall_content_selected($key, $page, $anchor['flags'])) {
                    $hidden[] = $anchor['id'];
                }
            } catch (Throwable $e) {
                continue;
            }
        }
    }
    return $hidden;
}

function cornwall_content_strip_links($html)
{
    foreach (cornwall_content_hidden_anchors() as $id) {
        $html = preg_replace('#\s*<a\b[^>]*\bhref="/?\#' . preg_quote($id, '#') . '"[^>]*>.*?</a>#s', '', $html);
    }
    return $html;
}

function cornwall_content_render_pattern($html, $block)
{
    $slug = isset($block['attrs']['slug']) ? (string) $block['attrs']['slug'] : '';
    $pages = cornwall_content_pattern_pages();
    try {
        if (isset($pages[$slug])) {
            return cornwall_content_apply($html, $pages[$slug]);
        }
        if ($slug === 'cornwall/header' || $slug === 'cornwall/footer') {
            return cornwall_content_strip_links($html);
        }
    } catch (Throwable $e) {
        return $html;
    }
    return $html;
}
add_filter('render_block_core/pattern', 'cornwall_content_render_pattern', 10, 2);

/* ---- Sitemap lastmod ---- */

function cornwall_content_touch($pages)
{
    $modified = (array) get_option('cornwall_content_modified', array());
    foreach ($pages as $page) {
        $modified[$page] = time();
    }
    update_option('cornwall_content_modified', $modified, false);
}

function cornwall_content_sitemap_entry($entry, $post)
{
    $modified = (array) get_option('cornwall_content_modified', array());
    $page = null;
    if ((int) $post->ID === (int) get_option('page_on_front')) {
        $page = 'home';
    } elseif ($post->post_name === cornwall_wedding_slug()) {
        $page = 'wedding';
    }
    if ($page !== null && ! empty($modified[$page])) {
        $current = isset($entry['lastmod']) ? strtotime($entry['lastmod']) : 0;
        if ((int) $modified[$page] > (int) $current) {
            $entry['lastmod'] = gmdate('Y-m-d\TH:i:s+00:00', (int) $modified[$page]);
        }
    }
    return $entry;
}
add_filter('wp_sitemaps_posts_entry', 'cornwall_content_sitemap_entry', 10, 2);

/* ---- Admin screens ---- */

function cornwall_content_admin_url($key, $args = array())
{
    return add_query_arg(array_merge(array('page' => 'cornwall-content-' . $key), $args), admin_url('admin.php'));
}

function cornwall_content_admin_menu()
{
    $parent = null;
    foreach (cornwall_content_editors() as $key => $editor) {
        $slug   = 'cornwall-content-' . $key;
        $screen = function () use ($key) {
            cornwall_content_admin_page($key);
        };
        if ($parent === null) {
            $parent = $slug;
            add_menu_page('Page content', 'Page content', 'edit_pages', $slug, $screen, 'dashicons-edit-page', 4);
        }
        $hook = add_submenu_page($parent, $editor['title'] . ' – Page content', $editor['title'], 'edit_pages', $slug, $screen);
        add_action('load-' . $hook, function () use ($key) {
            cornwall_content_admin_load($key);
        });
    }
}
add_action('admin_menu', 'cornwall_content_admin_menu');

function cornwall_content_admin_load($key)
{
    if (! current_user_can('edit_pages')) {
        return;
    }
    cornwall_content_seed();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cornwall_content_list'])) {
        cornwall_content_save($key);
    }
    wp_enqueue_media();
}

function cornwall_content_save($key)
{
    check_admin_referer('cornwall_content_' . $key);
    if (! current_user_can('edit_pages')) {
        wp_die('Not allowed.');
    }
    $editor = cornwall_content_editor($key);
    $post   = wp_unslash($_POST);
    $rows   = isset($post['items']) && is_array($post['items']) ? $post['items'] : array();
    $items  = array();
    $errors = array();
    $seen   = array();
    $n      = 0;
    foreach ($rows as $row) {
        $n++;
        $clean = cornwall_content_clean_item($editor, $row);
        if (! $clean['item'] || $clean['blank']) {
            continue;
        }
        if ($clean['missing'] !== '') {
            $errors[] = ucfirst($editor['singular']) . ' ' . $n . ' was left out: it needs a ' . strtolower($clean['missing']) . '.';
            continue;
        }
        $item = $clean['item'];
        if (isset($seen[$item['id']])) {
            $item['id'] = cornwall_content_new_id();
        }
        $seen[$item['id']] = true;
        $items[] = $item;
    }
    update_option($editor['option'], $items, false);

    foreach ($items as $i => $item) {
        foreach ($editor['fields'] as $name => $field) {
            if (in_array($field['type'], array('text', 'textarea'), true)) {
                foreach (cornwall_content_unfilled($item[$name]) as $bad) {
                    $errors[] = ucfirst($editor['singular']) . ' ' . ($i + 1) . ': ' . $bad . ' does not fill in'
                        . (stripos($bad, '{from:') === 0 ? ' (no price in that category yet), so it is left out.' : '. Placeholders are listed at the top.');
                }
            }
        }
    }

    cornwall_content_touch($editor['pages']);
    cornwall_write_root_files();
    cornwall_purge_page_cache();
    if ($errors) {
        set_transient('cornwall_content_errors_' . get_current_user_id(), array_values(array_unique($errors)), 300);
    }
    wp_safe_redirect(cornwall_content_admin_url($key, array('saved' => 1)));
    exit;
}

function cornwall_content_field($i, $name, $field, $value)
{
    $input = 'items[' . $i . '][' . $name . ']';
    $id    = 'cc-' . $i . '-' . $name;
    $max   = isset($field['max']) ? (int) $field['max'] : 200;
    $html  = '<div class="cc-field cc-' . esc_attr($field['type']) . '"><label for="' . esc_attr($id) . '">' . esc_html($field['label']) . '</label>';
    switch ($field['type']) {
        case 'textarea':
            $html .= '<textarea id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" rows="3" maxlength="' . $max . '" data-field="' . esc_attr($name) . '">' . esc_textarea($value) . '</textarea>';
            break;
        case 'select':
            $html .= '<select id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" data-field="' . esc_attr($name) . '">';
            foreach ($field['options'] as $option => $label) {
                $html .= '<option value="' . esc_attr($option) . '"' . selected((string) $value, (string) $option, false) . '>' . esc_html($label) . '</option>';
            }
            $html .= '</select>';
            break;
        case 'media':
            $thumb = $value ? wp_get_attachment_image_url((int) $value, 'thumbnail') : '';
            $html .= '<span class="cc-media" data-media><input type="hidden" id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" value="' . esc_attr($value ? (string) $value : '') . '" />'
                . '<img src="' . esc_url($thumb ? $thumb : '') . '" alt=""' . ($thumb ? '' : ' hidden') . ' data-media-preview />'
                . '<button type="button" class="button" data-media-choose>' . ($thumb ? 'Change image' : 'Choose image') . '</button> '
                . '<button type="button" class="button-link-delete" data-media-remove' . ($thumb ? '' : ' hidden') . '>Remove image</button></span>';
            break;
        case 'price':
            $html .= '<span class="cc-price">£ <input type="text" inputmode="decimal" id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" value="' . esc_attr($value) . '" placeholder="12.50" size="8" data-field="' . esc_attr($name) . '" /></span>';
            break;
        default:
            $list  = isset($field['list']) ? ' list="cc-list-' . esc_attr($name) . '"' : '';
            $html .= '<input type="text" id="' . esc_attr($id) . '" name="' . esc_attr($input) . '" value="' . esc_attr($value) . '" maxlength="' . $max . '" data-field="' . esc_attr($name) . '"' . $list . ' />';
    }
    if (! empty($field['help'])) {
        $html .= '<span class="description">' . esc_html($field['help']) . '</span>';
    }
    return $html . '</div>';
}

function cornwall_content_card($editor, $i, $item)
{
    $title = $editor['title_field'] !== '' && isset($item[$editor['title_field']]) ? $item[$editor['title_field']] : '';
    $html  = '<div class="cc-card" data-card>'
        . '<div class="cc-head"><strong><span data-card-number></span><span data-card-title>' . esc_html($title) . '</span></strong>'
        . '<span class="cc-actions"><button type="button" class="button-link" data-move="up">Move up</button> · <button type="button" class="button-link" data-move="down">Move down</button> · <button type="button" class="button-link-delete" data-remove>Remove</button></span></div>'
        . '<input type="hidden" name="items[' . esc_attr($i) . '][id]" value="' . esc_attr($item['id']) . '" />'
        . '<div class="cc-fields">';
    foreach ($editor['fields'] as $name => $field) {
        $html .= cornwall_content_field($i, $name, $field, isset($item[$name]) ? $item[$name] : '');
    }
    $html .= '</div>';
    if (count($editor['pages']) > 0) {
        $pages = cornwall_content_pages();
        $html .= '<p class="cc-show">Show on: ';
        foreach ($editor['pages'] as $page) {
            $html .= '<label><input type="checkbox" name="items[' . esc_attr($i) . '][show][]" value="' . esc_attr($page) . '" data-show="' . esc_attr($page) . '"' . checked(in_array($page, $item['show'], true), true, false) . ' /> ' . esc_html($pages[$page]) . '</label> ';
        }
        $html .= '</p>';
    }
    return $html . '</div>';
}

function cornwall_content_admin_page($key)
{
    if (! current_user_can('edit_pages')) {
        return;
    }
    $editor = cornwall_content_editor($key);
    $items  = cornwall_content_items($key);
    $errors = get_transient('cornwall_content_errors_' . get_current_user_id());
    delete_transient('cornwall_content_errors_' . get_current_user_id());

    $blank = array('id' => '__id__', 'show' => $editor['new_show']);
    foreach ($editor['fields'] as $name => $field) {
        $blank[$name] = $field['type'] === 'select' ? (string) key($field['options']) : '';
    }
    $counts = array();
    $pages  = cornwall_content_pages();
    foreach ($editor['counts'] as $page => $rule) {
        $counts[] = array_merge(array('page' => $page, 'pageLabel' => $pages[$page], 'field' => '', 'value' => '', 'max' => 0, 'hides' => '', 'what' => $editor['singular'] . 's'), $rule);
    }
    $placeholders = cornwall_content_placeholder_values();
    ?>
<div class="wrap cornwall-content">
  <h1><?php echo esc_html($editor['title']); ?></h1>
  <style>
    .cornwall-content .cc-guide{max-width:980px;background:#fff;border:1px solid #c3c4c7;border-left:4px solid #2f5480;padding:10px 16px;margin:12px 0}
    .cornwall-content .cc-guide p{margin:.4em 0}
    .cornwall-content .cc-guide code{font-size:12px}
    .cornwall-content .cc-status{max-width:980px;margin:8px 0 12px;display:flex;gap:8px;flex-wrap:wrap}
    .cornwall-content .cc-status span{padding:3px 10px;border-radius:999px;background:#e7f4ea;color:#1e5b2c;font-weight:600}
    .cornwall-content .cc-status span.is-warn{background:#fcf0e3;color:#8a4b0b}
    .cornwall-content .cc-card{max-width:980px;background:#fff;border:1px solid #c3c4c7;border-radius:6px;padding:12px 16px;margin:0 0 12px}
    .cornwall-content .cc-head{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:8px}
    .cornwall-content .cc-head strong{font-size:14px}
    .cornwall-content .cc-fields{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px 16px}
    .cornwall-content .cc-field label{display:block;font-weight:600;margin-bottom:3px}
    .cornwall-content .cc-field input[type=text],.cornwall-content .cc-field textarea,.cornwall-content .cc-field select{width:100%}
    .cornwall-content .cc-field.cc-textarea{grid-column:1/-1}
    .cornwall-content .cc-field .description{display:block;margin-top:2px}
    .cornwall-content .cc-price input{width:7em!important}
    .cornwall-content .cc-media img{width:60px;height:60px;object-fit:cover;vertical-align:middle;margin-right:8px;border-radius:4px}
    .cornwall-content .cc-show{margin:10px 0 0}
    .cornwall-content .cc-show label{margin-right:12px}
  </style>

  <?php if (isset($_GET['saved'])) : ?>
    <div class="notice notice-success is-dismissible"><p>Saved. The pages now show these <?php echo esc_html($editor['singular']); ?>s, Google’s structured data is updated, and the page cache was cleared.</p></div>
  <?php endif; ?>
  <?php if (is_array($errors)) : foreach ($errors as $message) : ?>
    <div class="notice notice-warning is-dismissible"><p><?php echo esc_html($message); ?></p></div>
  <?php endforeach; endif; ?>

  <div class="cc-guide">
    <p><?php echo esc_html($editor['guide']); ?></p>
    <p>Each card is one <?php echo esc_html($editor['singular']); ?>, shown in this order. Use Move up / Move down to reorder, Remove to delete, then <strong>Save</strong>. Text only: line breaks in a box become line breaks on the page.</p>
    <p>Placeholders, typed once elsewhere:
      <?php foreach ($placeholders as $name => $value) : ?><code>{<?php echo esc_html($name); ?>}</code> = <?php echo esc_html($value); ?> · <?php endforeach; ?>
      <code>{from:Category}</code> = the lowest price in that Prices category, e.g. “from £12”.</p>
  </div>

  <div class="cc-status" data-status aria-live="polite"></div>

  <form method="post" action="<?php echo esc_url(cornwall_content_admin_url($key)); ?>">
    <?php wp_nonce_field('cornwall_content_' . $key); ?>
    <input type="hidden" name="cornwall_content_list" value="<?php echo esc_attr($key); ?>" />
    <div data-cards data-counts="<?php echo esc_attr(wp_json_encode($counts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)); ?>" data-singular="<?php echo esc_attr(ucfirst($editor['singular'])); ?>">
      <?php foreach ($items as $i => $item) { echo cornwall_content_card($editor, (string) $i, $item); } ?>
    </div>
    <template data-card-template><?php echo cornwall_content_card($editor, '__i__', $blank); ?></template>
    <?php foreach ($editor['fields'] as $name => $field) : if (isset($field['list'])) : ?>
      <datalist id="cc-list-<?php echo esc_attr($name); ?>">
        <?php foreach (array_unique(array_filter(wp_list_pluck($items, $name))) as $option) : ?><option value="<?php echo esc_attr($option); ?>"></option><?php endforeach; ?>
      </datalist>
    <?php endif; endforeach; ?>
    <p><button type="button" class="button" data-add>Add new <?php echo esc_html($editor['singular']); ?></button></p>
    <?php submit_button('Save ' . strtolower($editor['title'])); ?>
  </form>
</div>
<script>
(function () {
  var cards = document.querySelector('[data-cards]');
  var template = document.querySelector('[data-card-template]');
  var status = document.querySelector('[data-status]');
  var rules = JSON.parse(cards.dataset.counts || '[]');
  var next = cards.children.length;

  function update() {
    var list = Array.prototype.slice.call(cards.querySelectorAll('[data-card]'));
    list.forEach(function (card, n) {
      card.querySelector('[data-card-number]').textContent = cards.dataset.singular + ' ' + (n + 1) + (card.querySelector('[data-card-title]').textContent ? ': ' : '');
    });
    status.textContent = '';
    rules.forEach(function (rule) {
      var count = list.filter(function (card) {
        var show = card.querySelector('[data-show="' + rule.page + '"]');
        var field = rule.field ? card.querySelector('[data-field="' + rule.field + '"]') : null;
        return show && show.checked && (!field || field.value === rule.value);
      }).length;
      var pill = document.createElement('span');
      var text = rule.pageLabel + ': ' + count + ' ' + (count === 1 ? rule.what.replace(/s$/, '') : rule.what);
      if (count === 0 && rule.hides) { text += ' — ' + rule.hides; pill.className = 'is-warn'; }
      else if (rule.max && count > rule.max) { text += ' ticked — only the first ' + rule.max + ' show'; pill.className = 'is-warn'; }
      pill.textContent = text;
      status.append(pill);
    });
  }

  document.querySelector('[data-add]').addEventListener('click', function () {
    var id = Math.random().toString(36).slice(2, 10);
    cards.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__i__/g, 'n' + next++).replace(/__id__/g, id));
    var field = cards.lastElementChild.querySelector('input[type=text], textarea');
    if (field) field.focus();
    update();
  });

  cards.addEventListener('click', function (event) {
    var button = event.target.closest('button');
    if (!button) return;
    var card = button.closest('[data-card]');
    if (button.matches('[data-remove]')) card.remove();
    if (button.dataset.move === 'up' && card.previousElementSibling) card.parentNode.insertBefore(card, card.previousElementSibling);
    if (button.dataset.move === 'down' && card.nextElementSibling) card.parentNode.insertBefore(card.nextElementSibling, card);
    var media = button.closest('[data-media]');
    if (media && button.matches('[data-media-remove]')) {
      media.querySelector('input').value = '';
      media.querySelector('[data-media-preview]').hidden = true;
      button.hidden = true;
      media.querySelector('[data-media-choose]').textContent = 'Choose image';
    }
    if (media && button.matches('[data-media-choose]') && window.wp && wp.media) {
      var frame = wp.media({ title: 'Choose an image', library: { type: 'image' }, multiple: false, button: { text: 'Use this image' } });
      frame.on('select', function () {
        var file = frame.state().get('selection').first().toJSON();
        var preview = media.querySelector('[data-media-preview]');
        media.querySelector('input').value = file.id;
        preview.src = file.sizes && file.sizes.thumbnail ? file.sizes.thumbnail.url : file.url;
        preview.hidden = false;
        media.querySelector('[data-media-remove]').hidden = false;
        button.textContent = 'Change image';
      });
      frame.open();
    }
    update();
  });

  cards.addEventListener('input', function (event) {
    var card = event.target.closest('[data-card]');
    var titleField = <?php echo wp_json_encode($editor['title_field']); ?>;
    if (card && titleField && event.target.dataset.field === titleField) card.querySelector('[data-card-title]').textContent = event.target.value;
    update();
  });
  cards.addEventListener('change', update);
  update();
})();
</script>
    <?php
}
