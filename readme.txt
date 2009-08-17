=== Plugin Name ===
Contributors: Jean-François “Jeff” VIAL
Donate link: http://www.modulaweb.fr/blog/wp-plugins-en/
Tags: slideshow, frontpage, categories
Requires at least: 2.0
Tested up to: 2.8.4
Stable tag: 0.1

Frontpage Slideshow provides a slide show like you can see in linux.com front page

== Description ==

This plugin allows you to put a slideshow on your Wordpress frontpage. The slide is made of :

    * an cliquable image zone
    * a 4 buttons zone with
          o a title
          o a button specific message
    * a text zone with
          o the same title as button
          o a specific message

Images are pre-loaded and the default design is elegant and clean with a black background, transparencies ans rounded corners (except for IE).

Transitions are faded to add more eye-candy and sweetness.

By default, the only 4 latest slides are displayed, but you can modify the code easily to display more or less.

Actually the plugin is in version 0.1 : its the very first version and there is quite a lot of things to do like a configuration page, but it is usable out of the box right now, and is quite easily customisable.

== Installation ==

1. Upload `frontpage-slideshow.zip` to the `/wp-content/plugins/` directory and uncompress it.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create a specific category called "fs-cat"
1. Put short-tag "[frontpage-slideshow]" onto your frontpage

== Frequently Asked Questions ==


== Changelog ==


== Screenshots ==


== How to use ==

When it has been installed and activated, you just have to create a category called "fs-cat". You could choose to not display this category or change its name by editing the source code of plugin. When done, add posts on if : the slides will be materialized by those articles.

The displayed image onto the slideshow is the first image of each articles.

Then you got to ad thoses custom parameters to each slide-posts :

    * fs-button-comment : the button specific message
    * fs-comment : the comment
    * fs-link : the URL of page where to redirect users when they click on the image.

The title of slides is made by the title of posts.

== Milestones ==

1. create a configuration page
	1. allow to edit of category name and number of posts
		1. calculate the buttons height automatically
		2. allow to not display buttons
		3. allow to display a preview and next button on side of slider
	2. allow to change the size of the slider
	3. allow to fine tune the appearance of buttons
	4. allow to change the slide display duration
2. allow the use of multiples categories to find slides
3. widgetize the slider to allow to put it on sidebar an create small sliders



