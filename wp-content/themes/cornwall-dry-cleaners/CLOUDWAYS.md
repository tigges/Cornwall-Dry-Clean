# Cloudways Git

This theme is meant to be pulled into a WordPress `public_html/`.

| Field | Value |
| --- | --- |
| Git Remote Address | `git@github.com:tigges/Cornwall-Dry-Clean.git` |
| Branch | `cursor/legal-pages-fed1` |
| Deployment Path | `public_html/` |

Fetch, select that branch, Pull, then **purge Breeze and Varnish**. Confirm `style.css` shows **1.5.10**. Footer is a flat horizontal bar. On mobile the header is logo + menu; the number is in the drawer.

1.5.10 sets the site title to "Cornwall Dry Cleaners" with tagline "Ealing", swaps the Google map iframe for a static OpenStreetMap image (pin on the Google coordinates, labelled "Cornwall Dry Cleaners") that opens Google Maps, points Directions at the Google listing, removes the review dates, adds a 404 page, a weekly Monday 8am scan email, GOV.UK bank holidays, and date-based holiday closures. A wedding dress page mockup is created as a **draft** (Pages → Drafts); answer its `[Confirm]` items before publishing.

`/review`, `/review/qr`, `/review/nfc`, `/review/receipt`, `/review/web`, `/qr` and `/nfc` 302 to the Google review form with a 3-layer fallback (`mu-plugins` copy of `inc/review-fallback.php`, `db-error.php`, `.htaccess`). Destination, scan counts, weekly email address, Google rating/review count, and holiday notice/closure dates: **Settings → General**. Exclude `https://cornwalldrycleaners.com/review` and `https://cornwalldrycleaners.com/review/(.*)` in Breeze.

For the weekly email, set up Cloudways SMTP and an hourly Cloudways cron job: `wget -q -O - "https://cornwalldrycleaners.com/wp-cron.php?doing_wp_cron" >/dev/null 2>&1`.

Business details (address, phone, hours, services, FAQ) live in `inc/business.php`.
