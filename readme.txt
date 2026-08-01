=== WPYog News ===
Contributors: wpyog
Tags: news, news ticker, news widget, news list, news grid
Requires at least: 5.0
Tested up to: 7.0
Stable tag: 1.4.2
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create modern news sections, announcements, and article showcases with List, Card, Carousel, and Ticker displays — all without coding.

== Description ==

WPYog News is a modern WordPress news and announcement plugin designed to help businesses, schools, organizations, startups, agencies, and publishers create clean and professional news sections directly inside WordPress.

Built from real-world business workflows, the plugin provides a lightweight and flexible way to manage company updates, announcements, featured articles, press releases, and curated external news without relying on bloated magazine themes or complicated page-builder setups.

Whether you are publishing company announcements, school notices, cybersecurity updates, healthcare news, product launches, industry articles, curated external news, or digital transformation insights — WPYog News helps you create organized and engaging news sections that are simple to manage and beautiful to browse.

= Live Demo =

[View Live Demo](https://demo.poppinsdigital.com/wpyog-news/)

= Key Features =

* Modern List, Card, and Carousel layouts
* Mix multiple post types (News, Posts, Products, custom CPTs) into a single layout
* Repeatable curated Collections — tag posts from any post type, pull them all in with one attribute
* News Ticker with Scroll, Fade, and Flap (push-up) animations
* Built-in Shortcode Generator — no coding needed
* Category and tag support
* Featured images with hover zoom
* Source attribution and external article linking
* Pagination and Load More support
* Elementor Theme Builder support
* Native Gutenberg block
* Mobile responsive layouts
* Lightweight and fast

= Modern News Layouts =

**List Layout** — Perfect for company updates, announcements, editorial/news websites, blogs, and industry news feeds.

**Card Layout** — Ideal for featured news sections, homepage highlights, magazine-style displays, and modern content showcases.

**Carousel Layout** — A sliding row of news cards with autoplay, arrows, dot navigation, and a true infinite loop (the last slide slides out and the first slides back in from the right — no reverse jump). Great for homepage highlights, sidebars, and any spot where a full grid would take up too much room. Mix WPYog News with Posts, Products, or any custom post type, or pull in a curated Collection — all in the same sliding carousel.

`[wpyog_news layout="carousel" slides_to_show="3" autoplay="true" autoplay_speed="4000" infinite="true" arrows="true" dots="true" transition="slide"]`

`[wpyog_news layout="carousel" post_type="post,wpyog_document,wpyog_news" collection="homepage-picks" slides_to_show="3"]`

= News Ticker =

Display scrolling news headlines at the top of any page with the `[wpyog_ticker]` shortcode. Three animation styles are included:

* **Scroll** — Continuous horizontal ticker tape. Classic and eye-catching.
* **Fade** — Headlines cross-fade one at a time. Clean and subtle.
* **Flap** — Push-up slide animation — current headline slides out upward while the next slides in from below, like an airport departure board.

The ticker supports a custom label chip with configurable background colour and text colour, pause-on-hover, a post counter badge (e.g. 3 / 10), date display, category filtering, and full accessibility support including `prefers-reduced-motion`.

`[wpyog_ticker animation="scroll" label="Breaking News" speed="medium" limit="10"]`

`[wpyog_ticker animation="flap" show_count="true" show_date="true" label="Top News" label_bg="#1a1a2e"]`

= Smart Shortcode Generator =

Generate custom shortcodes directly from the admin panel without coding. The Shortcode Generator has two tabs — one for News List/Card and one for the News Ticker — with a live preview that updates as you change settings.

`[wpyog_news layout="card" columns="3" limit="9"]`

= External News & Source Attribution =

WPYog News supports curated external news workflows with external article URLs, source name display, source website links, favicon/logo support, and proper source attribution. Perfect for curated industry news, cybersecurity updates, compliance news, digital transformation articles, and technology feeds.

= [wpyog_news] — Shortcode Attributes =

**`layout`** — Display style. Options: `list` or `card`. Default: `list`

**`limit`** — Number of news items to display. Default: `10`

**`category`** — Filter by category ID. Use comma-separated IDs for multiple categories. Default: all categories

**`show_date`** — Show or hide the publication date. Options: `true` or `false`. Default: `true`

**`show_excerpt`** — Show or hide the excerpt. Options: `true` or `false`. Default: `true`

**`excerpt_length`** — Excerpt length in words. Default: `20`

**`show_source`** — Show or hide source attribution. Options: `true` or `false`. Default: `true`

**`order`** — Sort direction. Options: `DESC` (newest first) or `ASC` (oldest first). Default: `DESC`

**`orderby`** — Sort field. Options: `date`, `title`, or `rand`. Default: `date`

**`columns`** — Grid columns (card layout only). Options: `2`, `3`, or `4`. Default: `3`

**`pagination_type`** — Pagination style (list layout only). Options: `numeric` or `prev-next`. Default: `numeric`

**`extra_class`** — Add a custom CSS class to the wrapper. Default: none

**`post_type`** — Mix WPYog News with other post types in the same layout. Comma-separated CPT slugs, e.g. `post_type="wpyog_news,post,product"`. Only shared fields (title, excerpt, featured image, date) are used for mixed items — anything WPYog-News-specific (External URL, Source Details) is simply skipped for items that don't have it. Default: `wpyog_news`

**`taxonomy`** — Taxonomy the `category` attribute filters against. Default: `wpyog_news_cat`. Only change this if the other post types you're mixing in share a different taxonomy (e.g. `category`) — WPYog News's own category taxonomy only applies to `wpyog_news` items.

**`show_type`** — Show a small badge with each item's post type label (e.g. "News", "Post") — handy for telling sources apart when mixing post types. Options: `true` or `false`. Default: `false`

**`ids`** — Hand-pick exact posts to display, across any mix of post types — no shared category needed. Comma-separated post IDs, e.g. `ids="12,45,78"`. Overrides `limit`, `category`, and pagination; posts appear in the order listed unless `orderby` is set to something else. Find a post's ID from the URL when editing it (`post.php?post=123`).

**`collection`** — Show a repeatable curated group of posts spanning any mix of post types, without editing the shortcode as the group grows. Tick the checkbox for a "Collection" on any post from its normal edit screen (or click "+ Add New Collection" to create one — no shortcode needed), then pull them all in with `collection="homepage-picks"` (comma-separated slugs for more than one). Combines with `category` (both must match) but is ignored when `ids` is set.

= [wpyog_ticker] — Shortcode Attributes =

**`animation`** — Animation style. Options: `scroll`, `fade`, or `flap`. Default: `scroll`

**`speed`** — Animation speed. Options: `slow`, `medium`, `fast`, or any integer (px/s). Default: `medium`

**`limit`** — Number of news items to show. Default: `10`

**`category`** — Filter by category ID, comma-separated. Default: all categories

**`order`** — Sort direction: `DESC` or `ASC`. Default: `DESC`

**`orderby`** — Sort field: `date`, `title`, or `rand`. Default: `date`

**`show_label`** — Show or hide the label chip. Options: `true` or `false`. Default: `true`

**`label`** — Label text. Default: `Breaking News`

**`label_bg`** — Label background colour (hex). Default: `#e74c3c`

**`label_color`** — Label text colour (hex). Default: `#ffffff`

**`show_date`** — Show publication date next to each headline. Options: `true` or `false`. Default: `true`

**`show_count`** — Show a post counter (e.g. 2 / 10). Options: `true` or `false`. Applies to `fade` and `flap` only. Default: `false`

**`pause_on_hover`** — Pause ticker on mouse hover. Options: `true` or `false`. Default: `true`

**`direction`** — Scroll direction (`scroll` mode only). Options: `left` or `right`. Default: `left`

**`separator`** — Character between items (`scroll` mode only). Default: `•`

**`extra_class`** — Add a custom CSS class to the wrapper. Default: none

Also registered as `[wpyog_news_ticker]` — both shortcode names work identically.

= Use Cases =

* **Company News & Announcements** — business updates, product launches, press releases
* **School & Educational Notices** — school updates, event notices, circulars
* **Cybersecurity & Technology News** — curated industry news feeds with external article support
* **Healthcare & Medical Updates** — healthcare notices, awareness articles, medical industry updates
* **Corporate Blogs & Editorial Sections** — lightweight editorial sections without magazine theme complexity
* **Curated Industry News Portals** — aggregate external news while properly crediting sources
* **Breaking News Tickers** — add a scrolling or animated news bar to any page or header

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
Yes. The plugin includes a List layout, a Card layout, and a News Ticker.

= What is the News Ticker and how do I add it? =
The News Ticker displays animated news headlines in a bar that you can place anywhere on your site using the `[wpyog_ticker]` shortcode. Three animation styles are available: scroll (continuous horizontal), fade (cross-fade), and flap (push-up slide). Use **WPYog News → Shortcode Generator → News Ticker** tab to configure it.

= Can I display external news articles? =
Yes. WPYog News supports external article URLs and source attribution.

= Can I display source names and favicons? =
Yes. You can display the source name, source URL, and source favicon/logo for curated news articles.

= Does the plugin include a shortcode generator? =
Yes. WPYog News includes a built-in shortcode generator with two tabs — one for News List/Card and one for the News Ticker — with a live preview.

= Can I filter news by category? =
Yes. News posts can be grouped and filtered using categories. Use the `category` attribute with the category ID: `[wpyog_news category="5"]`. Category IDs are shown in WPYog News → Categories.

= Can I mix WPYog News with other post types (like Posts or WooCommerce Products) in one layout? =
Yes. Add the `post_type` attribute with a comma-separated list of post type slugs, e.g. `[wpyog_news post_type="wpyog_news,post,product" layout="carousel"]`. Only the fields shared across all post types — title, excerpt, featured image, and date — are shown. External URL and Source Details still display for `wpyog_news` items but are simply skipped for other post types. The Shortcode Generator and Gutenberg block both include a Post Types checklist for this.

= I want to mix content from different post types but only show a hand-picked selection, not everything in a category — how? =
Two ways, depending on whether the selection is a one-off or something you'll keep adding to:

For a one-off selection, use the `ids` attribute. It takes a comma-separated list of post IDs and works across any mix of post types since it doesn't rely on a shared taxonomy: `[wpyog_news post_type="wpyog_news,post,product" ids="12,45,78"]`. Posts show in the exact order you list the IDs. Find a post's ID from its edit-screen URL (`post.php?post=123`).

For a selection you'll keep adding to over time, use a Collection instead — it's repeatable, so you never have to edit the shortcode again. Open any post (News, a Blog Post, a Product, any post type) and in its Collections box, tick the checkbox for the collection you want (or click "+ Add New Collection" to create one, e.g. "Homepage Picks"). Tick the same collection on posts from other post types too, then show them all with `[wpyog_news post_type="wpyog_news,post,product" collection="homepage-picks"]`. New posts checked into the collection later appear automatically — no shortcode edits.

Both the Shortcode Generator and the Gutenberg block have fields for `ids` and `collection`.

= Is the plugin mobile responsive? =
Yes. All layouts are optimized for desktop, tablet, and mobile devices. The News Ticker automatically adjusts its label and font size on small screens.

= Does WPYog News work with Elementor? =
Yes. WPYog News includes full Elementor Theme Builder support — News posts appear as conditions in the Theme Builder so you can build custom single post templates. A native Gutenberg block is also included.

= Is coding knowledge required? =
No. The plugin is designed for easy management directly from the WordPress dashboard.

= How do external links work? =
When editing a news item, add a URL in the External Link field. The news title and Read More button will then link to that external URL (opens in a new tab) instead of the internal news page. External links also work in the News Ticker.

= Does the card layout support pagination? =
The card layout uses a Load More button via AJAX instead of traditional pagination.

= Can I show a counter in the News Ticker? =
Yes. Add `show_count="true"` to `[wpyog_ticker]` to display a "3 / 10" badge on the right side of the ticker. This works with `fade` and `flap` animation modes.

== Screenshots ==

1. Front-end list layout with date, excerpt, source credit, and Read More button.
2. Front-end card layout with Load More button.
3. News Ticker — scroll animation with label chip and counter badge.
4. News item editor — External Link and Source Details metaboxes.
5. Shortcode Generator — News List tab with all options.
6. Shortcode Generator — mix WPYog News with any post type (Posts, Pages, Products, custom CPTs), pick a Collection, and see every shortcode attribute at a glance.
7. Getting Started — the 4-step setup guide, including building a repeatable curated Collection.
8. Carousel layout mixing WPYog News with other post types — type badges, arrows, and dot navigation.
9. Card layout combining a Collection with mixed post types (Posts, Documents, and WPYog News) in one grid.

== Changelog ==

= 1.4.2 =
* Fixed: Carousel infinite loop now wraps correctly — after the last slide, the first slide slides in from the right and continues in the same direction, instead of reversing back to slide 1.

= 1.4.1 =
* Changed: Collections now use the same checkbox-list meta box as Categories (with a "+ Add New Collection" toggle to create one inline), instead of the type-to-search tag-style box. Tick a box on any post to add it to a collection — no searching for the existing collection name required.

= 1.4.0 =
* Added: Collections — a new `wpyog_collection` taxonomy shared across every public post type, used to build a repeatable curated group of posts spanning any mix of post types. Tag posts from their normal edit screen (like adding a tag) and pull them all into a layout with the new `collection` attribute — no editing the shortcode as the collection grows.
* Added: Collection dropdown in the Shortcode Generator (with a helpful empty state until the first collection is created) and a Collection control in the Gutenberg block's Query panel.
* Added: "Build a Repeatable Curated Collection" step and a Manage Collections link on the Getting Started admin page.
* Changed: Post type list logic for the checklist/dropdowns is now shared via one helper function, so the mixable and taggable post type lists always stay in sync.

= 1.3.1 =
* Added: `ids` attribute — hand-pick exact posts to display, across any mix of post types, without needing a shared category/taxonomy. Comma-separated post IDs, e.g. `ids="12,45,78"`; overrides `limit`, `category`, and pagination, and preserves the listed order. Available in the shortcode, the Shortcode Generator (Specific Post IDs field), and the Gutenberg block (Advanced panel).

= 1.3.0 =
* Added: Mix multiple post types into one `[wpyog_news]` layout via the new `post_type` attribute (comma-separated CPT slugs) — e.g. combine WPYog News with Posts, WooCommerce Products, or any custom post type in the same List, Card, or Carousel display. Only shared fields (title, excerpt, featured image, date) are used for mixed items; WPYog-News-specific fields (External URL, Source Details) are skipped gracefully for other post types.
* Added: `taxonomy` attribute — lets the `category` filter target a different taxonomy when mixing post types that share one (e.g. `category`).
* Added: `show_type` attribute — shows a small badge with each item's post type label, useful for telling sources apart in a mixed feed.
* Added: Post Types checklist in the Shortcode Generator admin page and the Gutenberg block Inspector controls.
* Added: Load More (card layout) now correctly carries the selected post types, taxonomy, and badge setting across AJAX pages.

= 1.2.4 =
* Fixed: Carousel's last card still clipped on the right when `gap` was greater than 0 (confirmed via live testing — cutting disappeared at `gap="0"`). Real root cause: carousel items use `flex-shrink: 0` (they're meant to stay a fixed size), so they never shrink to make room for the track's `gap` — a plain percentage flex-basis (e.g. 3 items at 33.3333%) already fills the row, and the gap on top of that always overflows by exactly (number of gaps × gap width), which is what was being clipped. Fixed by computing each slide's width with `calc((100% - (n-1) × gap) / n)` instead of a flat percentage, so the gap is subtracted before dividing. Gap now works at any value without cutting off the last slide.

= 1.2.3 =
* Fixed: Carousel's last visible card was being clipped on the right (confirmed on the live demo at slides_to_show="3"). Root cause: the 1.2.2 card margin (`20px 6px`) added horizontal margin on top of flex-basis percentages that already summed to 100% of the row — the extra margin pushed the row wider than the viewport, and the viewport's overflow:hidden sliced off the overflow. Fixed by making the card margin vertical-only (`20px 0`); horizontal spacing between slides is handled by the track's `gap` instead, which browsers correctly account for when sizing flex-basis so it can't overflow.

= 1.2.2 =
* Fixed: Card grid's rightmost column could get clipped/overflow in narrower theme layouts (e.g. with a sidebar) when a title or image forced a column past its share of the row — grid/flex items now correctly allow shrinking (`min-width: 0`), and long titles/excerpts wrap instead of forcing overflow.
* Changed: Card and Carousel layouts now go straight to a single column/slide at tablet width (≤900px / ≤768px) instead of an intermediate 2-column step, so mobile visitors see one full-width card at a time.

= 1.2.1 =
* Fixed: Carousel prev/next arrows and dots no longer inherit theme button styling — colours, borders, and shape are now pinned by the plugin so dots render fully circular on every theme.
* Added: `arrows_on_hover` carousel attribute — arrows stay hidden until the carousel is hovered (desktop only; always visible on touch devices).
* Changed: Removed the card hover-lift (translateY) effect specifically for Carousel items — it looked glitchy next to neighbouring slides in a moving track. Card grid layout keeps the lift.
* Changed: Carousel cards now get their own `20px 6px` margin for breathing room between and around slides.
* Fixed: `.wpyog-card-no-thumb` height now matches the real thumbnail height (210px, was 160px), so cards with and without a featured image line up evenly — affects Card grid and Carousel.
* Improved: Carousel slide-position math now measures real rendered spacing between slides instead of parsing CSS gap, so it stays accurate with the new card margins and any future spacing tweaks.

= 1.2.0 =
* Added: New Carousel layout for `[wpyog_news]` — `layout="carousel"`. A sliding row of news cards with autoplay, prev/next arrows, dot navigation, infinite loop, slide or fade transition, touch swipe support, and responsive slides-to-show.
* Added: Carousel attributes — `slides_to_show`, `autoplay`, `autoplay_speed`, `infinite`, `arrows`, `dots`, `pause_on_hover`, `transition`, `gap`.
* Added: Carousel option to the Shortcode Generator admin page with live-updating preview.
* Added: Carousel support in the native Gutenberg block, including Inspector controls for all carousel options.
* Added: Lightweight vanilla-JS carousel engine (no external slider library, no jQuery dependency) respecting `prefers-reduced-motion`.

= 1.1.5 =
* Screenshots and banner updated

= 1.1.4 =
* Improved: Replaced the experimental split-flap animation with a reliable push-up slide for the `flap` animation mode — current headline slides out upward while the next headline slides in from below, clipped cleanly by the ticker viewport. Works consistently across all browsers.
* Fixed: Admin shortcode generator attribute table header text colour now displays correctly in all WordPress admin themes.
* Updated: `.wpyog-card-cats a` category pill padding updated.

= 1.1.3 =
* Added: `show_label` attribute — set `show_label="false"` to hide the label chip entirely.
* Added: `show_count` attribute — set `show_count="true"` to display a post counter (e.g. 3 / 10) on the right side of the ticker. Applies to `fade` and `flap` animation modes.
* Added: `[wpyog_news_ticker]` shortcode alias — both `[wpyog_ticker]` and `[wpyog_news_ticker]` work identically.
* Added: News Ticker tab to the Shortcode Generator admin page with live preview, colour pickers for label background and text, and controls for all ticker options.
* Improved: Ticker responsive styles — label shrinks gracefully on mobile, date hides on very small screens.
* Changed: `show_date` now defaults to `true`.

= 1.1.2 =
* Added: News Ticker — use `[wpyog_ticker]` to display animated news headlines anywhere on your site. Three animation styles: `scroll` (continuous horizontal ticker tape), `fade` (cross-fade), and `flap` (animated slide).
* Added: Ticker label chip with custom background and text colour.
* Added: Pause-on-hover for all ticker animation modes.
* Added: `prefers-reduced-motion` accessibility support — ticker pauses automatically for users who prefer reduced motion.
* Added: External URL support in the ticker — items with an external link open in a new tab.

= 1.1.1 =
* Fixed: Elementor Pro Theme Builder templates now correctly override single News post pages. Fix: when Elementor Pro is active the plugin's `single_template` filter is removed, allowing Elementor Pro's `template_include` hook to take over cleanly.

= 1.1.0 =
* Added: WPYog News post type is now automatically added to Elementor Pro's supported post types — no manual configuration needed in Elementor → Settings → Integrations.
* Fixed: Post type label corrected from "News Item" to "WPYog News" — fixes the section header in Elementor Theme Builder's condition picker.

= 1.0.9 =
* Fixed: Elementor integration timing bug — hooks now register correctly when Elementor loads before WPYog News.

= 1.0.8 =
* Fixed: Critical PHP error introduced in 1.0.7 — unsafe Elementor Pro class call replaced with a safe implementation.

= 1.0.7 =
* Added: Full Elementor Theme Builder support — News posts and News Category archives appear as selectable conditions in Theme Builder.
* Added: Elementor integration class for registering the News post type and taxonomy with Elementor's internal helpers.

= 1.0.6 =
* Fixed: Single news post template now has a max-width of 900px and a centred featured image.

= 1.0.5 =
* Updated: Category pill padding and admin table header styles.

= 1.0.4 =
* Fixed: Contributor username, pagination escaping, and admin nonce handling.

= 1.0.3 =
* Fixed: i18n translators comments, deprecated function calls, and missing `wp_unslash()` calls.
* Updated: Tested up to WordPress 6.9. Added translation template (.pot file).

= 1.0.2 =
* Fixed: Previous/Next navigation on single news pages when posts have no category assigned.
* Improved: CSS updates for title, excerpt, and pagination styles.

= 1.0.1 =
* Fixed: Admin sidebar icon, Load More AJAX, and post/category slugs.
* Added: Previous/Next navigation on single news detail pages.
* Improved: Modern card and list CSS — hover effects, image zoom, category pills, indigo Read More button.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.4.2 =
Fixes the carousel's infinite loop so it always slides forward (the first slide enters from the right after the last one) instead of reversing back to slide 1. Recommended update for anyone using the carousel layout with infinite loop enabled.

= 1.4.1 =
Collections now use a checkbox-list (like Categories) instead of a type-to-search tag box — tick a box to add a post to a collection, or click "+ Add New Collection" to create one. Recommended update for anyone using Collections.

= 1.4.0 =
Adds Collections — a repeatable way to build a curated group of posts across any mix of post types by tagging them from their edit screen, then pulling them in with the new `collection` attribute. No shortcode edits needed as the collection grows. Recommended update for all users mixing post types.

= 1.3.0 =
Adds the `post_type` attribute to mix WPYog News with other post types (Posts, Products, custom CPTs) in one List, Card, or Carousel layout, plus a Post Types checklist in the Shortcode Generator and Gutenberg block. Recommended update for all users.

= 1.1.4 =
Improves the Flap ticker animation (push-up slide replacing experimental split-flap), fixes admin table header colour, and updates category pill padding. Recommended update for all 1.1.x users.

= 1.1.3 =
Adds label show/hide, post counter badge, `[wpyog_news_ticker]` alias, and a dedicated Ticker tab in the Shortcode Generator with live preview. Recommended update for all users.

= 1.1.2 =
Adds the News Ticker (`[wpyog_ticker]`) with scroll, fade, and flap animations, pause-on-hover, and full accessibility support. Recommended update for all users.

= 1.1.1 =
Adds full Elementor Theme Builder support and fixes a critical PHP error introduced in 1.0.7. Recommended update for all Elementor Pro users.

= 1.0.0 =
Initial release of WPYog News.
