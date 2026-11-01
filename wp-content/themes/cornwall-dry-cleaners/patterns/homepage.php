<?php
/**
 * Title: Homepage
 * Slug: cornwall/homepage
 * Categories: featured
 * Block Types: core/post-content
 * Description: Blue Awning homepage — hero, services, visit steps, shop story, reviews and hours.
 */
$shop            = esc_url(get_theme_file_uri('assets/shop-front.jpg'));
$shop_webp       = esc_url(get_theme_file_uri('assets/shop-front.webp'));
$shop_webp_640   = esc_url(get_theme_file_uri('assets/shop-front-640.webp'));
$shop_webp_960   = esc_url(get_theme_file_uri('assets/shop-front-960.webp'));
$fascia          = esc_url(get_theme_file_uri('assets/fascia.jpg'));
$fascia_webp     = esc_url(get_theme_file_uri('assets/fascia.webp'));
$fascia_webp_640 = esc_url(get_theme_file_uri('assets/fascia-640.webp'));
$fascia_webp_960 = esc_url(get_theme_file_uri('assets/fascia-960.webp'));
$map_webp        = esc_url(get_theme_file_uri('assets/map.webp'));
$map_webp_640    = esc_url(get_theme_file_uri('assets/map-640.webp'));
$rating_value    = esc_html(cornwall_rating_value());
$review_count    = esc_html(cornwall_review_count_total());
$review_url      = esc_url(cornwall_review_url());
$holiday_notice  = cornwall_holiday_notice();
$tel             = esc_attr('tel:' . cornwall_biz('phone'));
$phone           = esc_html(cornwall_biz('phone_display'));
$maps_url        = esc_url(cornwall_biz('maps_url'));
$directions      = esc_url(cornwall_biz('directions_url'));
?>
<!-- wp:group {"tagName":"main","className":"cornwall-main"} -->
<main class="wp-block-group cornwall-main">

<!-- wp:group {"tagName":"section","className":"b-hero","layout":{"type":"default"}} -->
<section class="wp-block-group b-hero">
  <!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
  <div class="wp-block-group wrap">
    <!-- wp:group {"className":"b-kicker","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
    <div class="wp-block-group b-kicker">
      <!-- wp:paragraph -->
      <p>Haven Lane · Ealing Broadway</p>
      <!-- /wp:paragraph -->
      <!-- wp:paragraph -->
      <p><?php echo $phone; ?></p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->

    <!-- wp:heading {"level":1} -->
    <h1 class="wp-block-heading">The dry cleaners neighbours recommend.</h1>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"className":"lead"} -->
    <p class="lead">Proudly independent, right on Haven Lane. Suits, coats, laundry and repairs — often next day if dropped in by 10am.</p>
    <!-- /wp:paragraph -->

    <!-- wp:html -->
    <p class="btn-row hero-actions">
      <a class="btn btn-white" href="<?php echo $tel; ?>">Call <?php echo $phone; ?></a>
      <a class="btn btn-line" href="#visit">Hours</a>
      <a class="hours-chip" href="#visit" data-open-status><?php echo esc_html(cornwall_hours_summary(true)); ?></a>
    </p>
    <!-- /wp:html -->
  </div>
  <!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
<div class="wp-block-group wrap">
  <!-- wp:html -->
  <figure class="b-photo">
    <div class="b-photo-frame">
      <picture>
        <source srcset="<?php echo $shop_webp_640; ?> 640w, <?php echo $shop_webp_960; ?> 960w, <?php echo $shop_webp; ?> 1600w" sizes="(max-width: 720px) calc(100vw - 32px), (max-width: 1180px) calc(100vw - 48px), 1120px" type="image/webp" />
        <img src="<?php echo $shop; ?>" width="1600" height="1066" alt="White fascia and navy awning at Cornwall Dry Cleaners" fetchpriority="high" decoding="async" />
      </picture>
      <aside class="b-rating" aria-label="Google rating">
        <p class="b-rating-kicker">Google reviews</p>
        <p class="b-rating-score"><?php echo $rating_value; ?></p>
        <p class="stars" aria-label="<?php echo $rating_value; ?> out of 5 stars">★★★★★</p>
        <p><?php echo $review_count; ?> Google reviews</p>
        <a href="<?php echo $maps_url; ?>" target="_blank" rel="noreferrer">Google reviews</a>
      </aside>
    </div>
  </figure>
  <!-- /wp:html -->
</div>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"section","anchor":"services","layout":{"type":"default"}} -->
<section class="wp-block-group section" id="services">
  <!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
  <div class="wp-block-group wrap">
    <!-- wp:paragraph {"className":"eyebrow"} -->
    <p class="eyebrow">Services</p>
    <!-- /wp:paragraph -->
    <!-- wp:heading -->
    <h2 class="wp-block-heading">Everyday clothes and the pieces you care about.</h2>
    <!-- /wp:heading -->

    <!-- wp:group {"className":"service-grid","layout":{"type":"default"}} -->
    <div class="wp-block-group service-grid">
<!-- site-services:home section -->
<?php foreach (cornwall_services() as $i => $service) : ?>
      <!-- wp:group {"className":"card service-card","layout":{"type":"default"}} -->
      <div class="wp-block-group card service-card">
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading" data-num="<?php echo esc_attr(sprintf('%02d', $i + 1)); ?>"><?php echo esc_html($service[0]); ?></h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph {"className":"muted"} -->
        <p class="muted"><?php echo esc_html($service[1]); ?></p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
<?php endforeach; ?>
<!-- /site-services:home -->
    </div>
    <!-- /wp:group -->
  </div>
  <!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:html -->
<section class="section prices" id="prices">
  <div class="wrap">
    <p class="eyebrow">Prices</p>
    <h2>Prices at the counter.</h2>
    <p class="muted">We confirm the price before we start.</p>
    <div class="price-grid">
<!-- site-prices:home section -->
<!-- /site-prices:home -->
    </div>
  </div>
</section>
<!-- /wp:html -->

<!-- wp:group {"tagName":"section","className":"steps","layout":{"type":"default"}} -->
<section class="wp-block-group steps">
  <!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
  <div class="wp-block-group wrap">
    <!-- wp:paragraph {"className":"eyebrow"} -->
    <p class="eyebrow">How a visit works</p>
    <!-- /wp:paragraph -->
    <!-- wp:heading -->
    <h2 class="wp-block-heading">Drop in on Haven Lane.</h2>
    <!-- /wp:heading -->

    <!-- wp:group {"className":"step-grid","layout":{"type":"default"}} -->
    <div class="wp-block-group step-grid">
      <!-- wp:group {"layout":{"type":"default"}} -->
      <div class="wp-block-group">
        <!-- wp:paragraph -->
        <p><span>01</span></p>
        <!-- /wp:paragraph -->
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading">Bring it in</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph -->
        <p>A minute from Haven Green. Tell us when you need it back.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
      <!-- wp:group {"layout":{"type":"default"}} -->
      <div class="wp-block-group">
        <!-- wp:paragraph -->
        <p><span>02</span></p>
        <!-- /wp:paragraph -->
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading">We check it</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph -->
        <p>Stains and repairs are inspected before we start.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
      <!-- wp:group {"layout":{"type":"default"}} -->
      <div class="wp-block-group">
        <!-- wp:paragraph -->
        <p><span>03</span></p>
        <!-- /wp:paragraph -->
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading">Collect</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph -->
        <p>Often next day if in by 10am, clean and pressed.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:group -->
  </div>
  <!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"section story","anchor":"story","layout":{"type":"default"}} -->
<section class="wp-block-group section story" id="story">
  <!-- wp:group {"className":"wrap story-grid","layout":{"type":"default"}} -->
  <div class="wp-block-group wrap story-grid">
    <!-- wp:group {"className":"story-copy","layout":{"type":"default"}} -->
    <div class="wp-block-group story-copy">
      <!-- wp:paragraph {"className":"eyebrow"} -->
      <p class="eyebrow">The shop</p>
      <!-- /wp:paragraph -->
      <!-- wp:heading -->
      <h2 class="wp-block-heading">Easy to miss if you only look on the Broadway.</h2>
      <!-- /wp:heading -->
      <!-- wp:paragraph -->
      <p>Proudly independent, right on Haven Lane since 2003. The same people take clothes in, check them before you collect, and remember the regulars.</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->

    <!-- wp:html -->
    <figure class="wp-block-image size-full story-photo">
      <picture>
        <source srcset="<?php echo $fascia_webp_640; ?> 640w, <?php echo $fascia_webp_960; ?> 960w, <?php echo $fascia_webp; ?> 1600w" sizes="(max-width: 900px) calc(100vw - 32px), 560px" type="image/webp" />
        <img src="<?php echo $fascia; ?>" width="1600" height="930" alt="Cornwall script lettering and navy awning on the Haven Lane fascia" loading="lazy" decoding="async" />
      </picture>
    </figure>
    <!-- /wp:html -->

<!-- site-reviews:home quote -->
    <!-- wp:group {"className":"card quote-panel","layout":{"type":"default"}} -->
    <div class="wp-block-group card quote-panel">
      <!-- wp:quote -->
      <blockquote class="wp-block-quote">
        <p>“These guys really care about the quality of the service they provide and clearly check your goods before handing them back.”</p>
      </blockquote>
      <!-- /wp:quote -->
      <!-- wp:paragraph -->
      <p>Google review</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
<!-- /site-reviews:home -->
  </div>
  <!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"section","anchor":"reviews","layout":{"type":"default"}} -->
<section class="wp-block-group section" id="reviews">
  <!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
  <div class="wp-block-group wrap">
    <!-- wp:paragraph {"className":"eyebrow"} -->
    <p class="eyebrow">From the Google listing</p>
    <!-- /wp:paragraph -->
    <!-- wp:heading -->
    <h2 class="wp-block-heading"><?php echo $rating_value; ?> from people who use the shop.</h2>
    <!-- /wp:heading -->
    <!-- wp:paragraph {"className":"muted"} -->
    <p class="muted">Stain removal, next-day work and repairs.</p>
    <!-- /wp:paragraph -->

    <!-- wp:group {"className":"review-grid","layout":{"type":"default"}} -->
    <div class="wp-block-group review-grid">
<!-- site-reviews:home cards section -->
      <!-- wp:group {"className":"card","layout":{"type":"default"}} -->
      <div class="wp-block-group card">
        <!-- wp:group {"className":"review-meta","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
        <div class="wp-block-group review-meta">
          <!-- wp:paragraph {"className":"stars"} -->
          <p class="stars">★★★★★</p>
          <!-- /wp:paragraph -->
          <!-- wp:paragraph -->
          <p>Wool coat</p>
          <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
        <!-- wp:quote -->
        <blockquote class="wp-block-quote">
          <p>“Tucked away on a residential street. I’m so glad I found it. Cornwall cleaned my wool coat with such care and really good value.”</p>
        </blockquote>
        <!-- /wp:quote -->
        <!-- wp:paragraph {"className":"muted"} -->
        <p class="muted">Google review</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->

      <!-- wp:group {"className":"card","layout":{"type":"default"}} -->
      <div class="wp-block-group card">
        <!-- wp:group {"className":"review-meta","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
        <div class="wp-block-group review-meta">
          <!-- wp:paragraph {"className":"stars"} -->
          <p class="stars">★★★★★</p>
          <!-- /wp:paragraph -->
          <!-- wp:paragraph -->
          <p>Stain removal</p>
          <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
        <!-- wp:quote -->
        <blockquote class="wp-block-quote">
          <p>“Gina is the rock star of cleaning. I had a cocktail spilt over my suit trousers… they came back perfectly clean and pressed. Legendary service.”</p>
        </blockquote>
        <!-- /wp:quote -->
        <!-- wp:paragraph {"className":"muted"} -->
        <p class="muted">Google review</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->

      <!-- wp:group {"className":"card","layout":{"type":"default"}} -->
      <div class="wp-block-group card">
        <!-- wp:group {"className":"review-meta","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
        <div class="wp-block-group review-meta">
          <!-- wp:paragraph {"className":"stars"} -->
          <p class="stars">★★★★★</p>
          <!-- /wp:paragraph -->
          <!-- wp:paragraph -->
          <p>Repairs</p>
          <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
        <!-- wp:quote -->
        <blockquote class="wp-block-quote">
          <p>“I’ve used both the repairs and dry cleaning services over the years. The staff always try to go the extra mile — fixing a loose button without me pointing it out.”</p>
        </blockquote>
        <!-- /wp:quote -->
        <!-- wp:paragraph {"className":"muted"} -->
        <p class="muted">Google review</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
<!-- /site-reviews:home -->
    </div>
    <!-- /wp:group -->

    <!-- wp:buttons {"className":"follow-on btn-row"} -->
    <div class="wp-block-buttons follow-on btn-row">
      <!-- wp:button {"backgroundColor":"navy","textColor":"white","className":"btn-navy"} -->
      <div class="wp-block-button btn-navy"><a class="wp-block-button__link has-white-color has-navy-background-color has-text-color has-background wp-element-button" href="<?php echo $maps_url; ?>" target="_blank" rel="noreferrer">All reviews</a></div>
      <!-- /wp:button -->
      <!-- wp:button {"className":"btn-line"} -->
      <div class="wp-block-button btn-line"><a class="wp-block-button__link wp-element-button" href="<?php echo $review_url; ?>" data-review-link="/review/web" target="_blank" rel="noreferrer">Write a review</a></div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
  </div>
  <!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"section site-pad","anchor":"visit","layout":{"type":"default"}} -->
<section class="wp-block-group section site-pad" id="visit">
  <!-- wp:html -->
  <div class="wrap visit-stack">
    <div class="visit-locate">
      <div>
        <p class="eyebrow">Visit the shop</p>
        <h2 class="visit-address">
          <span class="visit-street"><?php echo esc_html(cornwall_biz('street')); ?></span>
          <span class="visit-locality"><?php echo esc_html(cornwall_biz('locality') . ', ' . cornwall_biz('postcode')); ?></span>
        </h2>
        <p class="muted">1 minute from Haven Green / Ealing Broadway station. White fascia, blue awning — not on the Broadway itself.</p>
        <p class="phone-xl"><a href="<?php echo $tel; ?>"><?php echo $phone; ?></a></p>
        <p class="btn-row">
          <a class="btn btn-navy" href="<?php echo $tel; ?>">Call</a>
          <a class="btn btn-line" href="<?php echo $directions; ?>" target="_blank" rel="noreferrer">Directions</a>
        </p>
      </div>
      <div class="map-frame">
        <a class="map-link" href="<?php echo $maps_url; ?>" target="_blank" rel="noreferrer">
          <picture>
            <source srcset="<?php echo $map_webp_640; ?> 640w, <?php echo $map_webp; ?> 1200w" sizes="(max-width: 799px) calc(100vw - 24px), 560px" type="image/webp" />
            <img src="<?php echo $map_webp; ?>" width="1200" height="800" alt="Map: Cornwall Dry Cleaners on Haven Lane, by Haven Green and Ealing Broadway station" loading="lazy" decoding="async" />
          </picture>
          <span class="map-label">Open in Google Maps</span>
        </a>
        <a class="map-credit" href="https://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">© OpenStreetMap contributors</a>
      </div>
    </div>
    <div class="visit-plan">
      <div>
        <table class="hours hours-table">
          <caption>Opening hours</caption>
          <tbody>
<?php foreach (cornwall_biz('hours') as $day => $span) : ?>
            <tr data-day="<?php echo (int) $day; ?>"><th><?php echo esc_html(cornwall_day_names()[$day]); ?></th><td><?php echo $span === null ? 'Closed' : esc_html(cornwall_format_time($span[0]) . ' – ' . cornwall_format_time($span[1])); ?></td></tr>
<?php endforeach; ?>
          </tbody>
        </table>
        <?php if ($holiday_notice !== '') : ?>
        <p class="holiday-notice" data-holiday-banner><?php echo esc_html($holiday_notice); ?></p>
        <?php endif; ?>
        <p class="muted">Closed on bank holidays — call the day before if you are unsure.</p>
      </div>
      <section class="visit-faq" id="faq" aria-label="Frequently asked questions">
        <p class="faq-caption">Questions</p>
        <div class="faq-list">
<!-- site-faqs:home section -->
<?php foreach (cornwall_faqs() as $faq) : ?>
          <details>
            <summary><?php echo esc_html($faq[0]); ?></summary>
            <p><?php echo esc_html($faq[1]); ?></p>
          </details>
<?php endforeach; ?>
<!-- /site-faqs:home -->
        </div>
      </section>
    </div>
  </div>
  <!-- /wp:html -->
</section>
<!-- /wp:group -->

</main>
<!-- /wp:group -->
