# Cloudways Git (this branch)

Matches a Deployment Path of `public_html/`.

| Field | Value |
| --- | --- |
| Git Remote Address | `git@github.com:tigges/Cornwall-Dry-Clean.git` |
| Branch | `cursor/cloudways-public-html-fed1` |
| Deployment Path | `public_html/` |

**Do not merge this branch into `main`.**

Theme **1.2.0** is the signed-off UX (open chip, Prices, 10am copy, compact FAQ beside hours, smaller map beside the address). The previous tip on this branch was **1.1.3**, which is why a Fetch/Pull before this version left `cornwalldrycleaners.com` looking unchanged.

1. Click **Fetch** so the latest commit appears.
2. Select **cursor/cloudways-public-html-fed1**.
3. Leave Deployment Path as `public_html/`.
4. Click **Pull**.
5. Purge cache or the old HTML stays: WordPress → **Breeze → Purge All Cache**, or Cloudways → Application Management → **Breeze** / **Purge**.
6. Hard-refresh `https://cornwalldrycleaners.com/`. Confirm `style.css` says `Version: 1.2.0` (the live file was still `1.1.3` before this pull).
7. Only if the theme is not already active: Appearance → Themes → Activate **Cornwall Dry Cleaners**.

A git pull does not run “switch theme”. Version `1.2.0` rewrites the Home page back to the `cornwall/homepage` pattern, resets stored header/footer copies so the theme files win, and asks Breeze to clear — but Breeze HTML cache (`x-cache: HIT`) is served without PHP, so **purge after Pull** is required.

This branch only contains `wp-content/themes/cornwall-dry-cleaners`. It does not include the static `index.html`, so it will not hide WordPress.

Prices, the 10am cutoff, and WhatsApp are still example copy until the shop confirms them.
