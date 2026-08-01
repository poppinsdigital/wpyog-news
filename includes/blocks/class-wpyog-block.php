<?php
/**
 * Gutenberg Block registration for WPYog News.
 *
 * Registers a server-side rendered block that wraps the [wpyog_news] shortcode.
 * Compatible with Gutenberg, Elementor, Divi, SiteOrigin, and Beaver Builder
 * — all of which can embed shortcodes via their native shortcode / HTML modules.
 *
 * @package WPYog_News
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPYOG_Block {

	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );

		// Elementor dynamic tag compatibility: make shortcode render inside Elementor editor.
		if ( defined( 'ELEMENTOR_PLUGIN_BASE' ) ) {
			add_filter( 'elementor/widget/render_content', array( $this, 'elementor_render' ), 10, 2 );
		}
	}

	/**
	 * Register the Gutenberg block.
	 */
	public function register_block() {

		register_block_type( 'wpyog/news', array(
			'title'           => __( 'WPYog News', 'wpyog-news' ),
			'description'     => __( 'Display news in a list, card, or carousel layout.', 'wpyog-news' ),
			'category'        => 'widgets',
			'icon'            => 'megaphone',
			'supports'        => array(
				'html'      => false,
				'align'     => array( 'wide', 'full' ),
				'className' => true,
			),
			'attributes'      => array(
				'layout'         => array( 'type' => 'string',  'default' => 'list' ),
				'limit'          => array( 'type' => 'integer', 'default' => 10 ),
				'category'       => array( 'type' => 'string',  'default' => '' ),
				'show_date'      => array( 'type' => 'boolean', 'default' => true ),
				'show_excerpt'   => array( 'type' => 'boolean', 'default' => true ),
				'excerpt_length' => array( 'type' => 'integer', 'default' => 20 ),
				'show_source'    => array( 'type' => 'boolean', 'default' => true ),
				'order'          => array( 'type' => 'string',  'default' => 'DESC' ),
				'orderby'        => array( 'type' => 'string',  'default' => 'date' ),
				'columns'        => array( 'type' => 'integer', 'default' => 3 ),
				'pagination'     => array( 'type' => 'boolean', 'default' => true ),
				'pagination_type'=> array( 'type' => 'string',  'default' => 'numeric' ),
				'extra_class'    => array( 'type' => 'string',  'default' => '' ),
				'className'      => array( 'type' => 'string',  'default' => '' ),
				'align'          => array( 'type' => 'string',  'default' => '' ),
				// Mixing multiple post types.
				'post_type'      => array( 'type' => 'string',  'default' => '' ),
				'taxonomy'       => array( 'type' => 'string',  'default' => '' ),
				'show_type'      => array( 'type' => 'boolean', 'default' => false ),
				'ids'            => array( 'type' => 'string',  'default' => '' ),
				'collection'     => array( 'type' => 'string',  'default' => '' ),
				// Carousel-only.
				'slides_to_show' => array( 'type' => 'integer', 'default' => 3 ),
				'autoplay'       => array( 'type' => 'boolean', 'default' => true ),
				'autoplay_speed' => array( 'type' => 'integer', 'default' => 4000 ),
				'infinite'       => array( 'type' => 'boolean', 'default' => true ),
				'arrows'         => array( 'type' => 'boolean', 'default' => true ),
				'arrows_on_hover'=> array( 'type' => 'boolean', 'default' => false ),
				'dots'           => array( 'type' => 'boolean', 'default' => true ),
				'pause_on_hover' => array( 'type' => 'boolean', 'default' => true ),
				'transition'     => array( 'type' => 'string',  'default' => 'slide' ),
				'gap'            => array( 'type' => 'integer', 'default' => 20 ),
			),
			'render_callback' => array( $this, 'render_block' ),
			'editor_script'   => 'wpyog-news-block-editor',
			'editor_style'    => 'wpyog-news-public',
		) );

		// Register the block editor script.
		wp_register_script(
			'wpyog-news-block-editor',
			WPYOG_NEWS_URL . 'assets/js/wpyog-block-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
			WPYOG_NEWS_VERSION,
			true
		);

		wp_localize_script( 'wpyog-news-block-editor', 'wpyogBlockData', array(
			'categories'  => $this->get_categories_for_block(),
			'postTypes'   => $this->get_post_types_for_block(),
			'collections' => $this->get_collections_for_block(),
		) );
	}

	/**
	 * Server-side render callback for the block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML.
	 */
	public function render_block( $attributes ) {

		$atts = array(
			'layout'          => sanitize_text_field( $attributes['layout']          ?? 'list' ),
			'limit'           => absint( $attributes['limit']                        ?? 10 ),
			'category'        => sanitize_text_field( $attributes['category']        ?? '' ),
			'show_date'       => empty( $attributes['show_date'] )       ? 'false' : 'true',
			'show_excerpt'    => empty( $attributes['show_excerpt'] )    ? 'false' : 'true',
			'excerpt_length'  => absint( $attributes['excerpt_length']               ?? 20 ),
			'show_source'     => empty( $attributes['show_source'] )     ? 'false' : 'true',
			'order'           => sanitize_text_field( $attributes['order']           ?? 'DESC' ),
			'orderby'         => sanitize_text_field( $attributes['orderby']         ?? 'date' ),
			'columns'         => absint( $attributes['columns']                      ?? 3 ),
			'pagination'      => empty( $attributes['pagination'] )      ? 'false' : 'true',
			'pagination_type' => sanitize_text_field( $attributes['pagination_type'] ?? 'numeric' ),
			'extra_class'     => sanitize_text_field( $attributes['extra_class']     ?? '' ),
			'className'       => sanitize_text_field( $attributes['className']       ?? '' ),
			'align'           => sanitize_text_field( $attributes['align']           ?? '' ),
			'slides_to_show'  => absint( $attributes['slides_to_show']               ?? 3 ),
			'autoplay'        => empty( $attributes['autoplay'] )        ? 'false' : 'true',
			'autoplay_speed'  => absint( $attributes['autoplay_speed']               ?? 4000 ),
			'infinite'        => empty( $attributes['infinite'] )        ? 'false' : 'true',
			'arrows'          => empty( $attributes['arrows'] )          ? 'false' : 'true',
			'arrows_on_hover' => empty( $attributes['arrows_on_hover'] ) ? 'false' : 'true',
			'dots'            => empty( $attributes['dots'] )            ? 'false' : 'true',
			'pause_on_hover'  => empty( $attributes['pause_on_hover'] )  ? 'false' : 'true',
			'transition'      => sanitize_text_field( $attributes['transition']     ?? 'slide' ),
			'gap'             => absint( $attributes['gap']                          ?? 20 ),
			'post_type'       => sanitize_text_field( $attributes['post_type']       ?? '' ),
			'taxonomy'        => sanitize_text_field( $attributes['taxonomy']        ?? '' ),
			'show_type'       => empty( $attributes['show_type'] )       ? 'false' : 'true',
			'ids'             => sanitize_text_field( $attributes['ids']             ?? '' ),
			'collection'      => sanitize_text_field( $attributes['collection']      ?? '' ),
		);

		return do_shortcode( '[wpyog_news ' . $this->build_shortcode_atts( $atts ) . ']' );
	}

	/**
	 * Convert an associative array into shortcode attribute string.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	private function build_shortcode_atts( $atts ) {
		$parts = array();
		foreach ( $atts as $key => $value ) {
			$parts[] = $key . '="' . esc_attr( $value ) . '"';
		}
		return implode( ' ', $parts );
	}

	/**
	 * Return categories formatted for the block editor select control.
	 *
	 * @return array
	 */
	private function get_categories_for_block() {
		$terms  = get_terms( array( 'taxonomy' => WPYOG_NEWS_CAT, 'hide_empty' => false ) );
		$output = array( array( 'label' => __( 'All Categories', 'wpyog-news' ), 'value' => '' ) );

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$output[] = array(
					'label' => $term->name . ' (ID: ' . $term->term_id . ')',
					'value' => (string) $term->term_id,
				);
			}
		}

		return $output;
	}

	/**
	 * Return public post types formatted for the block editor's "Post Types" checklist.
	 * Lets users mix WPYog News with other CPTs (e.g. Posts, Products) in one layout.
	 *
	 * @return array
	 */
	private function get_post_types_for_block() {
		$post_types = wpyog_news_get_mixable_post_types();
		$output     = array();

		foreach ( $post_types as $post_type ) {
			$output[] = array(
				'label' => $post_type->labels->singular_name,
				'value' => $post_type->name,
			);
		}

		return $output;
	}

	/**
	 * Return existing Collections formatted for the block editor's "Collection" select control.
	 * Collections are a shared tag-style taxonomy for building a repeatable curated group of
	 * posts across any mix of post types.
	 *
	 * @return array
	 */
	private function get_collections_for_block() {
		$terms  = get_terms( array( 'taxonomy' => WPYOG_COLLECTION_TAX, 'hide_empty' => false ) );
		$output = array( array( 'label' => __( 'No Collection', 'wpyog-news' ), 'value' => '' ) );

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$output[] = array(
					'label' => $term->name,
					'value' => $term->slug,
				);
			}
		}

		return $output;
	}

	/**
	 * Ensure [wpyog_news] renders correctly inside Elementor's editor preview.
	 *
	 * @param string $content   Widget rendered content.
	 * @param object $widget    Elementor widget instance.
	 * @return string
	 */
	public function elementor_render( $content, $widget ) {
		return $content; // Elementor processes shortcodes natively; no extra work needed.
	}
}
