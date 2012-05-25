== JDesrosiers Multilingual ==
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

== Installation ==

1. Upload `jdesrosiers-multilingual` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go in Pages -> Languages, and add your two languages from there. Name should
be something like "English", and Slug should be something like "en" (two
lowercase letters).
1. TODO: In your template, add this tag where you want the language switcher
to appear: `<?php jdml_language_switcher(); ?>`

