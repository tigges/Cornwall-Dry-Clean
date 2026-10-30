# Cloudways Git (this branch)

Matches a Deployment Path of `public_html/`.

| Field | Value |
| --- | --- |
| Git Remote Address | `git@github.com:tigges/Cornwall-Dry-Clean.git` |
| Branch | `cursor/legal-pages-fed1` |
| Deployment Path | `public_html/` |

**Do not merge this branch into `main`.**

Theme **1.5.7** is the current Cloudways pull: flattened horizontal footer, Privacy/Terms, two-line Visit address, SEO + mobile LCP pack (`srcset` WebP + async Google Fonts), the mobile header phone in the hamburger, and the `/review` short links with a 3-layer fallback safety net.

**Review links (1.5.7):** `/review`, `/review/qr`, `/review/nfc`, `/review/receipt` (plus `/qr` and `/nfc` shorthand) 302 to the Google review form (any case, with or without a trailing slash). Change the destination and see scan counts per source under **Settings → General → Review link / Review scans**. On version refresh, the theme also writes `wp-content/mu-plugins/cornwall-review-fallback.php`, `wp-content/db-error.php` and an `.htaccess` `ErrorDocument` block so `/review*`, `/qr` and `/nfc` still redirect to the Google review form even if a plugin or theme crashes or the database is unavailable. In Cloudways, add `https://cornwalldrycleaners.com/review` and `https://cornwalldrycleaners.com/review/(.*)` to Breeze → *Never Cache URL(s)*, and purge Varnish once after pulling.

To go live, switch Git to **cursor/legal-pages-fed1** (this branch already contains the SEO commits).

1. Click **Fetch** so the latest commit appears.
2. Select **cursor/legal-pages-fed1**.
3. Leave Deployment Path as `public_html/`.
4. Click **Pull**.
5. Purge cache: WordPress → **Breeze → Purge All Cache**.
6. Hard-refresh `https://cornwalldrycleaners.com/`. Confirm `style.css` says `Version: 1.5.7`.
7. Open the footer links: `/privacy/` and `/terms/`. Those pages are created on the version refresh.

A git pull does not run “switch theme”. Version `1.5.7` rewrites Home, writes the legal pages, resets stored header/footer copies, and asks Breeze to clear — but Breeze HTML cache still needs a **purge after Pull**.

This branch only contains `wp-content/themes/cornwall-dry-cleaners`.
