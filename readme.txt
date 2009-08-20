=== Plugin Name ===
Contributors: Jean-François “Jeff” VIAL
Donate link: http://www.modulaweb.fr/blog/wp-plugins-en/
Tags: slideshow, frontpage, categories
Requires at least: 2.0
Tested up to: 2.8.4
Stable tag: 0.4

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

Via its administration page, you can tune up the look and feel of the slider

== Installation ==

1. Upload `frontpage-slideshow.zip` to the `/wp-content/plugins/` directory and uncompress it.
1. Go to the plugin's administration page and configure it as you want it
1. Activate the plugin when you are ready to
1. No short-tag is needed : the plugin work as soon as activated

== Frequently Asked Questions ==


== Changelog ==

* v 0.1 : very first release usable but no option page
* v 0.2 : some terrible graphic bugs fixed : option page under construction and preview
* v 0.3 : some minor javascript and CSS bugs fixed : now the plugon is ready for the option-page and fine tunes.
* v 0.4 : Fully functional administration page with preview, reset to default

== Screenshots ==


== How to use ==

When it has been installed and activated, you just have to select from wich categories you want the slides comes from by using the administration page.
The displayed image onto the slideshow is the first image of each articles, so make sure the article contains at least one image.

Then you got to addd thoses custom parameters to each slide-posts :

    * fs-button-comment : the button specific message
    * fs-comment : the comment
    * fs-link : the URL of page where to redirect users when they click on the image.

The title of slides is made by the title of posts.

When all is done, tune up the slider and enable it.

== Milestones ==

1. create a configuration page <= done
	1. allow to edit of category name and number of posts <= done, maybe some issues... need to test more
		1. calculate the buttons height automatically <= work in progress
		2. allow to not display buttons  <= done
		3. allow to display a preview and next button on side of slider
	2. allow to change the size of the slider <= done
	3. allow to fine tune the appearance of buttons <= partially done
	4. allow to change the slide display duration <= work in progress
2. allow the use of multiples categories to find slides <= done
3. widgetize the slider to allow to put it on sidebar an create small sliders



