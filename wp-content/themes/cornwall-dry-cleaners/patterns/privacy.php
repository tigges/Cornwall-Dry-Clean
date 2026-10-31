<?php
/**
 * Title: Privacy
 * Slug: cornwall/privacy
 * Categories: featured
 * Description: Short privacy policy for the Haven Lane shop and website.
 */
$email = esc_html(cornwall_biz('email'));
?>
<!-- wp:paragraph {"className":"legal-date"} -->
<p class="legal-date">Effective 1 November 2026</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>This policy is for <strong><?php echo esc_html(cornwall_biz('legal_name')); ?></strong> (<?php echo esc_html(cornwall_biz('owner')); ?>) at <?php echo esc_html(cornwall_address_line()); ?>. It covers this website and a visit to the shop.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">What we collect</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>If you call, email or drop clothes in, we may have your name, phone number, email and address so we can take the order and get in touch. There are no customer accounts on the website.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>The site may record ordinary technical data (browser and pages viewed) through hosting. Fonts and the map image are served from this website. The Directions, map and review links open Google, which has its own policy.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Visitor statistics.</strong> We use <a href="https://plausible.io/data-policy" target="_blank" rel="noreferrer">Plausible Analytics</a> to see how the website is used: how many visits, which pages, where visitors came from (for example a Google search), the type of device and browser, the country, and taps on Call, Directions, the map and “Write a review”. Plausible does not use cookies, does not store IP addresses and does not build a profile of you. The figures are only kept as totals and are hosted in the EU. Because no cookies are used, there is no cookie banner. We do not use tracking cookies or advertising trackers.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Our short review links (such as /review) count how many times each link is opened, by day. They do not store IP addresses, cookies or anything else about you.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>We do not buy lists from data brokers, and we do not send marketing email from this site.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">How we use it</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>To take in and return your clothes, answer the phone or email, and keep the website working. We do not sell your information.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">How long we keep it</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Order notes are kept only as long as we need them to run the shop, or as the law requires. After that we delete or anonymise them.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Children</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The shop and website are not aimed at children.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Security and other sites</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We take reasonable care with tickets and email. No website is completely secure. Links to Google Maps and Google reviews go to other sites with their own policies.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Changes</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We will update this page if our practices change. The date at the top is the current version.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Contact</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html(cornwall_biz('legal_name') . ', ' . cornwall_address_line()); ?><br><?php echo esc_html(cornwall_biz('phone_display')); ?> · <a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></p>
<!-- /wp:paragraph -->
