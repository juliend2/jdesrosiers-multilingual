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
1. Go in Pages -> Languages, and add your two languages from there. Name should
be something like "English", and Slug should be something like "en" (two
lowercase letters).
1. In Settings -> Permalinks, put the following in the "Custom Structure"
field: `/%language%/%postname%/` . This will prepend the language slug to your
post permalinks.
1. In your template, add this tag where you want the language switcher
to appear: `<?php jdml_language_switcher(); ?>`

== Frequently Asked Questions ==

= Does it supports more than two languages? =

Right now, it only supports two languages. It is not my priority to support
more than two languages, but I'm always open to pull requests.

== Screenshots ==

== Changelog ==

== Todo ==

* Add a way to opt-out a custom post type from being translatable
* Merge the Language taxonomy meta box with the Corresponding Post meta box in the admin
* Add an admin settings page for centralizing some configurations


