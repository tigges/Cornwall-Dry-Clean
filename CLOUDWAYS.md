# Cloudways Git (this branch)

Matches a Deployment Path of `public_html/`.

| Field | Value |
| --- | --- |
| Git Remote Address | `git@github.com:tigges/Cornwall-Dry-Clean.git` |
| Branch | `cursor/legal-pages-fed1` |
| Deployment Path | `public_html/` |

**Do not merge this branch into `main`.**

Theme **1.5.3** is the current Cloudways pull: Privacy/Terms, two-line Visit address, SEO pack, and the mobile header phone pill moved back into the hamburger (Call/Directions stay in the bottom bar).

To go live, switch Git to **cursor/legal-pages-fed1** (this branch already contains the SEO commits).

1. Click **Fetch** so the latest commit appears.
2. Select **cursor/legal-pages-fed1**.
3. Leave Deployment Path as `public_html/`.
4. Click **Pull**.
5. Purge cache: WordPress → **Breeze → Purge All Cache**.
6. Hard-refresh `https://cornwalldrycleaners.com/`. Confirm `style.css` says `Version: 1.5.3`.
7. Open the footer links: `/privacy/` and `/terms/`. Those pages are created on the version refresh.

A git pull does not run “switch theme”. Version `1.5.3` rewrites Home, writes the legal pages, resets stored header/footer copies, and asks Breeze to clear — but Breeze HTML cache still needs a **purge after Pull**.

This branch only contains `wp-content/themes/cornwall-dry-cleaners`.
