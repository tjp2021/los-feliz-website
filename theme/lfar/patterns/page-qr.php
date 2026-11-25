<?php
/**
 * Title: QR code redirect
 * Slug: lfar/page-qr
 * Categories: lfar-pages
 * Inserter: yes
 * Post Types: page
 * Description: Printed QR codes use /qr/. Visitors are sent to the button link, which can be changed in the editor.
 */
?>
<!-- wp:group {"templateLock":"contentOnly","tagName":"section","className":"lfar-qr-page"} -->
<section class="wp-block-group lfar-qr-page"><!-- wp:group {"className":"container"} -->
<div class="wp-block-group container"><!-- wp:group {"className":"section-heading"} -->
<div class="wp-block-group section-heading"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">QR code</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Where the QR code goes</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"subhead"} -->
<p class="subhead">People who scan a printed code never see this page. They are sent to the button below. To send them somewhere else, select the button, click the link icon, paste the new address, and press Update.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"lfar-qr-destination"} -->
<div class="wp-block-group lfar-qr-destination"><!-- wp:paragraph {"className":"lfar-a"} -->
<p class="lfar-a"><a class="btn" href="/">Send people here</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"lfar-qr-note"} -->
<p class="lfar-qr-note">The printed address is /qr/. Add ?stay=1 to that address if you want to read this page in the browser instead of being sent onward.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
