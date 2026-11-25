<?php
/**
 * /qr/ — a stable address for printed QR codes.
 *
 * The public page redirects to a link Melody edits on the QR code page.
 * Change that link in the block editor; the next visit follows it.
 *
 * @package lfar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Turn a link from the page into an absolute http(s) URL.
 *
 * @param string $href
 */
function lfar_qr_normalize_url( $href ) {
	$href = trim( html_entity_decode( (string) $href, ENT_QUOTES, 'UTF-8' ) );
	if ( '' === $href || '#' === $href[0] ) {
		return '';
	}
	$scheme = strtolower( (string) wp_parse_url( $href, PHP_URL_SCHEME ) );
	if ( in_array( $scheme, array( 'javascript', 'data', 'mailto', 'tel' ), true ) ) {
		return '';
	}
	if ( '/' === $href[0] && ( strlen( $href ) < 2 || '/' !== $href[1] ) ) {
		$href = home_url( $href );
	}
	return wp_http_validate_url( $href ) ? $href : '';
}

/**
 * Path of a URL relative to this site, without a trailing slash.
 *
 * @param string $url
 */
function lfar_qr_relative_path( $url ) {
	$path = strtolower( (string) wp_parse_url( $url, PHP_URL_PATH ) );
	$path = trim( rawurldecode( $path ), '/' );
	$home = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $home && ( $path === $home || 0 === strpos( $path, $home . '/' ) ) ) {
		$path = trim( substr( $path, strlen( $home ) ), '/' );
	}
	return $path;
}

/**
 * First usable link inside the QR page. The button in .lfar-qr-destination wins.
 *
 * @param string $html Rendered page content.
 */
function lfar_qr_href_from_html( $html ) {
	$previous = libxml_use_internal_errors( true );
	$doc      = new DOMDocument();
	$loaded   = $doc->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );
	if ( ! $loaded ) {
		return '';
	}
	$xpath = new DOMXPath( $doc );
	$nodes = $xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " lfar-qr-destination ")]//a[@href]' );
	if ( $nodes instanceof DOMNodeList && $nodes->length ) {
		$url = lfar_qr_normalize_url( $nodes->item( 0 )->getAttribute( 'href' ) );
		if ( $url ) {
			return $url;
		}
	}
	$links = $xpath->query( '//a[@href]' );
	if ( $links instanceof DOMNodeList ) {
		foreach ( $links as $link ) {
			$url = lfar_qr_normalize_url( $link->getAttribute( 'href' ) );
			if ( $url ) {
				return $url;
			}
		}
	}
	return '';
}

/**
 * Where /qr/ currently sends people, or '' when the page has no usable link.
 *
 * @param WP_Post $post
 */
function lfar_qr_destination( $post ) {
	$html = apply_filters( 'the_content', $post->post_content );
	$url  = lfar_qr_href_from_html( $html );
	if ( ! $url || 'qr' === lfar_qr_relative_path( $url ) ) {
		return '';
	}
	return $url;
}

/**
 * Printed codes should pick up a new destination immediately, so this is a
 * short-lived redirect. Add ?stay=1 to view the page instead of following it.
 */
add_action(
	'template_redirect',
	function () {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed() || is_embed() || is_preview() || is_customize_preview() ) {
			return;
		}
		if ( ! is_page( 'qr' ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a public view flag, not a form action.
		if ( isset( $_GET['stay'] ) ) {
			return;
		}
		$post = get_queried_object();
		if ( ! $post instanceof WP_Post ) {
			return;
		}
		$url = lfar_qr_destination( $post );
		if ( ! $url ) {
			return;
		}
		nocache_headers();
		wp_redirect( $url, 302 ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- the link is chosen by an editor; external destinations are allowed.
		exit;
	}
);

/**
 * Keep the instructions page out of search results. Scanners use the redirect.
 */
add_action(
	'wp_head',
	function () {
		if ( is_page( 'qr' ) ) {
			echo "<meta name=\"robots\" content=\"noindex, nofollow\" />\n";
		}
	},
	1
);
