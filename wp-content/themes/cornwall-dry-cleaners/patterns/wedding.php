<?php
/**
 * Title: Wedding dress cleaning & alterations (mockup)
 * Slug: cornwall/wedding
 * Categories: featured
 * Description: Draft landing page for wedding dress cleaning, occasion wear and alterations. Bracketed text needs confirming before publishing.
 */
$tel        = esc_attr('tel:' . cornwall_biz('phone'));
$phone      = esc_html(cornwall_biz('phone_display'));
$directions = esc_url(cornwall_biz('directions_url'));
$cards = array(
    array('Wedding dresses', 'Cleaned after the day, or freshened before it. Lace, tulle, satin and beading are checked by hand first.', 'Confirm: any fabrics or trains you do not take'),
    array('Bridesmaid & occasion wear', 'Bridesmaid, cocktail and party pieces — including spills on the night.', ''),
    array('Suits & morning wear', 'Wedding suits, waistcoats and morning coats, cleaned and pressed.', ''),
    array('Alterations', 'Hems, zips, stitching and loose buttons.', 'Confirm: taking in, letting out, bustles, straps'),
    array('Stain check', 'Wine, make-up, grass and hem marks. We inspect first and say honestly what will come out.', ''),
    array('Ready to collect', 'Pressed and ready on the date we agree.', 'Confirm: bagged or boxed; any preservation boxing'),
);
$faqs = array(
    array('How far ahead should I bring it?', '', 'Confirm: how many weeks before the day for cleaning, and for alterations'),
    array('Can you remove old or set-in stains?', 'Bring it in. We inspect first and say honestly what will come out.', ''),
    array('Do I need an appointment?', '', 'Confirm: drop in during opening hours, or book fittings'),
    array('How much does it cost?', 'It depends on the fabric and the work. We tell you the price at the counter before we start.', 'Confirm: show a “from £…” price or not'),
);
$tbc = function ($note) {
    return $note === '' ? '' : ' <mark class="tbc">[' . esc_html($note) . ']</mark>';
};
?>
<!-- wp:html -->
<p class="mockup-banner"><span class="wrap">Mockup for review — not published. Highlighted <mark class="tbc">[Confirm …]</mark> notes need Gina’s answer before this goes live.</span></p>
<!-- /wp:html -->

<!-- wp:group {"tagName":"main","className":"cornwall-main landing"} -->
<main class="wp-block-group cornwall-main landing">

<!-- wp:html -->
<section class="b-hero landing-hero">
  <div class="wrap">
    <div class="b-kicker">
      <p>Haven Lane · Ealing Broadway</p>
      <p><?php echo $phone; ?></p>
    </div>
    <h1>Wedding dress cleaning and alterations in Ealing.</h1>
    <p class="lead">Bring your dress, suit or occasion outfit to the counter on Haven Lane. We look at the fabric, beading and any marks first, then tell you honestly what we can do and when it will be ready.</p>
    <p class="btn-row hero-actions">
      <a class="btn btn-white" href="<?php echo $tel; ?>">Call <?php echo $phone; ?></a>
      <a class="btn btn-line" href="<?php echo $directions; ?>" target="_blank" rel="noreferrer">Directions</a>
    </p>
  </div>
</section>

<section class="section" id="wedding-services">
  <div class="wrap">
    <p class="eyebrow">What we take in</p>
    <h2>Before the day, after the day, and everything in between.</h2>
    <div class="service-grid">
<?php foreach ($cards as $i => $card) : ?>
      <div class="card service-card">
        <h3 data-num="<?php echo esc_attr(sprintf('%02d', $i + 1)); ?>"><?php echo esc_html($card[0]); ?></h3>
        <p class="muted"><?php echo esc_html($card[1]); ?><?php echo $tbc($card[2]); ?></p>
      </div>
<?php endforeach; ?>
    </div>
  </div>
</section>

<section class="steps">
  <div class="wrap">
    <p class="eyebrow">How it works</p>
    <h2>Drop in on Haven Lane.</h2>
    <div class="step-grid">
      <div>
        <p><span>01</span></p>
        <h3>Bring it in</h3>
        <p>Tell us the date you need it back.<?php echo $tbc('Confirm: no appointment needed'); ?></p>
      </div>
      <div>
        <p><span>02</span></p>
        <h3>We check it</h3>
        <p>Fabric, beading and marks are looked at before we start, and we agree what we will do.</p>
      </div>
      <div>
        <p><span>03</span></p>
        <h3>Collect</h3>
        <p>Repairs and wedding pieces can take longer — we will say when you drop off.</p>
      </div>
    </div>
  </div>
</section>

<section class="section story">
  <div class="wrap landing-split">
    <div class="card quote-panel">
      <blockquote><p>“These guys really care about the quality of the service they provide and clearly check your goods before handing them back.”</p></blockquote>
      <p>Google review</p>
    </div>
    <div class="visit-faq landing-faq" aria-label="Wedding questions">
      <p class="faq-caption">Questions</p>
      <div class="faq-list">
<?php foreach ($faqs as $faq) : ?>
        <details open>
          <summary><?php echo esc_html($faq[0]); ?></summary>
          <p><?php echo esc_html($faq[1]); ?><?php echo $tbc($faq[2]); ?></p>
        </details>
<?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="section site-pad landing-visit">
  <div class="wrap">
    <p class="eyebrow">Visit the shop</p>
    <h2 class="visit-address">
      <span class="visit-street"><?php echo esc_html(cornwall_biz('street')); ?></span>
      <span class="visit-locality"><?php echo esc_html(cornwall_biz('locality') . ', ' . cornwall_biz('postcode')); ?></span>
    </h2>
    <p class="muted"><?php echo esc_html(cornwall_hours_summary(true)); ?>. <?php echo esc_html(cornwall_closed_days_sentence()); ?></p>
    <p class="btn-row">
      <a class="btn btn-navy" href="<?php echo $tel; ?>">Call</a>
      <a class="btn btn-line" href="<?php echo $directions; ?>" target="_blank" rel="noreferrer">Directions</a>
    </p>
  </div>
</section>
<!-- /wp:html -->

</main>
<!-- /wp:group -->
