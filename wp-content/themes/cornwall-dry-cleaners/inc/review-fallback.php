<?php
/**
 * Plugin Name: Cornwall Review Link Fallback
 * Description: Keeps /review, /review/qr, /review/nfc, /review/receipt, /review/web, /qr and /nfc redirecting to the Google review form even if the theme is rolled back, a plugin triggers a fatal error, or the database is down.
 *
 * Single source for the short review links. The theme loads this file and
 * copies it to wp-content/mu-plugins/cornwall-review-fallback.php on every
 * version refresh; wp-content/db-error.php includes the mu-plugin copy.
 * Every function is guarded because an older mu-plugin copy may already
 * have declared some of them earlier in the same request.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! defined('CORNWALL_REVIEW_URL')) {
    define('CORNWALL_REVIEW_URL', 'https://search.google.com/local/writereview?placeid=ChIJ81BJSewTdkgR1Ru6ekdBmKw');
}

if (! function_exists('cornwall_review_source_for_path')) {
    /**
     * Map a request path to a scan source key, or null if it is not a review link.
     */
    function cornwall_review_source_for_path($path)
    {
        $path = strtolower(trim((string) $path, '/'));
        if ($path === 'review') {
            return 'typed';
        }
        if ($path === 'qr' || $path === 'nfc') {
            return $path;
        }
        if (strpos($path, 'review/') !== 0) {
            return null;
        }
        $tag = substr($path, strlen('review/'));
        return in_array($tag, array('qr', 'nfc', 'receipt', 'web'), true) ? $tag : 'typed';
    }
}

if (! function_exists('cornwall_review_fallback_source')) {
    function cornwall_review_fallback_source()
    {
        if (function_exists('is_admin') && is_admin()) {
            return null;
        }
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        return cornwall_review_source_for_path((string) strtok($uri, '?'));
    }
}

if (! function_exists('cornwall_review_fallback_target')) {
    function cornwall_review_fallback_target($use_db = true)
    {
        if ($use_db && function_exists('get_option')) {
            try {
                $opt = (string) get_option('cornwall_review_url', '');
                if ($opt !== '' && strpos($opt, 'https://') === 0) {
                    return $opt;
                }
            } catch (\Throwable $e) {
            }
        }
        return defined('CORNWALL_REVIEW_FALLBACK_URL') ? CORNWALL_REVIEW_FALLBACK_URL : CORNWALL_REVIEW_URL;
    }
}

if (! function_exists('cornwall_review_fallback_send')) {
    function cornwall_review_fallback_send($reason = 'Cornwall review fallback', $use_db = true)
    {
        if (! empty($GLOBALS['cornwall_review_redirected'])) {
            return;
        }
        $GLOBALS['cornwall_review_redirected'] = true;
        $target = cornwall_review_fallback_target($use_db);
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        if (! headers_sent()) {
            header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
            header('X-Robots-Tag: noindex, nofollow');
            header('X-Redirect-By: ' . $reason);
            header('Location: ' . $target, true, 302);
        } else {
            $esc = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');
            echo '<!doctype html><meta http-equiv="refresh" content="0;url=' . $esc . '"><script>location.replace(' . json_encode($target) . ');</script>';
        }
        exit;
    }
}

if (! defined('CORNWALL_REVIEW_FALLBACK_NO_HOOKS') && empty($GLOBALS['cornwall_review_fallback_hooked']) && cornwall_review_fallback_source() !== null) {
    $GLOBALS['cornwall_review_fallback_hooked'] = true;
    @ob_start();

    add_filter('wp_php_error_message', function ($message) {
        cornwall_review_fallback_send('Cornwall review fallback (php-error)');
        return $message;
    }, 1);

    add_filter('wp_die_handler', function () {
        return function () {
            cornwall_review_fallback_send('Cornwall review fallback (wp-die)');
        };
    }, 1);

    add_action('template_redirect', function () {
        if (! empty($GLOBALS['cornwall_review_redirected'])) {
            return;
        }
        $src = cornwall_review_fallback_source();
        if ($src !== null && function_exists('cornwall_review_should_count') && function_exists('cornwall_review_count')) {
            try {
                if (cornwall_review_should_count()) {
                    cornwall_review_count($src);
                }
            } catch (\Throwable $e) {
            }
        }
        cornwall_review_fallback_send('Cornwall review fallback (template)');
    }, 999);

    register_shutdown_function(function () {
        if (empty($GLOBALS['cornwall_review_redirected'])) {
            cornwall_review_fallback_send('Cornwall review fallback (shutdown)');
        }
    });
}
