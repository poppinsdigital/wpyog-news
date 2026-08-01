<?php
/**
 * [wpyog_news] Shortcode handler.
 *
 * Attributes:
 *   layout          list|card|carousel Default: list
 *   limit           int                Default: 10
 *   category        int|string (IDs)   Default: '' (all)
 *   show_date       true|false         Default: true
 *   show_excerpt    true|false         Default: true
 *   excerpt_length  int                Default: 20
 *   show_source     true|false         Default: true
 *   order           ASC|DESC           Default: DESC
 *   orderby         date|title|rand    Default: date
 *   columns         2|3|4              Default: 3  (card layout only)
 *   pagination      true|false         Default: true  (list layout)
 *   pagination_type numeric|prev-next  Default: numeric
 *   extra_class     string             Default: ''
 *
 *   --- Mixing multiple post types ---
 *   post_type       CPT slug(s), comma-separated   Default: wpyog_news
 *                   Combine WPYog News with other post types (e.g. "wpyog_news,post,product")
 *                   in the same list/card/carousel. Only common fields are used across all
 *                   types: title, excerpt, featured image, date. Fields that don't exist on a
 *                   post type (External URL, Source Details) are simply skipped for that item.
 *   taxonomy        taxonomy slug      Default: wpyog_news_cat
 *                   Taxonomy the `category` attribute filters against. Only change this if
 *                   mixing post types that share a different taxonomy (e.g. "category").
 *                   Note: WPYog News's own category taxonomy only applies to wpyog_news items —
 *                   filtering by it while mixing other post types will exclude those other items.
 *   show_type       true|false         Default: false
 *                   Show a small badge with the post type's label on each item — useful to tell
 *                   sources apart when mixing post types.
 *   ids             int IDs, comma-separated        Default: '' (none)
 *                   Hand-pick exact posts to show, across any mix of post types — no shared
 *                   category/taxonomy needed. Overrides `limit`, `category`, and pagination;
 *                   posts appear in the order the IDs are listed unless `orderby` is set to
 *                   something other than the default.
 *   collection      collection slug(s), comma-separated   Default: '' (none)
 *                   Repeatable curated collection across any mix of post types — tag posts of
 *                   any type with a "Collection" from their normal edit screen (like adding a
 *                   tag), then pull them all in with `collection="homepage-picks"`. No shortcode
 *                   editing needed as the collection grows; combine with `category` (AND) if both
 *                   are set. Ignored when `ids` is used.
 *
 *   --- Carousel layout only ---
 *   slides_to_show  1-5                Default: 3   Slides visible at once (desktop).
 *   autoplay        true|false         Default: true
 *   autoplay_speed  int (ms)           Default: 4000
 *   infinite        true|false         Default: true   Loop back to start/end.
 *   arrows          true|false         Default: true   Prev/Next arrow buttons.
 *   arrows_on_hover true|false         Default: false  Hide arrows until the carousel is hovered (desktop only).
 *   dots            true|false         Default: true   Dot pagination.
 *   pause_on_hover  true|false         Default: true
 *   transition      slide|fade         Default: slide
 *   gap             int (px)           Default: 20     Space between slides.
 *
 * @package WPYog_News
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPYOG_Shortcode {

	public function __construct() {
		add_shortcode( 'wpyog_news', array( $this, 'render' ) );

		// AJAX: Load More for card layout.
		add_action( 'wp_ajax_wpyog_load_more',        array( $this, 'ajax_load_more' ) );
		add_action( 'wp_ajax_nopriv_wpyog_load_more', array( $this, 'ajax_load_more' ) );

		// Single news post: prev/next navigation.
		add_filter( 'the_content', array( $this, 'append_post_navigation' ) );
	}

	/**
	 * Main shortcode render method.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Enclosed content (unused).
	 * @return string HTML output.
	 */
	public function render( $atts, $content = null ) {

		global $post, $multipage, $paged;

		// SiteOrigin Page Builder preview bail-out — read-only context check, no form data processed.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['action'] )
			&& in_array( sanitize_text_field( wp_unslash( $_POST['action'] ) ), array( 'so_panels_layout_block_preview', 'so_panels_builder_content_json' ), true ) ) {
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			return '<p style="padding:10px;background:#f5f5f5;border-left:3px solid #0073aa;">'
				. esc_html__( '[wpyog_news] — WPYog News shortcode', 'wpyog-news' )
				. '</p>';
		}

		$atts = shortcode_atts(
			array(
				'layout'          => 'list',
				'limit'           => 10,
				'category'        => '',
				'show_date'       => 'true',
				'show_excerpt'    => 'true',
				'excerpt_length'  => 20,
				'show_source'     => 'true',
				'order'           => 'DESC',
				'orderby'         => 'date',
				'columns'         => 3,
				'pagination'      => 'true',
				'pagination_type' => 'numeric',
				'extra_class'     => '',
				'className'       => '',
				'align'           => '',
				// Mixing multiple post types.
				'post_type'       => '',
				'taxonomy'        => '',
				'show_type'       => 'false',
				'ids'             => '',
				'collection'      => '',
				// Carousel-only.
				'slides_to_show'  => 3,
				'autoplay'        => 'true',
				'autoplay_speed'  => 4000,
				'infinite'        => 'true',
				'arrows'          => 'true',
				'arrows_on_hover' => 'false',
				'dots'            => 'true',
				'pause_on_hover'  => 'true',
				'transition'      => 'slide',
				'gap'             => 20,
			),
			$atts,
			'wpyog_news'
		);

		// Normalise values.
		$layout          = in_array( $atts['layout'], array( 'list', 'card', 'carousel' ), true ) ? $atts['layout'] : 'list';
		$limit           = absint( $atts['limit'] ) ?: 10;
		$category        = ! empty( $atts['category'] ) ? array_filter( array_map( 'absint', explode( ',', $atts['category'] ) ) ) : array();
		$show_date       = ( 'true' === $atts['show_date'] );
		$show_excerpt    = ( 'true' === $atts['show_excerpt'] );
		$excerpt_length  = absint( $atts['excerpt_length'] ) ?: 20;
		$show_source     = ( 'true' === $atts['show_source'] );
		$order           = ( 'ASC' === strtoupper( $atts['order'] ) ) ? 'ASC' : 'DESC';
		$orderby         = sanitize_key( $atts['orderby'] );
		$columns         = min( max( absint( $atts['columns'] ), 2 ), 4 );
		$pagination      = ( 'false' !== $atts['pagination'] );
		$pagination_type = ( 'prev-next' === $atts['pagination_type'] ) ? 'prev-next' : 'numeric';
		$extra_class     = wpyog_sanitize_classes( $atts['extra_class'] . ' ' . $atts['className'] );
		$align_class     = ! empty( $atts['align'] ) ? 'align' . sanitize_html_class( $atts['align'] ) : '';
		$unique          = wpyog_news_unique();

		// Post types — allow mixing WPYog News with other CPTs (comma-separated).
		$post_types = wpyog_news_sanitize_post_types( $atts['post_type'] );

		// Taxonomy the `category` attribute filters against.
		$taxonomy = ! empty( $atts['taxonomy'] ) ? sanitize_key( $atts['taxonomy'] ) : WPYOG_NEWS_CAT;
		if ( ! taxonomy_exists( $taxonomy ) ) {
			$taxonomy = WPYOG_NEWS_CAT;
		}

		$show_type = ( 'true' === $atts['show_type'] );

		// Hand-picked posts — overrides limit/category/pagination and works across any mix of CPTs.
		$ids = ! empty( $atts['ids'] ) ? array_filter( array_map( 'absint', explode( ',', $atts['ids'] ) ) ) : array();
		if ( ! empty( $ids ) && 'date' === $orderby ) {
			$orderby = 'post__in'; // Preserve the order the IDs were listed in, unless orderby was set explicitly.
		}

		// Repeatable curated collection — a shared tag-style taxonomy across any mix of CPTs.
		$collection = ! empty( $atts['collection'] ) ? array_filter( array_map( 'sanitize_title', explode( ',', $atts['collection'] ) ) ) : array();

		// Carousel-only normalisation.
		$slides_to_show = min( max( absint( $atts['slides_to_show'] ), 1 ), 5 );
		$autoplay       = ( 'false' !== $atts['autoplay'] );
		$autoplay_speed = max( 1000, absint( $atts['autoplay_speed'] ) ?: 4000 );
		$infinite       = ( 'false' !== $atts['infinite'] );
		$arrows         = ( 'false' !== $atts['arrows'] );
		$arrows_on_hover = ( 'true' === $atts['arrows_on_hover'] );
		$dots           = ( 'false' !== $atts['dots'] );
		$pause_on_hover = ( 'false' !== $atts['pause_on_hover'] );
		$transition     = ( 'fade' === $atts['transition'] ) ? 'fade' : 'slide';
		$gap            = absint( $atts['gap'] ) ?: 20;

		// Card & Carousel layouts don't use server-side pagination.
		if ( 'card' === $layout || 'carousel' === $layout ) {
			$pagination = false;
		}

		// A hand-picked list of IDs is a fixed set — no pagination needed.
		if ( ! empty( $ids ) ) {
			$pagination = false;
		}

		// Determine current page.
		$multi_page   = ( $multipage || is_single() || is_front_page() || is_archive() ) ? true : false;
		$current_page = 1;
		if ( $multi_page ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Pagination query var, not form data; value is cast to absint.
			$current_page = isset( $_GET['news_page'] ) ? absint( wp_unslash( $_GET['news_page'] ) ) : 1;
		} elseif ( get_query_var( 'paged' ) ) {
			$current_page = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$current_page = get_query_var( 'page' );
		}

		// Build WP_Query.
		$query_args = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'orderby'        => $orderby,
			'order'          => $order,
			'posts_per_page' => ! empty( $ids ) ? -1 : $limit,
			'paged'          => $pagination ? $current_page : 1,
		);

		if ( ! empty( $ids ) ) {
			// Hand-picked posts — takes priority over category/collection filtering.
			$query_args['post__in'] = $ids;
			$query_args['ignore_sticky_posts'] = true;
		} else {
			$tax_query = array();

			if ( ! empty( $category ) ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $category,
				);
			}

			if ( ! empty( $collection ) ) {
				$tax_query[] = array(
					'taxonomy' => WPYOG_COLLECTION_TAX,
					'field'    => 'slug',
					'terms'    => $collection,
				);
			}

			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}

			if ( ! empty( $tax_query ) ) {
				$query_args['tax_query'] = $tax_query;
			}
		}

		$query = new WP_Query( $query_args );

		ob_start();

		$wrapper_classes = 'wpyog-news-wrap wpyog-layout-' . $layout;
		if ( 'card' === $layout ) {
			$wrapper_classes .= ' wpyog-cols-' . $columns;
		}
		if ( 'carousel' === $layout ) {
			$wrapper_classes .= ' wpyog-slides-' . $slides_to_show;
		}
		$wrapper_classes .= $extra_class ? ' ' . $extra_class : '';
		$wrapper_classes .= $align_class ? ' ' . $align_class : '';
		?>

		<div class="<?php echo esc_attr( $wrapper_classes ); ?>" id="wpyog-news-<?php echo esc_attr( $unique ); ?>">

			<?php if ( $query->have_posts() ) : ?>

				<?php if ( 'carousel' === $layout ) : ?>
				<div class="wpyog-carousel-wrap"
					data-slides-to-show="<?php echo esc_attr( $slides_to_show ); ?>"
					data-autoplay="<?php echo esc_attr( $autoplay ? 'true' : 'false' ); ?>"
					data-autoplay-speed="<?php echo esc_attr( $autoplay_speed ); ?>"
					data-infinite="<?php echo esc_attr( $infinite ? 'true' : 'false' ); ?>"
					data-transition="<?php echo esc_attr( $transition ); ?>"
					data-pause-on-hover="<?php echo esc_attr( $pause_on_hover ? 'true' : 'false' ); ?>"
					data-arrows-on-hover="<?php echo esc_attr( $arrows_on_hover ? 'true' : 'false' ); ?>">
					<div class="wpyog-carousel-viewport">
						<div class="wpyog-carousel-track" style="gap:<?php echo esc_attr( $gap ); ?>px;--wpyog-carousel-gap:<?php echo esc_attr( $gap ); ?>px;">
				<?php else : ?>
				<div class="wpyog-news-<?php echo esc_attr( $layout ); ?>-container">
				<?php endif; ?>
					<?php while ( $query->have_posts() ) : $query->the_post(); ?>
						<?php
						$item_id     = get_the_ID();
						$ext_url     = get_post_meta( $item_id, '_wpyog_external_url',    true );
						$source_name = get_post_meta( $item_id, '_wpyog_source_name',    true );
						$source_url  = get_post_meta( $item_id, '_wpyog_source_url',     true );
						$source_fav  = get_post_meta( $item_id, '_wpyog_source_favicon', true );

						$link_url    = ! empty( $ext_url ) ? $ext_url : get_permalink();
						$link_target = ! empty( $ext_url ) ? ' target="_blank" rel="noopener noreferrer"' : '';

						$terms     = get_the_terms( $item_id, $taxonomy );
						$cat_links = array();
						if ( $terms && ! is_wp_error( $terms ) ) {
							foreach ( $terms as $term ) {
								$cat_links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
							}
						}

						// Post type — used for the optional badge and per-type CSS class when mixing CPTs.
						$item_post_type = get_post_type( $item_id );
						$item_type_obj  = get_post_type_object( $item_post_type );
						$item_type_label = $item_type_obj ? $item_type_obj->labels->singular_name : $item_post_type;
						$type_class      = ' wpyog-type-' . sanitize_html_class( $item_post_type );
						?>

						<?php if ( 'list' === $layout ) : ?>
						<!-- ===== LIST ITEM ===== -->
						<article id="wpyog-item-<?php echo esc_attr( $item_id ); ?>" class="wpyog-news-item wpyog-list-item<?php echo has_post_thumbnail() ? '' : ' no-thumb'; ?><?php echo esc_attr( $type_class ); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="wpyog-item-thumb">
									<a href="<?php echo esc_url( $link_url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										<?php the_post_thumbnail( 'medium' ); ?>
									</a>
								</div>
							<?php endif; ?>

							<div class="wpyog-item-body">
								<?php if ( $show_type || ! empty( $cat_links ) ) : ?>
									<div class="wpyog-item-cats">
										<?php if ( $show_type ) : ?><span class="wpyog-item-type"><?php echo esc_html( $item_type_label ); ?></span><?php endif; ?>
										<?php echo wp_kses_post( implode( ' ', $cat_links ) ); ?>
									</div>
								<?php endif; ?>

								<h2 class="wpyog-item-title">
									<a href="<?php echo esc_url( $link_url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php the_title(); ?></a>
								</h2>

								<?php if ( $show_date ) : ?>
									<span class="wpyog-item-date"><?php echo esc_html( get_the_date() ); ?></span>
								<?php endif; ?>

								<?php if ( $show_excerpt ) : ?>
									<div class="wpyog-item-excerpt">
										<?php echo esc_html( wpyog_news_excerpt( $item_id, get_the_content(), $excerpt_length ) ); ?>
									</div>
								<?php endif; ?>

								<div class="wpyog-item-footer">
									<?php if ( $show_source && $source_name ) : ?>
										<span class="wpyog-source">
											<?php if ( $source_fav ) : ?>
												<img src="<?php echo esc_url( $source_fav ); ?>" alt="<?php echo esc_attr( $source_name ); ?>" class="wpyog-source-favicon" width="24" height="24" />
											<?php endif; ?>
											<?php if ( $source_url ) : ?>
												<a href="<?php echo esc_url( $source_url ); ?>" target="_blank" rel="noopener noreferrer" class="wpyog-source-name"><?php echo esc_html( $source_name ); ?></a>
											<?php else : ?>
												<span class="wpyog-source-name"><?php echo esc_html( $source_name ); ?></span>
											<?php endif; ?>
										</span>
									<?php endif; ?>

									<a href="<?php echo esc_url( $link_url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="wpyog-read-more">
										<?php esc_html_e( 'Read More', 'wpyog-news' ); ?>
									</a>
								</div>
							</div>
						</article>

						<?php else : ?>
						<!-- ===== CARD / CAROUSEL ITEM ===== -->
						<article id="wpyog-item-<?php echo esc_attr( $item_id ); ?>" class="wpyog-news-item wpyog-card-item<?php echo ( 'carousel' === $layout ) ? ' wpyog-carousel-item' : ''; ?><?php echo esc_attr( $type_class ); ?>">
							<div class="wpyog-card-inner">
								<div class="wpyog-card-thumb<?php echo has_post_thumbnail() ? '' : ' wpyog-card-no-thumb'; ?>">
									<a href="<?php echo esc_url( $link_url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										<?php if ( has_post_thumbnail() ) : ?>
											<?php the_post_thumbnail( 'medium_large' ); ?>
										<?php endif; ?>
									</a>
									<?php if ( $show_type || ! empty( $cat_links ) ) : ?>
										<div class="wpyog-card-cats">
											<?php if ( $show_type ) : ?><span class="wpyog-item-type"><?php echo esc_html( $item_type_label ); ?></span><?php endif; ?>
											<?php echo wp_kses_post( implode( ' ', $cat_links ) ); ?>
										</div>
									<?php endif; ?>
								</div>

								<div class="wpyog-card-body">
									<h2 class="wpyog-item-title">
										<a href="<?php echo esc_url( $link_url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php the_title(); ?></a>
									</h2>
									<?php if ( $show_excerpt ) : ?>
										<div class="wpyog-item-excerpt">
											<?php echo esc_html( wpyog_news_excerpt( $item_id, get_the_content(), $excerpt_length ) ); ?>
										</div>
									<?php endif; ?>
								</div>

								<div class="wpyog-card-footer">
									<?php if ( $show_source && $source_name ) : ?>
										<span class="wpyog-source">
											<?php if ( $source_fav ) : ?>
												<img src="<?php echo esc_url( $source_fav ); ?>" alt="<?php echo esc_attr( $source_name ); ?>" class="wpyog-source-favicon" width="24" height="24" />
											<?php endif; ?>
											<?php if ( $source_url ) : ?>
												<a href="<?php echo esc_url( $source_url ); ?>" target="_blank" rel="noopener noreferrer" class="wpyog-source-name"><?php echo esc_html( $source_name ); ?></a>
											<?php else : ?>
												<span class="wpyog-source-name"><?php echo esc_html( $source_name ); ?></span>
											<?php endif; ?>
										</span>
									<?php endif; ?>

									<div class="wpyog-card-bottom-row">
										<?php if ( $show_date ) : ?>
											<span class="wpyog-item-date"><?php echo esc_html( get_the_date() ); ?></span>
										<?php endif; ?>
										<a href="<?php echo esc_url( $link_url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="wpyog-read-more">
											<?php esc_html_e( 'Read More', 'wpyog-news' ); ?>
										</a>
									</div>
								</div>
							</div>
						</article>

						<?php endif; ?>

					<?php endwhile; ?>
				<?php if ( 'carousel' === $layout ) : ?>
						</div><!-- .wpyog-carousel-track -->
					</div><!-- .wpyog-carousel-viewport -->

					<?php if ( $arrows ) : ?>
					<button type="button" class="wpyog-carousel-arrow wpyog-carousel-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'wpyog-news' ); ?>">
						<svg width="10" height="16" viewBox="0 0 10 16" fill="none" aria-hidden="true"><path d="M9 1L2 8l7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
					<button type="button" class="wpyog-carousel-arrow wpyog-carousel-next" aria-label="<?php esc_attr_e( 'Next slide', 'wpyog-news' ); ?>">
						<svg width="10" height="16" viewBox="0 0 10 16" fill="none" aria-hidden="true"><path d="M1 1l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
					<?php endif; ?>

					<?php if ( $dots ) : ?>
					<div class="wpyog-carousel-dots" role="tablist" aria-label="<?php esc_attr_e( 'Carousel slide navigation', 'wpyog-news' ); ?>"></div>
					<?php endif; ?>
				</div><!-- .wpyog-carousel-wrap -->
				<?php else : ?>
				</div><!-- .wpyog-news-*-container -->
				<?php endif; ?>

				<?php
				// List: server-side pagination.
				if ( $pagination && $query->max_num_pages > 1 ) {
					echo '<div class="wpyog-pagination wpyog-paging-' . esc_attr( $pagination_type ) . '">';
					echo wp_kses_post( wpyog_news_pagination( array(
						'paged'           => $current_page,
						'total'           => $query->max_num_pages,
						'pagination_type' => $pagination_type,
						'unique'          => $unique,
						'multi_page'      => $multi_page,
					) ) );
					echo '</div>';
				}

				// Card: Load More button.
				// All config uses HTML5-standard hyphen-separated data attributes.
				if ( 'card' === $layout && $query->max_num_pages > 1 ) {
					echo '<div class="wpyog-load-more-wrap">'
						. '<button class="wpyog-load-more-btn"'
						. ' data-page="1"'
						. ' data-max="'            . esc_attr( $query->max_num_pages )            . '"'
						. ' data-limit="'          . esc_attr( $limit )                           . '"'
						. ' data-category="'       . esc_attr( implode( ',', $category ) )        . '"'
						. ' data-show-date="'      . esc_attr( $show_date    ? 'true' : 'false' ) . '"'
						. ' data-show-excerpt="'   . esc_attr( $show_excerpt ? 'true' : 'false' ) . '"'
						. ' data-excerpt-length="' . esc_attr( $excerpt_length )                  . '"'
						. ' data-show-source="'    . esc_attr( $show_source  ? 'true' : 'false' ) . '"'
						. ' data-order="'          . esc_attr( $order )                           . '"'
						. ' data-orderby="'        . esc_attr( $orderby )                         . '"'
						. ' data-post-type="'      . esc_attr( implode( ',', $post_types ) )       . '"'
						. ' data-taxonomy="'       . esc_attr( $taxonomy )                         . '"'
						. ' data-show-type="'      . esc_attr( $show_type    ? 'true' : 'false' )  . '"'
						. ' data-ids="'            . esc_attr( implode( ',', $ids ) )              . '"'
					. ' data-collection="'     . esc_attr( implode( ',', $collection ) )       . '"'
						. ' data-container="#wpyog-news-' . esc_attr( $unique ) . ' .wpyog-news-card-container"'
						. '>' . esc_html__( 'Load More', 'wpyog-news' ) . '</button>'
						. '</div>';
				}
				?>

			<?php else : ?>
				<p class="wpyog-no-news"><?php esc_html_e( 'No news found.', 'wpyog-news' ); ?></p>
			<?php endif; ?>

		</div><!-- #wpyog-news-<?php echo esc_attr( $unique ); ?> -->

		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * AJAX handler: load next page of card items.
	 */
	public function ajax_load_more() {

		check_ajax_referer( 'wpyog_load_more_nonce', 'nonce' );

		$page           = isset( $_POST['page'] )            ? absint( wp_unslash( $_POST['page'] ) )                                           : 1;
		$limit          = isset( $_POST['limit'] )           ? absint( wp_unslash( $_POST['limit'] ) )                                          : 10;
		$category_raw   = isset( $_POST['category'] )        ? sanitize_text_field( wp_unslash( $_POST['category'] ) )                         : '';
		$category       = ! empty( $category_raw )           ? array_filter( array_map( 'absint', explode( ',', $category_raw ) ) )             : array();
		$show_date      = isset( $_POST['show_date'] )       && 'true' === sanitize_text_field( wp_unslash( $_POST['show_date'] ) );
		$show_excerpt   = isset( $_POST['show_excerpt'] )    && 'true' === sanitize_text_field( wp_unslash( $_POST['show_excerpt'] ) );
		$excerpt_length = isset( $_POST['excerpt_length'] )  ? absint( wp_unslash( $_POST['excerpt_length'] ) )                                 : 20;
		$show_source    = isset( $_POST['show_source'] )     && 'true' === sanitize_text_field( wp_unslash( $_POST['show_source'] ) );
		$order          = isset( $_POST['order'] )           && 'ASC' === strtoupper( sanitize_text_field( wp_unslash( $_POST['order'] ) ) ) ? 'ASC' : 'DESC';
		$orderby        = isset( $_POST['orderby'] )         ? sanitize_key( wp_unslash( $_POST['orderby'] ) )                                  : 'date';
		$post_types     = isset( $_POST['post_type'] )       ? wpyog_news_sanitize_post_types( sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) ) : array( WPYOG_NEWS_POST_TYPE );
		$taxonomy       = isset( $_POST['taxonomy'] )        ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) )                                 : WPYOG_NEWS_CAT;
		if ( ! taxonomy_exists( $taxonomy ) ) {
			$taxonomy = WPYOG_NEWS_CAT;
		}
		$show_type      = isset( $_POST['show_type'] )       && 'true' === sanitize_text_field( wp_unslash( $_POST['show_type'] ) );
		$ids_raw        = isset( $_POST['ids'] )             ? sanitize_text_field( wp_unslash( $_POST['ids'] ) )                               : '';
		$ids            = ! empty( $ids_raw )                ? array_filter( array_map( 'absint', explode( ',', $ids_raw ) ) )                  : array();
		$collection_raw = isset( $_POST['collection'] )      ? sanitize_text_field( wp_unslash( $_POST['collection'] ) )                        : '';
		$collection     = ! empty( $collection_raw )         ? array_filter( array_map( 'sanitize_title', explode( ',', $collection_raw ) ) )    : array();

		$next_page = $page + 1;

		$query_args = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'orderby'        => $orderby,
			'order'          => $order,
			'posts_per_page' => ! empty( $ids ) ? -1 : $limit,
			'paged'          => $next_page,
		);

		if ( ! empty( $ids ) ) {
			$query_args['post__in'] = $ids;
			$query_args['ignore_sticky_posts'] = true;
		} else {
			$tax_query = array();

			if ( ! empty( $category ) ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $category,
				);
			}

			if ( ! empty( $collection ) ) {
				$tax_query[] = array(
					'taxonomy' => WPYOG_COLLECTION_TAX,
					'field'    => 'slug',
					'terms'    => $collection,
				);
			}

			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}

			if ( ! empty( $tax_query ) ) {
				$query_args['tax_query'] = $tax_query;
			}
		}

		$query = new WP_Query( $query_args );

		ob_start();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$item_id     = get_the_ID();
				$ext_url     = get_post_meta( $item_id, '_wpyog_external_url',    true );
				$source_name = get_post_meta( $item_id, '_wpyog_source_name',    true );
				$source_url  = get_post_meta( $item_id, '_wpyog_source_url',     true );
				$source_fav  = get_post_meta( $item_id, '_wpyog_source_favicon', true );
				$link_url    = ! empty( $ext_url ) ? $ext_url : get_permalink();
				$link_target = ! empty( $ext_url ) ? ' target="_blank" rel="noopener noreferrer"' : '';

				$terms     = get_the_terms( $item_id, $taxonomy );
				$cat_links = array();
				if ( $terms && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$cat_links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
					}
				}

				$item_post_type  = get_post_type( $item_id );
				$item_type_obj   = get_post_type_object( $item_post_type );
				$item_type_label = $item_type_obj ? $item_type_obj->labels->singular_name : $item_post_type;
				$type_class      = ' wpyog-type-' . sanitize_html_class( $item_post_type );
				?>

				<article id="wpyog-item-<?php echo esc_attr( $item_id ); ?>" class="wpyog-news-item wpyog-card-item<?php echo esc_attr( $type_class ); ?>">
					<div class="wpyog-card-inner">
						<div class="wpyog-card-thumb<?php echo has_post_thumbnail() ? '' : ' wpyog-card-no-thumb'; ?>">
							<a href="<?php echo esc_url( $link_url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'medium_large' ); endif; ?>
							</a>
							<?php if ( $show_type || ! empty( $cat_links ) ) : ?>
								<div class="wpyog-card-cats">
									<?php if ( $show_type ) : ?><span class="wpyog-item-type"><?php echo esc_html( $item_type_label ); ?></span><?php endif; ?>
									<?php echo wp_kses_post( implode( ' ', $cat_links ) ); ?>
								</div>
							<?php endif; ?>
						</div>

						<div class="wpyog-card-body">
							<h2 class="wpyog-item-title">
								<a href="<?php echo esc_url( $link_url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php the_title(); ?></a>
							</h2>
							<?php if ( $show_excerpt ) : ?>
								<div class="wpyog-item-excerpt">
									<?php echo esc_html( wpyog_news_excerpt( $item_id, get_the_content(), $excerpt_length ) ); ?>
								</div>
							<?php endif; ?>
						</div>

						<div class="wpyog-card-footer">
							<?php if ( $show_source && $source_name ) : ?>
								<span class="wpyog-source">
									<?php if ( $source_fav ) : ?>
										<img src="<?php echo esc_url( $source_fav ); ?>" alt="<?php echo esc_attr( $source_name ); ?>" class="wpyog-source-favicon" width="24" height="24" />
									<?php endif; ?>
									<?php if ( $source_url ) : ?>
										<a href="<?php echo esc_url( $source_url ); ?>" target="_blank" rel="noopener noreferrer" class="wpyog-source-name"><?php echo esc_html( $source_name ); ?></a>
									<?php else : ?>
										<span class="wpyog-source-name"><?php echo esc_html( $source_name ); ?></span>
									<?php endif; ?>
								</span>
							<?php endif; ?>
							<div class="wpyog-card-bottom-row">
								<?php if ( $show_date ) : ?>
									<span class="wpyog-item-date"><?php echo esc_html( get_the_date() ); ?></span>
								<?php endif; ?>
								<a href="<?php echo esc_url( $link_url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="wpyog-read-more">
									<?php esc_html_e( 'Read More', 'wpyog-news' ); ?>
								</a>
							</div>
						</div>
					</div>
				</article>

				<?php
			}
		}

		$html = ob_get_clean();
		wp_reset_postdata();

		wp_send_json_success( array(
			'html'     => $html,
			'has_more' => $next_page < $query->max_num_pages,
		) );
	}

	/**
	 * Append Previous/Next navigation on single news detail pages.
	 *
	 * Uses $in_same_term = false so navigation always works even when a
	 * news item has no category assigned (the previous bug: same-term lookup
	 * returned null for uncategorised posts).
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function append_post_navigation( $content ) {

		if ( ! is_singular( WPYOG_NEWS_POST_TYPE ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		// $in_same_term = false → finds any adjacent wpyog_news post, category not required.
		$prev_post = get_previous_post( false );
		$next_post = get_next_post( false );

		// WordPress's get_adjacent_post can return non-wpyog_news posts when $in_same_term is false.
		// Ensure we only link to the same post type.
		if ( $prev_post && WPYOG_NEWS_POST_TYPE !== $prev_post->post_type ) {
			$prev_post = null;
		}
		if ( $next_post && WPYOG_NEWS_POST_TYPE !== $next_post->post_type ) {
			$next_post = null;
		}

		if ( ! $prev_post && ! $next_post ) {
			return $content;
		}

		$nav  = '<nav class="wpyog-post-navigation" aria-label="' . esc_attr__( 'News navigation', 'wpyog-news' ) . '">';
		$nav .= '<div class="wpyog-nav-links">';

		if ( $prev_post ) {
			$prev_ext = get_post_meta( $prev_post->ID, '_wpyog_external_url', true );
			$prev_url = ! empty( $prev_ext ) ? $prev_ext : get_permalink( $prev_post->ID );
			$prev_tgt = ! empty( $prev_ext ) ? ' target="_blank" rel="noopener noreferrer"' : '';
			$nav .= '<div class="wpyog-nav-prev">'
				. '<span class="wpyog-nav-label">' . esc_html__( '← Previous', 'wpyog-news' ) . '</span>'
				. '<a href="' . esc_url( $prev_url ) . '"' . $prev_tgt . ' class="wpyog-nav-title">'
				. esc_html( get_the_title( $prev_post->ID ) )
				. '</a>'
				. '</div>';
		} else {
			// Placeholder to keep the two-column layout balanced.
			$nav .= '<div class="wpyog-nav-prev wpyog-nav-empty"></div>';
		}

		if ( $next_post ) {
			$next_ext = get_post_meta( $next_post->ID, '_wpyog_external_url', true );
			$next_url = ! empty( $next_ext ) ? $next_ext : get_permalink( $next_post->ID );
			$next_tgt = ! empty( $next_ext ) ? ' target="_blank" rel="noopener noreferrer"' : '';
			$nav .= '<div class="wpyog-nav-next">'
				. '<span class="wpyog-nav-label">' . esc_html__( 'Next →', 'wpyog-news' ) . '</span>'
				. '<a href="' . esc_url( $next_url ) . '"' . $next_tgt . ' class="wpyog-nav-title">'
				. esc_html( get_the_title( $next_post->ID ) )
				. '</a>'
				. '</div>';
		} else {
			$nav .= '<div class="wpyog-nav-next wpyog-nav-empty"></div>';
		}

		$nav .= '</div>';
		$nav .= '</nav>';

		return $content . $nav;
	}
}
