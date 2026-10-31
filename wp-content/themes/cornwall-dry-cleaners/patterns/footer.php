<?php
/**
 * Title: Footer
 * Slug: cornwall/footer
 * Inserter: no
 * Description: One-row navy footer bar and the mobile Call / Directions bar.
 */
$tel        = esc_attr('tel:' . cornwall_biz('phone'));
$phone      = esc_html(cornwall_biz('phone_display'));
$directions = esc_url(cornwall_biz('directions_url'));
?>
<!-- wp:group {"tagName":"div","className":"foot cornwall-footer","layout":{"type":"default"}} -->
<div class="wp-block-group foot cornwall-footer">
  <!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
  <div class="wp-block-group wrap">
    <!-- wp:html -->
    <div class="foot-bar">
      <a class="logo" href="/">
        <span class="logo-script">Cornwall</span>
        <span class="logo-caps">Dry Cleaners</span>
      </a>
      <p class="foot-meta"><?php echo esc_html(cornwall_address_line()); ?> · <a href="<?php echo $tel; ?>"><?php echo $phone; ?></a></p>
      <p class="foot-copy">Since <?php echo esc_html(cornwall_biz('founded')); ?>. © <span data-year><?php echo esc_html(wp_date('Y')); ?></span> <?php echo esc_html(cornwall_biz('copyright_name')); ?></p>
      <nav class="foot-legal" aria-label="Legal">
        <a href="/privacy/">Privacy</a>
        <a href="/terms/">Terms</a>
      </nav>
    </div>
    <!-- /wp:html -->
  </div>
  <!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:html -->
<div class="mobile-cta" aria-label="Quick actions">
  <a href="<?php echo $tel; ?>">Call</a>
  <a href="<?php echo $directions; ?>" target="_blank" rel="noreferrer">Directions</a>
</div>
<!-- /wp:html -->
