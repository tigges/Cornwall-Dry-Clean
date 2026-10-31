<?php
/**
 * Title: Terms
 * Slug: cornwall/terms
 * Categories: featured
 * Description: Short terms for the website and a visit to the shop.
 */
$email = esc_html(cornwall_biz('email'));
?>
<!-- wp:paragraph {"className":"legal-date"} -->
<p class="legal-date">Effective 31 October 2026</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>These terms cover the website and a visit to <strong><?php echo esc_html(cornwall_biz('legal_name')); ?></strong> at <?php echo esc_html(cornwall_address_line()); ?>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">The website</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The site is here so you can check services, hours and how to find us. You cannot book or pay online. Do not misuse the site. Content is for information.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">The shop</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Bring garments in to the counter. We inspect them first and will say if a stain or repair is uncertain. Payment is at the counter, card or cash. Hours are on the homepage. <?php echo esc_html(cornwall_closed_days_sentence(true)); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Care of garments</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We take reasonable care. Some stains and fabrics cannot be guaranteed. We will tell you when you drop off.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Law</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>These terms are governed by the law of England and Wales.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Contact</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html(cornwall_biz('phone_display')); ?> · <a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a><br><?php echo esc_html(cornwall_address_line()); ?></p>
<!-- /wp:paragraph -->
