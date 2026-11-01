# Cloudways Git

Matches a Deployment Path of `public_html/`.

| Field | Value |
| --- | --- |
| Git Remote Address | `git@github.com:tigges/Cornwall-Dry-Clean.git` |
| Branch | `main` |
| Deployment Path | `public_html/` |

**`main` is the live branch.** Cloudways pulls `main` into `public_html/`. Make changes on a short-lived branch, open a pull request into `main`, and merge only when it is ready to go live. Bump `Version` in `wp-content/themes/cornwall-dry-cleaners/style.css` on every theme change, or the homepage/settings refresh will not run on the next pull.

## Staging first

Staging is a Cloudways clone of the live app at `https://wordpress-1344959-6761381.cloudwaysapps.com/` (password-protected and `noindex`). Both apps use Deployment via Git with the same remote and `public_html/`; only the branch differs.

| App | Branch it pulls |
| --- | --- |
| Live (`cornwalldrycleaners.com`) | `main` — always |
| Staging | the pull request's branch, while it is being checked |

One-off setup on the staging app: Application → **Deployment via Git** → copy its SSH key → GitHub repo → Settings → **Deploy keys** → Add (read-only) → back in Cloudways, enter the remote, Authenticate, Deployment Path `public_html/`.

For each change:

1. The change is pushed to a branch (e.g. `cursor/…`) with a pull request into `main` and a bumped theme `Version`.
2. **Staging:** Deployment via Git → **Fetch** → select that branch → **Pull** → purge Breeze/Varnish on staging. Check desktop and mobile.
3. Happy: **merge the pull request** on GitHub.
4. **Live:** Deployment via Git → **Fetch** → `main` → **Pull** → purge Breeze and Varnish.
5. Optionally switch staging back to `main` and Pull, so it matches live.

Do **not** use Cloudways "Push to Live" (staging → live). Code goes live through Git; pushing the staging database would overwrite live settings, review-scan counts and anything edited since the clone. To refresh staging with live content, use "Pull from Live" / re-clone instead.

Staging-safe behaviour: the theme only sends the weekly scan email and the HSTS header when the site address is `cornwalldrycleaners.com`, so a staging or local copy never emails Gina duplicate numbers ("Send a test now" still works, to check SMTP). `/review` links on staging count into staging's own table, not live's.

Theme **1.6.0** adds **Shop details** (WordPress menu, under Dashboard): weekly opening hours, holidays and special days (closed or different hours, optional name, past dates drop off), the notice with show-from / show-until dates, the Google rating, review links, the weekly scan email and the review-scan table, all in one place (moved from Settings → General). Name, address and phone stay fixed in `inc/business.php` so they always match the Google listing. Saving clears the page cache. On pull, any 1.5.x "Closed from / to" dates become a special day. It also adds **Plausible analytics** (cookie-free; live site only, never staging or logged-in staff) with tap events Call, Directions, Map, Write a review and Email (add these as Goals in Plausible, with the custom property `where`), and the Privacy page (effective 1 November 2026) describes it. **1.6.1** adds `screenshot.png` (the homepage) so Appearance → Themes shows a preview. **1.6.2** turns the notice into a **notice banner**: a thin bar above the header on every page (Show my notice on/off, optional link, show from / until, brand colour). Visitors can close it and it comes back when the words change. When your own notice is off it announces bank holidays and special days automatically (default 14 days ahead). The bar is chosen in the browser by London date, so it switches on and off on time even on cached pages.

Theme **1.5.12** adds the staging guard and clears the 31 October 2026 review-scan counts (launch-day link tests) once on pull. **1.5.10** went live on 31 October 2026. It keeps everything signed off in 1.5.8 (flattened horizontal footer, Privacy/Terms, two-line Visit address, mobile header phone in the hamburger, `/review` short links with the 3-layer fallback, live hours, Settings → General controls) and adds:

- **Site title "Cornwall Dry Cleaners", tagline "Ealing".** The version refresh sets both, plus the London timezone and UK date format. Homepage title: `Cornwall Dry Cleaners – Ealing`; inner pages drop the tagline.
- **Static map.** The Visit map is a self-hosted OpenStreetMap image (no Google iframe, no third-party requests) that opens Google Maps when tapped, with an `© OpenStreetMap contributors` credit. The pin sits on the Google Business Profile coordinates and is labelled with the Google name, "Cornwall Dry Cleaners".
- **Aligned with the Google listing.** Structured data uses Google's name, coordinates and address town (`23 Haven Lane, London W5 2HZ`); Directions buttons open the Google listing itself (place ID) rather than a street address.
- **Review dates removed.** The three reviews are unchanged; each now just says "Google review".
- **Weekly scan email.** Every Monday at 8am (London time) Gina gets the week's `/review` scan counts per source. Change the address or send a test under **Shop details → Review links**. Leave the address blank to stop it.
- **Bank holidays update themselves.** The theme fetches the GOV.UK England & Wales list weekly (built-in 2026–2028 list as a fallback). Live hours and Google structured data both use it.
- **Holiday closures by date.** Now the special-days list in **Shop details** (1.6.0).
- **SEO / speed / tidy-up.** One shared business-data file feeds the header, footer, hours table, FAQ, structured data and `llms.txt`. Richer `LocalBusiness` schema (legal name, founded 2003, payment, area served, services, bank-holiday hours), a real 404 page (noindex), 301s away from unused WordPress views (feeds, search, author/date/category/tag archives, attachments), hidden REST user list, XML-RPC off, security headers, smaller CSS (one `site.css`, unused core presets removed), subset logo font, resized photos.
- **Wedding dress page mockup (draft, not live).** Pages → Drafts → *Wedding dress cleaning & alterations (mockup)*, slug `/wedding-dress-cleaning-alterations/`, template *Landing page (full width)*. Highlighted `[Confirm: …]` items need Gina's answers before it is published. To publish: answer/remove each `[Confirm]` item, delete the yellow mockup banner, remove "(mockup)" from the title, then Publish.
  Preview while logged in to WordPress: `https://cornwalldrycleaners.com/wedding-dress-cleaning-alterations/?preview=true`. Logged-out visitors get a 404 until it is published, after which the URL is `https://cornwalldrycleaners.com/wedding-dress-cleaning-alterations/`.

**Review links & shop settings:** `/review`, `/review/qr`, `/review/nfc`, `/review/receipt`, `/review/web` (plus `/qr` and `/nfc` shorthand) 302 to the Google review form (any case, with or without a trailing slash). Change the destination, see scan counts per source, update the Google rating/review count, set the weekly email address, or post holiday hours/closures under **Shop details**. On version refresh the theme copies `inc/review-fallback.php` to `wp-content/mu-plugins/cornwall-review-fallback.php`, and writes `wp-content/db-error.php` and an `.htaccess` `ErrorDocument` block, so `/review*`, `/qr` and `/nfc` still redirect to the Google review form even if a plugin or theme crashes or the database is unavailable. In Cloudways, add `https://cornwalldrycleaners.com/review` and `https://cornwalldrycleaners.com/review/(.*)` to Breeze → *Never Cache URL(s)*.

**Email and cron (recommended once).** Varnish/Breeze caching means WordPress's built-in scheduler only runs on uncached visits, so the Monday email can arrive late. In Cloudways → Application → **Cron Job Management**, add an hourly job:

```
wget -q -O - "https://cornwalldrycleaners.com/wp-cron.php?doing_wp_cron" >/dev/null 2>&1
```

Email goes through the **Google Workspace SMTP relay** (theme 1.6.3), set up for this site only, so the other apps on the Cloudways server are not affected and no password is stored. The domain's mail is Google Workspace (MX `smtp.google.com`, SPF `include:_spf.google.com`).

1. Google Admin (`admin.google.com`, super admin) → **Apps → Google Workspace → Gmail → Routing → SMTP relay service → Configure**: allowed senders **Only addresses in my domains**; authentication **Only accept mail from the specified IP addresses** with the Cloudways server's Public IP (Cloudways → Servers → AQUARI → Master Credentials, or the IP on the Servers list); leave "Require SMTP Authentication" off; tick **Require TLS encryption**. Save (can take up to an hour).
2. Gmail → **Authenticate email**: make sure DKIM is on for `cornwalldrycleaners.com` (add the TXT record it gives you if not).
3. WordPress → **Shop details → Email sending** → tick **Send the website's email through the Google Workspace SMTP relay** → Save.
4. **Shop details → Review links → Weekly scan email → Send a test now**, and check the inbox (and spam). If it fails, the message under Email sending shows Google's answer; "Mail relay denied [IP]" means that IP must be added in step 1.

Mail is sent as `wordpress@cornwalldrycleaners.com` ("Cornwall Dry Cleaners website") on `smtp-relay.gmail.com:587` with TLS. Only the live address uses the relay; staging uses the server's own mail. Leave Cloudways → Servers → SMTP unset.

To deploy (Cloudways → Application → **Deployment via Git**):

1. Click **Fetch** so the latest commit appears.
2. Select **main**.
3. Leave Deployment Path as `public_html/`.
4. Click **Pull**.
5. Purge cache: WordPress → **Breeze → Purge All Cache**, and Cloudways → **Varnish → Purge**.
6. Hard-refresh `https://cornwalldrycleaners.com/`. Confirm `/wp-content/themes/cornwall-dry-cleaners/style.css` shows the new `Version`.
7. Check `/privacy/`, `/terms/`, `/review` and a made-up URL such as `/nothing-here` (should show the new 404 page).

A git pull does not run “switch theme”. A new theme `Version` rewrites Home, writes the legal pages, creates the wedding draft (only if missing), resets stored header/footer copies, sets the site title/tagline, refreshes the review fallback files, and asks Breeze to clear — but Breeze/Varnish HTML cache still needs a **purge after Pull**.

The theme no longer prints a Site Icon from Settings → General; its own favicons are used instead.

The repository only contains this file and `wp-content/themes/cornwall-dry-cleaners`, so pulling into `public_html/` leaves WordPress core, plugins and uploads alone.
