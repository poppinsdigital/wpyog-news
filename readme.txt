=== WPYog News ===
Contributors: wpyog
Tags: news, news plugin, news widget, news list, news grid
Requires at least: 5.0
Tested up to: 7.0
Stable tag: 1.1.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create modern news sections, announcements, and article showcases with List and Card layouts, category filters, and source attribution.

== Description ==

WPYog News is a modern WordPress news and announcement plugin designed to help businesses, schools, organizations, startups, agencies, and publishers create clean and professional news sections directly inside WordPress.

Built from real-world business workflows, the plugin provides a lightweight and flexible way to manage company updates, announcements, featured articles, press releases, and curated external news without relying on bloated magazine themes or complicated page-builder setups.

Whether you are publishing company announcements, school notices, cybersecurity updates, healthcare news, product launches, industry articles, curated external news, or digital transformation insights — WPYog News helps you create organized and engaging news sections that are simple to manage and beautiful to browse.

= Live Demo =

[View Live Demo](https://demo.poppinsdigital.com/wpyog-news/)

= Key Features =

* Modern List and Card layouts
* Responsive news displays
* Shortcode generator
* Category and tag support
* Featured images
* Source attribution support
* External article linking
* Pagination support
* Mobile responsive layouts
* Lightweight and fast
* Easy content management
* Elementor-friendly

= Modern News Layouts =

WPYog News includes two responsive display layouts.

**List Layout** — Perfect for company updates, announcements, editorial/news websites, blogs, and industry news feeds.

**Card Layout** — Ideal for featured news sections, homepage highlights, magazine-style displays, and modern content showcases.

= Smart Shortcode Generator =

Generate custom shortcodes directly from the admin panel without coding. Control layout type, categories, pagination, excerpt length, sorting order, date visibility, source visibility, and column layouts.

`[wpyog_news layout="card" columns="3" limit="9"]`

= External News & Source Attribution =

WPYog News supports curated external news workflows with external article URLs, source name display, source website links, favicon/logo support, and proper source attribution. Perfect for curated industry news, cybersecurity updates, compliance news, digital transformation articles, and technology feeds.

= All Shortcode Attributes =

| Attribute        | Values                   | Default   |
|------------------|--------------------------|-----------|
| layout           | list \| card             | list      |
| limit            | any integer              | 10        |
| category         | category ID(s), comma-sep| (all)     |
| show_date        | true \| false            | true      |
| show_excerpt     | true \| false            | true      |
| excerpt_length   | integer (words)          | 20        |
| show_source      | true \| false            | true      |
| order            | DESC \| ASC              | DESC      |
| orderby          | date \| title \| rand    | date      |
| columns          | 2 \| 3 \| 4              | 3         |
| pagination       | true \| false            | true      |
| pagination_type  | numeric \| prev-next     | numeric   |
| extra_class      | CSS class string         | (none)    |

= Use Cases =

* **Company News & Announcements** — business updates, product launches, press releases
* **School & Educational Notices** — school updates, event notices, circulars
* **Cybersecurity & Technology News** — curated industry news feeds with external article support
* **Healthcare & Medical Updates** — healthcare notices, awareness articles, medical industry updates
* **Corporate Blogs & Editorial Sections** — lightweight editorial sections without magazine theme complexity
* **Curated Industry News Portals** — aggregate external news while properly crediting sources

= More Plugins from WPYog =

* [WPYog Team](https://wordpress.org/plugins/wpyog-team/) — Team showcase plugin
* [WPYog Docs](https://wordpress.org/plugins/wpyog-documents/) — Document management plugin

Built by [Poppins Digital](https://poppinsdigital.com/).

== Installation ==

1. Upload the `wpyog-news` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **WPYog News → Add News** to add your first news item.
4. Use **WPYog News → Shortcode Generator** to build and copy your shortcode.
5. Paste the shortcode into any page or post.

== Frequently Asked Questions ==

= Does WPYog News support multiple layouts? =
Yes. The plugin supports a List layout and a Card layout.

= Can I display external news articles? =
Yes. WPYog News supports external article URLs and source attribution.

= Can I display source names and favicons? =
Yes. You can display the source name, source URL, and source favicon/logo for curated news articles.

= Does the plugin include a shortcode generator? =
Yes. WPYog News includes a built-in shortcode generator for easy layout customization without coding.

= Can I filter news by category? =
Yes. News posts can be grouped and filtered using categories. Use the `category` attribute with the category ID: `[wpyog_news category="5"]`. Category IDs are shown in WPYog News → Categories.

= Is the plugin mobile responsive? =
Yes. All layouts are optimized for desktop, tablet, and mobile devices.

= Does WPYog News work with Elementor? =
Yes. WPYog News works with Elementor and most modern WordPress themes. A native Gutenberg block is also included.

= Is coding knowledge required? =
No. The plugin is designed for easy management directly from the WordPress dashboard.

= How do external links work? =
When editing a news item, add a URL in the External Link field. The news title and Read More button will then link to that external URL (opens in a new tab) instead of the internal news page.

= Does the card layout support pagination? =
The card layout uses a Load More button via AJAX instead of traditional pagination.

== Screenshots ==

1. Front-end list layout with date, excerpt, source credit, and Read More button.
2. Front-end card layout with Load More button.
3. News item editor — External Link and Source Details metaboxes.
4. Shortcode Generator admin page.
5. Getting Started admin page.

== Changelog ==

= 1.1.1 =
* Fixed: Elementor Pro Theme Builder template now correctly overrides single News post templates. Root cause: our `single_template` filter returned a plugin-path template that Elementor Pro's `template_include` hook (priority 12) did not recognise, silently skipping the canvas. Fix: when Elementor Pro is active the plugin's `single_template` filter is removed so Elementor can take over cleanly via `template_include`.

= 1.1.0 =
* Added: `option_elementor_cpt_support` filter — dynamically adds `wpyog_news` to Elementor Pro's supported post-type list so Theme Builder template overrides work without requiring manual configuration in Elementor → Settings → Integrations.
* Fixed: Post type singular label corrected from "News Item" to "WPYog News" — fixes the section header displayed in Elementor Theme Builder's condition picker.

= 1.0.9 =
* Fixed: Elementor integration timing bug. Constructor now uses `did_action('elementor/init')` to detect whether Elementor has already initialised and registers hooks immediately if so, preventing filters from being silently missed when Elementor loads before this plugin.

= 1.0.8 =
* Fixed: Critical PHP error introduced in 1.0.7 caused by an unsafe `\ElementorPro\Modules\ThemeBuilder\Module::instance()` call inside the `single_template` filter. PHP threw an `\Error` (not `\Exception`) which the try/catch block did not catch. Removed the unsafe method entirely; the `template_include` override path is handled correctly without it.

= 1.0.7 =
* Added: Elementor integration class (`includes/class-wpyog-elementor.php`) — registers the News CPT and News Categories taxonomy with Elementor's internal helpers.
* Added: Full Elementor Theme Builder support — News posts and News Category archives now appear as selectable conditions in Theme Builder (WPYog News, In Tag, In Category, In child News Categories, News by author).
* Updated: Post type registration now explicitly sets `show_in_nav_menus` and `show_in_admin_bar` for correct page-builder detection.
* Updated: News Categories taxonomy (`wpyog_news_cat`) added to the post type's `taxonomies` list for proper Elementor taxonomy condition support.

= 1.0.6 =
* Fixed: `.wpyog-single-wrap` max-width set to 900px — content column no longer stretches edge-to-edge on wide screens.
* Fixed: Single news featured image changed to max-width: 100% / object-fit: contain / margin: 0 auto — displays at natural proportions, centered in the container.

= 1.0.5 =
* Updated CSS: .wpyog-card-cats a padding updated to 10px.
* Updated CSS: .wpyog-attr-table thead th color now uses !important to prevent theme override.

= 1.0.4 =
* Fixed: Contributor username corrected to wpyog (WordPress.org account).
* Fixed: Removed $_GET['revision'] from updated_messages() — replaced with a static string to eliminate nonce requirement.
* Fixed: Pagination output now wrapped in wp_kses_post() for proper escaping.

= 1.0.3 =
* Fixed: Added missing translators comments to all i18n strings with placeholders.
* Fixed: Replaced deprecated rand() with wp_rand().
* Fixed: Replaced deprecated current_time( 'timestamp' ) with time().
* Fixed: Added wp_unslash() to all $_GET/$_POST reads missing it.
* Fixed: Removed unnecessary load_plugin_textdomain() call (auto-loaded since WP 4.6).
* Updated: Tested up to WordPress 6.9.
* Added: languages/wpyog-news.pot translation template.

= 1.0.2 =
* Fixed: Previous/Next navigation on single news pages now works even when posts have no category assigned (was using $in_same_term which returned null for uncategorised posts).
* Fixed: Navigation filtered to wpyog_news post type only — won't accidentally link to regular blog posts.
* Added: Empty nav placeholder keeps two-column layout balanced when only one adjacent post exists.
* Updated CSS: .wpyog-item-title font-size 1.4rem, font-weight bold.
* Updated CSS: .wpyog-pagination page numbers now have background #fff.

= 1.0.1 =
* Fixed: Admin sidebar icon now uses WPYog custom font via SVG data URI.
* Fixed: Load More AJAX — empty category bug and jQuery data() attribute reading.
* Fixed: Post slug changed from "news" to "latest-news" to avoid conflicts with other plugins.
* Fixed: Category taxonomy slug updated to "news-category".
* Added: Previous/Next navigation on single news detail pages.
* Improved: Modern card and list CSS — hover effects, image zoom, category pills, indigo Read More button.
* Changed: News title uses h2 instead of h3 for better semantic hierarchy.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.1.1 =
Adds full Elementor Theme Builder support — News posts now appear as conditions in Theme Builder and templates override correctly. Fixes a critical PHP error introduced in 1.0.7 and resolves the template override issue for Elementor Pro users. Recommended update for all users.

= 1.0.0 =
Initial release of WPYog News.
