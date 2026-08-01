<?php
/**
 * Admin class: menus, settings page, shortcode generator.
 *
 * @package WPYog_News
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPYOG_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menus' ), 12 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Add term ID column to category table.
		add_filter( WPYOG_NEWS_CAT . '_row_actions', array( $this, 'add_term_id_column' ), 10, 2 );

		// Include news tags in front-end tag archive.
		add_filter( 'pre_get_posts', array( $this, 'include_news_in_tag_archive' ) );
	}

	/**
	 * Register admin sub-menus.
	 */
	public function register_menus() {

		// Shortcode Generator / Getting Started page.
		add_submenu_page(
			'edit.php?post_type=' . WPYOG_NEWS_POST_TYPE,
			__( 'Shortcode Generator — WPYog News', 'wpyog-news' ),
			__( 'Shortcode Generator', 'wpyog-news' ),
			'edit_posts',
			'wpyog-news-shortcode',
			array( $this, 'render_shortcode_page' )
		);

		// Getting Started page.
		add_submenu_page(
			'edit.php?post_type=' . WPYOG_NEWS_POST_TYPE,
			__( 'Getting Started — WPYog News', 'wpyog-news' ),
			__( 'Getting Started', 'wpyog-news' ),
			'edit_posts',
			'wpyog-news-start',
			array( $this, 'render_getting_started' )
		);
	}

	/**
	 * Enqueue admin-only CSS/JS.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on our CPT pages.
		$screen = get_current_screen();
		if ( ! $screen || WPYOG_NEWS_POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'wpyog-news-admin',
			WPYOG_NEWS_URL . 'assets/css/wpyog-admin.css',
			array(),
			WPYOG_NEWS_VERSION
		);

		wp_enqueue_script(
			'wpyog-news-admin',
			WPYOG_NEWS_URL . 'assets/js/wpyog-admin.js',
			array( 'jquery' ),
			WPYOG_NEWS_VERSION,
			true
		);

		wp_localize_script( 'wpyog-news-admin', 'wpyogAdminData', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wpyog_admin_nonce' ),
		) );
	}

	/**
	 * Render the Shortcode Generator page.
	 */
	public function render_shortcode_page() {
		// Fetch news categories for the category selector.
		$categories = get_terms( array(
			'taxonomy'   => WPYOG_NEWS_CAT,
			'hide_empty' => false,
		) );

		// Fetch public post types so users can mix WPYog News with other CPTs.
		$selectable_post_types = wpyog_news_get_mixable_post_types();

		// Fetch existing Collections (curated groups spanning any mix of post types).
		$collections = get_terms( array(
			'taxonomy'   => WPYOG_COLLECTION_TAX,
			'hide_empty' => false,
		) );
		?>
		<div class="wrap wpyog-admin-wrap">
			<div class="wpyog-admin-header">
				<div class="wpyog-admin-header-inner">
					<span class="wpyog-logo">📰 WPYog News</span>
					<span class="wpyog-version">v<?php echo esc_html( WPYOG_NEWS_VERSION ); ?></span>
				</div>
			</div>

			<div class="wpyog-admin-body">

				<!-- ── Tab switcher ────────────────────────────────── -->
				<div class="wpyog-gen-tabs">
					<button class="wpyog-gen-tab wpyog-gen-tab-active" data-tab="news">
						📰 <?php esc_html_e( 'News List / Card', 'wpyog-news' ); ?>
					</button>
					<button class="wpyog-gen-tab" data-tab="ticker">
						📡 <?php esc_html_e( 'News Ticker', 'wpyog-news' ); ?>
					</button>
				</div>

				<!-- ══════════════════════════════════════════════════
				     TAB 1: News List / Card  [wpyog_news]
				     ══════════════════════════════════════════════════ -->
				<div class="wpyog-gen-panel" id="wpyog-panel-news">
					<div class="wpyog-generator-layout">

						<!-- Controls -->
						<div class="wpyog-generator-controls">
							<h2><?php esc_html_e( 'News List / Card Generator', 'wpyog-news' ); ?></h2>
							<p class="wpyog-generator-intro"><?php esc_html_e( 'Configure your news display and copy the shortcode.', 'wpyog-news' ); ?></p>

							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Layout', 'wpyog-news' ); ?></label>
								<select id="wg-layout">
									<option value="list"><?php esc_html_e( 'List', 'wpyog-news' ); ?></option>
									<option value="card"><?php esc_html_e( 'Card', 'wpyog-news' ); ?></option>
									<option value="carousel"><?php esc_html_e( 'Carousel', 'wpyog-news' ); ?></option>
								</select>
							</div>

							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Post Types to Include', 'wpyog-news' ); ?></label>
								<div class="wpyog-checkbox-list">
									<?php foreach ( $selectable_post_types as $pt ) : ?>
										<label class="wpyog-checkbox-item">
											<input type="checkbox" class="wg-post-type" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( WPYOG_NEWS_POST_TYPE === $pt->name ); ?> />
											<?php echo esc_html( $pt->labels->singular_name ); ?>
										</label>
									<?php endforeach; ?>
								</div>
								<p class="description"><?php esc_html_e( 'Mix WPYog News with other content types (Posts, Products, etc.) in the same layout. Only common fields — title, excerpt, featured image, date — are used for mixed items.', 'wpyog-news' ); ?></p>
							</div>

							<div class="wpyog-field-group wpyog-toggle-row wpyog-post-types-multi-only" style="display:none;">
								<label><?php esc_html_e( 'Show Post Type Badge', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wg-show-type" />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group wpyog-card-only" style="display:none;">
								<label><?php esc_html_e( 'Columns (card only)', 'wpyog-news' ); ?></label>
								<select id="wg-columns">
									<option value="2">2</option>
									<option value="3" selected>3</option>
									<option value="4">4</option>
								</select>
							</div>

							<div class="wpyog-field-group wpyog-carousel-only" style="display:none;">
								<label><?php esc_html_e( 'Slides to Show (carousel only)', 'wpyog-news' ); ?></label>
								<select id="wg-slides-to-show">
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3" selected>3</option>
									<option value="4">4</option>
									<option value="5">5</option>
								</select>
							</div>

							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Number of posts', 'wpyog-news' ); ?></label>
								<input type="number" id="wg-limit" value="10" min="1" max="100" />
							</div>

							<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Category (optional)', 'wpyog-news' ); ?></label>
								<select id="wg-category">
									<option value=""><?php esc_html_e( '— All Categories —', 'wpyog-news' ); ?></option>
									<?php foreach ( $categories as $cat ) : ?>
										<option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?> (ID: <?php echo esc_html( $cat->term_id ); ?>)</option>
									<?php endforeach; ?>
								</select>
							</div>
							<?php endif; ?>

							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Collection (optional)', 'wpyog-news' ); ?></label>
								<?php if ( ! empty( $collections ) && ! is_wp_error( $collections ) ) : ?>
									<select id="wg-collection">
										<option value=""><?php esc_html_e( '— No Collection —', 'wpyog-news' ); ?></option>
										<?php foreach ( $collections as $term ) : ?>
											<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'A repeatable curated group spanning any mix of post types — tick the box on any post to add it. Works with the Post Types checklist above; if a Category is also set, only posts matching both will show.', 'wpyog-news' ); ?></p>
								<?php else : ?>
									<select id="wg-collection" disabled>
										<option value=""><?php esc_html_e( '— No Collections Yet —', 'wpyog-news' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'No collections yet. Open any post (News, Blog Post, Product, etc.), click "+ Add New Collection" in its Collections box to create one, then refresh this page.', 'wpyog-news' ); ?></p>
								<?php endif; ?>
							</div>

							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Order', 'wpyog-news' ); ?></label>
								<select id="wg-order">
									<option value="DESC"><?php esc_html_e( 'Newest First (DESC)', 'wpyog-news' ); ?></option>
									<option value="ASC"><?php esc_html_e( 'Oldest First (ASC)', 'wpyog-news' ); ?></option>
								</select>
							</div>

							<div class="wpyog-field-group wpyog-list-only">
								<label><?php esc_html_e( 'Pagination Type', 'wpyog-news' ); ?></label>
								<select id="wg-pagination-type">
									<option value="numeric"><?php esc_html_e( 'Numeric', 'wpyog-news' ); ?></option>
									<option value="prev-next"><?php esc_html_e( 'Prev / Next', 'wpyog-news' ); ?></option>
								</select>
							</div>

							<div class="wpyog-field-group wpyog-carousel-only" style="display:none;">
								<label><?php esc_html_e( 'Transition', 'wpyog-news' ); ?></label>
								<select id="wg-transition">
									<option value="slide"><?php esc_html_e( 'Slide', 'wpyog-news' ); ?></option>
									<option value="fade"><?php esc_html_e( 'Fade', 'wpyog-news' ); ?></option>
								</select>
							</div>

							<div class="wpyog-field-group wpyog-carousel-only" style="display:none;">
								<label><?php esc_html_e( 'Autoplay Speed (ms)', 'wpyog-news' ); ?></label>
								<input type="number" id="wg-autoplay-speed" value="4000" min="1000" max="15000" step="500" />
							</div>

							<div class="wpyog-field-group wpyog-carousel-only" style="display:none;">
								<label><?php esc_html_e( 'Gap Between Slides (px)', 'wpyog-news' ); ?></label>
								<input type="number" id="wg-gap" value="20" min="0" max="80" />
							</div>

							<div class="wpyog-field-group wpyog-carousel-only wpyog-toggle-row" style="display:none;">
								<label><?php esc_html_e( 'Autoplay', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wg-autoplay" checked />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group wpyog-carousel-only wpyog-toggle-row" style="display:none;">
								<label><?php esc_html_e( 'Pause on Hover', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wg-pause-on-hover" checked />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group wpyog-carousel-only wpyog-toggle-row" style="display:none;">
								<label><?php esc_html_e( 'Infinite Loop', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wg-infinite" checked />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group wpyog-carousel-only wpyog-toggle-row" style="display:none;">
								<label><?php esc_html_e( 'Show Arrows', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wg-arrows" checked />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group wpyog-carousel-only wpyog-toggle-row" style="display:none;">
								<label><?php esc_html_e( 'Arrows Only on Hover', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wg-arrows-on-hover" />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group wpyog-carousel-only wpyog-toggle-row" style="display:none;">
								<label><?php esc_html_e( 'Show Dots', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wg-dots" checked />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Excerpt Length (words)', 'wpyog-news' ); ?></label>
								<input type="number" id="wg-excerpt-length" value="20" min="5" max="100" />
							</div>

							<div class="wpyog-field-group wpyog-toggle-row">
								<label><?php esc_html_e( 'Show Date', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wg-show-date" checked />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group wpyog-toggle-row">
								<label><?php esc_html_e( 'Show Excerpt', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wg-show-excerpt" checked />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group wpyog-toggle-row">
								<label><?php esc_html_e( 'Show Source', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wg-show-source" checked />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Specific Post IDs (optional)', 'wpyog-news' ); ?></label>
								<input type="text" id="wg-ids" placeholder="e.g. 12,45,78" />
								<p class="description"><?php esc_html_e( 'Hand-pick exact posts to show, across any mix of post types — no shared category needed. Overrides Number of Posts, Category, and pagination. Find an ID from the URL when editing a post (post.php?post=123).', 'wpyog-news' ); ?></p>
							</div>
						</div><!-- .wpyog-generator-controls -->

						<!-- Output -->
						<div class="wpyog-generator-output">
							<h2><?php esc_html_e( 'Your Shortcode', 'wpyog-news' ); ?></h2>
							<div class="wpyog-shortcode-preview">
								<code id="wpyog-generated-code">[wpyog_news layout="list" limit="10" show_date="true" show_excerpt="true" excerpt_length="20" show_source="true" order="DESC" pagination_type="numeric"]</code>
								<button type="button" class="button" id="wpyog-copy-code">
									<?php esc_html_e( 'Copy', 'wpyog-news' ); ?>
								</button>
							</div>

							<div class="wpyog-usage-guide">
								<h3><?php esc_html_e( 'How to use', 'wpyog-news' ); ?></h3>
								<ul>
									<li><?php esc_html_e( 'Paste the shortcode into any page, post, or widget.', 'wpyog-news' ); ?></li>
									<li><?php esc_html_e( 'In Gutenberg: use a Shortcode block or the WPYog News block.', 'wpyog-news' ); ?></li>
									<li><?php esc_html_e( 'In Elementor: use the Shortcode widget.', 'wpyog-news' ); ?></li>
									<li><?php esc_html_e( 'In Divi: use the Code module.', 'wpyog-news' ); ?></li>
								</ul>

								<h3><?php esc_html_e( 'All attributes', 'wpyog-news' ); ?></h3>
								<table class="wpyog-attr-table widefat">
									<thead><tr>
										<th><?php esc_html_e( 'Attribute', 'wpyog-news' ); ?></th>
										<th><?php esc_html_e( 'Values', 'wpyog-news' ); ?></th>
										<th><?php esc_html_e( 'Default', 'wpyog-news' ); ?></th>
									</tr></thead>
									<tbody>
										<tr><td>layout</td><td>list | card</td><td>list</td></tr>
										<tr><td>limit</td><td>any integer</td><td>10</td></tr>
										<tr><td>category</td><td>category ID(s), comma-separated</td><td>(all)</td></tr>
										<tr><td>show_date</td><td>true | false</td><td>true</td></tr>
										<tr><td>show_excerpt</td><td>true | false</td><td>true</td></tr>
										<tr><td>excerpt_length</td><td>integer (words)</td><td>20</td></tr>
										<tr><td>show_source</td><td>true | false</td><td>true</td></tr>
										<tr><td>order</td><td>DESC | ASC</td><td>DESC</td></tr>
										<tr><td>orderby</td><td>date | title | rand</td><td>date</td></tr>
										<tr><td>columns</td><td>2 | 3 | 4</td><td>3 (card only)</td></tr>
										<tr><td>pagination_type</td><td>numeric | prev-next</td><td>numeric</td></tr>
										<tr><td>extra_class</td><td>CSS class string</td><td>(none)</td></tr>
										<tr><td>post_type</td><td>CPT slug(s), comma-separated</td><td>wpyog_news</td></tr>
										<tr><td>taxonomy</td><td>taxonomy slug</td><td>wpyog_news_cat</td></tr>
										<tr><td>show_type</td><td>true | false</td><td>false</td></tr>
										<tr><td>ids</td><td>post ID(s), comma-separated</td><td>(none)</td></tr>
										<tr><td>collection</td><td>collection slug(s), comma-separated</td><td>(none)</td></tr>
									</tbody>
								</table>

								<p class="description"><?php esc_html_e( 'Mixing post types: use post_type to combine WPYog News with other content types in one layout — only shared fields (title, excerpt, featured image, date) are shown for mixed items. The category filter only matches WPYog News items unless the other post types share the same taxonomy. To show a hand-picked selection instead of a category, use ids — it works across any mix of post types and overrides category and limit.', 'wpyog-news' ); ?></p>

								<h3><?php esc_html_e( 'Carousel-only attributes', 'wpyog-news' ); ?></h3>
								<table class="wpyog-attr-table widefat">
									<thead><tr>
										<th><?php esc_html_e( 'Attribute', 'wpyog-news' ); ?></th>
										<th><?php esc_html_e( 'Values', 'wpyog-news' ); ?></th>
										<th><?php esc_html_e( 'Default', 'wpyog-news' ); ?></th>
									</tr></thead>
									<tbody>
										<tr><td>slides_to_show</td><td>1 - 5</td><td>3</td></tr>
										<tr><td>autoplay</td><td>true | false</td><td>true</td></tr>
										<tr><td>autoplay_speed</td><td>integer (ms)</td><td>4000</td></tr>
										<tr><td>infinite</td><td>true | false</td><td>true</td></tr>
										<tr><td>arrows</td><td>true | false</td><td>true</td></tr>
										<tr><td>arrows_on_hover</td><td>true | false</td><td>false</td></tr>
										<tr><td>dots</td><td>true | false</td><td>true</td></tr>
										<tr><td>pause_on_hover</td><td>true | false</td><td>true</td></tr>
										<tr><td>transition</td><td>slide | fade</td><td>slide</td></tr>
										<tr><td>gap</td><td>integer (px)</td><td>20</td></tr>
									</tbody>
								</table>
							</div>
						</div><!-- .wpyog-generator-output -->

					</div><!-- .wpyog-generator-layout -->
				</div><!-- #wpyog-panel-news -->

				<!-- ══════════════════════════════════════════════════
				     TAB 2: News Ticker  [wpyog_ticker]
				     ══════════════════════════════════════════════════ -->
				<div class="wpyog-gen-panel" id="wpyog-panel-ticker" style="display:none;">
					<div class="wpyog-generator-layout">

						<!-- Controls -->
						<div class="wpyog-generator-controls">
							<h2><?php esc_html_e( 'News Ticker Generator', 'wpyog-news' ); ?></h2>
							<p class="wpyog-generator-intro"><?php esc_html_e( 'Configure your news ticker and copy the shortcode.', 'wpyog-news' ); ?></p>

							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Animation Style', 'wpyog-news' ); ?></label>
								<select id="wt-animation">
									<option value="scroll"><?php esc_html_e( 'Scroll (horizontal ticker tape)', 'wpyog-news' ); ?></option>
									<option value="fade"><?php esc_html_e( 'Fade (cross-fade items)', 'wpyog-news' ); ?></option>
									<option value="flap"><?php esc_html_e( 'Flap (airport departure board)', 'wpyog-news' ); ?></option>
								</select>
							</div>

							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Speed', 'wpyog-news' ); ?></label>
								<select id="wt-speed">
									<option value="slow"><?php esc_html_e( 'Slow', 'wpyog-news' ); ?></option>
									<option value="medium" selected><?php esc_html_e( 'Medium', 'wpyog-news' ); ?></option>
									<option value="fast"><?php esc_html_e( 'Fast', 'wpyog-news' ); ?></option>
								</select>
							</div>

							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Number of posts', 'wpyog-news' ); ?></label>
								<input type="number" id="wt-limit" value="10" min="1" max="100" />
							</div>

							<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Category (optional)', 'wpyog-news' ); ?></label>
								<select id="wt-category">
									<option value=""><?php esc_html_e( '— All Categories —', 'wpyog-news' ); ?></option>
									<?php foreach ( $categories as $cat ) : ?>
										<option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?> (ID: <?php echo esc_html( $cat->term_id ); ?>)</option>
									<?php endforeach; ?>
								</select>
							</div>
							<?php endif; ?>

							<div class="wpyog-field-group">
								<label><?php esc_html_e( 'Order', 'wpyog-news' ); ?></label>
								<select id="wt-order">
									<option value="DESC"><?php esc_html_e( 'Newest First (DESC)', 'wpyog-news' ); ?></option>
									<option value="ASC"><?php esc_html_e( 'Oldest First (ASC)', 'wpyog-news' ); ?></option>
								</select>
							</div>

							<div class="wpyog-field-group wpyog-toggle-row">
								<label><?php esc_html_e( 'Show Label', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wt-show-label" checked />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group" id="wt-label-text-row">
								<label><?php esc_html_e( 'Label Text', 'wpyog-news' ); ?></label>
								<input type="text" id="wt-label" value="<?php esc_attr_e( 'Breaking News', 'wpyog-news' ); ?>" maxlength="40" />
							</div>

							<div class="wpyog-field-group" id="wt-label-colors-row" style="display:flex;gap:12px;align-items:flex-end;">
								<div style="flex:1;">
									<label><?php esc_html_e( 'Label BG', 'wpyog-news' ); ?></label>
									<input type="color" id="wt-label-bg" value="#e74c3c" style="height:34px;width:100%;padding:2px 4px;cursor:pointer;" />
								</div>
								<div style="flex:1;">
									<label><?php esc_html_e( 'Label Text Colour', 'wpyog-news' ); ?></label>
									<input type="color" id="wt-label-color" value="#ffffff" style="height:34px;width:100%;padding:2px 4px;cursor:pointer;" />
								</div>
							</div>

							<div class="wpyog-field-group wpyog-toggle-row">
								<label><?php esc_html_e( 'Show Date', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wt-show-date" checked />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group wpyog-toggle-row wpyog-ticker-cycle-only">
								<label><?php esc_html_e( 'Show Counter (e.g. 2 / 10)', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wt-show-count" />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group wpyog-toggle-row">
								<label><?php esc_html_e( 'Pause on Hover', 'wpyog-news' ); ?></label>
								<label class="wpyog-switch">
									<input type="checkbox" id="wt-pause" checked />
									<span class="wpyog-slider"></span>
								</label>
							</div>

							<div class="wpyog-field-group wpyog-ticker-scroll-only">
								<label><?php esc_html_e( 'Direction', 'wpyog-news' ); ?></label>
								<select id="wt-direction">
									<option value="left"><?php esc_html_e( 'Left (default)', 'wpyog-news' ); ?></option>
									<option value="right"><?php esc_html_e( 'Right (RTL)', 'wpyog-news' ); ?></option>
								</select>
							</div>

							<div class="wpyog-field-group wpyog-ticker-scroll-only">
								<label><?php esc_html_e( 'Separator', 'wpyog-news' ); ?></label>
								<input type="text" id="wt-separator" value="•" maxlength="10" style="width:60px;" />
							</div>

						</div><!-- .wpyog-generator-controls -->

						<!-- Output -->
						<div class="wpyog-generator-output">
							<h2><?php esc_html_e( 'Your Ticker Shortcode', 'wpyog-news' ); ?></h2>
							<div class="wpyog-shortcode-preview">
								<code id="wpyog-ticker-generated-code">[wpyog_ticker]</code>
								<button type="button" class="button" id="wpyog-copy-ticker-code">
									<?php esc_html_e( 'Copy', 'wpyog-news' ); ?>
								</button>
							</div>

							<div class="wpyog-usage-guide">
								<h3><?php esc_html_e( 'Quick examples', 'wpyog-news' ); ?></h3>
								<p><code>[wpyog_ticker animation="scroll" speed="medium" label="Latest"]</code></p>
								<p><code>[wpyog_ticker animation="flap" show_count="true" limit="8"]</code></p>
								<p><code>[wpyog_ticker animation="fade" show_label="false" show_date="true"]</code></p>

								<h3><?php esc_html_e( 'All ticker attributes', 'wpyog-news' ); ?></h3>
								<table class="wpyog-attr-table widefat">
									<thead><tr>
										<th><?php esc_html_e( 'Attribute', 'wpyog-news' ); ?></th>
										<th><?php esc_html_e( 'Values', 'wpyog-news' ); ?></th>
										<th><?php esc_html_e( 'Default', 'wpyog-news' ); ?></th>
									</tr></thead>
									<tbody>
										<tr><td>animation</td><td>scroll | fade | flap</td><td>scroll</td></tr>
										<tr><td>speed</td><td>slow | medium | fast | integer</td><td>medium</td></tr>
										<tr><td>limit</td><td>any integer</td><td>10</td></tr>
										<tr><td>category</td><td>category ID(s), comma-sep</td><td>(all)</td></tr>
										<tr><td>order</td><td>DESC | ASC</td><td>DESC</td></tr>
										<tr><td>orderby</td><td>date | title | rand</td><td>date</td></tr>
										<tr><td>show_label</td><td>true | false</td><td>true</td></tr>
										<tr><td>label</td><td>any string</td><td>Breaking News</td></tr>
										<tr><td>label_bg</td><td>hex colour</td><td>#e74c3c</td></tr>
										<tr><td>label_color</td><td>hex colour</td><td>#ffffff</td></tr>
										<tr><td>show_date</td><td>true | false</td><td>true</td></tr>
										<tr><td>show_count</td><td>true | false (fade/flap)</td><td>false</td></tr>
										<tr><td>pause_on_hover</td><td>true | false</td><td>true</td></tr>
										<tr><td>direction</td><td>left | right (scroll only)</td><td>left</td></tr>
										<tr><td>separator</td><td>any string (scroll only)</td><td>•</td></tr>
										<tr><td>extra_class</td><td>CSS class string</td><td>(none)</td></tr>
									</tbody>
								</table>
							</div>
						</div><!-- .wpyog-generator-output -->

					</div><!-- .wpyog-generator-layout -->
				</div><!-- #wpyog-panel-ticker -->

			</div><!-- .wpyog-admin-body -->
		</div><!-- .wrap -->
		<?php
	}

	/**
	 * Render the Getting Started page.
	 */
	public function render_getting_started() {
		?>
		<div class="wrap wpyog-admin-wrap">
			<div class="wpyog-admin-header">
				<div class="wpyog-admin-header-inner">
					<span class="wpyog-logo">📰 WPYog News</span>
					<span class="wpyog-version">v<?php echo esc_html( WPYOG_NEWS_VERSION ); ?></span>
				</div>
			</div>

			<div class="wpyog-admin-body wpyog-getting-started">
				<h2><?php esc_html_e( 'Getting Started with WPYog News', 'wpyog-news' ); ?></h2>

				<div class="wpyog-steps">
					<div class="wpyog-step">
						<div class="wpyog-step-num">1</div>
						<div class="wpyog-step-content">
							<h3><?php esc_html_e( 'Add News Items', 'wpyog-news' ); ?></h3>
							<p><?php esc_html_e( 'Go to WPYog News → Add News. Enter a title, content, featured image, and optionally set an External URL and Source Details.', 'wpyog-news' ); ?></p>
							<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . WPYOG_NEWS_POST_TYPE ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add Your First News Item', 'wpyog-news' ); ?></a>
						</div>
					</div>

					<div class="wpyog-step">
						<div class="wpyog-step-num">2</div>
						<div class="wpyog-step-content">
							<h3><?php esc_html_e( 'Organise with Categories', 'wpyog-news' ); ?></h3>
							<p><?php esc_html_e( 'Create categories under WPYog News → Categories to group your news by topic.', 'wpyog-news' ); ?></p>
							<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=' . WPYOG_NEWS_CAT . '&post_type=' . WPYOG_NEWS_POST_TYPE ) ); ?>" class="button"><?php esc_html_e( 'Manage Categories', 'wpyog-news' ); ?></a>
						</div>
					</div>

					<div class="wpyog-step">
						<div class="wpyog-step-num">3</div>
						<div class="wpyog-step-content">
							<h3><?php esc_html_e( 'Build a Repeatable Curated Collection', 'wpyog-news' ); ?></h3>
							<p><?php esc_html_e( 'Open any post — News, a Blog Post, a Product, any post type — and in its Collections box, click "+ Add New Collection" to create one (e.g. "Homepage Picks"), or just tick the box if it already exists. Tick the same collection on posts from any other post type. Then pull them all together with collection="homepage-picks" — no editing the shortcode as the collection grows.', 'wpyog-news' ); ?></p>
							<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=' . WPYOG_COLLECTION_TAX . '&post_type=' . WPYOG_NEWS_POST_TYPE ) ); ?>" class="button"><?php esc_html_e( 'Manage Collections', 'wpyog-news' ); ?></a>
						</div>
					</div>

					<div class="wpyog-step">
						<div class="wpyog-step-num">4</div>
						<div class="wpyog-step-content">
							<h3><?php esc_html_e( 'Display News on Your Site', 'wpyog-news' ); ?></h3>
							<p><?php esc_html_e( 'Use the shortcode generator to create a shortcode and paste it anywhere on your site.', 'wpyog-news' ); ?></p>
							<code>[wpyog_news layout="list" limit="10"]</code><br /><br />
							<code>[wpyog_news layout="card" columns="3" limit="9"]</code><br /><br />
							<code>[wpyog_news layout="carousel" slides_to_show="3" autoplay="true"]</code>
						</div>
					</div>

					<div class="wpyog-step">
						<div class="wpyog-step-num">5</div>
						<div class="wpyog-step-content">
							<h3><?php esc_html_e( 'External Links & Sources', 'wpyog-news' ); ?></h3>
							<p><?php esc_html_e( 'When editing a news item, fill in the External Link field to redirect clicks to an external article. Add Source Details to credit the publication.', 'wpyog-news' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Show term ID in the category row actions.
	 *
	 * @param array   $actions Row actions.
	 * @param WP_Term $term    Current term.
	 * @return array
	 */
	public function add_term_id_column( $actions, $term ) {
		return array_merge(
			array( 'wpyog_id' => esc_html__( 'ID', 'wpyog-news' ) . ': ' . esc_html( $term->term_id ) ),
			$actions
		);
	}

	/**
	 * Include news posts in front-end tag archives.
	 *
	 * @param WP_Query $query Main query.
	 */
	public function include_news_in_tag_archive( $query ) {
		if ( ! is_admin() && is_tag() && $query->is_main_query() ) {
			$query->set( 'post_type', array( 'post', WPYOG_NEWS_POST_TYPE ) );
		}
	}
}
