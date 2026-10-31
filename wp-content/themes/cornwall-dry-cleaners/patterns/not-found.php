<?php
/**
 * Title: Page not found
 * Slug: cornwall/not-found
 * Inserter: no
 * Description: 404 message with the way back, phone and address.
 */
$tel = esc_attr('tel:' . cornwall_biz('phone'));
?>
<!-- wp:html -->
<div class="wrap not-found">
  <p class="eyebrow">Page not found</p>
  <h1>That page isn’t here.</h1>
  <p class="lead">It may have moved, or the link was mistyped. Hours, services, reviews and directions are all on the homepage.</p>
  <p class="btn-row">
    <a class="btn btn-navy" href="/">Go to the homepage</a>
    <a class="btn btn-line" href="<?php echo $tel; ?>">Call <?php echo esc_html(cornwall_biz('phone_display')); ?></a>
  </p>
  <p class="muted"><?php echo esc_html(cornwall_address_line()); ?> · <?php echo esc_html(cornwall_hours_summary(true)); ?></p>
</div>
<!-- /wp:html -->
