<?php
/**
 * WPYog News Ticker Shortcode
 *
 * Registers the [wpyog_ticker] (alias: [wpyog_news_ticker]) shortcode.
 * Three animation styles are available:
 *
 *  scroll — continuous horizontal scroll (classic ticker tape).
 *  fade   — items cross-fade one at a time.
 *  flap   — split-flap airport display effect (Solari board style).
 *
 * Shortcode attributes:
 *
 *  limit          integer         10           Number of news items to show.
 *  category       comma-sep IDs   (all)        Filter by News Category IDs.
 *  animation      scroll|fade|flap scroll      Animation style.
 *  speed          slow|medium|fast|int medium  Scroll speed (px/s) or cycling interval.
 *  label          string          Breaking News Label text.
 *  show_label     true|false      true         Show or hide the label chip entirely.
 *  label_bg       hex color       #e74c3c      Label background colour.
 *  label_color    hex color       #ffffff      Label text colour.
 *  show_count     true|false      false        Show "n / total" counter (fade/flap only).
 *  pause_on_hover true|false      true         Pause ticker when hovered.
 *  show_date      true|false      true         Show publication date next to title.
 *  separator      string          •            Character between items (scroll mode only).
 *  direction      left|right      left         Scroll direction.
 *  order          DESC|ASC        DESC         Date order.
 *  orderby        date|title|rand date         Sort field.
 *  extra_class    string          (none)       Extra CSS class on the wrapper.
 *
 * @package WPYog_News
 * @since   1.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPYOG_Ticker {

	/** Speed presets in px/sec (scroll) / arbitrary unit (cycle). */
	const SPEED_MAP = array(
		'slow'   => 40,
		'medium' => 80,
		'fast'   => 160,
	);

	public function __construct() {
		add_shortcode( 'wpyog_ticker',      array( $this, 'render' ) );
		add_shortcode( 'wpyog_news_ticker', array( $this, 'render' ) ); // alias
	}

	/**
	 * Render the ticker shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render( $atts ) {

		$atts = shortcode_atts( array(
			'limit'          => 10,
			'category'       => '',
			'animation'      => 'scroll',
			'speed'          => 'medium',
			'label'          => __( 'Breaking News', 'wpyog-news' ),
			'show_label'     => 'true',
			'label_bg'       => '#e74c3c',
			'label_color'    => '#ffffff',
			'show_count'     => 'false',
			'pause_on_hover' => 'true',
			'show_date'      => 'true',
			'separator'      => '&bull;',
			'direction'      => 'left',
			'order'          => 'DESC',
			'orderby'        => 'date',
			'extra_class'    => '',
		), $atts, 'wpyog_ticker' );

		// --- Sanitise ---
		$limit       = max( 1, absint( $atts['limit'] ) );
		// 'slide' is an alias for 'flap' kept for backwards compatibility.
		$raw_anim    = $atts['animation'];
		if ( 'slide' === $raw_anim ) { $raw_anim = 'flap'; }
		$animation   = in_array( $raw_anim, array( 'scroll', 'fade', 'flap' ), true ) ? $raw_anim : 'scroll';
		$direction   = ( 'right' === $atts['direction'] ) ? 'right' : 'left';
		$order       = ( 'ASC' === strtoupper( $atts['order'] ) ) ? 'ASC' : 'DESC';
		$orderby     = in_array( $atts['orderby'], array( 'date', 'title', 'rand' ), true ) ? $atts['orderby'] : 'date';
		$pause       = ( 'false' === $atts['pause_on_hover'] ) ? 'false' : 'true';
		$show_date   = ( 'false' !== $atts['show_date'] );
		$show_label  = ( 'false' !== $atts['show_label'] );
		$show_count  = ( 'true' === $atts['show_count'] ) && ( 'scroll' !== $animation );
		$label_bg    = sanitize_hex_color( $atts['label_bg'] ) ?: '#e74c3c';
		$label_color = sanitize_hex_color( $atts['label_color'] ) ?: '#ffffff';
		$label       = sanitize_text_field( $atts['label'] );
		$separator   = wp_kses_post( $atts['separator'] );
		$extra_class = function_exists( 'wpyog_sanitize_classes' ) ? wpyog_sanitize_classes( $atts['extra_class'] ) : sanitize_html_class( $atts['extra_class'] );

		// Resolve speed to integer.
		if ( isset( self::SPEED_MAP[ $atts['speed'] ] ) ) {
			$speed_px = self::SPEED_MAP[ $atts['speed'] ];
		} else {
			$speed_px = max( 10, absint( $atts['speed'] ) ) ?: 80;
		}

		// --- Category filter ---
		$cat_ids = array();
		if ( ! empty( $atts['category'] ) ) {
			$cat_ids = array_filter( array_map( 'absint', explode( ',', $atts['category'] ) ) );
		}

		// --- Query ---
		$query_args = array(
			'post_type'      => WPYOG_NEWS_POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'order'          => $order,
			'orderby'        => $orderby,
			'no_found_rows'  => true,
		);

		if ( ! empty( $cat_ids ) ) {
			$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => WPYOG_NEWS_CAT,
					'field'    => 'term_id',
					'terms'    => $cat_ids,
				),
			);
		}

		$query = new WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			return '';
		}

		// --- Collect posts ---
		$posts = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id   = get_the_ID();
			$ext_url   = get_post_meta( $post_id, '_wpyog_external_url', true );
			$posts[]   = array(
				'title'  => get_the_title(),
				'url'    => $ext_url ? esc_url( $ext_url ) : esc_url( get_permalink() ),
				'target' => $ext_url ? ' target="_blank" rel="noopener noreferrer"' : '',
				'date'   => $show_date ? esc_html( get_the_date() ) : '',
			);
		}
		wp_reset_postdata();

		$total = count( $posts );

		// --- Build item markup depending on animation mode ---
		$items_html = '';

		if ( 'scroll' === $animation ) {
			// Inline <span> items — all visible simultaneously in the scrolling strip.
			foreach ( $posts as $p ) {
				$items_html .= '<span class="wpyog-ticker-item">';
				if ( $p['date'] ) {
					$items_html .= '<span class="wpyog-ticker-date">' . $p['date'] . '</span>';
				}
				$items_html .= '<a href="' . $p['url'] . '"' . $p['target'] . '>' . esc_html( $p['title'] ) . '</a>';
				$items_html .= '<span class="wpyog-ticker-sep" aria-hidden="true">' . $separator . '</span>';
				$items_html .= '</span>';
			}

		} else {
			// Fade / Flap (push-up) — block <div> items, JS animates them.
			foreach ( $posts as $p ) {
				$items_html .= '<div class="wpyog-ticker-item" role="listitem">';
				if ( $p['date'] ) {
					$items_html .= '<span class="wpyog-ticker-date">' . $p['date'] . '</span>';
				}
				$items_html .= '<a href="' . $p['url'] . '"' . $p['target'] . '>' . esc_html( $p['title'] ) . '</a>';
				$items_html .= '</div>';
			}
		}

		// --- Wrapper classes ---
		$classes = array_filter( array(
			'wpyog-ticker-wrap',
			'wpyog-ticker-anim-' . $animation,
			'wpyog-ticker-dir-' . $direction,
			$extra_class,
		) );

		// --- Output ---
		ob_start();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
		     data-animation="<?php echo esc_attr( $animation ); ?>"
		     data-speed="<?php echo esc_attr( $speed_px ); ?>"
		     data-pause="<?php echo esc_attr( $pause ); ?>"
		     data-direction="<?php echo esc_attr( $direction ); ?>"
		     role="region"
		     aria-label="<?php esc_attr_e( 'News ticker', 'wpyog-news' ); ?>">

			<?php if ( $show_label && $label ) : ?>
			<div class="wpyog-ticker-label"
			     style="background-color:<?php echo esc_attr( $label_bg ); ?>;color:<?php echo esc_attr( $label_color ); ?>;">
				<span class="wpyog-ticker-label-text"><?php echo esc_html( $label ); ?></span>
				<span class="wpyog-ticker-label-arrow"
				      style="border-left-color:<?php echo esc_attr( $label_bg ); ?>;"
				      aria-hidden="true"></span>
			</div>
			<?php endif; ?>

			<div class="wpyog-ticker-viewport">

				<?php if ( 'scroll' === $animation ) : ?>
					<div class="wpyog-ticker-track">
						<div class="wpyog-ticker-content"><?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<div class="wpyog-ticker-content" aria-hidden="true"><?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					</div>

				<?php else : ?>
					<div class="wpyog-ticker-cycle" role="list">
						<?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>

				<?php endif; ?>

			</div><!-- .wpyog-ticker-viewport -->

			<?php if ( $show_count ) : ?>
			<div class="wpyog-ticker-count" aria-live="polite" aria-atomic="true">
				<span class="wpyog-ticker-count-current">1</span><span class="wpyog-ticker-count-sep">/</span><span class="wpyog-ticker-count-total"><?php echo esc_html( $total ); ?></span>
			</div>
			<?php endif; ?>

		</div><!-- .wpyog-ticker-wrap -->
		<?php
		return ob_get_clean();
	}
}
