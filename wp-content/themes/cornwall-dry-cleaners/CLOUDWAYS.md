# Cloudways Git

This theme is meant to be pulled into a WordPress `public_html/`.

| Field | Value |
| --- | --- |
| Git Remote Address | `git@github.com:tigges/Cornwall-Dry-Clean.git` |
| Branch | `cursor/legal-pages-fed1` |
| Deployment Path | `public_html/` |

Fetch, select that branch, Pull, then **purge Breeze**. Confirm `style.css` shows **1.5.5**. Footer is a flat horizontal bar. On mobile the header is logo + menu; the number is in the drawer.

`/review`, `/review/qr`, `/review/nfc` and `/review/receipt` 302 to the Google review form. Destination and scan counts: **Settings → General**. Exclude `/review` from Varnish and Breeze, and purge Varnish once after pulling 1.5.5.
