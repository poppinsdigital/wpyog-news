/**
 * WPYog News — Gutenberg Block Editor (ES5, no build step needed)
 *
 * Registers the wpyog/news block in the Gutenberg editor with
 * a live server-side preview via ServerSideRender.
 *
 * @package WPYog_News
 */
(function (blocks, element, blockEditor, components, serverSideRender) {
	'use strict';

	var el             = element.createElement;
	var ServerSideRender = serverSideRender;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps  = blockEditor.useBlockProps;
	var PanelBody      = components.PanelBody;
	var SelectControl  = components.SelectControl;
	var TextControl    = components.TextControl;
	var ToggleControl  = components.ToggleControl;
	var RangeControl   = components.RangeControl;
	var CheckboxControl = components.CheckboxControl;

	var categories  = (typeof wpyogBlockData !== 'undefined') ? wpyogBlockData.categories  : [];
	var postTypes   = (typeof wpyogBlockData !== 'undefined') ? wpyogBlockData.postTypes   : [];
	var collections = (typeof wpyogBlockData !== 'undefined') ? wpyogBlockData.collections : [];

	blocks.registerBlockType('wpyog/news', {

		title:       'WPYog News',
		description: 'Display news items in a list, card, or carousel layout.',
		category:    'widgets',
		icon:        'megaphone',
		keywords:    ['news', 'wpyog', 'articles'],

		attributes: {
			layout:          { type: 'string',  default: 'list' },
			limit:           { type: 'integer', default: 10 },
			category:        { type: 'string',  default: '' },
			show_date:       { type: 'boolean', default: true },
			show_excerpt:    { type: 'boolean', default: true },
			excerpt_length:  { type: 'integer', default: 20 },
			show_source:     { type: 'boolean', default: true },
			order:           { type: 'string',  default: 'DESC' },
			orderby:         { type: 'string',  default: 'date' },
			columns:         { type: 'integer', default: 3 },
			pagination:      { type: 'boolean', default: true },
			pagination_type: { type: 'string',  default: 'numeric' },
			extra_class:     { type: 'string',  default: '' },
			post_type:       { type: 'string',  default: '' },
			taxonomy:        { type: 'string',  default: '' },
			show_type:       { type: 'boolean', default: false },
			ids:             { type: 'string',  default: '' },
			collection:      { type: 'string',  default: '' },
			slides_to_show:  { type: 'integer', default: 3 },
			autoplay:        { type: 'boolean', default: true },
			autoplay_speed:  { type: 'integer', default: 4000 },
			infinite:        { type: 'boolean', default: true },
			arrows:          { type: 'boolean', default: true },
			arrows_on_hover: { type: 'boolean', default: false },
			dots:            { type: 'boolean', default: true },
			pause_on_hover:  { type: 'boolean', default: true },
			transition:      { type: 'string',  default: 'slide' },
			gap:             { type: 'integer', default: 20 },
		},

		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();

			// Post types currently included (comma string attribute -> array; '' means just WPYog News).
			var selectedPostTypes = attributes.post_type ? attributes.post_type.split(',') : ['wpyog_news'];

			function togglePostType(slug, checked) {
				var next = selectedPostTypes.slice();
				if (checked) {
					if (next.indexOf(slug) === -1) { next.push(slug); }
				} else {
					next = next.filter(function (s) { return s !== slug; });
				}
				if (next.length === 0) { next = ['wpyog_news']; } // always keep at least one selected.
				var value = (next.length === 1 && next[0] === 'wpyog_news') ? '' : next.join(',');
				setAttributes({ post_type: value });
			}

			return el(
				'div',
				blockProps,

				// Inspector sidebar controls
				el(InspectorControls, {},

					el(PanelBody, { title: 'Layout', initialOpen: true },
						el(SelectControl, {
							label:    'Layout',
							value:    attributes.layout,
							options:  [
								{ label: 'List (with pagination)', value: 'list' },
								{ label: 'Card (with Load More)',  value: 'card' },
								{ label: 'Carousel (slider)',      value: 'carousel' },
							],
							onChange: function (val) { setAttributes({ layout: val }); },
						}),
						attributes.layout === 'card' && el(RangeControl, {
							label:    'Columns',
							value:    attributes.columns,
							min:      2,
							max:      4,
							onChange: function (val) { setAttributes({ columns: val }); },
						})
					),

					attributes.layout === 'carousel' && el(PanelBody, { title: 'Carousel Settings', initialOpen: true },
						el(RangeControl, {
							label:    'Slides to Show',
							value:    attributes.slides_to_show,
							min:      1,
							max:      5,
							onChange: function (val) { setAttributes({ slides_to_show: val }); },
						}),
						el(SelectControl, {
							label:    'Transition',
							value:    attributes.transition,
							options:  [
								{ label: 'Slide', value: 'slide' },
								{ label: 'Fade',  value: 'fade'  },
							],
							onChange: function (val) { setAttributes({ transition: val }); },
						}),
						el(ToggleControl, {
							label:    'Autoplay',
							checked:  attributes.autoplay,
							onChange: function (val) { setAttributes({ autoplay: val }); },
						}),
						attributes.autoplay && el(RangeControl, {
							label:    'Autoplay Speed (ms)',
							value:    attributes.autoplay_speed,
							min:      1000,
							max:      15000,
							step:     500,
							onChange: function (val) { setAttributes({ autoplay_speed: val }); },
						}),
						el(ToggleControl, {
							label:    'Pause on Hover',
							checked:  attributes.pause_on_hover,
							onChange: function (val) { setAttributes({ pause_on_hover: val }); },
						}),
						el(ToggleControl, {
							label:    'Infinite Loop',
							checked:  attributes.infinite,
							onChange: function (val) { setAttributes({ infinite: val }); },
						}),
						el(ToggleControl, {
							label:    'Show Arrows',
							checked:  attributes.arrows,
							onChange: function (val) { setAttributes({ arrows: val }); },
						}),
						attributes.arrows && el(ToggleControl, {
							label:    'Arrows Only on Hover',
							checked:  attributes.arrows_on_hover,
							onChange: function (val) { setAttributes({ arrows_on_hover: val }); },
						}),
						el(ToggleControl, {
							label:    'Show Dots',
							checked:  attributes.dots,
							onChange: function (val) { setAttributes({ dots: val }); },
						}),
						el(RangeControl, {
							label:    'Gap Between Slides (px)',
							value:    attributes.gap,
							min:      0,
							max:      80,
							onChange: function (val) { setAttributes({ gap: val }); },
						})
					),

					el(PanelBody, { title: 'Mix Post Types', initialOpen: false },
						el('p', { style: { fontSize: '12px', color: '#757575', marginTop: 0 } },
							'Combine WPYog News with other content types (Posts, Products, etc.) in this layout. Only common fields — title, excerpt, featured image, date — are used across mixed items.'
						),
						postTypes.map(function (pt) {
							return el(CheckboxControl, {
								key:      pt.value,
								label:    pt.label,
								checked:  selectedPostTypes.indexOf(pt.value) !== -1,
								onChange: function (checked) { togglePostType(pt.value, checked); },
							});
						}),
						selectedPostTypes.length > 1 && el(ToggleControl, {
							label:    'Show Post Type Badge',
							help:     'Displays a small label (e.g. "News", "Post") on each item so mixed sources are easy to tell apart.',
							checked:  attributes.show_type,
							onChange: function (val) { setAttributes({ show_type: val }); },
						})
					),

					el(PanelBody, { title: 'Query', initialOpen: false },
						el(RangeControl, {
							label:    'Number of Posts',
							value:    attributes.limit,
							min:      1,
							max:      100,
							onChange: function (val) { setAttributes({ limit: val }); },
						}),
						el(SelectControl, {
							label:    'Category',
							value:    attributes.category,
							options:  categories,
							help:     selectedPostTypes.length > 1 ? 'Category filtering only applies to WPYog News items when mixing other post types.' : undefined,
							onChange: function (val) { setAttributes({ category: val }); },
						}),
						el(SelectControl, {
							label:    'Collection',
							value:    attributes.collection,
							options:  collections,
							help:     collections.length > 1 ? 'A repeatable curated group spanning any mix of post types — tick the box on any post\'s Collections panel to add it.' : 'No collections yet — open any post, click "+ Add New Collection" in its Collections panel, then reload this editor.',
							onChange: function (val) { setAttributes({ collection: val }); },
						}),
						el(SelectControl, {
							label:    'Order',
							value:    attributes.order,
							options:  [
								{ label: 'Newest First', value: 'DESC' },
								{ label: 'Oldest First', value: 'ASC'  },
							],
							onChange: function (val) { setAttributes({ order: val }); },
						}),
						el(SelectControl, {
							label:    'Order By',
							value:    attributes.orderby,
							options:  [
								{ label: 'Date',   value: 'date'  },
								{ label: 'Title',  value: 'title' },
								{ label: 'Random', value: 'rand'  },
							],
							onChange: function (val) { setAttributes({ orderby: val }); },
						})
					),

					el(PanelBody, { title: 'Display', initialOpen: false },
						el(ToggleControl, {
							label:    'Show Date',
							checked:  attributes.show_date,
							onChange: function (val) { setAttributes({ show_date: val }); },
						}),
						el(ToggleControl, {
							label:    'Show Excerpt',
							checked:  attributes.show_excerpt,
							onChange: function (val) { setAttributes({ show_excerpt: val }); },
						}),
						attributes.show_excerpt && el(RangeControl, {
							label:    'Excerpt Length (words)',
							value:    attributes.excerpt_length,
							min:      5,
							max:      100,
							onChange: function (val) { setAttributes({ excerpt_length: val }); },
						}),
						el(ToggleControl, {
							label:    'Show Source',
							checked:  attributes.show_source,
							onChange: function (val) { setAttributes({ show_source: val }); },
						})
					),

					el(PanelBody, { title: 'Pagination', initialOpen: false },
						attributes.layout === 'list' && el(ToggleControl, {
							label:    'Enable Pagination',
							checked:  attributes.pagination,
							onChange: function (val) { setAttributes({ pagination: val }); },
						}),
						attributes.layout === 'list' && attributes.pagination && el(SelectControl, {
							label:    'Pagination Type',
							value:    attributes.pagination_type,
							options:  [
								{ label: 'Numeric',   value: 'numeric'   },
								{ label: 'Prev/Next', value: 'prev-next' },
							],
							onChange: function (val) { setAttributes({ pagination_type: val }); },
						})
					),

					el(PanelBody, { title: 'Advanced', initialOpen: false },
						el(TextControl, {
							label:    'Extra CSS Class',
							value:    attributes.extra_class,
							onChange: function (val) { setAttributes({ extra_class: val }); },
						}),
						el(TextControl, {
							label:    'Specific Post IDs (optional)',
							help:     'Comma-separated post IDs to hand-pick exact posts across any mix of post types — no shared category needed. Overrides Number of Posts, Category, and pagination. Find an ID from the URL when editing a post (post.php?post=123).',
							value:    attributes.ids,
							placeholder: 'e.g. 12,45,78',
							onChange: function (val) { setAttributes({ ids: val }); },
						})
					)

				), // end InspectorControls

				// Block preview via server-side render
				el(ServerSideRender, {
					block:      'wpyog/news',
					attributes: attributes,
				})
			);
		},

		save: function () {
			// Server-side rendered — save returns null.
			return null;
		},
	});

}(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.serverSideRender
));
