# Cloudways Git (this branch)

Matches a Deployment Path of `public_html/`.

| Field | Value |
| --- | --- |
| Git Remote Address | `git@github.com:tigges/Cornwall-Dry-Clean.git` |
| Branch | `cursor/legal-pages-fed1` |
| Deployment Path | `public_html/` |

**Do not merge this branch into `main`.**

Theme **1.5.9** is the current Cloudways pull. It keeps everything signed off in 1.5.8 (flattened horizontal footer, Privacy/Terms, two-line Visit address, mobile header phone in the hamburger, `/review` short links with the 3-layer fallback, live hours, Settings → General controls) and adds:

- **Site title "Cornwall Dry Cleaners", tagline "Ealing".** The version refresh sets both, plus the London timezone and UK date format. Homepage title: `Cornwall Dry Cleaners – Ealing`; inner pages drop the tagline.
- **Static map.** The Visit map is a self-hosted OpenStreetMap image (no Google iframe, no third-party requests) that opens Google Maps when tapped, with an `© OpenStreetMap contributors` credit.
- **Review dates removed.** The three reviews are unchanged; each now just says "Google review".
- **Weekly scan email.** Every Monday at 8am (London time) Gina gets the week's `/review` scan counts per source. Change the address or send a test under **Settings → General → Weekly scan email**. Leave the address blank to stop it.
- **Bank holidays update themselves.** The theme fetches the GOV.UK England & Wales list weekly (built-in 2026–2028 list as a fallback). Live hours and Google structured data both use it.
- **Holiday closures by date.** The old "Closed today" tick box is replaced by **Closed from / to** dates under Settings → General → Holiday / special hours. The notice and closed status switch off by themselves after the "to" date. An existing "Closed today" tick is converted to "Closed from today".
- **SEO / speed / tidy-up.** One shared business-data file feeds the header, footer, hours table, FAQ, structured data and `llms.txt`. Richer `LocalBusiness` schema (legal name, founded 2003, payment, area served, services, bank-holiday hours), a real 404 page (noindex), 301s away from unused WordPress views (feeds, search, author/date/category/tag archives, attachments), hidden REST user list, XML-RPC off, security headers, smaller CSS (one `site.css`, unused core presets removed), subset logo font, resized photos.
- **Wedding dress page mockup (draft, not live).** Pages → Drafts → *Wedding dress cleaning & alterations (mockup)*, slug `/wedding-dress-cleaning-alterations/`, template *Landing page (full width)*. Highlighted `[Confirm: …]` items need Gina's answers before it is published. To publish: answer/remove each `[Confirm]` item, delete the yellow mockup banner, remove "(mockup)" from the title, then Publish.

**Review links & shop settings:** `/review`, `/review/qr`, `/review/nfc`, `/review/receipt`, `/review/web` (plus `/qr` and `/nfc` shorthand) 302 to the Google review form (any case, with or without a trailing slash). Change the destination, see scan counts per source, update the Google rating/review count, set the weekly email address, or post holiday hours/closures under **Settings → General**. On version refresh the theme copies `inc/review-fallback.php` to `wp-content/mu-plugins/cornwall-review-fallback.php`, and writes `wp-content/db-error.php` and an `.htaccess` `ErrorDocument` block, so `/review*`, `/qr` and `/nfc` still redirect to the Google review form even if a plugin or theme crashes or the database is unavailable. In Cloudways, add `https://cornwalldrycleaners.com/review` and `https://cornwalldrycleaners.com/review/(.*)` to Breeze → *Never Cache URL(s)*.

**Email and cron (recommended once).** Varnish/Breeze caching means WordPress's built-in scheduler only runs on uncached visits, so the Monday email can arrive late. In Cloudways → Application → **Cron Job Management**, add an hourly job:

```
wget -q -O - "https://cornwalldrycleaners.com/wp-cron.php?doing_wp_cron" >/dev/null 2>&1
```

Cloudways servers also need SMTP for reliable mail: Cloudways → Server → **SMTP** (Elastic Email or your own provider), then use **Send a test now** in Settings → General.

To go live, switch Git to **cursor/legal-pages-fed1** (this branch already contains the SEO commits).

1. Click **Fetch** so the latest commit appears.
2. Select **cursor/legal-pages-fed1**.
3. Leave Deployment Path as `public_html/`.
4. Click **Pull**.
5. Purge cache: WordPress → **Breeze → Purge All Cache**, and Cloudways → **Varnish → Purge**.
6. Hard-refresh `https://cornwalldrycleaners.com/`. Confirm `style.css` says `Version: 1.5.9`.
7. Check `/privacy/`, `/terms/`, `/review` and a made-up URL such as `/nothing-here` (should show the new 404 page).

A git pull does not run “switch theme”. Version `1.5.9` rewrites Home, writes the legal pages, creates the wedding draft (only if missing), resets stored header/footer copies, sets the site title/tagline, refreshes the review fallback files, and asks Breeze to clear — but Breeze/Varnish HTML cache still needs a **purge after Pull**.

The theme no longer prints a Site Icon from Settings → General; its own favicons are used instead.

This branch only contains `wp-content/themes/cornwall-dry-cleaners`.
