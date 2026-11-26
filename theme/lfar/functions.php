<?php
/**
 * LFAR theme setup.
 *
 * Each of the six main pages brings its own stylesheet and script (assets/css/<page>.css, assets/js/<page>.js).
 * They started as a conversion of design/final-mockups but are now edited directly. Other pages (posts,
 * archives, new pages) fall back to the Home styles and assets/js/common.js.
 *
 * @package lfar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const LFAR_PAGES = array( 'home', 'about', 'adopt', 'get-involved', 'contact', 'donate', 'events', 'resources' );

/**
 * Stylesheet/script file for a page key. Events reuses Home; Resources reuses Contact.
 *
 * @param string $key
 */
function lfar_asset_key( $key ) {
	$map = array(
		'events'    => 'home',
		'resources' => 'contact',
	);
	if ( isset( $map[ $key ] ) ) {
		return $map[ $key ];
	}
	return $key ? $key : 'home';
}

// Instagram feed on About, Get Involved and Adopt (Settings → LFAR Instagram).
require_once get_theme_file_path( 'inc/instagram.php' );
require_once get_theme_file_path( 'inc/public.php' );
require_once get_theme_file_path( 'inc/qr.php' );

/**
 * Which mockup a page belongs to, or '' if none.
 *
 * @param int $post_id Optional post ID (used in the editor). Omit on the front end.
 */
function lfar_page_key( $post_id = 0 ) {
	if ( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}
		if ( (int) get_option( 'page_on_front' ) === (int) $post->ID ) {
			return 'home';
		}
		return ( 'page' === $post->post_type && in_array( $post->post_name, LFAR_PAGES, true ) ) ? $post->post_name : '';
	}
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( in_array( $slug, LFAR_PAGES, true ) ) {
			return $slug;
		}
	}
	return '';
}

/**
 * Page key on <body> so shared ids (Donate and Get Involved both use #give) can be styled per page.
 */
add_filter(
	'body_class',
	function ( $classes ) {
		$key = lfar_page_key();
		if ( $key ) {
			$classes[] = 'lfar-page-' . $key;
		}
		if ( is_page() ) {
			$slug = get_post_field( 'post_name', get_queried_object_id() );
			if ( in_array( $slug, array( 'adoption-form', 'qr' ), true ) ) {
				$classes[] = 'lfar-page-' . $slug;
			}
		}
		return $classes;
	}
);

/**
 * Titles each shortcode key looks up, first match wins.
 * Rename a form in Fluent Forms? Add the new title at the front of its list.
 *
 * @return array<string,string[]>
 */
function lfar_form_titles() {
	return array(
		// "Adoption form" is the name the dedicated page asks for. The form on the
		// site today is titled "Adoption application", so that title still matches.
		'adoption'   => array( 'Adoption form', 'Adoption application' ),
		'foster'     => array( 'Foster application' ),
		'volunteer'  => array( 'Volunteer sign-up' ),
		'contact'    => array( 'Contact' ),
		'newsletter' => array( 'Newsletter sign-up' ),
	);
}

/**
 * Fluent Forms ID for one of the site's forms, found by its title (so it survives a move to production).
 *
 * @param string $key A key from lfar_form_titles(), or an exact form title.
 */
function lfar_form_id( $key ) {
	static $by_title = null;
	$key = is_string( $key ) ? $key : '';
	if ( null === $by_title ) {
		global $wpdb;
		$by_title = array();
		$table    = $wpdb->prefix . 'fluentform_forms';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$rows = $wpdb->get_results( "SELECT id, title FROM {$table} ORDER BY id ASC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			foreach ( $rows as $row ) {
				if ( ! isset( $by_title[ $row->title ] ) ) {
					$by_title[ $row->title ] = (int) $row->id;
				}
			}
		}
	}
	$known  = lfar_form_titles();
	$wanted = isset( $known[ $key ] ) ? $known[ $key ] : ( '' !== $key ? array( $key ) : array() );
	foreach ( $wanted as $title ) {
		if ( isset( $by_title[ $title ] ) ) {
			return $by_title[ $title ];
		}
	}
	return 0;
}

/**
 * [lfar_form name="adoption"]: the Fluent Form with that title, looked up each time the page renders.
 * (Writing [fluentform id="3"] into the page would freeze whatever id existed when the page was first saved.)
 */
add_shortcode(
	'lfar_form',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'name' => '' ), $atts, 'lfar_form' );
		$id   = lfar_form_id( $atts['name'] );
		if ( ! $id ) {
			$known = lfar_form_titles();
			$label = isset( $known[ $atts['name'] ] ) ? implode( '" or "', $known[ $atts['name'] ] ) : $atts['name'];
			return current_user_can( 'edit_pages' ) ? '<p class="lfar-form-missing">[Form "' . esc_html( $label ) . '" not found in Fluent Forms]</p>' : '';
		}
		return do_shortcode( '[fluentform id="' . (int) $id . '"]' );
	}
);

/*
 * Shortcode blocks inside patterns used by templates (e.g. the newsletter form in every page footer)
 * are expanded after WordPress has already processed shortcodes, so run them here.
 */
add_filter(
	'render_block_core/shortcode',
	function ( $content ) {
		return do_shortcode( shortcode_unautop( $content ) );
	}
);

/** Cache-busting version: the file's modified time. */
function lfar_ver( $rel ) {
	$path = get_theme_file_path( $rel );
	return file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme()->get( 'Version' );
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'editor-styles' );
		add_theme_support( 'wp-block-styles' );
		// WordPress caches the pattern file list for the life of the theme version.
		// A new pattern (Adoption form, QR) would stay invisible until that cache
		// expired, so drop it once when those files change.
		$theme = wp_get_theme();
		if ( ! is_callable( array( $theme, 'delete_pattern_cache' ) ) ) {
			return;
		}
		$stamp = lfar_ver( 'patterns/page-adoption-form.php' ) . ':' . lfar_ver( 'patterns/page-qr.php' );
		if ( get_option( 'lfar_pattern_stamp' ) === $stamp ) {
			return;
		}
		$theme->delete_pattern_cache();
		update_option( 'lfar_pattern_stamp', $stamp, false );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$key  = lfar_page_key();
		$file = lfar_asset_key( $key );
		$css  = 'assets/css/' . $file . '.css';
		$js   = 'assets/js/' . ( $key ? $file : 'common' ) . '.js';
		wp_enqueue_style( 'lfar-base', get_theme_file_uri( 'assets/css/base.css' ), array(), lfar_ver( 'assets/css/base.css' ) );
		wp_enqueue_style( 'lfar-page', get_theme_file_uri( $css ), array( 'lfar-base' ), lfar_ver( $css ) );
		wp_enqueue_style( 'lfar-adjust', get_theme_file_uri( 'assets/css/adjust.css' ), array( 'lfar-page' ), lfar_ver( 'assets/css/adjust.css' ) );
		// Front end only: the paragraph wrapped around stand-alone links/buttons steps out of the layout,
		// so the link sits in flex/grid rows exactly as in the mockups. (Kept out of the editor so the block stays selectable.)
		wp_add_inline_style( 'lfar-base', 'p.lfar-a.lfar-a{display:contents}' );
		// Same for the paragraph wrapped around a converted text element (span, em, cite...): it adds no box and no styles,
		// so the element sits and looks exactly as in the mockup.
		wp_add_inline_style( 'lfar-base', 'p.lfar-w.lfar-w{all:unset;display:contents}' );
		wp_enqueue_script( 'lfar-page', get_theme_file_uri( $js ), array(), lfar_ver( $js ), array( 'in_footer' => true ) );
		wp_enqueue_style( 'lfar-forms', get_theme_file_uri( 'assets/css/forms.css' ), array( 'lfar-page' ), lfar_ver( 'assets/css/forms.css' ) );
		wp_enqueue_script( 'lfar-forms', get_theme_file_uri( 'assets/js/forms.js' ), array( 'lfar-page' ), lfar_ver( 'assets/js/forms.js' ), array( 'in_footer' => true ) );
		// Adopt: Petfinder's pet list widget (the <pet-scroller> element in the "See all adoptable animals" panel).
		if ( 'adopt' === $key ) {
			wp_enqueue_script( 'petfinder-pet-scroller', 'https://www.petfinder.com/pet-scroller.bundle.js', array(), null, array( 'in_footer' => true ) );
		}
		// Home and Donate: Donorbox's script, which sizes the donation form iframe to its content.
		if ( 'home' === $key || 'donate' === $key ) {
			wp_enqueue_script( 'donorbox-widget', 'https://donorbox.org/widget.js', array(), null, array( 'in_footer' => true ) );
		}
	}
);

// About, Get Involved, Contact: their scripts mark <html> with .js (hero parts wait for the hero image), but they load
// in the footer. Set it in the head too, so the no-JS fallback (e.g. About's map labels) never flashes first.
add_action(
	'wp_head',
	function () {
		$key = lfar_page_key();
		if ( in_array( $key, array( 'about', 'adopt', 'get-involved', 'contact' ), true ) ) {
			$script = "document.documentElement.classList.add('js');";
			// Hide the bottom bar until the Adopt hero has scrolled away (phone and tablet).
			if ( 'adopt' === $key ) {
				$script .= "document.documentElement.classList.add('lfar-hero-cover');";
			}
			wp_print_inline_script_tag( $script );
		}
	},
	1
);

// Donorbox's embed code loads widget.js with paypalExpress="false"; keep that attribute.
add_filter(
	'script_loader_tag',
	function ( $tag, $handle ) {
		return 'donorbox-widget' === $handle ? str_replace( ' src=', ' paypalExpress="false" src=', $tag ) : $tag;
	},
	10,
	2
);

// Editor: load the same page styles so pages look like the front end while editing.
add_action(
	'admin_init',
	function () {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$key     = $post_id ? lfar_page_key( $post_id ) : '';
		add_editor_style( array( 'assets/css/base.css', 'assets/css/' . lfar_asset_key( $key ) . '.css', 'assets/css/adjust.css', 'assets/css/editor.css' ) );
	}
);

add_action(
	'init',
	function () {
		register_block_pattern_category( 'lfar-pages', array( 'label' => __( 'LFAR pages', 'lfar' ) ) );
		register_block_pattern_category( 'lfar-chrome', array( 'label' => __( 'LFAR site sections', 'lfar' ) ) );
		register_block_style( 'core/image', array( 'name' => 'blob', 'label' => __( 'Blob', 'lfar' ) ) );
		register_block_style( 'core/image', array( 'name' => 'irregular', 'label' => __( 'Irregular', 'lfar' ) ) );

		// Featured pet block (Adopt page): its picker in the editor and its "Adopted!" check both read LFAR's animals from Petfinder.
		wp_register_script( 'lfar-petfinder', get_theme_file_uri( 'blocks/featured-pet/petfinder.js' ), array(), lfar_ver( 'blocks/featured-pet/petfinder.js' ), array( 'in_footer' => true ) );
		wp_add_inline_script( 'lfar-petfinder', 'lfarPetfinder.sherlockPhoto = ' . wp_json_encode( get_theme_file_uri( 'assets/images/sherlock-a-young-grey-brindle-dog-held-by-his-fo.jpg' ) ) . ';' );
		register_block_type( get_theme_file_path( 'blocks/featured-pet' ) );
	}
);

/**
 * Pages the theme expects. Resources stays a draft until its lists are written.
 * Existing pages are left alone, including a Resources page someone has published.
 *
 * @return array<string,array{0:string,1:string}>
 */
function lfar_page_defs() {
	return array(
		'home'         => array( 'Home', 'publish' ),
		'about'        => array( 'About', 'publish' ),
		'adopt'        => array( 'Adopt', 'publish' ),
		'get-involved' => array( 'Get Involved', 'publish' ),
		'contact'      => array( 'Contact', 'publish' ),
		'donate'       => array( 'Donate', 'publish' ),
		'events'         => array( 'Events', 'publish' ),
		'resources'      => array( 'Resources', 'draft' ),
		'adoption-form'  => array( 'Adoption form', 'publish' ),
		'qr'             => array( 'QR code', 'publish' ),
	);
}

/**
 * Create any missing pages. A page holds a reference to its theme pattern until
 * someone saves it in the editor. Re-run by deleting the lfar_pages_v option.
 */
function lfar_ensure_pages() {
	$ids = array();
	foreach ( lfar_page_defs() as $slug => $info ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			continue;
		}
		$ids[ $slug ] = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => $info[1],
				'post_title'   => $info[0],
				'post_name'    => $slug,
				'post_content' => '<!-- wp:pattern {"slug":"lfar/page-' . $slug . '"} /-->',
			)
		);
	}
	if ( ! empty( $ids['home'] ) && ! is_wp_error( $ids['home'] ) && 'page' !== get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $ids['home'] );
	}
	foreach ( array( 'news' => 'News', 'happy-tails' => 'Happy Tails' ) as $slug => $name ) {
		if ( ! term_exists( $slug, 'category' ) ) {
			wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
	}
	update_option( 'lfar_seeded', 1 );
	update_option( 'lfar_pages_v', 3 );
}

add_action(
	'init',
	function () {
		if ( 3 === (int) get_option( 'lfar_pages_v' ) ) {
			return;
		}
		lfar_ensure_pages();
		flush_rewrite_rules( false );
	},
	20
);

