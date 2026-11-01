<?php
/**
 * Shop facts used by the patterns, structured data, llms.txt, emails and
 * script.js. Name, phone and address live here only (they must match the
 * Google Business Profile). Hours, special days, the notice and the rating are
 * edited in WordPress under Shop details.
 */

if (! defined('ABSPATH')) {
    exit;
}

function cornwall_business()
{
    return array(
        'name'           => 'Cornwall Dry Cleaners',
        'legal_name'     => 'Cornwall Dry Cleaners Ltd',
        'copyright_name' => 'Cornwall Dry Cleaners Limited',
        'owner'          => 'Gina Zhou',
        'founded'        => '2003',
        'domain'         => 'cornwalldrycleaners.com',
        'email'          => 'gina@cornwalldrycleaners.com',
        'phone'          => '+442089919413',
        'phone_display'  => '020 8991 9413',
        'phone_intl'     => '+44 20 8991 9413',
        'street'         => '23 Haven Lane',
        'locality'       => 'Ealing',
        'postal_town'    => 'London',
        'postcode'       => 'W5 2HZ',
        'country'        => 'GB',
        'lat'            => 51.5166877,
        'lng'            => -0.300401,
        'maps_url'       => 'https://maps.app.goo.gl/cw7sznF2TJq2SkL69',
        'place_id'       => 'ChIJ81BJSewTdkgR1Ru6ekdBmKw',
        'directions_url' => 'https://www.google.com/maps/dir/?api=1&destination=Cornwall+Dry+Cleaners&destination_place_id=ChIJ81BJSewTdkgR1Ru6ekdBmKw',
        'hours'          => cornwall_opening_hours(),
    );
}

/** Default weekly hours, Monday first. 0 = Sunday … 6 = Saturday. null = closed. */
function cornwall_default_hours()
{
    return array(
        1 => array('08:00', '18:00'),
        2 => array('08:00', '18:00'),
        3 => array('08:00', '18:00'),
        4 => array('08:00', '18:00'),
        5 => array('08:00', '18:00'),
        6 => array('09:00', '17:00'),
        0 => null,
    );
}

function cornwall_valid_time($value)
{
    $value = trim((string) $value);
    return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) ? $value : '';
}

/** Weekly hours from Shop details, Monday first, falling back to the defaults per day. */
function cornwall_opening_hours()
{
    $saved = get_option('cornwall_hours');
    $hours = array();
    foreach (cornwall_default_hours() as $day => $default) {
        $span = (is_array($saved) && array_key_exists($day, $saved)) ? $saved[$day] : $default;
        if (is_array($span) && count($span) === 2 && cornwall_valid_time($span[0]) !== '' && cornwall_valid_time($span[1]) !== '' && strcmp($span[1], $span[0]) > 0) {
            $hours[$day] = array($span[0], $span[1]);
        } else {
            $hours[$day] = $span === null ? null : $default;
        }
    }
    return $hours;
}

function cornwall_biz($key)
{
    $b = cornwall_business();
    return isset($b[$key]) ? $b[$key] : '';
}

/** True only on the live domain, not on staging or local copies. */
function cornwall_is_live()
{
    $host = strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
    return in_array($host, array(cornwall_biz('domain'), 'www.' . cornwall_biz('domain')), true);
}

/** "23 Haven Lane, Ealing, London W5 2HZ" */
function cornwall_address_line()
{
    return cornwall_biz('street') . ', ' . cornwall_biz('locality') . ', ' . cornwall_biz('postal_town') . ' ' . cornwall_biz('postcode');
}

function cornwall_google_place_url()
{
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(cornwall_biz('name')) . '&query_place_id=' . cornwall_biz('place_id');
}

function cornwall_day_names()
{
    return array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
}

/** "08:00" → "8:00am" (table) or "8am" (summary). */
function cornwall_format_time($hhmm, $with_minutes = true)
{
    list($h, $m) = array_map('intval', explode(':', $hhmm));
    $suffix = $h >= 12 ? 'pm' : 'am';
    $twelve = $h % 12 === 0 ? 12 : $h % 12;
    if (! $with_minutes && $m === 0) {
        return $twelve . $suffix;
    }
    return $twelve . ':' . sprintf('%02d', $m) . $suffix;
}

/**
 * Consecutive Monday-first days with the same hours.
 * Each group: array('days' => array(1, 2, …), 'hours' => array('08:00', '18:00') | null).
 */
function cornwall_hours_groups()
{
    $groups = array();
    foreach (cornwall_biz('hours') as $day => $span) {
        $last = count($groups) - 1;
        if ($last >= 0 && $groups[$last]['hours'] === $span) {
            $groups[$last]['days'][] = $day;
            continue;
        }
        $groups[] = array('days' => array($day), 'hours' => $span);
    }
    return $groups;
}

/**
 * "Monday–Friday 8am–6pm, Saturday 9am–5pm, Sunday closed" ($short = false)
 * "Mon–Fri 8am–6pm · Sat 9am–5pm" ($short = true, closed days left out)
 */
function cornwall_hours_summary($short = false)
{
    $names = cornwall_day_names();
    $parts = array();
    foreach (cornwall_hours_groups() as $group) {
        $first = $names[$group['days'][0]];
        $last  = $names[end($group['days'])];
        if ($short) {
            $first = substr($first, 0, 3);
            $last  = substr($last, 0, 3);
        }
        $label = count($group['days']) > 1 ? $first . '–' . $last : $first;
        if ($group['hours'] === null) {
            if (! $short) {
                $parts[] = $label . ' closed';
            }
            continue;
        }
        $parts[] = $label . ' ' . cornwall_format_time($group['hours'][0], false) . '–' . cornwall_format_time($group['hours'][1], false);
    }
    return implode($short ? ' · ' : ', ', $parts);
}

function cornwall_services()
{
    return array(
        array('Dry cleaning', 'Suits, coats and office wear, cleaned and pressed.'),
        array('Stain removal', 'We inspect first and say honestly what will come out.'),
        array('Laundry', 'Service washes for homes and Airbnb hosts, on time.'),
        array('Repairs & alterations', 'Hems, zips, stitching and loose buttons.'),
        array('Formal & luxury pieces', 'Wedding and cocktail pieces, wool and restored goods.'),
        array('Express service', 'Often next day if dropped in by 10am. Ask at the counter.'),
    );
}

function cornwall_faqs()
{
    return array(
        array('How long does it take?', 'Often next day if dropped in by 10am. Repairs and wedding pieces can take longer — we will say when you drop off.'),
        array('Collect or deliver?', 'No. Drop in on Haven Lane.'),
        array('Cards or cash?', 'Both. Contactless at the counter.'),
        array('Bank holidays?', 'Closed on bank holidays. Call the day before if you are unsure.'),
        array('Not sure we can take it?', 'Bring it in. We inspect first and say honestly what will come out.'),
    );
}

/** "Sundays" / "Mondays and Sundays" from the weekly hours, '' if open every day. */
function cornwall_closed_days_list()
{
    $names = array();
    foreach (cornwall_biz('hours') as $day => $span) {
        if ($span === null) {
            $names[] = cornwall_day_names()[$day] . 's';
        }
    }
    if (count($names) < 2) {
        return implode('', $names);
    }
    $last = array_pop($names);
    return implode(', ', $names) . ' and ' . $last;
}

/** "Closed Sundays and bank holidays." ($we = false) or "We are closed on Sundays and bank holidays." */
function cornwall_closed_days_sentence($we = false)
{
    $days = cornwall_closed_days_list();
    $what = $days !== '' ? $days . ' and bank holidays' : 'bank holidays';
    return $we ? 'We are closed on ' . $what . '.' : 'Closed ' . ($days !== '' ? $what : 'on bank holidays') . '.';
}

function cornwall_site_description()
{
    return 'Independent dry cleaners on Haven Lane, Ealing. Suits, coats, laundry and repairs — often next day if dropped in by 10am. Call ' . cornwall_biz('phone_display') . '.';
}

function cornwall_rating_value()
{
    $val = trim((string) get_option('cornwall_rating_value', '4.9'));
    return preg_match('/^[1-5](\.[0-9])?$/', $val) ? $val : '4.9';
}

function cornwall_review_count_total()
{
    $val = (int) get_option('cornwall_review_count_total', 45);
    return $val > 0 ? (string) $val : '45';
}

/* ---- Bank holidays (England and Wales) ---- */

function cornwall_bank_holidays_fallback()
{
    return array(
        '2026-01-01', '2026-04-03', '2026-04-06', '2026-05-04', '2026-05-25', '2026-08-31', '2026-12-25', '2026-12-28',
        '2027-01-01', '2027-03-26', '2027-03-29', '2027-05-03', '2027-05-31', '2027-08-30', '2027-12-27', '2027-12-28',
        '2028-01-03', '2028-04-14', '2028-04-17', '2028-05-01', '2028-05-29', '2028-08-28', '2028-12-25', '2028-12-26',
    );
}

/** Y-m-d dates, sorted. GOV.UK copy when available, built-in list otherwise. */
function cornwall_bank_holidays()
{
    $stored = get_option('cornwall_bank_holidays');
    if (is_array($stored) && ! empty($stored['dates']) && is_array($stored['dates'])) {
        return $stored['dates'];
    }
    return cornwall_bank_holidays_fallback();
}

/** Weekly cron: refresh from https://www.gov.uk/bank-holidays.json. */
function cornwall_refresh_bank_holidays()
{
    $res = wp_remote_get('https://www.gov.uk/bank-holidays.json', array('timeout' => 10));
    if (is_wp_error($res) || (int) wp_remote_retrieve_response_code($res) !== 200) {
        return false;
    }
    $json = json_decode((string) wp_remote_retrieve_body($res), true);
    if (! isset($json['england-and-wales']['events']) || ! is_array($json['england-and-wales']['events'])) {
        return false;
    }
    $from  = ((int) wp_date('Y') - 1) . '-01-01';
    $dates = array();
    foreach ($json['england-and-wales']['events'] as $event) {
        $date = isset($event['date']) ? (string) $event['date'] : '';
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strcmp($date, $from) >= 0) {
            $dates[$date] = true;
        }
    }
    $dates = array_keys($dates);
    sort($dates);
    if (empty($dates)) {
        return false;
    }
    $changed = cornwall_bank_holidays() !== $dates;
    update_option('cornwall_bank_holidays', array('dates' => $dates, 'fetched' => time()), false);
    if ($changed) {
        cornwall_purge_page_cache();
    }
    return true;
}
add_action('cornwall_bank_holidays_refresh', 'cornwall_refresh_bank_holidays');

/* ---- Special days and the notice (Shop details) ---- */

function cornwall_valid_date($value)
{
    $value = trim((string) $value);
    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return '';
    }
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $value, wp_timezone());
    return ($d && $d->format('Y-m-d') === $value) ? $value : '';
}

/**
 * Saved special days that have not ended yet, sorted by start date.
 * Each: array('from', 'to', 'closed' => bool, 'opens', 'closes', 'name').
 */
function cornwall_special_days($include_past = false)
{
    $saved = get_option('cornwall_special_days');
    $today = wp_date('Y-m-d');
    $days = array();
    foreach ((array) $saved as $row) {
        if (! is_array($row)) {
            continue;
        }
        $from = cornwall_valid_date(isset($row['from']) ? $row['from'] : '');
        if ($from === '') {
            continue;
        }
        $to = cornwall_valid_date(isset($row['to']) ? $row['to'] : '');
        if ($to === '' || strcmp($to, $from) < 0) {
            $to = $from;
        }
        if (! $include_past && strcmp($to, $today) < 0) {
            continue;
        }
        $opens  = cornwall_valid_time(isset($row['opens']) ? $row['opens'] : '');
        $closes = cornwall_valid_time(isset($row['closes']) ? $row['closes'] : '');
        $closed = ! empty($row['closed']) || $opens === '' || $closes === '' || strcmp($closes, $opens) <= 0;
        $days[] = array(
            'from'   => $from,
            'to'     => $to,
            'closed' => $closed,
            'opens'  => $closed ? '' : $opens,
            'closes' => $closed ? '' : $closes,
            'name'   => isset($row['name']) ? sanitize_text_field((string) $row['name']) : '',
        );
    }
    usort($days, function ($a, $b) {
        return strcmp($a['from'], $b['from']);
    });
    return $days;
}

/**
 * Date => special day for every upcoming special date (up to a year ahead).
 * Later rows win where ranges overlap.
 */
function cornwall_special_dates()
{
    $tz    = wp_timezone();
    $today = new DateTimeImmutable('today', $tz);
    $limit = $today->modify('+1 year')->format('Y-m-d');
    $dates = array();
    foreach (cornwall_special_days() as $row) {
        $day = DateTimeImmutable::createFromFormat('!Y-m-d', $row['from'], $tz);
        for ($i = 0; $day && $i < 370; $i++, $day = $day->modify('+1 day')) {
            $ymd = $day->format('Y-m-d');
            if (strcmp($ymd, $row['to']) > 0 || strcmp($ymd, $limit) > 0) {
                break;
            }
            if (strcmp($ymd, $today->format('Y-m-d')) >= 0) {
                $dates[$ymd] = $row;
            }
        }
    }
    ksort($dates);
    return $dates;
}

function cornwall_holiday_notice_raw()
{
    return trim((string) get_option('cornwall_holiday_notice', ''));
}

/** array('from' => 'Y-m-d' | '', 'to' => 'Y-m-d' | '') for when the notice shows. */
function cornwall_notice_window()
{
    return array(
        'from' => cornwall_valid_date(get_option('cornwall_notice_from', '')),
        'to'   => cornwall_valid_date(get_option('cornwall_notice_to', '')),
    );
}

/** Colour slug => array(label, background, text). Brand palette only. */
function cornwall_notice_colours()
{
    return array(
        'mist' => array('Light blue', '#E8EEF4', '#122033'),
        'blue' => array('Blue', '#2F5480', '#FFFFFF'),
        'navy' => array('Navy', '#00294d', '#FFFFFF'),
        'ink'  => array('Dark', '#122033', '#FFFFFF'),
    );
}

/** "/visit", "#faq", https://…, tel: or mailto:, else ''. */
function cornwall_sanitize_notice_link($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (preg_match('#^(/(?!/)|\#)#', $value)) {
        return preg_match('#^[^\s<>"\']+$#', $value) ? $value : '';
    }
    return (string) esc_url_raw($value, array('https', 'http', 'tel', 'mailto'));
}

/** Notice banner settings. Before 1.6.2 a saved message always showed, so "on" defaults to having one. */
function cornwall_notice_settings()
{
    $saved   = get_option('cornwall_notice_banner');
    $saved   = is_array($saved) ? $saved : array();
    $colours = cornwall_notice_colours();
    $colour  = isset($saved['colour']) && isset($colours[$saved['colour']]) ? $saved['colour'] : 'mist';
    $days    = isset($saved['auto_days']) ? (int) $saved['auto_days'] : 14;
    return array(
        'on'        => array_key_exists('on', $saved) ? (bool) $saved['on'] : cornwall_holiday_notice_raw() !== '',
        'link_text' => isset($saved['link_text']) ? (string) $saved['link_text'] : '',
        'link'      => isset($saved['link']) ? cornwall_sanitize_notice_link($saved['link']) : '',
        'colour'    => $colour,
        'auto'      => array_key_exists('auto', $saved) ? (bool) $saved['auto'] : true,
        'auto_days' => max(1, min(60, $days)),
    );
}

/** The notice if it is switched on and today is inside its show-from / show-until dates, else ''. */
function cornwall_holiday_notice()
{
    $notice = cornwall_holiday_notice_raw();
    $window = cornwall_notice_window();
    $today  = wp_date('Y-m-d');
    if ($notice === '' || ! cornwall_notice_settings()['on'] || ($window['from'] !== '' && strcmp($today, $window['from']) < 0) || ($window['to'] !== '' && strcmp($today, $window['to']) > 0)) {
        return '';
    }
    return $notice;
}

/** "Thu 25 Dec" or "Thu 24 Dec – Sat 26 Dec". */
function cornwall_special_day_label($row)
{
    $tz   = wp_timezone();
    $from = DateTimeImmutable::createFromFormat('!Y-m-d', $row['from'], $tz);
    $to   = DateTimeImmutable::createFromFormat('!Y-m-d', $row['to'], $tz);
    if (! $from || ! $to) {
        return '';
    }
    if ($row['from'] === $row['to']) {
        return $from->format('D j M');
    }
    return $from->format('D j M') . ' – ' . $to->format('D j M');
}

function cornwall_hhmm_to_decimal($hhmm)
{
    list($h, $m) = array_map('intval', explode(':', $hhmm));
    return $h + $m / 60;
}

/** Data for script.js (hours as decimal hours). */
function cornwall_shop_data_for_js()
{
    $hours = array();
    foreach (cornwall_biz('hours') as $day => $span) {
        $hours[(string) $day] = $span === null ? null : array(cornwall_hhmm_to_decimal($span[0]), cornwall_hhmm_to_decimal($span[1]));
    }
    $today = wp_date('Y-m-d');
    $holidays = array_values(array_filter(cornwall_bank_holidays(), function ($d) use ($today) {
        return strcmp($d, $today) >= 0;
    }));
    $special = array();
    foreach (cornwall_special_dates() as $ymd => $row) {
        $special[$ymd] = array(
            'hours' => $row['closed'] ? null : array(cornwall_hhmm_to_decimal($row['opens']), cornwall_hhmm_to_decimal($row['closes'])),
            'name'  => $row['name'],
        );
    }
    $window = cornwall_notice_window();
    return array(
        'hours'        => $hours,
        'bankHolidays' => $holidays,
        'special'      => (object) $special,
        'notice'       => cornwall_notice_settings()['on'] ? cornwall_holiday_notice_raw() : '',
        'noticeFrom'   => $window['from'],
        'noticeTo'     => $window['to'],
    );
}
