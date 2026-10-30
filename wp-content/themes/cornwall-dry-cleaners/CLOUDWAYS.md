# Cloudways Git

This theme is meant to be pulled into a WordPress `public_html/`.

| Field | Value |
| --- | --- |
| Git Remote Address | `git@github.com:tigges/Cornwall-Dry-Clean.git` |
| Branch | `cursor/legal-pages-fed1` |
| Deployment Path | `public_html/` |

Fetch, select that branch, Pull, then **purge Breeze**. Confirm `style.css` shows **1.5.8**. Footer is a flat horizontal bar. On mobile the header is logo + menu; the number is in the drawer.

`/review`, `/review/qr`, `/review/nfc`, `/review/receipt`, `/review/web`, `/qr` and `/nfc` 302 to the Google review form with a 3-layer fallback (`mu-plugins`, `db-error.php`, `.htaccess`). Destination, scan counts, Google rating/review count, and holiday hours notice: **Settings → General**. Exclude `https://cornwalldrycleaners.com/review` and `https://cornwalldrycleaners.com/review/(.*)` in Breeze, and purge Varnish after pulling.
