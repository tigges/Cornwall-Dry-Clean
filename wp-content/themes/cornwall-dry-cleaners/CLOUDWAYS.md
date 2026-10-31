# Cloudways Git

This repository is pulled into the WordPress `public_html/`. Full notes: `CLOUDWAYS.md` at the repository root.

| Field | Value |
| --- | --- |
| Git Remote Address | `git@github.com:tigges/Cornwall-Dry-Clean.git` |
| Branch | `main` |
| Deployment Path | `public_html/` |

`main` is live. Change things on a branch, merge into `main` via a pull request, bump `Version` in `style.css`, then in Cloudways: Fetch, select `main`, Pull, and **purge Breeze and Varnish**. Confirm `style.css` shows the new version (1.5.10 went live on 31 October 2026). Footer is a flat horizontal bar. On mobile the header is logo + menu; the number is in the drawer.

1.5.9–1.5.10 set the site title to "Cornwall Dry Cleaners" with tagline "Ealing", swapped the Google map iframe for a static OpenStreetMap image (pin on the Google coordinates, labelled "Cornwall Dry Cleaners") that opens Google Maps, pointed Directions at the Google listing, removed the review dates, and added a 404 page, a weekly Monday 8am scan email, GOV.UK bank holidays, and date-based holiday closures. A wedding dress page mockup is a **draft** (Pages → Drafts; logged-in preview at `/wedding-dress-cleaning-alterations/?preview=true`); answer its `[Confirm]` items before publishing.

`/review`, `/review/qr`, `/review/nfc`, `/review/receipt`, `/review/web`, `/qr` and `/nfc` 302 to the Google review form with a 3-layer fallback (`mu-plugins` copy of `inc/review-fallback.php`, `db-error.php`, `.htaccess`). Destination, scan counts, weekly email address, Google rating/review count, and holiday notice/closure dates: **Settings → General**. Exclude `https://cornwalldrycleaners.com/review` and `https://cornwalldrycleaners.com/review/(.*)` in Breeze.

For the weekly email, set Cloudways SMTP to Gmail (`smtp.gmail.com`, port 587, Google app password; the domain's mail is Google Workspace) and add an hourly Cloudways cron job: `wget -q -O - "https://cornwalldrycleaners.com/wp-cron.php?doing_wp_cron" >/dev/null 2>&1`.

Business details (address, phone, hours, services, FAQ) live in `inc/business.php`.
