<?php
/**
 * Template: Single News Post
 *
 * Loaded automatically for single wpyog_news posts when the active theme
 * does not provide its own single-wpyog_news.php. Inherits the theme's
 * header, footer and sidebar via the standard WordPress template functions.
 *
 * Themes can override this by placing a file named single-wpyog_news.php
 * in their theme (or child theme) root directory.
 *
 * @package WPYog_News
 * @since   1.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="wpyog-single-wrap">
	<div class="wpyog-single-container">

		<?php while ( have_posts() ) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'wpyog-single-article' ); ?>>

				<!-- ===== HEADER: Title + Meta ===== -->
				<header class="wpyog-single-header">

					<?php
					// Categories
					$terms = get_the_terms( get_the_ID(), WPYOG_NEWS_CAT );
					if ( $terms && ! is_wp_error( $terms ) ) {
						$cat_links = array();
						foreach ( $terms as $term ) {
							$cat_links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
						}
						echo '<div class="wpyog-single-cats">' . wp_kses_post( implode( ' ', $cat_links ) ) . '</div>';
					}
					?>

					<h1 class="wpyog-single-title"><?php the_title(); ?></h1>

					<div class="wpyog-single-meta">
						<span class="wpyog-single-date">
							<?php echo esc_html( get_the_date() ); ?>
						</span>
						<?php if ( get_the_author() ) : ?>
							<span class="wpyog-single-author">
								<?php esc_html_e( 'By', 'wpyog-news' ); ?>
								<?php echo esc_html( get_the_author() ); ?>
							</span>
						<?php endif; ?>
						<?php
						// Source credit in header
						$source_name = get_post_meta( get_the_ID(), '_wpyog_source_name',    true );
						$source_url  = get_post_meta( get_the_ID(), '_wpyog_source_url',     true );
						$source_fav  = get_post_meta( get_the_ID(), '_wpyog_source_favicon', true );
						if ( $source_name ) :
						?>
							<span class="wpyog-single-source">
								<?php if ( $source_fav ) : ?>
									<img src="<?php echo esc_url( $source_fav ); ?>" alt="<?php echo esc_attr( $source_name ); ?>" width="16" height="16" class="wpyog-source-favicon" />
								<?php endif; ?>
								<?php if ( $source_url ) : ?>
									<a href="<?php echo esc_url( $source_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $source_name ); ?></a>
								<?php else : ?>
									<span><?php echo esc_html( $source_name ); ?></span>
								<?php endif; ?>
							</span>
						<?php endif; ?>
					</div><!-- .wpyog-single-meta -->

				</header><!-- .wpyog-single-header -->

				<!-- ===== FEATURED IMAGE ===== -->
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="wpyog-single-thumb">
						<?php the_post_thumbnail( 'large', array( 'class' => 'wpyog-single-thumb-img' ) ); ?>
					</div>
				<?php endif; ?>

				<!-- ===== EXTERNAL LINK NOTICE ===== -->
				<?php
				$ext_url = get_post_meta( get_the_ID(), '_wpyog_external_url', true );
				if ( $ext_url ) :
				?>
					<div class="wpyog-single-ext-notice">
						<?php esc_html_e( 'This article is sourced from an external publication.', 'wpyog-news' ); ?>
						<a href="<?php echo esc_url( $ext_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Read the original →', 'wpyog-news' ); ?>
						</a>
					</div>
				<?php endif; ?>

				<!-- ===== CONTENT ===== -->
				<div class="wpyog-single-content entry-content">
					<?php the_content(); ?>
					<?php
					wp_link_pages( array(
						'before'      => '<div class="wpyog-page-links"><span>' . esc_html__( 'Pages:', 'wpyog-news' ) . '</span>',
						'after'       => '</div>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
					) );
					?>
				</div><!-- .wpyog-single-content -->

			</article><!-- .wpyog-single-article -->

		<?php endwhile; ?>

	</div><!-- .wpyog-single-container -->
</div><!-- .wpyog-single-wrap -->

<?php get_footer(); ?>
