/**
 * WPYog News — Admin JS
 * Powers the shortcode generator and metabox helpers.
 *
 * @package WPYog_News
 */
(function ($) {
	'use strict';

	/* ============================================================
	   Shortcode Generator
	   ============================================================ */
	function buildShortcode() {

		var layout         = $('#wg-layout').val()          || 'list';
		var limit          = $('#wg-limit').val()            || '10';
		var category       = $('#wg-category').val()         || '';
		var order          = $('#wg-order').val()            || 'DESC';
		var paginationType = $('#wg-pagination-type').val()  || 'numeric';
		var excerptLength  = $('#wg-excerpt-length').val()   || '20';
		var columns        = $('#wg-columns').val()          || '3';
		var showDate       = $('#wg-show-date').is(':checked')    ? 'true' : 'false';
		var showExcerpt    = $('#wg-show-excerpt').is(':checked')  ? 'true' : 'false';
		var showSource     = $('#wg-show-source').is(':checked')   ? 'true' : 'false';

		var sc = '[wpyog_news';
		sc += ' layout="'          + layout        + '"';
		sc += ' limit="'           + limit         + '"';
		if (category) {
			sc += ' category="'    + category      + '"';
		}
		sc += ' show_date="'       + showDate      + '"';
		sc += ' show_excerpt="'    + showExcerpt   + '"';
		sc += ' excerpt_length="'  + excerptLength + '"';
		sc += ' show_source="'     + showSource    + '"';
		sc += ' order="'           + order         + '"';

		if (layout === 'card') {
			sc += ' columns="'     + columns       + '"';
		}

		if (layout === 'list') {
			sc += ' pagination_type="' + paginationType + '"';
		}

		sc += ']';

		$('#wpyog-generated-code').text(sc);
	}

	// Show / hide card-only / list-only fields based on layout.
	function toggleLayoutFields() {
		var layout = $('#wg-layout').val();
		if (layout === 'card') {
			$('.wpyog-card-only').show();
			$('.wpyog-list-only').hide();
		} else {
			$('.wpyog-card-only').hide();
			$('.wpyog-list-only').show();
		}
	}

	$(document).ready(function () {

		// Init
		if ($('#wpyog-generated-code').length) {
			toggleLayoutFields();
			buildShortcode();

			// Rebuild on any change
			$(document).on('change input', '#wg-layout, #wg-limit, #wg-category, #wg-order, #wg-pagination-type, #wg-excerpt-length, #wg-columns, #wg-show-date, #wg-show-excerpt, #wg-show-source', function () {
				toggleLayoutFields();
				buildShortcode();
			});
		}

		// Copy shortcode to clipboard
		$('#wpyog-copy-code').on('click', function () {
			var code = $('#wpyog-generated-code').text();
			if (navigator.clipboard) {
				navigator.clipboard.writeText(code).then(function () {
					var $btn = $('#wpyog-copy-code');
					var original = $btn.text();
					$btn.text('Copied!');
					setTimeout(function () { $btn.text(original); }, 2000);
				});
			} else {
				// Fallback for older browsers
				var $temp = $('<textarea>').val(code).appendTo('body').select();
				document.execCommand('copy');
				$temp.remove();
				var $btn = $('#wpyog-copy-code');
				var original = $btn.text();
				$btn.text('Copied!');
				setTimeout(function () { $btn.text(original); }, 2000);
			}
		});

		/* ============================================================
		   Metabox: Auto-fetch favicon from source URL
		   ============================================================ */
		$(document).on('click', '.wpyog-auto-favicon', function () {
			var $btn        = $(this);
			var targetId    = $btn.data('target');
			var sourceId    = $btn.data('source');
			var sourceUrl   = $('#' + sourceId).val().trim();

			if (!sourceUrl) {
				alert('Please enter a Source URL first.');
				return;
			}

			try {
				var parsed = new URL(sourceUrl);
				var favicon = 'https://www.google.com/s2/favicons?sz=64&domain_url=' + encodeURIComponent(parsed.origin);
				$('#' + targetId).val(favicon);
			} catch (e) {
				alert('Please enter a valid URL (including https://).');
			}
		});

	});

}(jQuery));
