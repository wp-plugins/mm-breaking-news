=== Plugin Name ===
Contributors: mmilan81
Donate link: http://www.svetnauke.org/
Tags: latest, post, category, front, page, archive, news, widget
Requires at least: 2.8
Tested up to: 3.3.1
Stable tag: 0.7.5

Displays lists of posts from selected categories. You can select how many different lists you want, sort posts by date or random.

== Description ==

This plugin gives you possibility to create bullet lists for your posts. You can choose as many lists as you want and for each list you can select categories to include or to exclude posts. 

Your posts can be randomized or sord by date. With title you can chose to show date or number of comments.

Lists can be show wherever you want - above or below posts, in archive, or on single post or page. Also, you can put a widget in a sidbar. You can choose to show lists only on first page or not.

You can modify your themplate to display list of post, add <?php if (function_exists('mm_bnlist')) mm_bnlist() ?> for single column or <?php if (function_exists('mm_bnlist_multi')) mm_bnlist_multi(2) ?> for multiple columns. If you want to show a list on one page (or post) you can use a shortcode [mm-breaking-news] when you write and the whole bullet list will be displayed. 

Changelog:

	2012-02-27, ver 0.7.5
		Add: Widget custom CSS classes (now you can style display the way you like)
		Add: Number of posts to display in widget
		Some minor bug fixes.

	2010-07-11, ver 0.7.1
		Bugfix: widget style and settings

	2010-07-06, ver 0.7
		Add: display lists of posts in multiple columns
		Add: custom date/time format
		There are changes in CSS classes and IDs. Your custom CSS can be broken after update.
		Code, speed, size optimization.

	2010-04-02, ver 0.6.5
		Add: widget
		Bugfix: multilanguage posts
		
	2010-01-30, ver 0.6.3
		Bugfix: wrong post date
		
	2010-01-14, ver 0.6.2
		Bugfix: problem with links

	2010-01-13, ver 0.6.1
		Bugfix: problems with placement of shortcode content.
		
	2010-01-13, ver 0.6
		Added shortcode [mm-breaking-news] for displaying a list in a page/post

	2009-12-25, ver 0.5.3
		Bugfix: Wordpress 2.9

	2009-04-03, ver 0.5.2
		Bugfix: problem with CSS file

	2009-03-6, ver 0.5
		Uploaded to Wordpress.org


== Installation ==

1. Upload plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place `<?php if (function_exists('mm_bnlist')) mm_bnlist() ?>` in your templates. If you would like to show posts in multiple columns use <?php if (function_exists('mm_bnlist_multi')) mm_bnlist_multi(2) ?> instead (number 2 is the number of columns; it can be anything you want).
4. Edit CSS file in plugin folder. You must edit mm-bnlist.css if you want to display more than two columns.
5. In WordPress menu go to 'Setting / MM Breaking News' and configure plugin

== Frequently Asked Questions ==

= Why one more "latest post" similar plugin? =

Other plugins didn't have options I need. And... it's my first plugin :)

== Screenshots ==

1. Plugin in use (code is added to template)
2. Settings I
3. Setting II
4. Multiple columns display

