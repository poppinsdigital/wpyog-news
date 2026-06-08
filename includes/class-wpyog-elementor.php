<?php
/**
 * Elementor Integration — WPYog News
 *
 * Registers the News CPT and its category taxonomy with Elementor's
 * internal helpers so they surface in:
 *
 *  - Elementor Widget Query  (Posts / Archive widgets)
 *  - Elementor Dynamic Tags  (Post-related tags)
 *  - Elementor Theme Builder (singular + archive conditions and template override)
 *
 * Works with both free Elementor and Elementor Pro.
 * Theme Builder template override requires Elementor Pro.
 *
 * HOW THE TEMPLATE OVERRIDE WORKS
 * ─────────────────────────────────
 * Elementor Pro hooks into WordPress's `template_include` filter (runs after
 * `single_template`) and replaces the resolved template with its canvas when
 * a matching Theme Builder document exists. We do NOT need to touch
 * `single_template` at all — our plugin's fallback template is resolved first,
 * and Elementor Pro then wins cleanly via `template_include`.
 *
 * The critical requirement is that `wpyog_news` appears in Elementor's public
 * post-type list — that is what this class ensures.
 *
 * @package WPYog_News
 * @since   1.0.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPYOG_Elementor {

	/**
	 * Constructor.
	 *
	 * Handles the timing problem: by the time plugins_loaded fires for our
	 * plugin, Elementor may have already fired `elementor/init`. We detect
	 * this with did_action() and call register_hooks() immediately if needed.
	 */
	public function __construct() {
		if ( did_action( 'elementor/init' ) ) {
			// Elementor already initialised — register filters right away.
			$this->register_hooks();
		} else {
			add_action( 'elementor/init', array( $this, 'register_hooks' ) );
		}
	}

	/**
	 * Attach all Elementor-specific filters.
	 *
	 * @since 1.0.9
	 */
	public function register_hooks() {

		// ── Post-type registry ──────────────────────────────────────────────
		// Adds wpyog_news to every place Elementor queries "public post types":
		// Widget Query, Dynamic Tags, Theme Builder singular conditions.
		add_filter( 'elementor/utils/get_public_post_types', array( $this, 'add_post_type' ) );

		// ── Taxonomy registry ───────────────────────────────────────────────
		// Exposes wpyog_news_cat so the Query widget and Theme Builder show
		// "In Category" / "In child Category" sub-conditions.
		add_filter( 'elementor/utils/get_taxonomies', array( $this, 'add_taxonomy' ) );

		// ── Hand template control to Elementor Pro (Theme Builder) ──────────
		// Elementor Pro overrides templates via the template_include filter at
		// priority 12. For that override to trigger cleanly, it must receive
		// the theme's own template path — NOT a path from our plugin.
		//
		// Our single_template filter (registered in wpyog-news.php) returns
		// templates/single-wpyog_news.php, which Elementor Pro does not
		// recognise and silently ignores — so its canvas never loads.
		//
		// Fix: remove our single_template filter when Elementor Pro is active.
		// With Elementor Pro:
		//   • Theme Builder template assigned → Elementor Pro canvas renders  ✓
		//   • No theme builder template → theme's single.php renders          ✓
		// Without Elementor Pro:
		//   • Our filter remains → plugin's styled fallback template renders  ✓
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			remove_filter( 'single_template', 'wpyog_news_single_template' );
		}

		// ── CPT support option ──────────────────────────────────────────────
		// Elementor Pro checks the `elementor_cpt_support` option to decide
		// which post types it processes for Theme Builder template overrides.
		// We inject wpyog_news dynamically so users don't have to manually
		// tick a checkbox in Elementor → Settings → Integrations.
		add_filter( 'option_elementor_cpt_support',         array( $this, 'add_to_cpt_support' ) );
		add_filter( 'default_option_elementor_cpt_support', array( $this, 'add_to_cpt_support' ) );

		// ── Theme Builder archive conditions (Elementor Pro) ────────────────
		// Elementor Pro auto-builds archive conditions for every taxonomy
		// returned by elementor/utils/get_taxonomies. This action hook is kept
		// here for extensibility (child plugins can add custom conditions).
		add_action( 'elementor/theme/register_conditions', array( $this, 'register_archive_conditions' ) );
	}

	/**
	 * Dynamically add wpyog_news to Elementor's supported CPT list.
	 *
	 * This is the key that allows Elementor Pro's Theme Builder to apply
	 * template overrides to singular News posts. Without it, Elementor Pro
	 * skips the post type entirely regardless of the condition settings.
	 *
	 * @param  mixed $supported Stored option value (array or false).
	 * @return array
	 */
	public function add_to_cpt_support( $supported ) {
		if ( ! is_array( $supported ) ) {
			$supported = array( 'post', 'page' );
		}
		if ( ! in_array( WPYOG_NEWS_POST_TYPE, $supported, true ) ) {
			$supported[] = WPYOG_NEWS_POST_TYPE;
		}
		return $supported;
	}

	/**
	 * Add the News CPT to Elementor's public post types list.
	 *
	 * @param  array $post_types Existing list keyed by post-type slug.
	 * @return array
	 */
	public function add_post_type( $post_types ) {
		$obj = get_post_type_object( WPYOG_NEWS_POST_TYPE );
		if ( $obj ) {
			$post_types[ WPYOG_NEWS_POST_TYPE ] = $obj->label;
		}
		return $post_types;
	}

	/**
	 * Add the News category taxonomy to Elementor's taxonomy list.
	 *
	 * @param  array $taxonomies Existing list keyed by taxonomy slug.
	 * @return array
	 */
	public function add_taxonomy( $taxonomies ) {
		$obj = get_taxonomy( WPYOG_NEWS_CAT );
		if ( $obj ) {
			$taxonomies[ WPYOG_NEWS_CAT ] = $obj;
		}
		return $taxonomies;
	}

	/**
	 * Hook for Theme Builder archive condition extensibility.
	 *
	 * @param object $conditions_manager Elementor Pro conditions manager.
	 */
	public function register_archive_conditions( $conditions_manager ) {
		do_action( 'wpyog_news_elementor_conditions', $conditions_manager );
	}
}
