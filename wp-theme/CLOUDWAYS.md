# Replace the Cloudways WordPress homepage

App: `wordpress-1344959-6734053.cloudwaysapps.com`

**Do not** deploy this whole repository into `public_html`. The repo root has a static `index.html` that would hide WordPress.

## Git settings

| Field | Value |
| --- | --- |
| Repository | `https://github.com/tigges/Cornwall-Dry-Clean.git` |
| Branch | `cursor/cloudways-wp-theme-fed1` |
| Deployment path | `public_html/wp-content/themes/cornwall-dry-cleaners` |

Then **Appearance → Themes → Activate Cornwall Dry Cleaners**.

That activation:

- Replaces `/` with the Blue Awning block homepage
- Sets the site title to Cornwall Dry Cleaners
- Sets timezone to Europe/London
- Leaves Sample Page at `/sample-page/`

## WP-CLI

```bash
wp theme activate cornwall-dry-cleaners --path=public_html
```
