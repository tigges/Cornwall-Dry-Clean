<?php
/**
 * Shop facts used by the patterns, structured data, llms.txt, emails and
 * script.js. Change hours, phone or address here only.
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
        'email'          => 'gina@cornwalldrycleaners.com',
        'phone'          => '+442089919413',
        'phone_display'  => '020 8991 9413',
        'phone_intl'     => '+44 20 8991 9413',
        'street'         => '23 Haven Lane',
        'locality'       => 'Ealing',
        'region'         => 'London',
        'postcode'       => 'W5 2HZ',
        'country'        => 'GB',
        'lat'            => 51.51661,
        'lng'            => -0.300303,
        'maps_url'       => 'https://maps.app.goo.gl/cw7sznF2TJq2SkL69',
        'place_id'       => 'ChIJ81BJSewTdkgR1Ru6ekdBmKw',
        'directions_url' => 'https://www.google.com/maps/dir/?api=1&destination=23+Haven+Lane,+Ealing,+London+W5+2HZ',
        // 0 = Sunday … 6 = Saturday. null = closed.
        'hours'          => array(
            1 => array('08:00', '18:00'),
            2 => array('08:00', '18:00'),
            3 => array('08:00', '18:00'),
            4 => array('08:00', '18:00'),
            5 => array('08:00', '18:00'),
            6 => array('09:00', '17:00'),
            0 => null,
        ),
    );
}

function cornwall_biz($key)
{
    $b = cornwall_business();
    return isset($b[$key]) ? $b[$key] : '';
}

/** "23 Haven Lane, Ealing, London W5 2HZ" */
function cornwall_address_line()
{
    return cornwall_biz('street') . ', ' . cornwall_biz('locality') . ', ' . cornwall_biz('region') . ' ' . cornwall_biz('postcode');
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

/* ---- Holiday notice and closure dates (Settings → General) ---- */

function cornwall_valid_date($value)
{
    $value = trim((string) $value);
    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return '';
    }
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $value, wp_timezone());
    return ($d && $d->format('Y-m-d') === $value) ? $value : '';
}

/** array('from' => 'Y-m-d', 'to' => 'Y-m-d') or null. */
function cornwall_closed_range()
{
    $from = cornwall_valid_date(get_option('cornwall_closed_from', ''));
    $to   = cornwall_valid_date(get_option('cornwall_closed_to', ''));
    if ($from === '' && $to === '') {
        return null;
    }
    if ($from === '') {
        $from = $to;
    }
    if ($to === '' || strcmp($to, $from) < 0) {
        $to = $from;
    }
    return array('from' => $from, 'to' => $to);
}

function cornwall_holiday_notice_raw()
{
    return trim((string) get_option('cornwall_holiday_notice', ''));
}

/** The notice, or '' once the closure end date has passed. */
function cornwall_holiday_notice()
{
    $notice = cornwall_holiday_notice_raw();
    $range  = cornwall_closed_range();
    if ($notice !== '' && $range !== null && strcmp(wp_date('Y-m-d'), $range['to']) > 0) {
        return '';
    }
    return $notice;
}

/** Data for script.js (hours as decimal hours). */
function cornwall_shop_data_for_js()
{
    $hours = array();
    foreach (cornwall_biz('hours') as $day => $span) {
        if ($span === null) {
            $hours[(string) $day] = null;
            continue;
        }
        $to_dec = function ($hhmm) {
            list($h, $m) = array_map('intval', explode(':', $hhmm));
            return $h + $m / 60;
        };
        $hours[(string) $day] = array($to_dec($span[0]), $to_dec($span[1]));
    }
    $today = wp_date('Y-m-d');
    $holidays = array_values(array_filter(cornwall_bank_holidays(), function ($d) use ($today) {
        return strcmp($d, $today) >= 0;
    }));
    return array(
        'hours'        => $hours,
        'bankHolidays' => $holidays,
        'closed'       => cornwall_closed_range(),
        'notice'       => cornwall_holiday_notice_raw(),
    );
}
