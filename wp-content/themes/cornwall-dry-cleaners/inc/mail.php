<?php
/**
 * Outgoing email through the Google Workspace SMTP relay (smtp-relay.gmail.com).
 * Google accepts it by the server's IP address (Admin console → Gmail →
 * Routing → SMTP relay service), so no password is stored. Only this site uses
 * it; the other apps on the Cloudways server keep their own mail setup.
 */

if (! defined('ABSPATH')) {
    exit;
}

function cornwall_smtp_relay_host()
{
    return 'smtp-relay.gmail.com';
}

function cornwall_smtp_relay_on()
{
    return (bool) get_option('cornwall_smtp_relay', false);
}

/** Live site only: Google only accepts the live server's IP, and staging should not send as the shop. */
function cornwall_smtp_relay_active()
{
    return cornwall_smtp_relay_on() && cornwall_is_live();
}

function cornwall_smtp_relay_configure($phpmailer)
{
    if (! cornwall_smtp_relay_active()) {
        return;
    }
    $domain = cornwall_biz('domain');
    $phpmailer->isSMTP();
    $phpmailer->Host        = cornwall_smtp_relay_host();
    $phpmailer->Port        = 587;
    $phpmailer->SMTPSecure  = 'tls';
    $phpmailer->SMTPAutoTLS = true;
    $phpmailer->SMTPAuth    = false;
    $phpmailer->Timeout     = 20;
    // Google rejects EHLO "localhost"; the relay is set to "only addresses in my domains".
    $phpmailer->Hostname = $domain;
    $phpmailer->Helo     = $domain;
    if (! preg_match('/@' . preg_quote($domain, '/') . '$/i', (string) $phpmailer->From)) {
        $phpmailer->From = 'wordpress@' . $domain;
    }
    if ($phpmailer->FromName === '' || $phpmailer->FromName === 'WordPress') {
        $phpmailer->FromName = cornwall_biz('name') . ' website';
    }
}
add_action('phpmailer_init', 'cornwall_smtp_relay_configure', 20);

function cornwall_mail_record_failure($error)
{
    $message = is_wp_error($error) ? $error->get_error_message() : 'Unknown error';
    update_option('cornwall_mail_status', array('ok' => false, 'time' => time(), 'message' => mb_substr(trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($message))), 0, 400)), false);
}
add_action('wp_mail_failed', 'cornwall_mail_record_failure');

function cornwall_mail_record_success()
{
    update_option('cornwall_mail_status', array('ok' => true, 'time' => time(), 'message' => ''), false);
}
add_action('wp_mail_succeeded', 'cornwall_mail_record_success');

/** Status line for Shop details. */
function cornwall_mail_status_text()
{
    $host  = wp_parse_url(home_url(), PHP_URL_HOST);
    $route = cornwall_smtp_relay_active()
        ? 'Email goes through ' . cornwall_smtp_relay_host() . ' (port 587, TLS) as wordpress@' . cornwall_biz('domain') . '.'
        : (cornwall_smtp_relay_on()
            ? 'The relay is ticked, but this copy (' . $host . ') is not the live site, so email uses the server’s own mail here.'
            : 'Email uses the server’s own mail.');
    $status = get_option('cornwall_mail_status');
    if (is_array($status) && ! empty($status['time'])) {
        $when   = wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) $status['time']);
        $route .= $status['ok'] ? ' Last email sent ' . $when . '.' : ' Last email failed ' . $when . ': ' . $status['message'];
    }
    return $route;
}
