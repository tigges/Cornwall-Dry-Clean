<?php
/**
 * Title: Header
 * Slug: cornwall/header
 * Inserter: no
 * Description: Sticky navy header — logo, anchors, phone pill and mobile drawer.
 */
$tel   = esc_attr('tel:' . cornwall_biz('phone'));
$phone = esc_html(cornwall_biz('phone_display'));
?>
<!-- wp:html -->
<div class="top b-top cornwall-header">
  <div class="wrap top-inner">
    <a class="logo" href="/">
      <span class="logo-script">Cornwall</span>
      <span class="logo-caps">Dry Cleaners</span>
    </a>
    <nav class="nav" aria-label="Primary">
      <a href="/#services">Services</a>
      <a href="/#story">The shop</a>
      <a href="/#reviews">Reviews</a>
      <a href="/#visit">Visit</a>
    </nav>
    <a class="btn btn-white phone-link" href="<?php echo $tel; ?>"><?php echo $phone; ?></a>
    <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="site-mobile-nav">
      <span></span>
      <span></span>
      <span></span>
      <span class="sr-only">Menu</span>
    </button>
  </div>
  <nav class="mobile-nav" id="site-mobile-nav" data-mobile-nav hidden>
    <div class="wrap">
      <a href="/#services">Services</a>
      <a href="/#story">The shop</a>
      <a href="/#reviews">Reviews</a>
      <a href="/#visit">Visit</a>
      <a class="btn btn-white" href="<?php echo $tel; ?>"><?php echo $phone; ?></a>
    </div>
  </nav>
</div>
<!-- /wp:html -->
