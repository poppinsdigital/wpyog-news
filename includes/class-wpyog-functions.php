<?php
/**
 * Global helper functions.
 *
 * @package WPYog_News
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a unique incrementing integer for the current request.
 * Handles Elementor AJAX previews by using a timestamp-based value.
 *
 * @return int|string
 */
function wpyog_news_unique() {
	static $count = 0;
	$count++;

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Read-only detection of Elementor AJAX context; no form data is processed.
	if ( defined( 'ELEMENTOR_PLUGIN_BASE' )
		&& isset( $_POST['action'] )
		&& 'elementor_ajax' === sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		return time() . '-' . wp_rand();
	}

	return $count;
}

/**
 * Sanitize a space-separated list of HTML class names.
 *
 * @param string $classes Space-separated class names.
 * @return string
 */
function wpyog_sanitize_classes( $classes ) {
	$list   = explode( ' ', $classes );
	$clean  = array_map( 'sanitize_html_class', $list );
	$clean  = array_filter( $clean );
	return implode( ' ', $clean );
}

/**
 * Recursively sanitize a scalar or array value.
 *
 * @param mixed $var Value to clean.
 * @return mixed
 */
function wpyog_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'wpyog_clean', $var );
	}
	return is_scalar( $var ) ? wp_unslash( sanitize_text_field( $var ) ) : $var;
}

/**
 * Return public post types that can be mixed into a [wpyog_news] layout or tagged
 * with a Collection (everything except attachments). Shared by the admin Shortcode
 * Generator, the Gutenberg block, and the Collections taxonomy registration so the
 * "mixable" and "taggable" post type lists always stay in sync.
 *
 * @return WP_Post_Type[] Keyed by post type slug.
 */
function wpyog_news_get_mixable_post_types() {
	return array_filter(
		get_post_types( array( 'public' => true ), 'objects' ),
		function ( $post_type ) {
			return 'attachment' !== $post_type->name;
		}
	);
}

/**
 * Sanitize a comma-separated list of post type slugs for the [wpyog_news] shortcode
 * and Gutenberg block, so multiple CPTs can be mixed into the same layout.
 *
 * Unknown/unregistered post types are dropped. Falls back to WPYOG_NEWS_POST_TYPE
 * when the list is empty or nothing valid remains.
 *
 * @param string $raw Comma-separated post type slugs, e.g. "wpyog_news,post,product".
 * @return string[] Array of valid, registered post type slugs.
 */
function wpyog_news_sanitize_post_types( $raw ) {

	$requested = ! empty( $raw ) ? array_filter( array_map( 'sanitize_key', explode( ',', $raw ) ) ) : array();

	$post_types = array();
	foreach ( $requested as $post_type ) {
		if ( post_type_exists( $post_type ) ) {
			$post_types[] = $post_type;
		}
	}

	$post_types = array_unique( $post_types );

	if ( empty( $post_types ) ) {
		$post_types = array( WPYOG_NEWS_POST_TYPE );
	}

	return $post_types;
}

/**
 * Return excerpt-length content for a news post, respecting manual excerpts.
 *
 * @param int    $post_id      Post ID.
 * @param string $content      Raw post content fallback.
 * @param int    $word_count   Max words.
 * @param string $more         Suffix appended when truncated.
 * @return string
 */
function wpyog_news_excerpt( $post_id, $content = '', $word_count = 20, $more = '&hellip;' ) {

	if ( has_excerpt( $post_id ) ) {
		return get_the_excerpt();
	}

	$content = strip_shortcodes( $content );
	$content = wp_strip_all_tags( $content );
	return wp_trim_words( $content, absint( $word_count ), $more );
}

/**
 * Render numeric / prev-next pagination links for the news shortcode.
 *
 * @param array $args {
 *   @type int    $paged           Current page number.
 *   @type int    $total           Total pages.
 *   @type string $pagination_type 'numeric' or 'prev-next'.
 *   @type string $unique          Unique shortcode identifier (for scroll anchor).
 *   @type bool   $multi_page      Whether shortcode is on a multi-post page.
 * }
 * @return string HTML pagination links.
 */
function wpyog_news_pagination( $args = array() ) {

	$defaults = array(
		'paged'           => 1,
		'total'           => 1,
		'pagination_type' => 'numeric',
		'unique'          => 1,
		'multi_page'      => false,
	);
	$args = wp_parse_args( $args, $defaults );

	$big = 999999999;

	$paging_args = array(
		'base'         => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format'       => '?paged=%#%',
		'current'      => max( 1, $args['paged'] ),
		'total'        => $args['total'],
		'prev_text'    => '&laquo; ' . esc_html__( 'Previous', 'wpyog-news' ),
		'next_text'    => esc_html__( 'Next', 'wpyog-news' ) . ' &raquo;',
		'add_fragment' => '#wpyog-news-' . $args['unique'],
	);

	// When shortcode appears inside a single post / front page, use query-string paging.
	if ( $args['multi_page'] ) {
		$paging_args['base']   = esc_url_raw( add_query_arg( 'news_page', '%#%' ) );
		$paging_args['format'] = '?news_page=%#%';
	}

	if ( 'prev-next' === $args['pagination_type'] ) {
		$paging_args['type']     = 'array';
		$paging_args['show_all'] = false;
		$paging_args['end_size'] = 1;
		$paging_args['mid_size'] = 0;

		$links = paginate_links( $paging_args );

		if ( is_array( $links ) ) {
			$filtered = array_filter( $links, function( $link ) {
				return strpos( $link, 'next page-numbers' ) !== false
					|| strpos( $link, 'prev page-numbers' ) !== false;
			} );
			return implode( "\n", $filtered );
		}

		return '';
	}

	return paginate_links( $paging_args );
}
