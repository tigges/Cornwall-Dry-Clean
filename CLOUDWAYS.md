# Cloudways Git (this branch)

Matches a Deployment Path of `public_html/`.

| Field | Value |
| --- | --- |
| Git Remote Address | `git@github.com:tigges/Cornwall-Dry-Clean.git` |
| Branch | `cursor/seo-optimisation-fed1` |
| Deployment Path | `public_html/` |

**Do not merge this branch into `main`.**

Theme **1.4.1** is the current Cloudways pull: homepage meta description and share image, WebP hero, leftover WordPress URLs removed from the sitemap, and `sameAs` on the shop schema. Title and H1 are unchanged.

To go live from the previous Cloudways branch, switch Git to **cursor/seo-optimisation-fed1** (or merge this PR into `cursor/cloudways-public-html-fed1` and keep pulling that).

1. Click **Fetch** so the latest commit appears.
2. Select **cursor/seo-optimisation-fed1**.
3. Leave Deployment Path as `public_html/`.
4. Click **Pull**.
5. Purge cache or the old HTML stays: WordPress → **Breeze → Purge All Cache**, or Cloudways → Application Management → **Breeze** / **Purge**.
6. Hard-refresh `https://cornwalldrycleaners.com/`. Confirm `style.css` says `Version: 1.4.1`.
7. After Pull, `Hello world!` is moved to Trash, `/llms.txt` plus `/indexnow.txt` start answering, and Settings → General uses the built-in UK date `d/m/Y` and time `g:i a` radios. Purge is still required.
8. Only if the theme is not already active: Appearance → Themes → Activate **Cornwall Dry Cleaners**.

A git pull does not run “switch theme”. Version `1.4.1` rewrites the Home page back to the `cornwall/homepage` pattern, resets stored header/footer copies so the theme files win, trashes the default Hello world post, and asks Breeze to clear — but Breeze HTML cache (`x-cache: HIT`) is served without PHP, so **purge after Pull** is required.

This branch only contains `wp-content/themes/cornwall-dry-cleaners`. It does not include the static `index.html`, so it will not hide WordPress.

The 10am cutoff is still example copy until the shop confirms it.

## After this pull (SOE Control Room)

IndexNow on the Control Room is red until the hosted key matches the GitHub secret. Set `INDEXNOW_KEY` in `tigges/SOE` to the same value as `https://cornwalldrycleaners.com/indexnow.txt`, then re-run SOE. Search Console still needs a CSV import or API key — that is not a site-code change.
