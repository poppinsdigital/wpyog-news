<?php
/**
 * Register Custom Post Type and Taxonomy.
 *
 * @package WPYog_News
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPYOG_Post_Types {

	/**
	 * Constructor — hook into WordPress init.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		// Late priority so other plugins/themes have already registered their post types
		// by the time we build the list of post types the Collections taxonomy attaches to.
		add_action( 'init', array( $this, 'register_collection_taxonomy' ), 999 );
		add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );
	}

	/**
	 * Register post type and taxonomy.
	 */
	public function register() {
		self::register_post_type();
		self::register_taxonomy();
	}

	/**
	 * Build the SVG data URI for the admin menu icon (uses the wpyog font glyph path).
	 * The font uses a bottom-left origin (y increases upward), so we flip with a transform.
	 *
	 * @return string data:image/svg+xml;base64,…
	 */
	private static function menu_icon_svg() {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">'
			. '<g transform="translate(0,512) scale(1,-1)">'
			. '<path fill="black" d="M255 501c-135-1-244-111-244-246 1-135 111-244 246-244 135 1 244 111 244 246-1 135-111 244-246 244z'
			. ' m-188-246c0 27 22 49 50 49 27 1 49-21 49-49 0-27-22-49-49-49-27 0-50 22-50 49z'
			. ' m305 166c-5-6-11-16-16-29l-89-236-18-48c-7-18-15-31-23-39-11-9-25-14-43-14-11 0-20 3-27 8-7 6-11 14-11 25'
			. ' 0 8 3 15 8 21 5 5 12 8 21 8 9 0 15-3 20-7 6-4 8-11 8-19 1-11-4-19-14-25 1 0 2 0 3 0 20 0 36 14 46 43'
			. ' l20 50-101 239c-6 13-13 21-19 25-6 4-12 6-19 6l0 12c18-3 35-4 54-4 16 0 40 2 72 4l0-11c-9 0-16-1-21-1'
			. ' -5-1-9-3-13-6-3-2-5-7-5-13 0-6 3-15 7-27l67-163 60 159c4 11 6 19 6 25 0 9-3 15-10 19-6 4-17 7-31 7'
			. ' l0 11c19-1 35-1 46-1 16 0 30 1 42 2l0-11c-7-1-14-5-20-10z"/>'
			. '</g></svg>';
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Register the 'wpyog_news' post type.
	 *
	 * @since 1.0.0
	 */
	public static function register_post_type() {

		$labels = array(
			'name'                  => _x( 'News', 'Post type general name', 'wpyog-news' ),
			'singular_name'         => _x( 'WPYog News', 'Post type singular name', 'wpyog-news' ),
			'menu_name'             => __( 'News', 'wpyog-news' ),
			'all_items'             => __( 'All News', 'wpyog-news' ),
			'add_new'               => __( 'Add News', 'wpyog-news' ),
			'add_new_item'          => __( 'Add New News', 'wpyog-news' ),
			'edit_item'             => __( 'Edit News', 'wpyog-news' ),
			'new_item'              => __( 'New News', 'wpyog-news' ),
			'view_item'             => __( 'View News', 'wpyog-news' ),
			'search_items'          => __( 'Search News', 'wpyog-news' ),
			'not_found'             => __( 'No news found', 'wpyog-news' ),
			'not_found_in_trash'    => __( 'No news found in Trash', 'wpyog-news' ),
			'featured_image'        => __( 'News Image', 'wpyog-news' ),
			'set_featured_image'    => __( 'Set news image', 'wpyog-news' ),
			'remove_featured_image' => __( 'Remove news image', 'wpyog-news' ),
			'use_featured_image'    => __( 'Use as news image', 'wpyog-news' ),
			'items_list'            => __( 'News list', 'wpyog-news' ),
			'item_published'        => __( 'News published.', 'wpyog-news' ),
			'item_updated'          => __( 'News updated.', 'wpyog-news' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,  // Required for Elementor Theme Builder conditions.
			'show_in_admin_bar'   => true,
			'show_in_rest'        => true,
			'query_var'           => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'menu_icon'           => self::menu_icon_svg(),
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions' ),
			'taxonomies'          => array( WPYOG_NEWS_CAT, 'post_tag' ), // Include our taxonomy so Elementor picks it up.
			'has_archive'         => true,
			'rewrite'             => array(
				'slug'       => apply_filters( 'wpyog_news_slug', 'latest-news' ),
				'with_front' => false,
			),
		);

		register_post_type( WPYOG_NEWS_POST_TYPE, apply_filters( 'wpyog_news_post_type_args', $args ) );
	}

	/**
	 * Register the 'wpyog_news_cat' taxonomy.
	 *
	 * @since 1.0.0
	 */
	public static function register_taxonomy() {

		$labels = array(
			'name'              => _x( 'News Categories', 'Taxonomy general name', 'wpyog-news' ),
			'singular_name'     => _x( 'Category', 'Taxonomy singular name', 'wpyog-news' ),
			'search_items'      => __( 'Search Categories', 'wpyog-news' ),
			'all_items'         => __( 'All Categories', 'wpyog-news' ),
			'parent_item'       => __( 'Parent Category', 'wpyog-news' ),
			'parent_item_colon' => __( 'Parent Category:', 'wpyog-news' ),
			'edit_item'         => __( 'Edit Category', 'wpyog-news' ),
			'update_item'       => __( 'Update Category', 'wpyog-news' ),
			'add_new_item'      => __( 'Add New Category', 'wpyog-news' ),
			'new_item_name'     => __( 'New Category Name', 'wpyog-news' ),
			'menu_name'         => __( 'Categories', 'wpyog-news' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'       => apply_filters( 'wpyog_news_cat_slug', 'news-category' ),
				'with_front' => false,
			),
		);

		register_taxonomy( WPYOG_NEWS_CAT, array( WPYOG_NEWS_POST_TYPE ), apply_filters( 'wpyog_news_taxonomy_args', $args ) );
	}

	/**
	 * Register the 'wpyog_collection' taxonomy — a checkbox-style taxonomy attached to
	 * every public post type, so editors can build a repeatable curated collection (e.g.
	 * "Homepage Picks") by ticking a box on posts of any type from their normal edit
	 * screen, instead of typing post IDs into a shortcode every time.
	 *
	 * Hierarchical like Categories: WordPress renders the standard checkbox-list meta box
	 * (post_categories_meta_box) with a "+ Add New Collection" toggle to create one inline
	 * — no searching/typing an existing name required, just tick the box. No rewrite/archive
	 * pages — this taxonomy is a curation tool for the [wpyog_news] `collection` attribute,
	 * not a public browsing feature.
	 *
	 * @since 1.4.0
	 */
	public function register_collection_taxonomy() {

		$post_types = array_keys( wpyog_news_get_mixable_post_types() );

		if ( empty( $post_types ) ) {
			return;
		}

		$labels = array(
			'name'              => _x( 'Collections', 'Taxonomy general name', 'wpyog-news' ),
			'singular_name'     => _x( 'Collection', 'Taxonomy singular name', 'wpyog-news' ),
			'search_items'      => __( 'Search Collections', 'wpyog-news' ),
			'all_items'         => __( 'All Collections', 'wpyog-news' ),
			'parent_item'       => __( 'Parent Collection', 'wpyog-news' ),
			'parent_item_colon' => __( 'Parent Collection:', 'wpyog-news' ),
			'edit_item'         => __( 'Edit Collection', 'wpyog-news' ),
			'update_item'       => __( 'Update Collection', 'wpyog-news' ),
			'add_new_item'      => __( 'Add New Collection', 'wpyog-news' ),
			'new_item_name'     => __( 'New Collection Name', 'wpyog-news' ),
			'not_found'         => __( 'No collections found.', 'wpyog-news' ),
			'menu_name'         => __( 'Collections', 'wpyog-news' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_in_rest'      => true,
			'show_tagcloud'     => false,
			'query_var'         => true,
			'rewrite'           => false,
		);

		register_taxonomy( WPYOG_COLLECTION_TAX, $post_types, apply_filters( 'wpyog_collection_taxonomy_args', $args ) );
	}

	/**
	 * Customise admin update messages for the CPT.
	 *
	 * @param array $messages Existing messages.
	 * @return array
	 */
	public function updated_messages( $messages ) {
		global $post, $post_ID;

		$messages[ WPYOG_NEWS_POST_TYPE ] = array(
			0  => '',
			/* translators: %s: URL of the news item. */
			1  => sprintf( __( 'News updated. <a href="%s">View News</a>', 'wpyog-news' ), esc_url( get_permalink( $post_ID ) ) ),
			2  => __( 'Custom field updated.', 'wpyog-news' ),
			3  => __( 'Custom field deleted.', 'wpyog-news' ),
			4  => __( 'News updated.', 'wpyog-news' ),
			5  => __( 'News restored to a previous revision.', 'wpyog-news' ),
			/* translators: %s: URL of the news item. */
			6  => sprintf( __( 'News published. <a href="%s">View News</a>', 'wpyog-news' ), esc_url( get_permalink( $post_ID ) ) ),
			7  => __( 'News saved.', 'wpyog-news' ),
			/* translators: %s: URL to preview the news item. */
			8  => sprintf( __( 'News submitted. <a target="_blank" href="%s">Preview</a>', 'wpyog-news' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			/* translators: 1: Scheduled date and time. 2: URL to preview the news item. */
			9  => sprintf( __( 'News scheduled for: %1$s. <a target="_blank" href="%2$s">Preview</a>', 'wpyog-news' ), date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			/* translators: %s: URL to preview the news item. */
			10 => sprintf( __( 'News draft updated. <a target="_blank" href="%s">Preview</a>', 'wpyog-news' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}
}
