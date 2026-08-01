<?php
/**
 * Enqueue front-end scripts and styles.
 *
 * @package WPYog_News
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPYOG_Scripts {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
	}

	/**
	 * Register and enqueue public CSS and JS.
	 */
	public function enqueue_public_assets() {

		wp_enqueue_style(
			'wpyog-news-public',
			WPYOG_NEWS_URL . 'assets/css/wpyog-public.css',
			array(),
			WPYOG_NEWS_VERSION
		);

		wp_enqueue_script(
			'wpyog-news-public',
			WPYOG_NEWS_URL . 'assets/js/wpyog-public.js',
			array( 'jquery' ),
			WPYOG_NEWS_VERSION,
			true
		);

		wp_localize_script( 'wpyog-news-public', 'wpyogNewsData', array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'wpyog_load_more_nonce' ),
			'loadMoreText' => esc_html__( 'Load More', 'wpyog-news' ),
			'loadingText'  => esc_html__( 'Loading…', 'wpyog-news' ),
			'noMoreText'   => esc_html__( 'No more news', 'wpyog-news' ),
		) );

		// Ticker script — vanilla JS, no jQuery dependency.
		wp_enqueue_script(
			'wpyog-news-ticker',
			WPYOG_NEWS_URL . 'assets/js/wpyog-ticker.js',
			array(),
			WPYOG_NEWS_VERSION,
			true
		);

		// Carousel script — vanilla JS, no jQuery dependency.
		wp_enqueue_script(
			'wpyog-news-carousel',
			WPYOG_NEWS_URL . 'assets/js/wpyog-carousel.js',
			array(),
			WPYOG_NEWS_VERSION,
			true
		);
	}
}
