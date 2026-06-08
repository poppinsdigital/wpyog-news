/**
 * WPYog News — Public JS
 * Handles Load More for card layout.
 *
 * Data attributes use standard HTML5 hyphens; jQuery converts to camelCase
 * automatically: data-show-date → .data('showDate')
 *
 * @package WPYog_News
 */
(function ($) {
	'use strict';

	$(document).ready(function () {

		/* ============================================================
		   Load More — card layout
		   ============================================================ */
		$(document).on('click', '.wpyog-load-more-btn', function () {

			var $btn      = $(this);
			var container = $btn.data('container'); // e.g. "#wpyog-news-1 .wpyog-news-card-container"
			var $container = $(container);

			if ( ! $container.length ) {
				console.warn('WPYog News: Load More container not found:', container);
				return;
			}

			var currentPage = parseInt( $btn.data('page'), 10 ) || 1;
			var maxPages    = parseInt( $btn.data('max'),  10 ) || 1;

			// Already at last page?
			if ( currentPage >= maxPages ) {
				$btn.text( wpyogNewsData.noMoreText ).prop('disabled', true);
				return;
			}

			// Disable and show loading state.
			$btn.prop('disabled', true).text( wpyogNewsData.loadingText );

			$.ajax({
				url:    wpyogNewsData.ajaxUrl,
				method: 'POST',
				data: {
					action:         'wpyog_load_more',
					nonce:          wpyogNewsData.nonce,
					page:           currentPage,
					limit:          $btn.data('limit'),
					category:       $btn.data('category')      || '',
					show_date:      $btn.data('showDate')      || 'false',
					show_excerpt:   $btn.data('showExcerpt')   || 'false',
					excerpt_length: $btn.data('excerptLength') || 20,
					show_source:    $btn.data('showSource')    || 'false',
					order:          $btn.data('order')         || 'DESC',
					orderby:        $btn.data('orderby')       || 'date',
				},
				success: function (response) {
					if ( response.success && response.data.html ) {

						// Append items with a fade-in effect.
						var $newItems = $( response.data.html );
						$newItems.css('opacity', 0);
						$container.append( $newItems );
						$newItems.animate({ opacity: 1 }, 300);

						// Advance page counter.
						var newPage = currentPage + 1;
						$btn.data('page', newPage);

						if ( response.data.has_more ) {
							$btn.prop('disabled', false).text( wpyogNewsData.loadMoreText );
						} else {
							$btn.text( wpyogNewsData.noMoreText ).prop('disabled', true);
						}

					} else {
						// No more posts or error.
						$btn.text( wpyogNewsData.noMoreText ).prop('disabled', true);
					}
				},
				error: function () {
					// Re-enable on network error.
					$btn.prop('disabled', false).text( wpyogNewsData.loadMoreText );
				}
			});
		});

	});

}(jQuery));
