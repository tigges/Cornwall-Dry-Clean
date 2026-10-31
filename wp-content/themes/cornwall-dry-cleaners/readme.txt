=== Cornwall Dry Cleaners ===
Contributors: cornwall
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
License: GPLv2 or later

Block theme for Cornwall Dry Cleaners on Haven Lane, Ealing. Activating it (or pulling a new Version) replaces the WordPress homepage with the Blue Awning block page.

Name, address, phone, services and FAQ live in inc/business.php. Hours, special
days, the notice, rating, review links and analytics: WordPress → Shop details.

== Cloudways git pull ==

Pull branch `main` into `public_html/` (see CLOUDWAYS.md), then purge Breeze
and Varnish. Bump `Version` in style.css on every theme change.

== Upload zip ==

1. Zip the `cornwall-dry-cleaners` folder.
2. Appearance → Themes → Add New → Upload Theme.
3. Activate **Cornwall Dry Cleaners**. The homepage is replaced automatically.
