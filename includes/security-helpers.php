<?php
/**
 * Small, dependency-free helpers that guard widget-controlled input.
 *
 * Widget settings are saved verbatim by Elementor for anyone who may edit a post
 * with it, Contributors included: a SELECT value is never checked against its
 * options, and a URL control keeps whatever scheme it was given. These helpers
 * are the single place where such values are checked before they reach markup
 * or a query. Every function is defined only if missing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'pixel_gallery_window_open_handler' ) ) {
	/**
	 * Build a safe window.open() call for an onclick attribute.
	 *
	 * Link URLs come from widget settings that any page editor (Contributor
	 * included) can save. Putting esc_url() output inside a quoted JS string is
	 * not safe: esc_url() turns ' into &#039;, esc_attr() does not double-encode
	 * it, and the browser decodes it back to ' before the handler is compiled,
	 * so ?');alert(1);(' breaks out of the string. Here the URL is JSON-encoded
	 * with every quote and markup character hex-escaped, and only web, mail,
	 * phone and relative URLs are accepted.
	 *
	 * @param string $url    Link URL from a URL control.
	 * @param bool   $is_new Open in a new tab.
	 * @return string The handler, or an empty string when the URL is not allowed.
	 */
	function pixel_gallery_window_open_handler( $url, $is_new = false ) {
		// A URL saved by a user without unfiltered_html has been through kses, which
		// stores & as &amp;. The browser used to decode that inside the attribute; the
		// JSON string below is not decoded again, so decode it here, before the scheme
		// check runs on the real value.
		$url = html_entity_decode( (string) $url, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$url = esc_url_raw( trim( $url ), [ 'http', 'https', 'mailto', 'tel' ] );

		if ( '' === $url ) {
			return '';
		}

		$flags = JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP;

		return 'window.open(' . wp_json_encode( $url, $flags ) . ', ' . wp_json_encode( $is_new ? '_blank' : '_self' ) . ')';
	}
}

if ( ! function_exists( 'pixel_gallery_query_post_types' ) ) {
	/**
	 * Post types a gallery query (and the editor's post search) may use.
	 *
	 * These are exactly the types the Source control offers: public post types,
	 * minus media and saved templates. Elementor stores a SELECT value verbatim
	 * and never checks it against the control's options, so without this list a
	 * Contributor could point a gallery at any registered type (WooCommerce
	 * coupons, form submissions, ...) and have its published titles printed.
	 *
	 * @return string[] Post type names.
	 */
	function pixel_gallery_query_post_types() {
		$excluded = [ 'elementor_library', 'attachment', 'bdt_template_manager', 'bdt-custom-template' ];

		$post_types = array_values( array_diff( array_keys( get_post_types( [ 'public' => true ] ) ), $excluded ) );

		/**
		 * Filters the post types a Pixel Gallery query may use.
		 *
		 * Only add types whose published entries visitors may see.
		 *
		 * @param string[] $post_types Post type names.
		 */
		return array_values( array_filter( (array) apply_filters( 'pixel_gallery/query/post_types', $post_types ), 'is_string' ) );
	}
}

if ( ! function_exists( 'pixel_gallery_sanitize_query_post_type' ) ) {
	/**
	 * Restrict a post type taken from widget settings to pixel_gallery_query_post_types().
	 *
	 * @param mixed  $post_type Requested post type (the saved Source value).
	 * @param string $fallback  Post type used when the requested one is not allowed.
	 * @return string
	 */
	function pixel_gallery_sanitize_query_post_type( $post_type, $fallback = 'post' ) {
		if ( is_string( $post_type ) && in_array( $post_type, pixel_gallery_query_post_types(), true ) ) {
			return $post_type;
		}

		return $fallback;
	}
}
