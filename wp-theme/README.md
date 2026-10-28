# WordPress block theme

`cornwall-dry-cleaners` is a full-site-editing theme. Activating it **replaces the WordPress homepage** with the Blue Awning page (`templates/front-page.html` + the `cornwall/homepage` pattern).

Do **not** git-pull the full repository into `public_html`. That would overwrite WordPress with the static `index.html`. Pull the theme-only branch into the theme folder instead.

## Cloudways git pull (preferred)

In Cloudways → the WordPress app → **Git**:

- Repository: `https://github.com/tigges/Cornwall-Dry-Clean.git`
- Branch: `cursor/cloudways-wp-theme-fed1`
- Deployment path: `public_html/wp-content/themes/cornwall-dry-cleaners`

Then in wp-admin: **Appearance → Themes → Activate Cornwall Dry Cleaners**.

Activation sets the site title, London timezone, and a Home page as the front page. The default Sample Page stays at `/sample-page/` and is no longer the homepage.

If WP-CLI is available on the server:

```bash
wp theme activate cornwall-dry-cleaners --path=public_html
```

## Upload zip instead

1. Upload `wp-theme/cornwall-dry-cleaners.zip` in Appearance → Themes.
2. Activate **Cornwall Dry Cleaners**.

The static GitHub Pages site on `cornwalldrycleaners.com` is unchanged.
