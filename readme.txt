=== Plugin Name ===
Tags: twitter, AJAX
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 0.5.1

AJAXed Twitter for Wordpress displays your public timeline in your blog.

== Description ==

This plugin is based on ["Twitter for Wordpress"](http://wordpress.org/extend/plugins/twitter-for-wordpress/ "Wordpress Plugins") 1.9.7 by [Ricardo Gonz&aacute;lez](http://rick.jinlabs.com/ "Ricardos Homepage").

It supports [MooTools](http://mootools.net/) as well as jQuery which comes with Wordpress and can be used as a widget.


== Installation ==

You can use this plugin as a widget or add it manually to your theme. In widget-mode it depends on jQuery (the one that comes with Wordpress is sufficient).

= Widget =

Use the management functionality for widgets provided by Wordpress (simply drag and drop, edit the preferences, save).


= Manual Installation =

First of all tell the plugin (in wp_config.php) which framework to use:

`define('AJAXED_TWITTER_FRAMEWORK', 'mootools');`

You can also use 'both' which will allow you to add a widget and the MooTools version.


I did not include a release of MooTools, so you will have to add a script tag *before wp_head();* like this (and of course provide that script there):

`<script type="text/javascript" src="<?php bloginfo('stylesheet_directory'); ?>/scripts/mootools-1.2.4-core-yc.js"></script>`

Then you need a PHP-file that can be called by the JavaScript part, for example you could put a file named "twitter.php" with the following content in your theme folder. This is only an example and you can do whatever you like to get the tweets.

`if (!defined('DB_NAME')) {
	require_once("../../../wp-config.php");
}

echo AJAXedTwitter::messages(array(
	'username' => 'username'
));`

The Twitter-class is easy to configure (and already enqueued by the plugin).

`var twitter = new Twitter('tweets', {
	url: '/blog/wp-content/themes/yourtheme/twitter.php',
	retries: 2,
	animate: true
});`

In this example the element 'tweets' (that could be e.g. in your sidebar) is replaced by a public timeline after the page is loaded.

Additionally to the CSS classes provided by "Twitter for Wordpress" this plugins provides first/last for list items.


For more details (options, configuration) visit [the plugin hompage](http://derhofbauer.at/blog/ajaxed-twitter-plugin-for-wordpress/ "derhofbauer.at").


== Changelog ==

= 0.5.1 =
* Escape single quotes in passed options.
* Unset not needed options.

= 0.5 =
* Bugfixed custom title
* Implemented WP 2.8 Widget API (allows mupltiple widgets now)
* Rewrote to allow multiple instances, even MooTools and jQuery

= 0.4 =
* Widget does not depend on MooTools any more or require editing of the theme

= 0.3.1 =
* Fixed problem with forgotten timeout definition

= 0.3 =
* Implemented own copy of fetch_rss() for seperated cache handling
* Added option "cache expiry" (cache-age), defaulting to half an hour
