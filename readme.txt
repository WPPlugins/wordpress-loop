=== WordPress Loop ===
Contributors: ptahdunbar
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=11341928
Tags: widget, pages, posts, attachments, post types
Requires at least: 2.9
Tested up to: 2.9
Stable tag: 0.4

A WordPress widget that gives you unprecendeted control over displaying your content.

== Description ==

The *WordPress Loop* widget was written to allow users that don't know their way around PHP to easily show their content in any way they'd like. 

The widget has over 35 options to choose from. Customize your WordPress loop by one or more post types, categories, tags, custom taxonomies, authors, dates, custom fields, and a whole lot more! 

In addition, it has support for post thumbnails, sticky posts, pagination, offsetting, customizable content length (by word count), and you can change the ordering from a variety of options. Oh, and you can also customize the .

*WordPress Loop* is truly an all-in-one solution for displaying your content on your site.

View the [FAQ](http://wordpress.org/extend/plugins/wordpress-loop/faq/) section for more info on how to use WordPress Loop.

== Frequently Asked Questions ==

= How does this widget work? =

The WordPress Loop utilizes the [WP_Query](http://codex.wordpress.org/Function_Reference/WP_Query) class to generate the widget loops. You can see a list of `$args` at the [query_posts() codex page](http://codex.wordpress.org/Template_Tags/query_posts). The WordPress Loop widget also makes use of the [action hooks and filters](http://codex.wordpress.org/Plugin_API) for advance customization.

= What are the available shortcodes in this widget? =
The `before_content` and `after_content` sections may contain shortcodes.
In addition, the WordPress Loop widget comes bundled with:

* `[title]` - Displays the title of the post.
* `[author]` - Displays the author of the post.
* `[date]` - Displays the date the post was published.
* `[time]` - Displays the time of day the post was posted.
* `[last_modified]` - Displays the date the post was last modified.
* `[comments]` - Displays the comment count of the post.
* `[cats]` - Displays all categories (in a comma seperated link format) associated with the post.
* `[tags]` - Displays any tags (in a comma seperated link format) associated with the post.
* `[tax]` - Displays all taxonomies (in a comma seperated link format) associated with the post.
* `[edit]` - Displays the edit link to edit the post.

= What hooks are available in this widget? =

The WordPress Loop has several action hooks available throughout the loop process:

* `before_loop` - At the beginning of the loop
* `the_loop` - In the loop, after all the content
* `in_the_loop_x` - In the loop, after the `.hentry` div. Replace x with the number position you want to insert content into
* `after_loop` - At the ending of the loop
* `loop_404` - When the loop can't find any post

In addition, it also has several filter hooks where you can modify the content's output:

* `wl_the_content` - The post content
* `wl_postmeta` - The text of the `before_content` and `after_content` widget settings
* `wl_entry_title` - The post title
* `wl_entry_author` - The post author
* `wl_entry_date` - The date
* `wl_entry_time` - the time of day the post was posted
* `wl_entry_last_modified` - The date the post was last modified.
* `wl_entry_comments` - The comment count of the post.
* `wl_entry_cats` - Categories (in a comma seperated link format) associated with the post.
* `wl_entry_tags` - Tags (in a comma seperated link format) associated with the post.
* `wl_entry_tax` - Taxonomies (in a comma seperated link format) associated with the post.
* `wl_entry_edit` - The edit link to edit the post.

== Installation ==

1. Upload 'wordpress-loop' to the '/wp-content/plugins/' directory.
1. Activate the plugin through the *Plugins* menu in WordPress.
1. Go to *Appearance > Widgets* and place the *WordPress Loop* widget where you want.

== Changelog ==

**0.4** _(01/26/2010)_

	* ADDED: new checkbox `use $wp_query` overrides all the widget query settings
	* ADDED: new filter hook for `wl_the_content`
	* UPDATED: readme.txt with info on the new `in_the_loop_x` hook from `0.3`
	* UPDATED: updated the default text in `before_content`
	* UPDATED: bug fix for `wl_entry_cats` if terms don't exists
	* UPDATED: bug fix for `wl_entry_tags` if terms don't exists
	* UPDATED: bug fix for `wl_entry_tax` if taxonomies don't exists
	* UPDATED: changed the output of `wp_link_pages()` from `.entry-pages` to `.paged-links`
	* UPDATED: readme.txt with info on all filter hooks + other minor changes.

**0.3** _(01/26/2010)_

	* ADDED: new hook `in_the_loop_x` where x is a non-negative number.
	* ADDED: the post thumbnail image links to the actual post.
	* ADDED: `widget-title` css class to the widget title.
	* UPDATED: `wl_the_content` now wraps excerpts with p tags.
	* UPDATED: all shortcodes and `wl_postmeta` now are filterable.

**0.2** _(01/25/2010)_

	* ADDED: ability to customize the main loop tag (div/ol/ul)
	* ADDED: new shortcode [last_modified]
	* UPDATED: readme.txt with more info
	
**0.1** _(01/24/2010)_

	* Initial release.
	
== Screenshots ==

1. View of the *WordPress Loop* widget settings.