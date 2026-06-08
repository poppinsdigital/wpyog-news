<?php
/**
 * Admin Metaboxes: External URL & Source Details.
 *
 * @package WPYog_News
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPYOG_Metabox {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
		add_action( 'save_post',      array( $this, 'save_metaboxes' ), 10, 2 );
	}

	/**
	 * Register all metaboxes for the news CPT.
	 */
	public function register_metaboxes() {
		add_meta_box(
			'wpyog_external_link',
			__( 'External Link', 'wpyog-news' ),
			array( $this, 'render_external_link' ),
			WPYOG_NEWS_POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'wpyog_source_details',
			__( 'Source Details', 'wpyog-news' ),
			array( $this, 'render_source_details' ),
			WPYOG_NEWS_POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the External Link metabox.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_external_link( $post ) {
		wp_nonce_field( 'wpyog_external_link_nonce', 'wpyog_external_link_nonce' );
		$url = get_post_meta( $post->ID, '_wpyog_external_url', true );
		?>
		<div class="wpyog-metabox-wrap">
			<p class="wpyog-metabox-desc">
				<?php esc_html_e( 'When an external URL is set, the news title and "Read More" link will point to this URL instead of the internal news detail page.', 'wpyog-news' ); ?>
			</p>
			<table class="wpyog-meta-table">
				<tr>
					<th><label for="wpyog_external_url"><?php esc_html_e( 'External URL', 'wpyog-news' ); ?></label></th>
					<td>
						<input
							type="url"
							id="wpyog_external_url"
							name="wpyog_external_url"
							value="<?php echo esc_url( $url ); ?>"
							placeholder="https://example.com/article"
							class="widefat"
						/>
						<p class="description"><?php esc_html_e( 'Leave blank to use the internal news page URL.', 'wpyog-news' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render the Source Details metabox.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_source_details( $post ) {
		wp_nonce_field( 'wpyog_source_details_nonce', 'wpyog_source_details_nonce' );

		$name    = get_post_meta( $post->ID, '_wpyog_source_name',    true );
		$url     = get_post_meta( $post->ID, '_wpyog_source_url',     true );
		$favicon = get_post_meta( $post->ID, '_wpyog_source_favicon', true );
		?>
		<div class="wpyog-metabox-wrap">
			<p class="wpyog-metabox-desc">
				<?php esc_html_e( 'Optionally credit the news source. The source name and favicon will be displayed on news cards.', 'wpyog-news' ); ?>
			</p>
			<table class="wpyog-meta-table">
				<tr>
					<th><label for="wpyog_source_name"><?php esc_html_e( 'Source Name', 'wpyog-news' ); ?></label></th>
					<td>
						<input
							type="text"
							id="wpyog_source_name"
							name="wpyog_source_name"
							value="<?php echo esc_attr( $name ); ?>"
							placeholder="<?php esc_attr_e( 'e.g. Reuters', 'wpyog-news' ); ?>"
							class="widefat"
						/>
					</td>
				</tr>
				<tr>
					<th><label for="wpyog_source_url"><?php esc_html_e( 'Source URL', 'wpyog-news' ); ?></label></th>
					<td>
						<input
							type="url"
							id="wpyog_source_url"
							name="wpyog_source_url"
							value="<?php echo esc_url( $url ); ?>"
							placeholder="https://reuters.com"
							class="widefat"
						/>
					</td>
				</tr>
				<tr>
					<th><label for="wpyog_source_favicon"><?php esc_html_e( 'Favicon Icon URL', 'wpyog-news' ); ?></label></th>
					<td>
						<div class="wpyog-favicon-wrap">
							<input
								type="url"
								id="wpyog_source_favicon"
								name="wpyog_source_favicon"
								value="<?php echo esc_url( $favicon ); ?>"
								placeholder="https://reuters.com/favicon.ico"
								class="widefat"
							/>
							<button type="button" class="button wpyog-auto-favicon" data-target="wpyog_source_favicon" data-source="wpyog_source_url">
								<?php esc_html_e( 'Auto-Fetch from URL', 'wpyog-news' ); ?>
							</button>
						</div>
						<?php if ( $favicon ) : ?>
							<img src="<?php echo esc_url( $favicon ); ?>" alt="" style="width:16px;height:16px;margin-top:6px;vertical-align:middle;" />
						<?php endif; ?>
						<p class="description"><?php esc_html_e( 'Small icon (16×16 or 32×32) displayed next to the source name.', 'wpyog-news' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Save metabox data securely.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_metaboxes( $post_id, $post ) {

		// Bail on autosave / revisions / wrong post type.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( WPYOG_NEWS_POST_TYPE !== $post->post_type ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// --- External Link ---
		if ( isset( $_POST['wpyog_external_link_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpyog_external_link_nonce'] ) ), 'wpyog_external_link_nonce' ) ) {

			$ext_url = isset( $_POST['wpyog_external_url'] ) ? esc_url_raw( wp_unslash( $_POST['wpyog_external_url'] ) ) : '';
			update_post_meta( $post_id, '_wpyog_external_url', $ext_url );
		}

		// --- Source Details ---
		if ( isset( $_POST['wpyog_source_details_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpyog_source_details_nonce'] ) ), 'wpyog_source_details_nonce' ) ) {

			$source_name    = isset( $_POST['wpyog_source_name'] )    ? sanitize_text_field( wp_unslash( $_POST['wpyog_source_name'] ) )    : '';
			$source_url     = isset( $_POST['wpyog_source_url'] )     ? esc_url_raw( wp_unslash( $_POST['wpyog_source_url'] ) )             : '';
			$source_favicon = isset( $_POST['wpyog_source_favicon'] ) ? esc_url_raw( wp_unslash( $_POST['wpyog_source_favicon'] ) )         : '';

			update_post_meta( $post_id, '_wpyog_source_name',    $source_name );
			update_post_meta( $post_id, '_wpyog_source_url',     $source_url );
			update_post_meta( $post_id, '_wpyog_source_favicon', $source_favicon );
		}
	}
}
