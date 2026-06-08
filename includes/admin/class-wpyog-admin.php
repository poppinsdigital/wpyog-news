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
		?>
		<div class="wrap wpyog-admin-wrap">
			<div class="wpyog-admin-header">
				<div class="wpyog-admin-header-inner">
					<span class="wpyog-logo">📰 WPYog News</span>
					<span class="wpyog-version">v<?php echo esc_html( WPYOG_NEWS_VERSION ); ?></span>
				</div>
			</div>

			<div class="wpyog-admin-body">

				<div class="wpyog-generator-layout">

					<!-- Controls Panel -->
					<div class="wpyog-generator-controls">
						<h2><?php esc_html_e( 'Shortcode Generator', 'wpyog-news' ); ?></h2>
						<p class="wpyog-generator-intro"><?php esc_html_e( 'Configure your news display and copy the shortcode.', 'wpyog-news' ); ?></p>

						<div class="wpyog-field-group">
							<label><?php esc_html_e( 'Layout', 'wpyog-news' ); ?></label>
							<select id="wg-layout">
								<option value="list"><?php esc_html_e( 'List', 'wpyog-news' ); ?></option>
								<option value="card"><?php esc_html_e( 'Card', 'wpyog-news' ); ?></option>
							</select>
						</div>

						<div class="wpyog-field-group wpyog-card-only" style="display:none;">
							<label><?php esc_html_e( 'Columns (card only)', 'wpyog-news' ); ?></label>
							<select id="wg-columns">
								<option value="2">2</option>
								<option value="3" selected>3</option>
								<option value="4">4</option>
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
					</div><!-- .wpyog-generator-controls -->

					<!-- Shortcode Output -->
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
								<li><?php esc_html_e( 'In Beaver Builder / SiteOrigin: use the HTML/Shortcode module.', 'wpyog-news' ); ?></li>
							</ul>

							<h3><?php esc_html_e( 'All attributes', 'wpyog-news' ); ?></h3>
							<table class="wpyog-attr-table widefat">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Attribute', 'wpyog-news' ); ?></th>
										<th><?php esc_html_e( 'Values', 'wpyog-news' ); ?></th>
										<th><?php esc_html_e( 'Default', 'wpyog-news' ); ?></th>
									</tr>
								</thead>
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
									<tr><td>pagination</td><td>true | false</td><td>true (list only)</td></tr>
									<tr><td>pagination_type</td><td>numeric | prev-next</td><td>numeric</td></tr>
									<tr><td>extra_class</td><td>CSS class string</td><td>(none)</td></tr>
								</tbody>
							</table>
						</div>
					</div><!-- .wpyog-generator-output -->

				</div><!-- .wpyog-generator-layout -->

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
							<h3><?php esc_html_e( 'Display News on Your Site', 'wpyog-news' ); ?></h3>
							<p><?php esc_html_e( 'Use the shortcode generator to create a shortcode and paste it anywhere on your site.', 'wpyog-news' ); ?></p>
							<code>[wpyog_news layout="list" limit="10"]</code><br /><br />
							<code>[wpyog_news layout="card" columns="3" limit="9"]</code>
						</div>
					</div>

					<div class="wpyog-step">
						<div class="wpyog-step-num">4</div>
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
