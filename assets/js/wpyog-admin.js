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

		// Post types to mix into this layout.
		var postTypes = [];
		$('.wg-post-type:checked').each(function () {
			postTypes.push($(this).val());
		});
		var isDefaultPostType = (postTypes.length === 1 && postTypes[0] === 'wpyog_news');
		var showType = $('#wg-show-type').is(':checked') ? 'true' : 'false';
		var ids = $.trim($('#wg-ids').val() || '');
		var collection = $('#wg-collection').val() || '';

		// Carousel-only fields.
		var slidesToShow  = $('#wg-slides-to-show').val()    || '3';
		var transition    = $('#wg-transition').val()        || 'slide';
		var autoplaySpeed = $('#wg-autoplay-speed').val()    || '4000';
		var gap           = $('#wg-gap').val()               || '20';
		var autoplay      = $('#wg-autoplay').is(':checked')       ? 'true' : 'false';
		var pauseOnHover  = $('#wg-pause-on-hover').is(':checked')  ? 'true' : 'false';
		var infinite      = $('#wg-infinite').is(':checked')        ? 'true' : 'false';
		var arrows        = $('#wg-arrows').is(':checked')          ? 'true' : 'false';
		var arrowsOnHover = $('#wg-arrows-on-hover').is(':checked') ? 'true' : 'false';
		var dots          = $('#wg-dots').is(':checked')            ? 'true' : 'false';

		var sc = '[wpyog_news';
		sc += ' layout="'          + layout        + '"';
		if (!ids) {
			sc += ' limit="'       + limit         + '"';
		}
		if (category && !ids) {
			sc += ' category="'    + category      + '"';
		}
		sc += ' show_date="'       + showDate      + '"';
		sc += ' show_excerpt="'    + showExcerpt   + '"';
		sc += ' excerpt_length="'  + excerptLength + '"';
		sc += ' show_source="'     + showSource    + '"';
		sc += ' order="'           + order         + '"';

		if (!isDefaultPostType && postTypes.length) {
			sc += ' post_type="' + postTypes.join(',') + '"';
		}
		if (!isDefaultPostType && showType === 'true') {
			sc += ' show_type="true"';
		}
		if (ids) {
			sc += ' ids="' + ids + '"';
		} else if (collection) {
			sc += ' collection="' + collection + '"';
		}

		if (layout === 'card') {
			sc += ' columns="'     + columns       + '"';
		}

		if (layout === 'list') {
			sc += ' pagination_type="' + paginationType + '"';
		}

		if (layout === 'carousel') {
			sc += ' slides_to_show="' + slidesToShow  + '"';
			sc += ' autoplay="'       + autoplay      + '"';
			sc += ' autoplay_speed="' + autoplaySpeed + '"';
			sc += ' infinite="'       + infinite      + '"';
			sc += ' arrows="'         + arrows        + '"';
			sc += ' arrows_on_hover="' + arrowsOnHover + '"';
			sc += ' dots="'           + dots          + '"';
			sc += ' pause_on_hover="' + pauseOnHover  + '"';
			sc += ' transition="'     + transition    + '"';
			sc += ' gap="'            + gap           + '"';
		}

		sc += ']';

		$('#wpyog-generated-code').text(sc);
	}

	// Show the "Show Post Type Badge" toggle only when more than one post type is selected.
	function togglePostTypeFields() {
		var checkedCount = $('.wg-post-type:checked').length;
		$('.wpyog-post-types-multi-only').toggle(checkedCount > 1);
	}

	// Show / hide card-only / list-only / carousel-only fields based on layout.
	function toggleLayoutFields() {
		var layout = $('#wg-layout').val();

		$('.wpyog-card-only, .wpyog-list-only, .wpyog-carousel-only').hide();

		if (layout === 'card') {
			$('.wpyog-card-only').show();
		} else if (layout === 'carousel') {
			$('.wpyog-carousel-only').show();
		} else {
			$('.wpyog-list-only').show();
		}
	}

	/* ============================================================
	   Ticker Shortcode Generator
	   ============================================================ */
	function buildTickerShortcode() {
		var animation  = $('#wt-animation').val()            || 'scroll';
		var speed      = $('#wt-speed').val()                || 'medium';
		var limit      = $('#wt-limit').val()                || '10';
		var category   = $('#wt-category').val()             || '';
		var order      = $('#wt-order').val()                || 'DESC';
		var showLabel  = $('#wt-show-label').is(':checked');
		var label      = $('#wt-label').val()                || '';
		var labelBg    = $('#wt-label-bg').val()             || '#e74c3c';
		var labelColor = $('#wt-label-color').val()          || '#ffffff';
		var showDate   = $('#wt-show-date').is(':checked')   ? 'true' : 'false';
		var showCount  = $('#wt-show-count').is(':checked')  ? 'true' : 'false';
		var pause      = $('#wt-pause').is(':checked')       ? 'true' : 'false';
		var direction  = $('#wt-direction').val()            || 'left';
		var separator  = $('#wt-separator').val()            || '•';

		var sc = '[wpyog_ticker';
		sc += ' animation="' + animation + '"';
		sc += ' speed="'     + speed     + '"';
		sc += ' limit="'     + limit     + '"';
		if (category) { sc += ' category="' + category + '"'; }
		sc += ' order="'     + order     + '"';
		sc += ' show_label="' + (showLabel ? 'true' : 'false') + '"';
		if (showLabel && label) { sc += ' label="' + label + '"'; }
		if (showLabel) {
			sc += ' label_bg="'    + labelBg    + '"';
			sc += ' label_color="' + labelColor + '"';
		}
		sc += ' show_date="'  + showDate  + '"';
		if (animation !== 'scroll') {
			sc += ' show_count="' + showCount + '"';
		}
		sc += ' pause_on_hover="' + pause + '"';
		if (animation === 'scroll') {
			if (direction !== 'left') { sc += ' direction="' + direction + '"'; }
			if (separator !== '•')   { sc += ' separator="' + separator + '"'; }
		}
		sc += ']';

		$('#wpyog-ticker-generated-code').text(sc);
	}

	function toggleTickerFields() {
		var animation = $('#wt-animation').val();
		if (animation === 'scroll') {
			$('.wpyog-ticker-scroll-only').show();
			$('.wpyog-ticker-cycle-only').hide();
		} else {
			$('.wpyog-ticker-scroll-only').hide();
			$('.wpyog-ticker-cycle-only').show();
		}
	}

	function toggleLabelFields() {
		var show = $('#wt-show-label').is(':checked');
		$('#wt-label-text-row, #wt-label-colors-row').toggle(show);
	}

	function copyToClipboard(text, $btn) {
		if (navigator.clipboard) {
			navigator.clipboard.writeText(text).then(function () {
				var original = $btn.text();
				$btn.text('Copied!');
				setTimeout(function () { $btn.text(original); }, 2000);
			});
		} else {
			var $temp = $('<textarea>').val(text).appendTo('body').select();
			document.execCommand('copy');
			$temp.remove();
			var original = $btn.text();
			$btn.text('Copied!');
			setTimeout(function () { $btn.text(original); }, 2000);
		}
	}

	$(document).ready(function () {

		/* ── Tab switcher ─────────────────────────────────── */
		$(document).on('click', '.wpyog-gen-tab', function () {
			var tab = $(this).data('tab');
			$('.wpyog-gen-tab').removeClass('wpyog-gen-tab-active');
			$(this).addClass('wpyog-gen-tab-active');
			$('.wpyog-gen-panel').hide();
			$('#wpyog-panel-' + tab).show();
		});

		// Init
		if ($('#wpyog-generated-code').length) {
			toggleLayoutFields();
			togglePostTypeFields();
			buildShortcode();

			// Rebuild on any change
			$(document).on('change input', '#wg-layout, #wg-limit, #wg-category, #wg-order, #wg-pagination-type, #wg-excerpt-length, #wg-columns, #wg-show-date, #wg-show-excerpt, #wg-show-source, #wg-slides-to-show, #wg-transition, #wg-autoplay-speed, #wg-gap, #wg-autoplay, #wg-pause-on-hover, #wg-infinite, #wg-arrows, #wg-arrows-on-hover, #wg-dots, .wg-post-type, #wg-show-type, #wg-ids, #wg-collection', function () {
				toggleLayoutFields();
				togglePostTypeFields();
				buildShortcode();
			});
		}

		// Ticker: init
		if ($('#wpyog-ticker-generated-code').length) {
			toggleTickerFields();
			toggleLabelFields();
			buildTickerShortcode();

			$(document).on('change input', '#wt-animation, #wt-speed, #wt-limit, #wt-category, #wt-order, #wt-show-label, #wt-label, #wt-label-bg, #wt-label-color, #wt-show-date, #wt-show-count, #wt-pause, #wt-direction, #wt-separator', function () {
				toggleTickerFields();
				toggleLabelFields();
				buildTickerShortcode();
			});
		}

		// Copy news shortcode
		$('#wpyog-copy-code').on('click', function () {
			copyToClipboard($('#wpyog-generated-code').text(), $(this));
		});

		// Copy ticker shortcode
		$('#wpyog-copy-ticker-code').on('click', function () {
			copyToClipboard($('#wpyog-ticker-generated-code').text(), $(this));
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
