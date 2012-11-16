=== JDesrosiers Multilingual ===
Contributors: JDesrosiers
Tags: multilingual
Requires at least: 3.3.1
Tested up to: 3.3.1

A plugin that adds simple features to help make your WordPress site
multilingual.

== Description ==

JDesrosiers Multilingual is a Plugin that adds simple features, such as a
language taxonomy, to help you make Multilingual WordPress sites without
creating extra tables in your database or polluting your post entries with
shortcodes.

It needs a bit more work (not that much) from you. But it works great if
you create your own theme from scratch.

For now, it only supports two languages. 

This project is hosted on https://github.com/juliend2/jdesrosiers-multilingual

== Installation ==

1. Upload `jdesrosiers-multilingual` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go in Pages -> Language, and add your two languages from there. Name should
be something like "English", and Slug should be something like "en" (two
lowercase letters).
1. In Settings -> Permalinks, put the following in the "Custom Structure"
field: `/%language%/%postname%/` . This will prepend the language slug to your
post permalinks.
1. In your template, add this tag where you want the language switcher
to appear: `<?php jdml_language_switcher(); ?>`
1. Add a file named `taxonomy-language.php` in your theme, with the following
content:
	<?php
	/*
	Modify the current query to only get the page, and not the list of all the 
	pages in this language.
	*/
	$lang_slug = get_query_var('language');
	query_posts(array(
	  'pagename' => $lang_slug
	));

	get_template_part('content', 'home');
1. Create a file named `content-home.php` in your theme, with whatever you
want as a home page.
1. Make sure you set "Your latest post" in Settings > Reading > Front page
displays.
1. You now have to create a "Page" entry for the home page of every language
you want to support. And for its slug, give it the locale name (like 'en' or
'fr').
1. Every page that you want to appear under `/fr/` (in the URL) must be a child
page of its corresponding home page. For example, the About page must be a
child page of the Home page to appear under the `/en/about/` URL.


== Frequently Asked Questions ==

= Does it supports more than two languages? =

Right now, it only supports two languages. It is not my priority to support
more than two languages, but I'm always open to pull requests.

== Screenshots ==

== Changelog ==

= 1.2.1 =
* In posts admin view: hightlight the current language in the filters list

= 1.2 =
* Add language filter links in posts (or any custom post type) admin view
* Display all the posts (or any custom post type) in admin posts list view

= 1.1 =
* Update the corresponding post's translation when selecting a corresponding
  post via the metabox

= 1.0 =
* Working version

== Todo ==

* Add a way to opt-out a custom post type from being translatable
* Merge the Language taxonomy meta box with the Corresponding Post meta box in the admin
* Add an admin settings page for centralizing some configurations

== Known issues ==

* When we're in a page that has the same slug as its corresponding page, both
  posts show up

