<?php
/**
 * Shop details → Site map: every page, short link and outside destination,
 * where each is linked from, and whether Google is given it. Built from the
 * live theme (sitemap, rendered header/footer and page content) each time the
 * screen opens, so it cannot go out of date.
 */

if (! defined('ABSPATH')) {
    exit;
}

function cornwall_site_map_menu()
{
    add_submenu_page('cornwall-shop', 'Shop details', 'Shop details', 'manage_options', 'cornwall-shop', 'cornwall_shop_details_page');
    add_submenu_page('cornwall-shop', 'Site map', 'Site map', 'manage_options', 'cornwall-site-map', 'cornwall_site_map_page');
}
add_action('admin_menu', 'cornwall_site_map_menu', 11);

/** Page URLs in the WordPress sitemap (what Google is given). */
function cornwall_site_map_listed()
{
    $listed = array();
    $server = function_exists('wp_sitemaps_get_server') ? wp_sitemaps_get_server() : null;
    $provider = $server ? $server->registry->get_provider('posts') : null;
    if ($provider) {
        foreach ($provider->get_url_list(1, 'page') as $entry) {
            $listed[] = cornwall_site_map_path($entry['loc']);
        }
    }
    return $listed;
}

/** "/privacy/" for links on this site, null for anything else. */
function cornwall_site_map_path($href)
{
    $href = html_entity_decode((string) $href, ENT_QUOTES);
    $home = home_url('/');
    if (strpos($href, $home) === 0) {
        $href = '/' . substr($href, strlen($home));
    }
    if ($href === '' || $href[0] !== '/' || strpos($href, '//') === 0) {
        return null;
    }
    $path = (string) strtok($href, '?#');
    return $path === '' ? '/' : $path;
}

/** href => link text for every link in $html. */
function cornwall_site_map_links($html)
{
    $links = array();
    if (preg_match_all('#<a\b[^>]*\bhref="([^"]+)"[^>]*>(.*?)</a>#s', (string) $html, $all, PREG_SET_ORDER)) {
        foreach ($all as $m) {
            $href = html_entity_decode($m[1], ENT_QUOTES);
            if (! isset($links[$href])) {
                $links[$href] = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($m[2])));
            }
        }
    }
    return $links;
}

function cornwall_site_map_render($content)
{
    try {
        return do_blocks((string) $content);
    } catch (Throwable $e) {
        return '';
    }
}

function cornwall_site_map_data()
{
    $listed  = cornwall_site_map_listed();
    $front   = (int) get_option('page_on_front');
    $wedding = cornwall_wedding_slug();
    $pages   = array();
    $posts   = get_posts(array('post_type' => 'page', 'post_status' => array('publish', 'draft', 'pending', 'private', 'future'), 'numberposts' => 50, 'orderby' => 'menu_order title', 'order' => 'ASC'));
    foreach ($posts as $post) {
        $public = $post->post_status === 'publish';
        $path   = $public ? cornwall_site_map_path(get_permalink($post)) : '/' . $post->post_name . '/';
        $pages[$path] = array(
            'title'   => (int) $post->ID === $front ? 'Home' : get_the_title($post),
            'path'    => $path,
            'status'  => $post->post_status,
            'listed'  => in_array($path, $listed, true),
            'landing' => get_page_template_slug($post) === 'landing' || $post->post_name === $wedding,
            'preview' => $public ? '' : get_preview_post_link($post),
            'edit'    => get_edit_post_link($post->ID, 'raw'),
            'html'    => $public ? cornwall_site_map_render($post->post_content) : '',
            'front'   => (int) $post->ID === $front,
        );
    }

    $chrome = array(
        'header' => cornwall_site_map_links(cornwall_site_map_render('<!-- wp:pattern {"slug":"cornwall/header"} /-->')),
        'footer' => cornwall_site_map_links(cornwall_site_map_render('<!-- wp:pattern {"slug":"cornwall/footer"} /-->')),
    );
    $public_count = count(array_filter($pages, function ($page) {
        return $page['status'] === 'publish';
    }));

    // Where each target is linked from: header/footer (every page) or a page's text.
    $from     = array();
    $external = array();
    $note     = function ($target, $where) use (&$from) {
        $from[$target][$where] = true;
    };
    foreach ($chrome as $where => $links) {
        foreach ($links as $href => $text) {
            $path = cornwall_site_map_path($href);
            if ($path !== null) {
                $note($path, $where);
                continue;
            }
            if ($href[0] === '#') {
                continue;
            }
            if (! isset($external[$href])) {
                $external[$href] = $text;
            }
            $note($href, $where);
        }
    }
    foreach ($pages as $page) {
        foreach (cornwall_site_map_links($page['html']) as $href => $text) {
            $path = cornwall_site_map_path($href);
            if ($path === null && $href[0] === '#') {
                continue;
            }
            $target = $path !== null ? $path : $href;
            if ($path === null && ! isset($external[$href])) {
                $external[$href] = $text;
            }
            if ($target !== $page['path']) {
                $note($target, 'page:' . $page['title']);
            }
        }
    }

    $menu = array();
    foreach ($chrome['header'] as $href => $text) {
        if (preg_match('~^/?#([a-z0-9-]+)$~', $href, $m) && $text !== '') {
            $menu[$m[1]] = $text;
        }
    }

    $short = array();
    $hits  = cornwall_review_hits_summary(array('all' => 0));
    foreach (array('/review', '/review/qr', '/review/nfc', '/review/receipt', '/review/web', '/qr', '/nfc') as $path) {
        $source = cornwall_review_source_for_path($path);
        $short[] = array(
            'path'  => $path,
            'label' => (isset(cornwall_review_sources()[$source]) ? cornwall_review_sources()[$source] : $source) . (in_array($path, array('/qr', '/nfc'), true) ? ' (short form)' : ''),
            'scans' => isset($hits['rows'][$source]['all']) ? (int) $hits['rows'][$source]['all'] : 0,
        );
    }

    return array(
        'pages'    => $pages,
        'from'     => $from,
        'external' => $external,
        'menu'     => $menu,
        'short'    => $short,
        'public'   => $public_count,
        'listed'   => count($listed),
    );
}

/** "Linked from footer on every page; text on Home" */
function cornwall_site_map_from_text($from, $target)
{
    if (empty($from[$target])) {
        return 'Not linked from any page yet';
    }
    $parts  = array();
    $chrome = array_values(array_filter(array('header', 'footer'), function ($where) use ($from, $target) {
        return isset($from[$target][$where]);
    }));
    if ($chrome) {
        $parts[] = implode(' and ', $chrome) . ' on every page';
    }
    $pages = array();
    foreach (array_keys($from[$target]) as $where) {
        if (strpos($where, 'page:') === 0) {
            $pages[] = substr($where, 5);
        }
    }
    if ($pages) {
        $parts[] = 'text on ' . (count($pages) <= 2 ? implode(' and ', $pages) : count($pages) . ' pages');
    }
    return 'Linked from ' . implode('; ', $parts);
}

function cornwall_site_map_external_label($href, $text)
{
    $known = array(
        cornwall_biz('maps_url')       => 'Google Maps listing',
        cornwall_biz('directions_url') => 'Directions (Google Maps)',
        cornwall_review_url()          => 'Google review form',
    );
    if (isset($known[$href])) {
        return $known[$href];
    }
    if (strpos($href, 'tel:') === 0) {
        return 'Phone call';
    }
    if (strpos($href, 'mailto:') === 0) {
        return 'Email';
    }
    if (strpos($href, 'openstreetmap.org') !== false) {
        return 'OpenStreetMap credit';
    }
    return $text !== '' ? $text : (string) wp_parse_url($href, PHP_URL_HOST);
}

function cornwall_site_map_badge($page)
{
    if ($page['status'] !== 'publish') {
        return '<span class="sm-badge is-off">Draft · not on Google</span>';
    }
    return $page['listed'] ? '<span class="sm-badge">On Google</span>' : '<span class="sm-badge is-off">Kept off Google</span>';
}

function cornwall_site_map_card($page, $from)
{
    $url  = home_url($page['path']);
    $html = '<div class="sm-card"><strong>' . esc_html($page['title']) . '</strong>';
    if ($page['status'] === 'publish') {
        $html .= '<a href="' . esc_url($url) . '" target="_blank" rel="noreferrer">' . esc_html($url) . '</a>';
    } else {
        $html .= '<code>' . esc_html($url) . '</code>';
    }
    $html .= cornwall_site_map_badge($page);
    $html .= '<span class="sm-from">' . esc_html($page['status'] === 'publish' ? cornwall_site_map_from_text($from, $page['path']) : 'Publish it to put it on the site and in the sitemap') . '</span>';
    $links = array();
    if ($page['preview']) {
        $links[] = '<a href="' . esc_url($page['preview']) . '" target="_blank" rel="noreferrer">Preview</a>';
    }
    if ($page['edit']) {
        $links[] = '<a href="' . esc_url($page['edit']) . '">Edit</a>';
    }
    return $html . ($links ? '<span class="sm-actions">' . implode(' · ', $links) . '</span>' : '') . '</div>';
}

function cornwall_site_map_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }
    $data   = cornwall_site_map_data();
    $pages  = $data['pages'];
    $home   = null;
    $groups = array('Landing pages' => array(), 'Shop pages' => array(), 'Kept off Google' => array());
    foreach ($pages as $page) {
        if ($page['front']) {
            $home = $page;
        } elseif ($page['status'] !== 'publish' || ! $page['listed']) {
            $groups['Kept off Google'][] = $page;
        } elseif ($page['landing']) {
            $groups['Landing pages'][] = $page;
        } else {
            $groups['Shop pages'][] = $page;
        }
    }
    $off = count($groups['Kept off Google']) + 2;
    $sitemap = home_url('/wp-sitemap.xml');
    $hidden  = cornwall_content_hidden_anchors();
    ?>
<div class="wrap cornwall-site-map">
  <style>
    .cornwall-site-map h1{display:flex;align-items:center;gap:16px}
    .cornwall-site-map .sm-intro{max-width:980px}
    .cornwall-site-map .sm-home{width:260px;margin:18px auto 8px;padding:14px 16px;background:#003868;color:#fff;border-radius:8px;text-align:center}
    .cornwall-site-map .sm-home strong{display:block;font-size:14px}
    .cornwall-site-map .sm-home a{color:#fff;font-family:monospace}
    .cornwall-site-map .sm-home .sm-badge{background:#fff;color:#003868}
    .cornwall-site-map .sm-menu{display:flex;justify-content:center;flex-wrap:wrap;gap:8px;margin:6px 0 4px}
    .cornwall-site-map .sm-chip{padding:4px 14px;border-radius:999px;background:#e8eef4;color:#003868}
    .cornwall-site-map .sm-chip.is-off{background:#f6f7f7;color:#8c8f94;text-decoration:line-through}
    .cornwall-site-map .sm-caption{text-align:center;color:#646970;margin:4px 0 18px}
    .cornwall-site-map h2{border-top:1px solid #dcdcde;padding-top:16px;margin-top:22px}
    .cornwall-site-map .sm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:10px;max-width:1100px}
    .cornwall-site-map .sm-card{display:flex;flex-direction:column;gap:6px;background:#fff;border:1px solid #c3c4c7;border-radius:6px;padding:10px 12px}
    .cornwall-site-map .sm-card a,.cornwall-site-map .sm-card code{word-break:break-all;font-family:monospace;font-size:12px}
    .cornwall-site-map .sm-badge{align-self:flex-start;padding:2px 10px;border-radius:999px;background:#e7f4ea;color:#1e5b2c;font-size:12px}
    .cornwall-site-map .sm-home .sm-badge{align-self:center;display:inline-block;margin-top:6px}
    .cornwall-site-map .sm-badge.is-off{background:#fcf0e3;color:#8a4b0b}
    .cornwall-site-map .sm-from{color:#646970;font-size:12px}
    .cornwall-site-map .sm-actions{font-size:12px}
    @media print{#adminmenumain,#wpadminbar,#wpfooter,.cornwall-site-map .button,.notice{display:none!important}#wpcontent{margin-left:0!important}.cornwall-site-map .sm-card{break-inside:avoid}}
  </style>
  <h1>Site map <button type="button" class="button" onclick="window.print()">Print</button></h1>
  <p class="sm-intro"><strong><?php echo (int) $data['listed']; ?> pages on Google</strong> (listed in <a href="<?php echo esc_url($sitemap); ?>" target="_blank" rel="noreferrer"><?php echo esc_html($sitemap); ?></a>), <?php echo (int) $off; ?> kept off Google, <?php echo count($data['short']); ?> review short links and <?php echo count($data['external']); ?> outside destinations.<br>Drawn from the theme each time you open this page.</p>

  <?php if ($home) : ?>
  <div class="sm-home">
    <strong>Home</strong>
    <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noreferrer"><?php echo esc_html(home_url('/')); ?></a><br>
    <?php echo cornwall_site_map_badge($home); ?>
  </div>
  <div class="sm-menu">
    <?php foreach ($data['menu'] as $id => $label) : ?><span class="sm-chip"><?php echo esc_html($label); ?></span><?php endforeach; ?>
    <?php foreach (array('services' => 'Services', 'reviews' => 'Reviews') as $id => $label) : if (in_array($id, $hidden, true)) : ?><span class="sm-chip is-off" title="Hidden: its Page content list is empty"><?php echo esc_html($label); ?></span><?php endif; endforeach; ?>
  </div>
  <p class="sm-caption">Header menu: sections on the homepage. <?php echo in_array('prices', $hidden, true) ? 'Prices appears after Services once a price is added under Page content → Prices.' : 'Prices shows after Services.'; ?></p>
  <?php endif; ?>

  <?php foreach ($groups as $title => $list) : ?>
    <h2><?php echo esc_html($title); ?></h2>
    <div class="sm-grid">
      <?php foreach ($list as $page) { echo cornwall_site_map_card($page, $data['from']); } ?>
      <?php if ($title === 'Landing pages' && ! $list) : ?><p class="description">None published yet.</p><?php endif; ?>
      <?php if ($title === 'Kept off Google') : ?>
        <div class="sm-card"><strong>For AI assistants</strong><a href="<?php echo esc_url(home_url('/llms.txt')); ?>" target="_blank" rel="noreferrer"><?php echo esc_html(home_url('/llms.txt')); ?></a><span class="sm-badge is-off">Kept off Google</span><span class="sm-from">Shop facts in plain text; noindex</span></div>
        <div class="sm-card"><strong>Page not found</strong><code><?php echo esc_html(home_url('/any-missing-page/')); ?></code><span class="sm-badge is-off">Kept off Google</span><span class="sm-from">Shown for any address that does not exist; noindex</span></div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <h2>Review short links</h2>
  <p class="description">Each one sends people straight to the <?php echo esc_html(cornwall_site_map_external_label(cornwall_review_url(), '')); ?> (302, not indexed). Destination and scan counts: Shop details → Review links.</p>
  <div class="sm-grid">
    <?php foreach ($data['short'] as $row) : ?>
      <div class="sm-card"><strong><?php echo esc_html($row['label']); ?></strong><a href="<?php echo esc_url(home_url($row['path'])); ?>" target="_blank" rel="noreferrer"><?php echo esc_html(home_url($row['path'])); ?></a><span class="sm-from"><?php echo esc_html($row['scans'] . ' ' . ($row['scans'] === 1 ? 'scan' : 'scans') . ' so far for this source'); ?></span></div>
    <?php endforeach; ?>
  </div>

  <h2>Outside destinations</h2>
  <div class="sm-grid">
    <?php foreach ($data['external'] as $href => $text) : ?>
      <div class="sm-card"><strong><?php echo esc_html(cornwall_site_map_external_label($href, $text)); ?></strong><code><?php echo esc_html(preg_match('/^(tel|mailto):/', $href) ? preg_replace('/^mailto:/', '', $href) : (string) wp_parse_url($href, PHP_URL_HOST)); ?></code><span class="sm-from"><?php echo esc_html(cornwall_site_map_from_text($data['from'], $href)); ?></span></div>
    <?php endforeach; ?>
  </div>
</div>
    <?php
}
