=== Plugin Name ===
Contributors: Jean-François “Jeff” VIAL
Donate link: http://www.modulaweb.fr/blog/wp-plugins-en/
Tags: slideshow, pictures, no-flash, css, javascript
Requires at least: 2.8.0
Tested up to: 2.8.4
Stable tag: 0.7

Frontpage Slideshow provides a slide show like you can see in linux.com front page

== Description ==

This plugin allows you to put a slideshow on your Wordpress frontpage. like the one on linux.com

The slide is made of a cliquable picture zone, some buttons with title and specific comment allowing to display a particular slide and a text zone to add a comment on slide.

pictures are pre-loaded and the default design is elegant and clean with a black background, transparencies ans rounded corners (except for IE).

Transitions are faded to add more eye-candy and sweetness.

By default, the only 4 latest slides are displayed, but you can modify that with admin page.

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
* v 0.3 : some minor javascript and CSS bugs fixed : now the plugin is ready for the option-page and fine tunes.
* v 0.4 : fully functional administration page with preview, reset to default
* v 0.5 : important bug fix when the plugin is loaded before jQuery and some CSS improvement
* v 0.6 : improving the loading of javascript frameworks needed, the shortcode funtionnality added, alternative picture option added, when no link is set, the URL of the post can be used
* v 0.6.1 : minor bug correction (replacing php short tags by long ones)
* v 0.7 : allow to use the WP Text Widget to display the slideshow by inserting the shortcode onto the text itself, modify the original WP Text Widget to allow the use of all other shortcodes

== Screenshots ==

1. live at http://wwww.modulaweb.fr french webagency
2. live at http://www.smartyweb.org/

== How to use ==

When it has been installed and activated, you just have to select from wich categories you want the slides comes from by using the administration page.
The displayed picture onto the slideshow is the first picture of each articles, so make sure the article contains at least one picture.

Then you got to addd thoses custom parameters to each slide-posts :

    * fs-button-comment : the button specific message
    * fs-comment : the comment
    * fs-link : the URL of page where to redirect users when they click on the picture, can be set to the page URL if none is given
    * fs-picture : the URL of the picture to display, the first picture on post-content is used if this parameter is missing

The title of posts is used as the title of slides

When all is done, tune up the slider and enable it.

By default, the slideshow is added at the beginning of the front-page content. The best way to use if is to use a static-page as the front-page. If you are displaying a list of last posts, the slideshow is added at the beginning of the fist post shown.
You can use a customisable shortcode to display the slideshow. You can use the shortcode as an enclosing one : you can put replacement content in case of the slideshow cannot be shown (if it has already been added earlier in the document flow) or is not activated.
Example : 
`[FrontpageSlideshow fs_main_width=100% fs_main_color=pink]
&lt;a href="/images/me.png" rel="lightbox" title="This is image caption">&lt;img src="/images/me.png" width="80" height="80" alt="This is image title" />&lt;/a>
&lt;a href="/images/me2.png" rel="lightbox" title="This is image caption">&lt;img src="/images/me2.png" width="80" height="80" alt="This is image title" />&lt;/a>
[/FrontpageSlideshow]`

You can use this shortcode into a Text Widget to display the slideshow into the sidebar

=== Inserting the slideshow anywhere into your text ===

You can insert the slideshow anywhere you want by inserting the following php code where you want it to be displayed : 
`<?php echo do_shortcode('[FrontpageSlideshow]'); ?>`
Note that you can use all the features that shortcodes offers : for example, you can specify an alternative content (as the example before) this way.

Note that FrontpageSlideshow modifies the normal Text Widget and allow you to use all the shortcodes in Text Widget.

If you need a new functionality, you can ask for it by contacting the author directly or by posting a feature request on related forum on wordpress.org website.
If those functionnlity can't wait, consider making a donation.

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
3. widgetize the slider to allow to put it on sidebar an create small sliders <= done
4. Allowing the use of shortcode to add the slideshow <= done



