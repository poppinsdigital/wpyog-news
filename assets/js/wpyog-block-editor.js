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

	var categories = (typeof wpyogBlockData !== 'undefined') ? wpyogBlockData.categories : [];

	blocks.registerBlockType('wpyog/news', {

		title:       'WPYog News',
		description: 'Display news items in a list or card layout.',
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
		},

		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();

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
							onChange: function (val) { setAttributes({ category: val }); },
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
