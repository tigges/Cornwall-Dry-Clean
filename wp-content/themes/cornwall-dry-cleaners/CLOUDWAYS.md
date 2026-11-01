# Cloudways Git

This repository is pulled into the WordPress `public_html/`. Full notes: `CLOUDWAYS.md` at the repository root.

| Field | Value |
| --- | --- |
| Git Remote Address | `git@github.com:tigges/Cornwall-Dry-Clean.git` |
| Branch | `main` |
| Deployment Path | `public_html/` |

`main` is live. Change things on a branch with a pull request into `main` and a bumped `Version` in `style.css`. Pull that branch on the **staging** app first and check it; then merge, and on the live app Fetch, select `main`, Pull, and **purge Breeze and Varnish**. Never use Cloudways "Push to Live". The weekly scan email only sends from `cornwalldrycleaners.com`, so staging never emails duplicates. Confirm `style.css` shows the new version (1.5.10 went live on 31 October 2026). Footer is a flat horizontal bar. On mobile the header is logo + menu; the number is in the drawer.

1.5.9–1.5.10 set the site title to "Cornwall Dry Cleaners" with tagline "Ealing", swapped the Google map iframe for a static OpenStreetMap image (pin on the Google coordinates, labelled "Cornwall Dry Cleaners") that opens Google Maps, pointed Directions at the Google listing, removed the review dates, and added a 404 page, a weekly Monday 8am scan email, GOV.UK bank holidays, and date-based holiday closures. A wedding dress page mockup is a **draft** (Pages → Drafts; logged-in preview at `/wedding-dress-cleaning-alterations/?preview=true`); answer its `[Confirm]` items before publishing.

`/review`, `/review/qr`, `/review/nfc`, `/review/receipt`, `/review/web`, `/qr` and `/nfc` 302 to the Google review form with a 3-layer fallback (`mu-plugins` copy of `inc/review-fallback.php`, `db-error.php`, `.htaccess`). Destination, scan counts, weekly email address, Google rating/review count, opening hours, special days and the notice: **Shop details** (1.6.0). Plausible analytics (cookie-free, live site only) is set there too. `screenshot.png` (1.6.1) is the Appearance → Themes preview; retake it at 1200×900 if the hero changes. The notice banner (1.6.2, Shop details → Notice banner) sits above the header on every page and can announce bank holidays and special days by itself. Exclude `https://cornwalldrycleaners.com/review` and `https://cornwalldrycleaners.com/review/(.*)` in Breeze.

For the weekly email, switch on the Google Workspace SMTP relay for the server IP (Google Admin → Gmail → Routing → SMTP relay service, IP only, TLS), tick Shop details → Email sending (1.6.3; live site only, no password) and add an hourly Cloudways cron job: `wget -q -O - "https://cornwalldrycleaners.com/wp-cron.php?doing_wp_cron" >/dev/null 2>&1`.

Name, address and phone live in `inc/business.php`; hours, special days and the notice are edited in **Shop details**; services, prices, reviews and FAQs in **Page content** (1.7.0; `inc/content.php` plus one file per list). Test: `wp eval-file wp-content/themes/cornwall-dry-cleaners/tests/content-editor-test.php`.
