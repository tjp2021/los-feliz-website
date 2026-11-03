<?php
/**
 * Title: Events page
 * Slug: lfar/page-events
 * Categories: lfar-pages
 * Inserter: yes
 * Post Types: page
 */
?>
<!-- wp:group {"templateLock":"contentOnly","tagName":"section","className":"hero"} -->
<section class="wp-block-group hero"><!-- wp:image {"className":"hero-bg"} -->
<figure class="wp-block-image hero-bg"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/a-volunteer-holding-a-small-tan-dog.jpg' ) ); ?>" alt="A volunteer holding a small tan dog at an adoption event"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"container"} -->
<div class="wp-block-group container"><!-- wp:group {"className":"hero-card"} -->
<div class="wp-block-group hero-card"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">Events</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Upcoming and past events</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"header-p"} -->
<p class="header-p">What we're doing next, and a record of what we've already done.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lfar-a"} -->
<p class="lfar-a"><a class="btn" href="#upcoming">Upcoming</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"templateLock":"contentOnly","tagName":"section","className":"fw bg-light section2 events-block"} -->
<section class="wp-block-group fw bg-light section2 events-block" id="upcoming"><!-- wp:group {"className":"container"} -->
<div class="wp-block-group container"><!-- wp:group {"className":"section-heading"} -->
<div class="wp-block-group section-heading"><!-- wp:heading -->
<h2 class="wp-block-heading">Upcoming events</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"subhead"} -->
<p class="subhead">The next one also stays pinned on the homepage until it's over.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:html -->
<div class="news-grid events-upcoming">
<article class="news-card is-pinned">
<a class="thumb" href="#upcoming"><span class="tag">Event</span><img alt="A volunteer holding a small tan dog" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/a-volunteer-holding-a-small-tan-dog.jpg' ) ); ?>"/><span class="overlay-mask">Event details</span></a>
<div class="news-body">
<p class="col-kicker">Next event</p>
<div class="meta"><span>Saturday, November 21, 2026 · 11 am – 2 pm</span><span>Petco, Los Feliz</span></div>
<h4>Adoption Meet &amp; Greet at Petco</h4>
<p>Meet adoptable dogs and cats from our foster homes, talk with our volunteers and pick up an adoption application.</p>
</div>
</article>
</div>
<!-- /wp:html --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"templateLock":"contentOnly","tagName":"section","className":"fw section2 events-block"} -->
<section class="wp-block-group fw section2 events-block" id="past"><!-- wp:group {"className":"container"} -->
<div class="wp-block-group container"><!-- wp:group {"className":"section-heading"} -->
<div class="wp-block-group section-heading"><!-- wp:heading -->
<h2 class="wp-block-heading">Past events</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"subhead"} -->
<p class="subhead">An archive of what the rescue has already held.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:html -->
<div class="news-grid events-past">
<article class="news-card">
<a class="thumb" href="#past"><span class="tag">Past event</span><img alt="Three volunteers sitting with a dog" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/three-volunteers-sitting-with-a-dog.jpg' ) ); ?>"/><span class="overlay-mask">Event details</span></a>
<div class="news-body">
<p class="col-kicker">Past event</p>
<div class="meta"><span>Saturday, October 3, 2026</span></div>
<h4>Adoption Meet &amp; Greet at Petco</h4>
<p>Meet adoptable dogs and cats from our foster homes.</p>
</div>
</article>
<article class="news-card">
<a class="thumb" href="#past"><span class="tag">Past event</span><img alt="A volunteer orientation group with a dog" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/three-volunteers-sitting-with-a-dog.jpg' ) ); ?>"/><span class="overlay-mask">Event details</span></a>
<div class="news-body">
<p class="col-kicker">Past event</p>
<div class="meta"><span>Saturday, September 12, 2026</span></div>
<h4>Volunteer Orientation</h4>
<p>Fourteen new volunteers joined us on Saturday.</p>
</div>
</article>
</div>
<!-- /wp:html --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
