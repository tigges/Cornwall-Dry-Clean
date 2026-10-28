<?php
/**
 * Title: Homepage
 * Slug: cornwall/homepage
 * Categories: featured
 * Block Types: core/post-content
 * Description: Blue Awning homepage — hero, services, visit steps, shop story, reviews and hours.
 */
$shop   = esc_url(get_theme_file_uri('assets/shop-front.jpg'));
$fascia = esc_url(get_theme_file_uri('assets/fascia.jpg'));
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
      <p>020 8991 9413</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->

    <!-- wp:heading {"level":1} -->
    <h1 class="wp-block-heading">The dry cleaners neighbours recommend.</h1>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"className":"lead"} -->
    <p class="lead">Proudly independent, right on Haven Lane. Suits, coats, laundry and repairs — often next day if dropped in by 10am.</p>
    <!-- /wp:paragraph -->

    <!-- wp:buttons {"className":"btn-row"} -->
    <div class="wp-block-buttons btn-row">
      <!-- wp:button {"backgroundColor":"white","textColor":"navy","className":"btn-white"} -->
      <div class="wp-block-button btn-white"><a class="wp-block-button__link has-navy-color has-white-background-color has-text-color has-background wp-element-button" href="tel:+442089919413">Call 020 8991 9413</a></div>
      <!-- /wp:button -->
      <!-- wp:button {"className":"btn-line"} -->
      <div class="wp-block-button btn-line"><a class="wp-block-button__link wp-element-button" href="#visit">Hours</a></div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->

    <!-- wp:paragraph -->
    <p data-open-status>Checking today’s hours…</p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:group {"className":"wrap","layout":{"type":"default"}} -->
<div class="wp-block-group wrap">
  <!-- wp:html -->
  <figure class="b-photo">
    <div class="b-photo-frame">
      <img src="<?php echo $shop; ?>" alt="White fascia and navy awning at Cornwall Dry Cleaners" />
      <aside class="b-rating" aria-label="Google rating">
        <p class="b-rating-kicker">Google reviews</p>
        <p class="b-rating-score">4.9</p>
        <p class="stars" aria-label="4.9 out of 5 stars">★★★★★</p>
        <p>45 Google reviews</p>
        <a href="https://maps.app.goo.gl/cw7sznF2TJq2SkL69" target="_blank" rel="noreferrer">Google reviews</a>
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
      <!-- wp:group {"className":"card service-card","layout":{"type":"default"}} -->
      <div class="wp-block-group card service-card">
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading" data-num="01">Dry cleaning</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph {"className":"muted"} -->
        <p class="muted">Suits, coats and office wear, cleaned and pressed.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->

      <!-- wp:group {"className":"card service-card","layout":{"type":"default"}} -->
      <div class="wp-block-group card service-card">
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading" data-num="02">Stain removal</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph {"className":"muted"} -->
        <p class="muted">We inspect first and say honestly what will come out.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->

      <!-- wp:group {"className":"card service-card","layout":{"type":"default"}} -->
      <div class="wp-block-group card service-card">
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading" data-num="03">Laundry</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph {"className":"muted"} -->
        <p class="muted">Service washes for homes and Airbnb hosts, on time.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->

      <!-- wp:group {"className":"card service-card","layout":{"type":"default"}} -->
      <div class="wp-block-group card service-card">
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading" data-num="04">Repairs &amp; alterations</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph {"className":"muted"} -->
        <p class="muted">Hems, zips, stitching and loose buttons.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->

      <!-- wp:group {"className":"card service-card","layout":{"type":"default"}} -->
      <div class="wp-block-group card service-card">
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading" data-num="05">Formal &amp; luxury pieces</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph {"className":"muted"} -->
        <p class="muted">Wedding and cocktail pieces, wool and restored goods.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->

      <!-- wp:group {"className":"card service-card","layout":{"type":"default"}} -->
      <div class="wp-block-group card service-card">
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading" data-num="06">Express service</h3>
        <!-- /wp:heading -->
        <!-- wp:paragraph {"className":"muted"} -->
        <p class="muted">Often next day if dropped in by 10am. Ask at the counter.</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:group -->
  </div>
  <!-- /wp:group -->
</section>
<!-- /wp:group -->

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

    <!-- wp:image {"sizeSlug":"full","className":"story-photo"} -->
    <figure class="wp-block-image size-full story-photo"><img src="<?php echo $fascia; ?>" alt="Cornwall script lettering and navy awning on the Haven Lane fascia"/></figure>
    <!-- /wp:image -->

    <!-- wp:group {"className":"card quote-panel","layout":{"type":"default"}} -->
    <div class="wp-block-group card quote-panel">
      <!-- wp:quote -->
      <blockquote class="wp-block-quote">
        <p>“These guys really care about the quality of the service they provide and clearly check your goods before handing them back.”</p>
      </blockquote>
      <!-- /wp:quote -->
      <!-- wp:paragraph -->
      <p>Google review · December 2025</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
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
    <h2 class="wp-block-heading">4.9 from people who use the shop.</h2>
    <!-- /wp:heading -->
    <!-- wp:paragraph {"className":"muted"} -->
    <p class="muted">Stain removal, next-day work and repairs.</p>
    <!-- /wp:paragraph -->

    <!-- wp:group {"className":"review-grid","layout":{"type":"default"}} -->
    <div class="wp-block-group review-grid">
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
        <p class="muted">Google review · December 2025</p>
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
        <p class="muted">Google review · December 2025</p>
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
        <p class="muted">Google review · December 2025</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:group -->

    <!-- wp:buttons {"className":"follow-on btn-row"} -->
    <div class="wp-block-buttons follow-on btn-row">
      <!-- wp:button {"backgroundColor":"navy","textColor":"white","className":"btn-navy"} -->
      <div class="wp-block-button btn-navy"><a class="wp-block-button__link has-white-color has-navy-background-color has-text-color has-background wp-element-button" href="https://maps.app.goo.gl/cw7sznF2TJq2SkL69" target="_blank" rel="noreferrer">All reviews</a></div>
      <!-- /wp:button -->
      <!-- wp:button {"className":"btn-line"} -->
      <div class="wp-block-button btn-line"><a class="wp-block-button__link wp-element-button" href="https://maps.app.goo.gl/cw7sznF2TJq2SkL69" target="_blank" rel="noreferrer">Write a review</a></div>
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
        <h2>23 Haven Lane, Ealing, W5 2HZ</h2>
        <p class="muted">1 minute from Haven Green / Ealing Broadway station. White fascia, blue awning — not on the Broadway itself.</p>
        <p class="phone-xl"><a href="tel:+442089919413">020 8991 9413</a></p>
        <p class="btn-row">
          <a class="btn btn-navy" href="tel:+442089919413">Call</a>
          <a class="btn btn-line" href="https://www.google.com/maps/dir/?api=1&amp;destination=23+Haven+Lane,+Ealing,+London+W5+2HZ" target="_blank" rel="noreferrer">Directions</a>
          <a class="btn btn-line" href="https://wa.me/442089919413" target="_blank" rel="noreferrer">WhatsApp</a>
        </p>
      </div>
      <div class="map-frame">
        <iframe class="map" title="Map showing Cornwall Dry Cleaners on Haven Lane, Ealing" src="https://maps.google.com/maps?q=Cornwall+Dry+Cleaners,+23+Haven+Lane,+Ealing+W5+2HZ&amp;z=16&amp;output=embed" loading="lazy"></iframe>
      </div>
    </div>
    <div class="visit-plan">
      <div>
        <table class="hours hours-table">
          <caption>Opening hours</caption>
          <tbody>
            <tr data-day="1"><th>Monday</th><td>8:00am – 6:00pm</td></tr>
            <tr data-day="2"><th>Tuesday</th><td>8:00am – 6:00pm</td></tr>
            <tr data-day="3"><th>Wednesday</th><td>8:00am – 6:00pm</td></tr>
            <tr data-day="4"><th>Thursday</th><td>8:00am – 6:00pm</td></tr>
            <tr data-day="5"><th>Friday</th><td>8:00am – 6:00pm</td></tr>
            <tr data-day="6"><th>Saturday</th><td>9:00am – 5:00pm</td></tr>
            <tr data-day="0"><th>Sunday</th><td>Closed</td></tr>
          </tbody>
        </table>
        <p class="muted">Closed on bank holidays — call the day before if you are unsure.</p>
      </div>
      <nav class="visit-faq" id="faq" aria-label="Frequently asked questions">
        <p class="faq-caption">Questions</p>
        <div class="faq-list">
          <details>
            <summary>How long does it take?</summary>
            <p>Often next day if dropped in by 10am. Repairs and wedding pieces can take longer — we will say when you drop off.</p>
          </details>
          <details>
            <summary>Collect or deliver?</summary>
            <p>No. Drop in on Haven Lane.</p>
          </details>
          <details>
            <summary>Cards or cash?</summary>
            <p>Both. Contactless at the counter.</p>
          </details>
          <details>
            <summary>Bank holidays?</summary>
            <p>Usually closed. Call the day before if you need to be sure.</p>
          </details>
          <details>
            <summary>Not sure we can take it?</summary>
            <p>Bring it in. We inspect first and say honestly what will come out.</p>
          </details>
        </div>
      </nav>
    </div>
  </div>
  <!-- /wp:html -->
</section>
<!-- /wp:group -->

</main>
<!-- /wp:group -->
