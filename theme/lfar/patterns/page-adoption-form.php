<?php
/**
 * Title: Adoption form page
 * Slug: lfar/page-adoption-form
 * Categories: lfar-pages
 * Inserter: yes
 * Post Types: page
 * Description: A standalone page for the Fluent Forms adoption form. The form is found by its title, Adoption form, or Adoption application.
 */
?>
<!-- wp:group {"templateLock":"contentOnly","tagName":"section","className":"lfar-form-page"} -->
<section class="wp-block-group lfar-form-page"><!-- wp:group {"className":"container"} -->
<div class="wp-block-group container"><!-- wp:group {"className":"section-heading"} -->
<div class="wp-block-group section-heading"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">Adopt</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Adoption form</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"subhead"} -->
<p class="subhead">Tell us about yourself and the animal you hope to welcome home. We read every application.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"app lfar-app lfar-form-card"} -->
<div class="wp-block-group app lfar-app lfar-form-card"><!-- wp:group {"className":"lfar-form lfar-form-adopt"} -->
<div class="wp-block-group lfar-form lfar-form-adopt"><!-- wp:shortcode -->
[lfar_form name="adoption"]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
