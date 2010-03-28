<?php
/*
Plugin Name: Frontpage-Slideshow
Plugin URI: http://www.modulaweb.fr/blog/wp-plugins/frontside-slideshow/en/
Description: Frontpage Slideshow provides a slide show like you can see on <a href="http://linux.com">linux.com</a> or <a href="http://modulaweb.fr/">modulaweb.fr</a> front page. <a href="options-general.php?page=frontpage-slideshow">Configuration Page</a>
Version: 0.9.5
Author: Jean-François VIAL
Author URI: http://www.modulaweb.fr/
*/
/*  Copyright 2009 Jean-François VIAL  (email : jeff@modulaweb.fr)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
define ('FRONTPAGE_SLIDESHOW_VERSION', '0.9.5');
$fs_already_displayed = false; // the slideshow dont have been displayed yet
function frontpageSlideshow($content,$force_display=false,$options=array()) {
	global $fs_already_displayed;

	if ($fs_already_displayed) return false;

	if (!count($options)) $options = frontpageSlideshow_get_options();
	if (!$options['values']['fs_is_activated'] && !$force_display) return $content;

	$fscategories = join(',',$options['values']['fs_cats']);

	if ((!is_feed() && is_front_page() && $options['values']['fs_insert']!='shortcode') || $force_display) { // the slideshow is only displayed on frontpage
		$fs_already_displayed = true;
		// get the 4th newer posts
		$fsposts = get_posts('category='.$fscategories.'&orderby='.$options['values']['fs_orderby'].'&numberposts='.$options['values']['fs_slides'].'&order='.$options['values']['fs_order']);
		// put post in more logical order
		$fsposts = array_reverse($fsposts);
		$fsentries = array();
		foreach ($fsposts as $fspost) {
			// format informations
			$title = get_post_meta($fspost->ID,'fs-title',true);
			if ($title == '') $title = $fspost->post_title;
			$comment = get_post_meta($fspost->ID,'fs-comment',true);
			$buttoncomment = get_post_meta($fspost->ID,'fs-button-comment',true);
			$link='';
			// if the option is on, uses the post permalink as slide link
			($options['values']['fs_default_link_to_page_link'] && get_post_meta($fspost->ID,'fs-link',true) == '') ? $link = get_permalink($fspost->ID) : $link = get_post_meta($fspost->ID,'fs-link',true);
			$image = get_post_meta($fspost->ID,'fs-picture',true);
			if ($image == '') { // if no image : use the first image on the post
				$image = $fspost->post_content;
				if (preg_match('/<img[^>]*src="([^"]*)"/i',$image,$matches)) {
					$image = $matches[1];
				} else {
					(is_ssl()) ? $url = str_replace('http://','https://',get_bloginfo('url')) : $url = str_replace('https://','http://',get_bloginfo('url')); 
					$image = $url.'/wp-content/plugins/frontpage-slideshow/images/one_transparent_pixel.gif';
				}
			}

			// handles https for the link
			(!is_ssl()) ? $link = str_replace('https://','http://',$link) : $link = str_replace('http://','https://',$link);
			// handles https for image
			(!is_ssl()) ? $image = str_replace('https://','http://',$image) : $image = str_replace('http://','https://',$image);
			// put infos into an array

			$fsentries[] = array('title' => $title.'&nbsp;', 'image' => $image, 'comment' => $comment.'&nbsp;', 'button-comment' => $buttoncomment.'&nbsp;', 'link' => $link);
		}
		// construct the slider
		$fscontent = '';
		$fslast = count($fsentries) -1;
		if (count($fsentries)) {
			$fscontent = '<div id="fs-main"><div id="fs-slide"><div id="fs-picture"><div id="fs-placeholder"><a href="#frontpage-slideshow" onclick="if (fsid>-1) { if (jQuery(\'#fs-entry-link-\'+fsid).html() != \'\') { this.href=jQuery(\'#fs-entry-link-\'+fsid).html(); } }">&nbsp;</a></div><div id="fs-text"><div id="fs-title">&nbsp;</div><div id="fs-excerpt">&nbsp;</div></div></div></div><ul>';
			foreach ($fsentries as $id=>$entry) {
				$fscontent .= '<li id="fs-entry-'.$id.'" class="fs-entry" onclick="window.clearInterval(fsinterval); fsChangeSlide('.$id.')">';
				$fscontent .= '<div id="fs-entry-title-'.$id.'" class="fs-title">'.$entry['title'].'</div>';
				$fscontent .= '<div id="fs-entry-button-comment-'.$id.'" class="fs-comment">'.$entry['button-comment'].'</div>';
				$fscontent .= '<img id="fs-entry-img-'.$id.'" class="fs-skip" alt=" " src="'.$entry['image'].'"';
				if ($id == $fslast) $fscontent .= ' onload="fsDoSlide()"'; // put this to make another loop after the last image
				$fscontent .= ' />';
				$fscontent .= '<span id="fs-entry-comment-'.$id.'" class="fs-skip">'.$entry['comment'].'</span>';
				$fscontent .= '<span id="fs-entry-link-'.$id.'" class="fs-skip">'.$entry['link'].'</span>';
				$fscontent .= '</li>';
			}
			$fscontent .= '</ul></div>';
		}
		return "\n<!-- Frontpage Slideshow begin -->\n".$fscontent."\n<!-- Frontpage Slideshow end -->\n".$content;
	} else {
		return $content;
	}
}

function frontpageSlideshow_init() {
	// loads the needed frameworks to load as a safe way
	// now using jQuery framework instead of Prototype+Scriptaculous
	wp_register_script('jquery-ui-effects',WP_PLUGIN_URL .'/frontpage-slideshow/js/jquery-ui-effects.js', array('jquery-ui-core'));
	wp_enqueue_script('jquery-ui-effects');
}
function frontpageSlideshow_admin_init() {
	// loads the needed frameworks to load as a safe way into admin page
	// now using jQuery framework instead of Prototype+Scriptaculous
	wp_register_script('jquery-ui-interactions',WP_PLUGIN_URL .'/frontpage-slideshow/js/jquery-ui-interactions.js', array('jquery-ui-core'));
	wp_enqueue_script('jquery-ui-interactions');
}

function frontpageSlideshow_header($force_display=false,$options=array()) {
		if (!count($options)) $options = frontpageSlideshow_get_options();
		if (!$options['values']['fs_is_activated'] && !$force_display) return;

		$fscategories = join(',',$options['values']['fs_cats']);
		if ((!is_feed() && is_front_page()) || $force_display) { // the slideshow is only displayed on frontpage
			$fsposts = get_posts('category='.$fscategories.'&orderby=ID&numberposts='.$options['values']['fs_slides']);
			$fslast = count($fsposts) - 1;

			frontpageSlideshow_JS($options,$fslast,$force_display);
			frontpageSlideshow_CSS($options,$force_display);
		}
}

function frontpageSlideshow_JS_effect($effect) {
	if ($effect == 'same') $effect = $options['values']['fs_transition'];
	if ($effect == 'random') {
		$transitions = array('fade', 'shrink', 'dropout', 'jumpup', 'explode', 'clip', 'dropleft', 'dropright', 'slideleft', 'slideright', 'fold', 'puff');
		$effect = $transitions[rand(0,count($transitions)-1)];
	}
	switch ($effect) {
		case 'fadeout':
		case 'fade':
			return 'jQuery("#fs-slide").toggle("fade", {}, 500, fsChangeSlide2);';
		case 'scale':
		case 'shrink':
			return 'jQuery("#fs-slide").toggle("scale", {}, 500, fsChangeSlide2);';
		case 'dropout':
		case 'drodown':
			return 'jQuery("#fs-slide").toggle("drop", {direction: "down"}, 500, fsChangeSlide2);';
		case 'jumpup':
		case 'dropup':
			return 'jQuery("#fs-slide").toggle("drop", {direction: "up"}, 500, fsChangeSlide2);';
		case 'explode':
			return 'jQuery("#fs-slide").toggle("explode", {}, 500, fsChangeSlide2);';
		case 'clip':
			return 'jQuery("#fs-slide").toggle("clip", {direction: "vertical"}, 500, fsChangeSlide2);';
		case 'dropleft':
			return 'jQuery("#fs-slide").toggle("drop", {direction: "left"}, 500, fsChangeSlide2);';
		case 'dropright':
			return 'jQuery("#fs-slide").toggle("drop", {direction: "right"}, 500, fsChangeSlide2);';
		case 'slideleft':
			return 'jQuery("#fs-slide").toggle("slide", {direction: "left"}, 500, fsChangeSlide2);';
		case 'slideright':
			return 'jQuery("#fs-slide").toggle("drop", {direction: "right"}, 500, fsChangeSlide2);';
		case 'fold':
			return 'jQuery("#fs-slide").toggle("fold", {}, 500, fsChangeSlide2);';
		case 'puff':
			return 'jQuery("#fs-slide").toggle("puff", {}, 500, fsChangeSlide2);';
		default:
			return 'jQuery("#fs-slide").toggle("fade", {}, 500, fsChangeSlide2);';
	}
}
function frontpageSlideshow_JS($options,$fslast) {
?>
<!--  added by plugin FrontpageSlideshow -->
<script type="text/javascript">
// <![CDATA[
var fslast = <?php echo $fslast?>; // # of last slide
var fsid = -1; // the current slide
var fsinterval = 0; //  the setInterval var
function fsChangeSlide(id) {
	jQuery("#fs-entry-"+fsid).removeClass("fs-current");
	fsid=id;
	window.clearInterval(fsinterval);
	<?php echo frontpageSlideshow_JS_effect($options['values']['fs_transition']); ?>
	
}
function fsChangeSlide2() {
	jQuery('#fs-picture').css({backgroundImage : "url("+jQuery("#fs-entry-img-"+fsid).attr("src")+")"});
	jQuery('#fs-title').html(jQuery('#fs-entry-title-'+fsid).html());
	jQuery('#fs-excerpt').html(jQuery('#fs-entry-comment-'+fsid).html());
	jQuery("#fs-slide").fadeIn(500);
	//<?php //echo str_replace(', fsChangeSlide2','',frontpageSlideshow_JS_effect($options['values']['fs_transition_on'])); ?>
	
	jQuery("#fs-entry-"+fsid).addClass('fs-current');
	frontpageSlideshow();
}
function fsDoSlide() {
	// fixes the width of slide to convert its width from % to px
	jQuery("#fs-slide").css({width : jQuery("#fs-slide").css('width')});
	
	if (fsid>-1) jQuery("#fs-entry-"+fsid).removeClass("fs-current");
	fsid++;
	if (fsid>fslast) fsid = 0; // new loop !
	fsChangeSlide(fsid);
}
function frontpageSlideshow() {
	fsinterval = window.setInterval('fsDoSlide()',5000);
}
// ]]>
</script>
<!--  /added by plugin FrontpageSlideshow -->
<?php 
}

function frontpageSlideshow_CSS($options) {
/*
	Here comes the CSS ruleset
	You can, of course, edit it to fit your needs
	Maybe later, a configuration page will come and allow to tweak the css rules in a more flexible way

*/
?>
<!--  added by plugin FrontpageSlideshow -->
<!--[if IE]>
	<style type="text/css">
		#fs-text { filter: alpha(opacity=<?php echo str_replace('%','',$options['values']['fs_text_opacity'])?>); }
	</style>
<![endif]-->
<?php ob_start(); ?>
#fs-main {
	width: <?php echo $options['values']['fs_main_width']?>;
	height: <?php echo $options['values']['fs_main_height']?>;
	border: 1px solid <?php echo $options['values']['fs_main_border_color']?>;
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	overflow: hidden;
	background: <?php echo $options['values']['fs_main_color']?> <?php 
				if ($options['values']['fs_main_background_image'] != '' && $options['values']['fs_main_background_image'] != 'none') {
					$url = $options['values']['fs_main_background_image'];
					(is_ssl()) ? $url = str_replace('http://','https://',$url) : $url = str_replace('https://','http://',$url); echo 'url('.$url.')';
				} else {
					echo 'none';
				}
			  ?> repeat scroll center center!important;
	color: <?php echo $options['values']['fs_font_color']?>;
	font-family: Verdana, Sans, Helvetica, Arial, sans-serif!important;
	text-align: left;
}

#fs-slide {
	float: <?php  if ($options['values']['fs_buttons_position']=='right') echo 'left'; else echo 'right'; ?>;
	width: <?php  if ($options['values']['fs_show_buttons']) echo $options['values']['fs_slide_width']; else echo '100%'; ?>;
	height: 100%;
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}
#fs-picture {
	width: 100%;
	height: 100%;
	background-position: center center;
	background-repeat: no-repeat;
	background-image: url(<?php
					if ($options['values']['fs_loader_image'] != '') {
						$url = $options['values']['fs_loader_image'];
					} else {
						$url = get_bloginfo('url').'/wp-content/plugins/frontpage-slideshow/images/loading_black.gif';
					}
					(is_ssl()) ? $url = str_replace('http://','https://',$url) : $url = str_replace('https://','http://',$url); echo $url ?>);
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}
#fs-placeholder {
	height: <?php echo $options['values']['fs_placeholder_height']?>;
}
#fs-placeholder a {
	display: block;
	height: 100%;
	width: 100%;
	text-decoration: none;
	color: transparent;
	border: none;
}
#fs-placeholder a:hover {
	text-decoration: none;
}
#fs-text {
<?php if ($options['values']['fs_show_comment']) {?>
	opacity: <?php  echo intval(str_replace('%','',$options['values']['fs_text_opacity'])) / 100; ?>;
	background-color: <?php echo $options['values']['fs_text_bgcolor']?>;
	/*margin-top: 10px;*/
	padding: 10px;
<?php } else { ?>
	display: none;
<?php } ?>
}
#fs-text a {
	color: #c0e7f8;
	text-decoration: underline;
}
#fs-text a:visited {
	color: #99fbac;
	text-decoration: underline;
}
#fs-title {
	font-weight: bold;
	font-size: 14px!important;
	line-height: 1.1em;
	margin-bottom: 0.25em;
	font-family: Verdana, Sans, Helvetica, Arial, sans-serif!important;
}
.fs-title {
	font-weight: bold;
	font-size: 11px!important;
	line-height: 1.4em;
	margin: 0!important;
	padding: 0!important;
	margin-bottom: 0.25em;
	font-family: Verdana, Sans, Helvetica, Arial, sans-serif!important;
}
#fs-excerpt {
	font-size: 14px!important;
	padding-left: 10px;
	line-height: 1.4em;
}
.fs-comment {
	font-size: 8px!important;
	line-height: 1.2em;
	font-family: Verdana, Sans, Helvetica, Arial, sans-serif!important;
}
#fs-main ul {
	display: block;
	float: <?php echo $options['values']['fs_buttons_position']?>!important;
	clear: none!important;
	margin: 0!important;
	padding: 0!important;
	width: <?php  if ($options['values']['fs_slide_width']=='100%' || !$options['values']['fs_show_buttons']) echo '0'; else echo $options['values']['fs_buttons_width']?>!important;
	height: 100%;
	list-style: none!important;
	background: <?php echo $options['values']['fs_ul_background_color']?> <?php 
				if ($options['values']['fs_ul_background_image'] != '' && $options['values']['fs_ul_background_image'] != 'none') {
					$url = $options['values']['fs_ul_background_image'];
					(is_ssl()) ? $url = str_replace('http://','https://',$url) : $url = str_replace('https://','http://',$url); echo 'url('.$url.')';
				} else {
					echo 'none';
				}
			  ?> repeat scroll center center!important;
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	z-index: 99999;
}
#fs-main li {
	display: block!important;
	padding: 5px!important;
	margin: 0!important;
	width: 100%!important;
	height: 55px!important;
	-moz-border-radius: 3px;
	-khtml-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	cursor: pointer;
}
#fs-main li:before { content:""; }
#fs-main li:after { content:""; }

.fs-entry {
	background: <?php echo $options['values']['fs_button_normal_color']?> <?php 
				if ($options['values']['fs_button_background_image'] != '' && $options['values']['fs_button_background_image'] != 'none') {
					$url = $options['values']['fs_button_background_image'];
					(is_ssl()) ? $url = str_replace('http://','https://',$url) : $url = str_replace('https://','http://',$url); echo 'url('.$url.')';
				} else {
					echo 'none';
				}
			  ?> repeat scroll center center!important;
	margin: 0!important;
	overflow: hidden!important;
}
.fs-entry:hover {
	background: <?php echo $options['values']['fs_button_hover_color']?> <?php 
				if ($options['values']['fs_button_hover_background_image'] != '' && $options['values']['fs_button_hover_background_image'] != 'none') {
					$url = $options['values']['fs_button_hover_background_image'];
					(is_ssl()) ? $url = str_replace('http://','https://',$url) : $url = str_replace('https://','http://',$url); echo 'url('.$url.')';
				} else {
					echo 'none';
				}
			  ?> repeat scroll center center!important;
}
.fs-current {
	background: <?php echo $options['values']['fs_button_current_color']?> <?php 
				if ($options['values']['fs_current_button_background_image'] != '' && $options['values']['fs_current_button_background_image'] != 'none') {
					$url = $options['values']['fs_current_button_background_image'];
					(is_ssl()) ? $url = str_replace('http://','https://',$url) : $url = str_replace('https://','http://',$url); echo 'url('.$url.')';
				} else {
					echo 'none';
				}
			  ?> repeat scroll center center!important;
}
.fs-skip {
	position: absolute!important;
	top: -300000px!important;
	width: 0!important;
	height: 0!important;
}
<?php
$css = ob_get_contents();
define('FS_CSS',$css);
ob_end_clean();
?>
<style type="text/css">
	<?php echo str_replace("\n",' ',str_replace("\n\t",' ',str_replace('"','\"',$css))); ?>
	
</style>
<!--  /added by plugin FrontpageSlideshow -->

<?php 
}

function frontpageSlideshow_dedicated_shortcode ($attributes, $content=null) {
	global $fs_already_displayed;

	$options = frontpageSlideshow_get_options(); // get default or tweaked options

	// dont do anything if
	// 	- the slideshow has already been displayed
	//	- the slideshow has not been activated
	//	- the shortcode option is not activated
	// parse the other eventually nested shortcodes and display the enventualy specified content
	if ($fs_already_displayed || !$options['values']['fs_is_activated'] || $options['values']['fs_insert']!='shortcode') return do_shortcode($content);

	$options['values'] = shortcode_atts($options['values'], $attributes);
	$force_display_if_shortcode = true;
//	frontpageSlideshow_header(true,$options);
	return frontpageSlideshow('',true,$options);
}

class frontpageSlideshow_Widget extends WP_Widget {

	function frontpageSlideshow_Widget() {
		$widget_ops = array('classname' => 'widget_text', 'description' => __('Arbitrary text or HTML'));
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('text', __('Text'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		$text = apply_filters( 'widget_text', $instance['text'] );
		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
			<div class="textwidget"><?php echo $instance['filter'] ? wpautop(do_shortcode($text)) : do_shortcode($text); ?></div>
		<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( current_user_can('unfiltered_html') )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = wp_filter_post_kses( $new_instance['text'] );
		$instance['filter'] = isset($new_instance['filter']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = strip_tags($instance['title']);
		$text = format_to_edit($instance['text']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>

		<p><input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked($instance['filter']); ?> />&nbsp;<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs.'); ?></label></p>
<?php
	}
}




function frontpageSlideshow_get_options($get_defaults=false,$return_unique=null) {
	$defaults = array (
				'values' => array (
					'fs_is_activated' 		=> 0,
					'fs_cats' 			=> array ('1'),
					'fs_slides' 			=> 4,
					'fs_show_buttons' 		=> 1,
					'fs_shortcode' 			=> 'FrontpageSlideshow',
					'fs_insert' 			=> 'frontpage',
					'fs_default_link_to_page_link'	=> 0,
					'fs_main_width'			=> '732px',
					'fs_main_height'		=> '260px',
					'fs_slide_width'		=> '80%',
					'fs_buttons_width'		=> '20%',
					'fs_placeholder_height'		=> '195px',
					'fs_button_normal_color'	=> '#000',
					'fs_button_hover_color'		=> '#333',
					'fs_button_current_color'	=> '#444',
					'fs_buttons_position'		=> 'right',
					'fs_ul_color'			=> '#000',
					'fs_text_bgcolor'		=> '#000',
					'fs_text_opacity'		=> '75%',
					'fs_main_color'			=> '#000',
					'fs_font_color'			=> '#fff',
					'fs_main_border_color'		=> '#444',
					'fs_transition'			=> 'fade',
					//'fs_transition_on'		=> 'fade',
					'fs_orderby'			=> 'ID',
					'fs_order'			=> 'DESC',
					'fs_show_comment'		=> 1,
					'fs_main_background_image'	=> '',
					'fs_ul_background_image'	=> '',
					'fs_button_background_image'	=> '',
					'fs_button_hover_background_image'	=> '',
					'fs_current_button_background_image'	=> '',
					'fs_loader_image'		=> get_bloginfo('url').'/wp-content/plugins/frontpage-slideshow/images/loading_black.gif',
				));
	$infos = array (
				'types' => array (
					'fs_is_activated' 		=> 'bool',
					'fs_cats' 			=> 'array of cats',
					'fs_slides' 			=> 'integer',
					'fs_show_buttons' 		=> 'bool',
					'fs_shortcode' 			=> 'shortcode',
					'fs_insert' 			=> 'insert-mode',
					'fs_default_link_to_page_link'	=> 'bool',
					'fs_main_width'			=> 'css-size',
					'fs_main_height'		=> 'css-size',
					'fs_slide_width'		=> 'css-size',
					'fs_buttons_width'		=> 'css-size',
					'fs_placeholder_height'		=> 'css-size',
					'fs_button_normal_color'	=> 'css-color',
					'fs_button_hover_color'		=> 'css-color',
					'fs_button_current_color'	=> 'css-color',
					'fs_buttons_position'		=> 'left-right',
					'fs_ul_color'			=> 'css-color',
					'fs_text_bgcolor'		=> 'css-color',
					'fs_text_opacity'		=> 'percent',
					'fs_main_color'			=> 'css-color',
					'fs_font_color'			=> 'css-color',
					'fs_main_border_color'		=> 'css-color',
					'fs_transition'			=> 'transition',
					//'fs_transition_on'		=> 'transition_on',
					'fs_orderby'			=> 'orderby',
					'fs_order'			=> 'order',
					'fs_show_comment'		=> 'bool',
					'fs_main_background_image'	=> 'variant',
					'fs_ul_background_image'	=> 'variant',
					'fs_button_background_image'	=> 'variant',
					'fs_button_hover_background_image'	=> 'variant',
					'fs_current_button_background_image'	=> 'variant',
					'fs_loader_image'		=> 'variant',
				),
				'names' => array (
					'fs_is_activated' 		=> __('The activation command','frontpage-slideshow'),
					'fs_cats' 			=> __('The categories','frontpage-slideshow'),
					'fs_slides' 			=> __('The number of slides to show','frontpage-slideshow'),
					'fs_show_buttons' 		=> __('The "Show buttons" option','frontpage-slideshow'),
					'fs_shortcode' 			=> __('The "Custom shortcode" option','frontpage-slideshow'),
					'fs_insert' 			=> __('The "how to include" mode','frontpage-slideshow'),
					'fs_default_link_to_page_link'	=> __('The "default link behavior" mode','frontpage-slideshow'),
					'fs_main_width'			=> __('The slideshow width','frontpage-slideshow'),
					'fs_main_height'		=> __('The slideshow height','frontpage-slideshow'),
					'fs_slide_width'		=> __('The image width','frontpage-slideshow'),
					'fs_buttons_width'		=> __('The buttons width','frontpage-slideshow'),
					'fs_placeholder_height'		=> __('The main text top','frontpage-slideshow'),
					'fs_button_normal_color'	=> __('The buttons\' color (normal state)','frontpage-slideshow'),
					'fs_button_hover_color'		=> __('The buttons\' color (hover)','frontpage-slideshow'),
					'fs_button_current_color'	=> __('The buttons\' color (current)','frontpage-slideshow'),
					'fs_buttons_position'		=> __('The buttons position','frontpage-slideshow'),
					'fs_ul_color'			=> __('The buttons bar background color','frontpage-slideshow'),
					'fs_text_bgcolor'		=> __('The main text background color','frontpage-slideshow'),
					'fs_text_opacity'		=> __('The main text opacity','frontpage-slideshow'),
					'fs_main_color'			=> __('The slideshow background color','frontpage-slideshow'),
					'fs_font_color'			=> __('The font color','frontpage-slideshow'),
					'fs_main_border_color'		=> __('The slideshow border color','frontpage-slideshow'),
					'fs_transition'			=> __('The transition mode at the end of a slide','frontpage-slideshow'),
					//'fs_transition_on'		=> __('The transition mode at the begining of a slide','frontpage-slideshow'),
					'fs_orderby'			=> __('The slide order base','frontpage-slideshow'),
					'fs_order'			=> __('The slide order','frontpage-slideshow'),
					'fs_order'			=> __('The show comment option','frontpage-slideshow'),
					'fs_main_background_image'	=> __('The slideshow background image','frontpage-slideshow'),
					'fs_ul_background_image'	=> __('The buttons bar background image','frontpage-slideshow'),
					'fs_button_background_image'	=> __('The buttons background image','frontpage-slideshow'),
					'fs_button_hover_background_image'	=> __('The hovered button background image','frontpage-slideshow'),
					'fs_current_button_background_image'	=> __('The current button background image','frontpage-slideshow'),
					'fs_loader_image'		=> __('The loader animation image','frontpage-slideshow'),
				),
			  );

	if ($get_defaults) {
		$options = $defaults;
	} else {
		$options = get_option('frontpage-slideshow',$defaults);
	}
	if (!is_null($return_unique) && isset($options['values'][$return_unique])) return $options['values'][$return_unique];

	return $options + $infos;
}

/******************************************************************************/
/*	Administration page						      */
/******************************************************************************/

function frontpageSlideshow_admin_menu() {
	add_options_page('Frontpage Slideshow', 'Frontpage Slideshow', 8, 'frontpage-slideshow', 'frontpageSlideshow_admin_options');
}
function frontpageSlideshow_validate_options() {
	$options = frontpageSlideshow_get_options();
	$submit_buttons = array('fs_submit','fs_preview','fs_reset_preview','fr_reset');
	$bad_values = array();
	foreach($_POST as $key => $val) {
		if (!in_array($key,$submit_buttons)) {
			$value_ok = false;
			switch ($options['types'][$key]) {
				case 'array of cats': 
					if (!is_array($val)) {
						$bad_values[] = $options['names'][$key];
					} else {
						$cats = get_categories('hide_empty=0&depth=1');
						$cats_ = array();
						foreach($cats as $c) {
							$cats_[] = $c->cat_ID;
						}
						unset ($cats);
						foreach ($val as $v) {
							if (!in_array($v,$cats_)) {
								$bad_values[] = $options['names'][$key];
								$value_ok = false;
								break;
							} else {
								$value_ok = true;
							}
						}
					}
					break;
				case 'integer': 
					if (!preg_match('/^[1-9]$/i',trim($val))) {
						$bad_values[] = $options['names'][$key];
					} else {
						$value_ok = true;
					}
					break;
				case 'bool': 
					if (is_bool($val) || $val ==  1 || $val == 0) {
						$value_ok = true;
					} else {
						$bad_values[] = $options['names'][$key];
					}
					break;
				case 'css-size':
					if (!preg_match('/^-{0,1}[0-9]{1,5}\.{0,1}[0-9]{0,2} {0,}(em|in|pt|pc|cm|mm|ex|px|%){0,1}$/i',trim($val))) {
						$bad_values[] = $options['names'][$key];
					} else {
						$val = strtolower(str_replace(' ','',trim($val)));
						$value_ok = true;
					}
					break;
				case 'css-color':
					$colors = array('aqua','green','orange','white','black','lime','purple','yellow','blue','maroon','red','fuschia','navy','silver','gray','olive','teal');
					if (!preg_match('/^#([0-9A-F]{3}|[[0-9A-F]{6})$/i',trim($val)) && !in_array(strtolower(trim($val)),$colors)) {
						$bad_values[] = $options['names'][$key];
					} else {
						$val = strtolower(trim($val));
						$value_ok = true;
					}
					break;
				case 'left-right':
					$choices = array('left','right');
					if (!in_array(strtolower(trim($val)),$choices)) {
						$bad_values[] = $options['names'][$key];
					} else {
						$val = strtolower(trim($val));
						$value_ok = true;
					}
					break;
				case 'percent':
					if (!preg_match('/^[0-9]{1,3}%{0,1}$/i',trim($val))) {
						$bad_values[] = $options['names'][$key];
					} else {
						$val = str_replace('%','',trim($val)).'%';
						$value_ok = true;
					}
					break;
				case 'shortcode':
					if (strlen(trim($val))==0 && trim($_POST['fs_insert']) == 'shortcode' && !preg_match('/^[a-z0-9-_]*$/i',trim($val)) ) {
						$bad_values[] = $options['names'][$key];
					} else {
						$val = trim($val);
						$value_ok = true;
					}
					break;
				case 'insert-mode':
					$insertModes = array('frontpage', 'shortcode');
					if (!in_array(trim($val),$insertModes)) {
						$bad_values[] = $options['names'][$key];
					} else {
						$val = trim($val);
						$value_ok = true;
					}
					break;
				case 'transition':
					$transitions = array('fade', 'shrink', 'dropout', 'jumpup', 'explode', 'clip', 'dropleft', 'dropright', 'slideleft', 'slideright', 'fold', 'puff', 'random');
					if (!in_array(trim($val),$transitions)) {
						$bad_values[] = $options['names'][$key];
					} else {
						$val = trim($val);
						$value_ok = true;
					}
					break;
/*				case 'transition_on':
					$transitions = array('fade', 'shrink', 'dropout', 'jumpup', 'explode', 'clip', 'dropleft', 'dropright', 'slideleft', 'slideright', 'fold', 'puff', 'random', 'same');
					if (!in_array(trim($val),$transitions)) {
						$bad_values[] = $options['names'][$key];
					} else {
						$val = trim($val);
						$value_ok = true;
					}
					break;*/
				case 'orderby':
					$orderby = array('date', 'modified', 'menu_order', 'ID', 'rand');
					if (!in_array(trim($val),$orderby)) {
						$bad_values[] = $options['names'][$key];
					} else {
						$val = trim($val);
						$value_ok = true;
					}
					break;
				case 'order':
					$order = array('ASC', 'DESC');
					if (!in_array(trim($val),$order)) {
						$bad_values[] = $options['names'][$key];
					} else {
						$val = trim($val);
						$value_ok = true;
					}
					break;
				case 'variant':
				default:
					$val = trim($val);
					$value_ok = true;
			}
			if ($value_ok) {
				if ( is_array($val) ) {
					$options['values'][$key] = $val;
				} else {
					$options['values'][$key] = stripslashes($val);
				}
			}
		}
	}
	if (count($bad_values)) {
		$message = '<p>'.__('The following values got to be corrected : ','frontpage-slideshow').'</p><ul style="list-style:disc inside!important;margin-left: 15px;">';
		foreach($bad_values as $b) {
			$message .= '<li>'.$b.'</li>';
		}
		$message .= '</ul>';
		foreach($_POST as $key => $val) {
			if (!in_array($key,$submit_buttons)) {
				$options['values'][$key] = $val;
			}
		}

		return array('ok'=>false,'message'=>$message,'options'=>$options);
	} else {
		return array('ok'=>true,'options'=>$options);
	}
}

// utility function
function frontpageSlideshow_createShortcodeString($opt=array()) {
	$def = frontpageSlideshow_get_options(true);
	if (count($opt)==0) $opt = frontpageSlideshow_get_options();
	$argz = '';
	$dont_add=array('fs_is_activated','fs_shortcode','fs_cats','fs_insert');
	foreach ($opt['values'] as $k=>$v) {
		if (!in_array($k,$dont_add) && $def['values'][$k] != $v) $argz .= " {$k}={$v}";
	}
	return "[{$opt['values']['fs_shortcode']}{$argz}]";
}

function frontpageSlideshow_admin_options() {
	global $wp_version;
	$options = frontpageSlideshow_get_options();
	$message = '';
//	delete_option('frontpage-slideshow');
	if($_POST['fs_submit']) {
		$test = frontpageSlideshow_validate_options();
		$options = $test['options'];
		if ($test['ok']) {
			update_option('frontpage-slideshow', array('values'=>$options['values']));
			$message = '<p>'.__('The options have been updated.', 'frontpage-slideshow').'</p>';
		} else {
			$message = '<p>'.__('The options have NOT been updated.', 'frontpage-slideshow').'</p>'.$test['message'];
		}
		unset($test);
	} else if ($_POST['fs_preview']) {
		$test = frontpageSlideshow_validate_options();
		$options = $test['options'];
		if ($test['ok']) {
			$message = '<p>'.__('The preview have been updated (the options have NOT been saved yet).', 'frontpage-slideshow').'</p>';
		} else {
			$message = '<p>'.__('The preview have NOT been updated (the options have NOT been saved yet).', 'frontpage-slideshow').'</p>'.$test['message'];
		}
		unset($test);
	} else if ($_POST['fs_reset_preview']) {
		$options = frontpageSlideshow_get_options();
		$message = '<p>'.__('The preview have been updated with actual values.', 'frontpage-slideshow').'</p>';
		$message .= '<p>'.__('Note that the preview has been reseted to the ACTUAL values, not with default ones.', 'frontpage-slideshow').'</p>';
	} else if ($_POST['fs_reset']) {
		$options = frontpageSlideshow_get_options(true);
		delete_option('frontpage-slideshow');
		update_option('frontpage-slideshow', array('values'=>$options['values']));
		$message = '<p>'.__('The plugins runs now with default values.', 'frontpage-slideshow').'</p>';
	} else if ($_POST['fs_disable']) {
		$options['values']['fs_is_activated'] = 0;
		update_option('frontpage-slideshow', array('values'=>$options['values']));
		$message = '<p>'.__('The plugins has been disabled.', 'frontpage-slideshow').'</p>';
	} else if ($_POST['fs_enable']) {
		$options['values']['fs_is_activated'] = 1;
		update_option('frontpage-slideshow', array('values'=>$options['values']));
		$message = '<p>'.__('The plugins has been enabled.', 'frontpage-slideshow').'</p>';
	}

	if (!$options['values']['fs_is_activated']) $message .= '<p style="color: red; font-weight: bold;">'.__('The plugin is currently disabled.', 'frontpage-slideshow').'</p>';
	?>
	<div class="wrap">
		<div id="icon-plugins" class="icon32"><br/></div>
		<h2>Frontpage Slideshow – <?php _e('Option page','frontpage-slideshow')?></h2>
			<?php  if ($message!='') { ?>
			<div id="message" class="updated fade"><?php echo $message?></div>
			<?php  } ?>
			<div id="poststuff" class="meta-box-sortables">
				<div class="postbox closed">
					<h3><span><?php  _e('How to use - Getting help','frontpage-slideshow'); ?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><?php _e('There are 2 ways to use this plugin: ','frontpage-slideshow'); ?></p>
						<ul style="list-style: disc; padding-left: 20px;">
							<li>
								<?php _e('If you are using a static page as the front-page, use the front-page mode','frontpage-slideshow'); ?><br />
								<?php _e('With this mode, the slideshow will be automatically added in top of the front-page content, before all other content. You don\'t have anything else to do.','frontpage-slideshow'); ?><br />
							</li>
							<li>
								<?php _e('If you are not using a static page as the front-page, use the shortcode mode','frontpage-slideshow'); ?><br />
								<?php _e('With this mode, you got to put a shortcode (like [FrontpageSlideshow]) where you want the slideshow to be displayed : ','frontpage-slideshow'); ?>
								<ul style="list-style: disc; padding-left: 20px;">
									<li><?php _e('Somewhere into your posts content','frontpage-slideshow'); ?></li>
									<li><?php _e('Somewhere into some sidebar text-box','frontpage-slideshow'); ?></li>
									<li><?php _e('Everywhere else into the pages by inserting the following code snippet into your theme\'s .php files where you want the slideshow to be displayed: ','frontpage-slideshow'); ?><br />
										<pre style="background-color: #f5f5f5; border: 1px solid #dadada; padding: 11px; font-size: 11px; line-height: 1.3em;">
&lt;?php
// <?php _e('added by &lt;yourname> in order to add the slideshow using the frontpage-slideshow plugin ','frontpage-slideshow'); ?>
<br />echo do_shortcode('[FrontpageSlideshow]');
?></pre>
									</li>
								</ul>
							</li>
						</ul>
						<br />
						<p><strong><?php _e('Note that this plugin is using the Wordpress API In order to include its needed Javascript files. Some other plugins or themes that are not using that API could mess up with this plugin.','frontpage-slideshow'); ?></strong></p>
						<br />
						<p><big><strong><?php _e('Creating different slideshows with different parameters:','frontpage-slideshow'); ?></strong></big></p>
						<p><?php _e('You can use different slideshows with different parameters easily ! Simply use the shortcode way to insert slideshows, save this options, then configure the slider, make a preview, copy the shortcode relulting of those parameters, and isert this shortcode everywhere you want a slideshow to be displayed ! You can create as many different slideshow as you got posts and pages into your blog. Remember that only the fist slideshow displayed on a page will work.','frontpage-slideshow'); ?></p>
						<br />
						<p><big><strong><?php _e('In case of trouble:','frontpage-slideshow'); ?></strong></big></p>
						<ul style="list-style: disc; padding-left: 20px;">
							<li><?php _e('Make sure you have read the "How to use": ','frontpage-slideshow'); ?> <a href="http://wordpress.org/extend/plugins/frontpage-slideshow/other_notes/">http://wordpress.org/extend/plugins/frontpage-slideshow/other_notes/</a></li>
							<li><?php _e('Read this page: ','frontpage-slideshow'); ?> <a href="http://wordpress.org/support/topic/322689">http://wordpress.org/support/topic/322689</a></li>
							<li><?php _e('Look at the other support questions there: ','frontpage-slideshow'); ?> <a href="http://wordpress.org/tags/frontpage-slideshow">http://wordpress.org/tags/frontpage-slideshow</a></li>
							<li><?php _e('If you want to post a support question, create a new topic by using this link: ','frontpage-slideshow'); ?> <a href="http://wordpress.org/tags/frontpage-slideshow#postform">http://wordpress.org/tags/frontpage-slideshow#postform</a></li>
						</ul>
<?php
	$args = array('plugin' => 'frontpage-slideshow', 'plugin_version' => FRONTPAGE_SLIDESHOW_VERSION);
	$args['siteurl'] = get_option('siteurl');
	$args['admin_email'] = get_option('admin_email');
	$args['WP_version'] = $wp_version;
	$args['theme'] = get_option('template');
	$css = file_get_contents('../wp-content/themes/'.get_option('template').'/style.css');
	preg_match('#Theme URI: (.*)#i',$css,$m);
	$args['theme_URI'] = $m[1];
	$req = file_get_contents('https://www.modulaweb.fr/wp-plugins-support/?args='.urlencode(json_encode($args)));
	$req = json_decode($req, true);
	$plugin_ID = $req['ID'];
?>
						<p><big><strong><?php _e('Plugin unique ID','frontpage-slideshow'); ?></strong></big></p>
						<p>
							<?php _e('In order to faster bug reports, troubleshoot and for some statistics, some informations are collected and sent to this plugin:\'s author.','frontpage-slideshow');?><br />
							<?php _e('The informations that are sent are this site URL, this site admin email address, the Wordpress version, the used theme and its URI, and the used version of this plugin.','frontpage-slideshow');?><br />
							<?php _e('If you need help to troubleshoot, dont forget to transmit your plugin unique ID','frontpage-slideshow');?><br />
						</p>
						<p><?php _e('Your plugin unique ID is: ','frontpage-slideshow'); echo "<big><strong>{$req['ID']}</strong></big>"; ?></p>
				</div>
				</div>
				<div class="postbox">
					<h3><span><?php _e('Preview')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<?php 
							frontpageSlideshow_header(true,$options);
							echo frontpageSlideshow('',true,$options);
						?>
						<p><strong><?php _e('Important: ','frontpage-slideshow')?></strong> <?php _e('the slideshow may appear differently here and on your site due to the stylesheet of your theme.','frontpage-slideshow')?></p>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
							<input name="cmd" value="_s-xclick" type="hidden">
							<input name="hosted_button_id" value="9112063" type="hidden">
							<p>
								<?php _e('If you find this plugin useful, you can support his author by making a donation.','frontpage-slideshow'); ?> 
								<input name="submit" type="submit" class="button-primary" value="<?php _e('Donate to this plugin','frontpage-slideshow'); ?>" />
							</p>
						</form>
						<p><?php _e('Actual complete shortcode (use it to insert a slideshow with the actual settins when using the shortcode insert method):','frontpage-slideshow')?> </p>
								<pre style="overflow: auto; background-color: #f5f5f5; border: 1px solid #dadada; padding: 11px; font-size: 11px; line-height: 1.3em;">
<?php echo frontpageSlideshow_createShortcodeString($options); ?></pre>
						
					</div>
				</div>
			<form method="post">
				<div class="postbox<?php  if ($options['values']['fs_is_activated']) echo ' closed' ?>">
					<div class="handlediv" title="<?php _e('Click to open/close','frontpage-slideshow')?>"><br /></div>
					<h3><span><?php  if ($options['values']['fs_is_activated']) _e('Disable the plugin','frontpage-slideshow'); else _e('Enable the plugin','frontpage-slideshow');?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><?php 
							if ($options['values']['fs_is_activated']) {
						?><label for="fs_disable"><?php 
								_e('The plugin is currently ENABLED : you can use the following button to disable it.','frontpage-slideshow')?> 
								<input type="submit" class="button-primary" id="fs_disable" name="fs_disable" size="2" maxlength="2" value="<?php _e('Disable the plugin','frontpage-slideshow')?>" />
						<?php 
							} else {
						?><label for="fs_enable"><?php 
								_e('The plugin is currently DISABLED : you can use the following button to enable it.','frontpage-slideshow')?> 
								<input type="submit" class="button-primary" id="fs_enable" name="fs_enable" size="2" maxlength="2" value="<?php _e('Enable the plugin now !!','frontpage-slideshow')?>" />
						<?php 
							}
						?>
						</label></p>
					</div>
				</div>
				<div class="postbox closed">
					<div class="handlediv" title="<?php _e('Click to open/close','frontpage-slideshow')?>"><br /></div>
					<h3><span><?php _e('About inserting the slideshow','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><?php _e('Where to insert the slideshow ?','frontpage-slideshow')?></p><?php  echo $options['values']['fs_insert']; ?>
						<ul style="list-style: none">
							<li><label for="fs_insert_1"><input type="radio" id="fs_insert_1" name="fs_insert" value="frontpage"<?php  if ($options['values']['fs_insert']=='frontpage') echo ' checked="checked"'; ?> /> <?php _e('On front-page','frontpage-slideshow')?></label><br />
								<label for="fs_insert_shortcode">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;→ <?php _e('The slideshow will appear only on the front page when it has been configured to display a static-page only.','frontpage-slideshow')?>
							</li>
							<li><label for="fs_insert_2"><input type="radio" id="fs_insert_2" name="fs_insert" value="shortcode"<?php  if ($options['values']['fs_insert']=='shortcode') echo ' checked="checked"'; ?> /> <?php _e('Everywhere on content post (using the dedicated shortcode)','frontpage-slideshow')?></label><br />
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for="fs_insert_shortcode">→ <?php _e('Shortcode','frontpage-slideshow')?> : [<input id="fs_shortcode" name="fs_shortcode" value="<?php echo $options['values']['fs_shortcode']?>" />]</label>
								<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;→ <?php _e('Actual complete shortcode (to insert a slideshow with the actual settins):','frontpage-slideshow')?> 
								<pre style="margin-left: 47px; overflow: auto; background-color: #f5f5f5; border: 1px solid #dadada; padding: 11px; font-size: 11px; line-height: 1.3em;">
<?php echo frontpageSlideshow_createShortcodeString($options); ?></pre>
							</li>
						</ul>
						<p><?php _e('The default shortcode is [FrontpageSlideshow]. By using the shortcode, you will be able to pass some directives to the slideshow directly from the shortcode in order to override the current slideshow options.','frontpage-slideshow')?></p>
						<p><?php _e('The accepted chars are a to z 0 to 9 - (minus) and _ (underscore). ','frontpage-slideshow')?></p>
						<p><?php _e('You can use the shortcode as an enclosing one : you can put replacement content in case of the slideshow cannot be shown (if it has already been added earlier in the document flow) or is not activated. ','frontpage-slideshow')?></p>
						<p><?php _e('When using shortcode, you can use other shortcodes into the replacement content : they will be parsed well, so that you can use another plugin (a gallery for example) to show some content','frontpage-slideshow')?></p>
						<p><?php _e('Note that only one slideshow can be displayed at this time, if you need to display more than one slideshow, contact the author.','frontpage-slideshow')?></p>
					</div>
				</div>
				<div class="postbox closed">
					<div class="handlediv" title="<?php _e('Click to open/close','frontpage-slideshow')?>"><br /></div>
					<h3><span><?php _e('About categories and posts','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><?php _e('Frontpage Slideshow will look for posts to display as slides into these categories : ','frontpage-slideshow')?></p>
						<ul style="list-style: none">
						<?php 
							$cats = get_categories('hide_empty=0&depth=1');
							$count=1;
							//echo '<li><label for="fs_cats_'.$count.'"><input type="checkbox" disabled="disabled" checked="checked" id="fs_cats_'.$count.'" name="fs_cats[]" value="fs-cat"> fs-cat</label></li>';
							foreach ($cats as $c) {
//								if ($c->cat_name!='fs-cat') {
									echo '<li><label for="fs_cats_'.$count.'"><input type="checkbox" id="fs_cats_'.$count.'" name="fs_cats[]" value="' . $c->cat_ID . '"';
									if (in_array($c->cat_ID,$options['values']['fs_cats'])) echo ' checked="checked"';
									echo '> ' . $c->cat_name . '</label></li>'."\n";
									$count++;
//								}
							}
				?>
						</ul>
						<p><label for="fs_orderby"><?php _e('Slides / Posts order:','frontpage-slideshow')?> <select id="fs_orderby" name="fs_orderby">
							<option value="date"<?php  if ($options['values']['fs_orderby']=='date') echo ' selected="selected"'?>><?php  _e('date','frontpage-slideshow'); ?></option>
							<option value="modified"<?php  if ($options['values']['fs_orderby']=='modified') echo ' selected="selected"'?>><?php  _e('modification date','frontpage-slideshow'); ?></option>
							<option value="menu_order"<?php  if ($options['values']['fs_orderby']=='menu_order') echo ' selected="selected"'?>><?php  _e('specified order (menu order)','frontpage-slideshow'); ?></option>
							<option value="ID"<?php  if ($options['values']['fs_orderby']=='ID') echo ' selected="selected"'?>><?php  _e('ID','frontpage-slideshow'); ?></option>
							<option value="rand"<?php  if ($options['values']['fs_orderby']=='rand') echo ' selected="selected"'?>><?php  _e('random','frontpage-slideshow'); ?></option>
						</select></label>
						<select id="fs_order" name="fs_order">
							<option value="ASC"<?php  if ($options['values']['fs_order']=='ASC') echo ' selected="selected"'?>><?php  _e('ascending','frontpage-slideshow'); ?></option>
							<option value="DESC"<?php  if ($options['values']['fs_order']=='DESC') echo ' selected="selected"'?>><?php  _e('descending','frontpage-slideshow'); ?></option>
						</select>
						</p>
						<p><input type="submit" name="fs_preview" class="button-primary" value="<?php  _e('Preview'); ?>" /></p>
					</div>
				</div>
				<div class="postbox closed">
					<div class="handlediv" title="<?php _e('Click to open/close','frontpage-slideshow')?>"><br /></div>
					<h3><span><?php _e('About slides and buttons','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><label for="fs_slides"><?php _e('How many slides to show ?','frontpage-slideshow')?> <input type="text" id="fs_slides" name="fs_slides" size="2" maxlength="2" value="<?php echo $options['values']['fs_slides']?>" /></label></p>
						<p><label for="fs_show_buttons"><?php _e('Show buttons ?','frontpage-slideshow')?> <select id="fs_show_buttons" name="fs_show_buttons">
							<option value="1"<?php  if ($options['values']['fs_show_buttons']) echo ' selected="selected"'?>><?php  _e('Yes','frontpage-slideshow'); ?></option>
							<option value="0"<?php  if (!$options['values']['fs_show_buttons']) echo ' selected="selected"'?>><?php  _e('No','frontpage-slideshow'); ?></option>
						</select></p>
						<p><label for="fs_transition"><?php _e('Tansition mode between slides : at the end of a slide','frontpage-slideshow')?> <select id="fs_transition" name="fs_transition">
							<option value="fade"<?php  	if ($options['values']['fs_transition']=='fade') 	echo ' selected="selected"'?>><?php  _e('fade','frontpage-slideshow'); ?></option>
							<option value="shrink"<?php  	if ($options['values']['fs_transition']=='shrink') 	echo ' selected="selected"'?>><?php  _e('shrink / scale','frontpage-slideshow'); ?></option>
							<option value="dropout"<?php  	if ($options['values']['fs_transition']=='dropout') 	echo ' selected="selected"'?>><?php  _e('drop (fade and slide) out / down','frontpage-slideshow'); ?></option>
							<option value="jumpup"<?php  	if ($options['values']['fs_transition']=='jumpup') 	echo ' selected="selected"'?>><?php  _e('jump up / slide up','frontpage-slideshow'); ?></option>
							<option value="explode"<?php  	if ($options['values']['fs_transition']=='explode') 	echo ' selected="selected"'?>><?php  _e('explode','frontpage-slideshow'); ?></option>
							<option value="clip"<?php  	if ($options['values']['fs_transition']=='clip') 	echo ' selected="selected"'?>><?php  _e('clip','frontpage-slideshow'); ?></option>
							<option value="dropleft"<?php  	if ($options['values']['fs_transition']=='dropleft') 	echo ' selected="selected"'?>><?php  _e('drop (fade and slide) on left','frontpage-slideshow'); ?></option>
							<option value="dropright"<?php  if ($options['values']['fs_transition']=='dropright') 	echo ' selected="selected"'?>><?php  _e('drop (fade and slide) on right','frontpage-slideshow'); ?></option>
							<option value="slideleft"<?php  if ($options['values']['fs_transition']=='slideleft') 	echo ' selected="selected"'?>><?php  _e('slide on left','frontpage-slideshow'); ?></option>
							<option value="slideright"<?php if ($options['values']['fs_transition']=='slideright') 	echo ' selected="selected"'?>><?php  _e('slide on right','frontpage-slideshow'); ?></option>
							<option value="fold"<?php  	if ($options['values']['fs_transition']=='fold') 	echo ' selected="selected"'?>><?php  _e('fold','frontpage-slideshow'); ?></option>
							<option value="random"<?php  	if ($options['values']['fs_transition']=='random') 	echo ' selected="selected"'?>><?php  _e('random effect','frontpage-slideshow'); ?></option>
						</select></p>
						<!--p><label for="fs_transition_on"><?php _e('Tansition mode between slides : at the begining','frontpage-slideshow')?> <select id="fs_transition_on" name="fs_transition_on">
							<option value="fade"<?php 	if ($options['values']['fs_transition_on']=='fade') 	echo ' selected="selected"'?>><?php  _e('fade','frontpage-slideshow'); ?></option>
							<option value="shrink"<?php 	if ($options['values']['fs_transition_on']=='shrink') 	echo ' selected="selected"'?>><?php  _e('shrink / scale','frontpage-slideshow'); ?></option>
							<option value="dropout"<?php 	if ($options['values']['fs_transition_on']=='dropout') 	echo ' selected="selected"'?>><?php  _e('drop (fade and slide) out / down','frontpage-slideshow'); ?></option>
							<option value="jumpup"<?php 	if ($options['values']['fs_transition_on']=='jumpup') 	echo ' selected="selected"'?>><?php  _e('jump up / slide up','frontpage-slideshow'); ?></option>
							<option value="explode"<?php 	if ($options['values']['fs_transition_on']=='explode') 	echo ' selected="selected"'?>><?php  _e('explode','frontpage-slideshow'); ?></option>
							<option value="clip"<?php 	if ($options['values']['fs_transition_on']=='clip') 	echo ' selected="selected"'?>><?php  _e('clip','frontpage-slideshow'); ?></option>
							<option value="dropleft"<?php 	if ($options['values']['fs_transition_on']=='dropleft') echo ' selected="selected"'?>><?php  _e('drop (fade and slide) on left','frontpage-slideshow'); ?></option>
							<option value="dropright"<?php  if ($options['values']['fs_transition_on']=='dropright') echo ' selected="selected"'?>><?php  _e('drop (fade and slide) on right','frontpage-slideshow'); ?></option>
							<option value="slideleft"<?php  if ($options['values']['fs_transition_on']=='slideleft') echo ' selected="selected"'?>><?php  _e('slide on left','frontpage-slideshow'); ?></option>
							<option value="slideright"<?php if ($options['values']['fs_transition_on']=='slideright') echo ' selected="selected"'?>><?php  _e('slide on right','frontpage-slideshow'); ?></option>
							<option value="fold"<?php  	if ($options['values']['fs_transition_on']=='fold') 	echo ' selected="selected"'?>><?php  _e('fold','frontpage-slideshow'); ?></option>
							<option value="random"<?php  	if ($options['values']['fs_transition_on']=='random') 	echo ' selected="selected"'?>><?php  _e('random effect','frontpage-slideshow'); ?></option>
							<option value="same"<?php  	if ($options['values']['fs_transition_on']=='same') 	echo ' selected="selected"'?>><?php  _e('same as the end of slide','frontpage-slideshow'); ?></option>
						</select></p-->
						<p><label for="fs_show_comment"><?php _e('Show slide comment zone ?','frontpage-slideshow')?> <select id="fs_show_comment" name="fs_show_comment">
							<option value="1"<?php  if ($options['values']['fs_show_comment']) echo ' selected="selected"'?>><?php  _e('Yes','frontpage-slideshow'); ?></option>
							<option value="0"<?php  if (!$options['values']['fs_show_comment']) echo ' selected="selected"'?>><?php  _e('No','frontpage-slideshow'); ?></option>
						</select></p>
						<p><input type="submit" name="fs_preview" class="button-primary" value="<?php  _e('Preview'); ?>" /></p>
					</div>
				</div>
				<div class="postbox closed">
					<div class="handlediv" title="<?php _e('Click to open/close','frontpage-slideshow')?>"><br /></div>
					<h3><span><?php _e('About default link','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><label for="fs_default_link_to_page_link"><select id="fs_default_link_to_page_link" name="fs_default_link_to_page_link">
							<option value="0"<?php  if (!$options['values']['fs_default_link_to_page_link']) echo ' selected="selected"'?>><?php  _e('If no link is specidied : dont use the slide URL','frontpage-slideshow'); ?></option>
							<option value="1"<?php  if ($options['values']['fs_default_link_to_page_link']) echo ' selected="selected"'?>><?php  _e('If no link is specidied : use the slide URL','frontpage-slideshow'); ?></option>
						</select></p>
						<p><input type="submit" name="fs_preview" class="button-primary" value="<?php  _e('Preview'); ?>" /></p>
					</div>
				</div>
				<div class="postbox closed">
					<div class="handlediv" title="<?php _e('Click to open/close','frontpage-slideshow')?>"><br /></div>
					<h3><span><?php _e('About sizes and positions','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><label for="fs_main_width"><?php _e('Slideshow width :','frontpage-slideshow')?> <input type="text" id="fs_main_width" name="fs_main_width" size="5" value="<?php echo $options['values']['fs_main_width']?>" /></label></p>
						<p><label for="fs_main_height"><?php _e('Slideshow height :','frontpage-slideshow')?> <input type="text" id="fs_main_height" name="fs_main_height" size="5" value="<?php echo $options['values']['fs_main_height']?>" /></label></p>
						<p><label for="fs_slide_width"><?php _e('Image width :','frontpage-slideshow')?> <input type="text" id="fs_slide_width" name="fs_slide_width" size="5" value="<?php echo $options['values']['fs_slide_width']?>" /></label></p>
						<p><label for="fs_buttons_width"><?php _e('Buttons width :','frontpage-slideshow')?> <input type="text" id="fs_buttons_width" name="fs_buttons_width" size="5" value="<?php echo $options['values']['fs_buttons_width']?>" /></label></p>
						<p><label for="fs_placeholder_height"><?php _e('Main text top :','frontpage-slideshow')?> <input type="text" id="fs_placeholder_height" name="fs_placeholder_height" size="5" value="<?php echo $options['values']['fs_placeholder_height']?>" /></label></p>
						<p><label for="fs_buttons_position"><?php _e('Buttons position :','frontpage-slideshow')?> <select id="fs_buttons_position" name="fs_buttons_position">
							<option value="right"<?php  if ($options['values']['fs_buttons_position']=='right') echo ' selected="selected"';?>><?php  _e('right','frontpage-slideshow') ?></option>
							<option value="left"<?php  if ($options['values']['fs_buttons_position']=='left') echo ' selected="selected"';?>><?php  _e('left','frontpage-slideshow') ?></option>
						</select></label></p>
						<p><input type="submit" name="fs_preview" class="button-primary" value="<?php  _e('Preview'); ?>" /></p>
					</div>
				</div>
				<div class="postbox closed">
					<div class="handlediv" title="<?php _e('Click to open/close','frontpage-slideshow')?>"><br /></div>
					<h3><span><?php _e('About colors and opacities','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><label for="fs_main_color"><?php _e('Slideshow background color','frontpage-slideshow')?> <input type="text" id="fs_main_color" name="fs_main_color" class="colorpicker" size="15" value="<?php echo $options['values']['fs_main_color']?>" /></label></p>
						<p><label for="fs_main_border_color"><?php _e('Slideshow border color','frontpage-slideshow')?> <input type="text" id="fs_main_border_color" name="fs_main_border_color" class="colorpicker" size="15" value="<?php echo $options['values']['fs_main_border_color']?>" /></label></p>
						<p><label for="fs_font_color"><?php _e('Font color','frontpage-slideshow')?> <input type="text" id="fs_font_color" name="fs_font_color" class="colorpicker" size="15" value="<?php echo $options['values']['fs_font_color']?>" /></label></p>
						<p><label for="fs_ul_color"><?php _e('Buttons bar\'s color','frontpage-slideshow')?> <input type="text" id="fs_ul_color" name="fs_ul_color" class="colorpicker" size="15" value="<?php echo $options['values']['fs_ul_color']?>" /></label></p>
						<p><label for="fs_button_normal_color"><?php _e('Buttons\' color (normal state)','frontpage-slideshow')?> <input type="text" id="fs_button_normal_color" name="fs_button_normal_color" class="colorpicker" size="15" value="<?php echo $options['values']['fs_button_normal_color']?>" /></label></p>
						<p><label for="fs_button_hover_color"><?php _e('Buttons\' color (hover)','frontpage-slideshow')?> <input type="text" id="fs_button_hover_color" name="fs_button_hover_color" class="colorpicker" size="15" value="<?php echo $options['values']['fs_button_hover_color']?>" /></label></p>
						<p><label for="fs_button_current_color"><?php _e('Buttons\' color (current)','frontpage-slideshow')?> <input type="text" id="fs_button_current_color" name="fs_button_current_color" class="colorpicker" size="15" value="<?php echo $options['values']['fs_button_current_color']?>" /></label></p>
						<p><label for="fs_text_bgcolor"><?php _e('Main text background color','frontpage-slideshow')?> <input type="text" id="fs_text_bgcolor" name="fs_text_bgcolor" class="colorpicker" size="15" value="<?php echo $options['values']['fs_text_bgcolor']?>" /></label></p>
						<p><label for="fs_text_opacity"><?php _e('Main text opacity','frontpage-slideshow')?> <input type="text" id="fs_text_opacity" name="fs_text_opacity" size="15" value="<?php echo $options['values']['fs_text_opacity']?>" /></label></p>
						<p><input type="submit" name="fs_preview" class="button-primary" value="<?php  _e('Preview'); ?>" /></p>
					</div>
				</div>
				<div class="postbox closed">
					<div class="handlediv" title="<?php _e('Click to open/close','frontpage-slideshow')?>"><br /></div>
					<h3><span><?php _e('About background images / textures and loader animation','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
					
					<?php
						$attachments = get_children(array(
									'post_type'		=> 'attachment',
									'post_mime_type' 	=> 'image',
									'post_status' 		=> null,
									'post_parent'		=> null,
								));
						$pics = '';
						if (is_array($attachments) && count($attachments)) {
							foreach ($attachments as $attachment) {
								$pics.= '<img class="draggable" style="border: 1px solid transparent; cursor: pointer;" src="'.$attachment->guid.'" width="100" onmouseover="jQuery(this).draggable({revert: true});" /> ';
							}
						} else {
							$pics = __('No images found.','frontpage-slideshow');
						}
						echo '<p>'._e('Drag\'n\'drop a picture to the place you want it to be to specify a background image/texture or loader.','frontpage-slideshow').'</p>';
						echo '<textarea id="fs-pictures-chooser-keeper" style="display: none!important;">'.$pics.'</textarea><div id="background_images_selector" style="display: block; width: 100%; border: 1px solid #dfdfdf;"><a href="#" onclick="jQuery(this).parent().html(jQuery(\'#fs-pictures-chooser-keeper\').val()); return false;">'.__('Click here to load the image selector and choose an image.').'</a></div>';
						
						$image_selectors = array(
							array(
								'id' => 'fs_main_background_image',
								'message' => __('Drop here the image you want to use as slideshow background','frontpage-slideshow'),
								'name' => __('Slideshow background image','frontpage-slideshow'),
							),
							array(
								'id' => 'fs_ul_background_image',
								'message' => __('Drop here the image you want to use as buttons bar background','frontpage-slideshow'),
								'name' => __('Buttons bar background image','frontpage-slideshow'),
							),
							array(
								'id' => 'fs_button_background_image',
								'message' => __('Drop here the image you want to use as buttons background','frontpage-slideshow'),
								'name' => __('Buttons background image','frontpage-slideshow'),
							),
							array(
								'id' => 'fs_button_hover_background_image',
								'message' => __('Drop here the image you want to use as hovered button background','frontpage-slideshow'),
								'name' => __('Hovered button background image','frontpage-slideshow'),
							),
							array(
								'id' => 'fs_current_button_background_image',
								'message' => __('Drop here the image you want to use as active button background','frontpage-slideshow'),
								'name' => __('Current button background image','frontpage-slideshow'),
							),
							array(
								'id' => 'fs_loader_image',
								'message' => __('Drop here the image you want to use as loader animation','frontpage-slideshow'),
								'name' => __('Loader animation','frontpage-slideshow'),
							),
						);
						$size = floor(100 / count($image_selectors)) - 1;
						foreach ($image_selectors as $selector) {
					?>
						<div id="<?php echo $selector['id']; ?>_droppable" class="droppable" style="width: <?php echo $size; ?>%; margin: 0.2%; padding: 3px; border: solid 1px #aaa; text-align: center; float: left;">
							<label for="<?php echo $selector['id']; ?>"><?php 
								if ($options['values'][$selector['id']]!='') {
									echo $selector['name']; 
								} else {
									echo $selector['message']; 
								}
							?></label>
							<div style="width: 100%; height: 100px; border: solid 1px #ddd;<?php
								if ($options['values'][$selector['id']]!='') {
									echo ' background-image: url('.$options['values'][$selector['id']].');';
								} else {
									if ($selector['id'] == 'fs_loader_image') {
										$url = get_bloginfo('url').'/wp-content/plugins/frontpage-slideshow/images/loading_black.gif';
										(is_ssl()) ? $url = str_replace('http://','https://',$url) : $url = str_replace('https://','http://',$url); 
										echo ' background-image: url('.$url.'); background-repeat: no-repeat!important; background-position: center center;';
										$options['values'][$selector['id']] = $url;
									} else {
										echo ' display: none;';
									}
								}
							?>"></div>
							<input type="text" title="<?php _e('Type here the URL of an external image','frontpage-slideshow')?>" name="<?php echo $selector['id']; ?>" id="<?php echo $selector['id']; ?>" value="<?php echo $options['values'][$selector['id']]; ?>" style="width: 100%" />
							<a href="#" onclick="if (confirm('<?php _e('Press OK to reset','frontpage-slideshow'); ?>')) {<?php
								if ($selector['id'] != 'fs_loader_image') {
							?>jQuery(this).parent().find('input').val(''); jQuery(this).parent().find('div').hide('clip'); jQuery(this).parent().find('div').css('background-image','none'); jQuery(this).parent().find('label').html('<?php echo $selector['message']; ?>');<?php
								} else {
							?>jQuery(this).parent().find('input').val('<?php
									$url = get_bloginfo('url').'/wp-content/plugins/frontpage-slideshow/images/loading_black.gif';
									(is_ssl()) ? $url = str_replace('http://','https://',$url) : $url = str_replace('https://','http://',$url); echo $url;
							?>'); jQuery(this).parent().find('div').hide('clip'); jQuery(this).parent().find('div').css('background-image','url(<?php echo $url; ?>)'); jQuery(this).find('div').css('background-repeat', 'no-repeat'); jQuery(this).find('div').css('background-position', 'center center'); jQuery(this).parent().find('div').show('clip');<?php
								
								}
							?>} return false;">Reset</a>
						</div>
					<?php
						}
					?>
						<div style="clear: both"></div>
						<div><?php _e('You can use the following service to create a custom loader animation :','frontpage-slideshow'); ?> <a href="http://www.ajaxload.info/" target="_blank">Ajaxload</a></div>
						<script type="text/javascript">
							// <![CDATA[
							jQuery(".droppable").each(function() {
								jQuery(this).droppable({
									accept: '#background_images_selector img',
									activeClass: 'ui-state-hover',
									hoverClass: 'ui-state-active',
									over: function(event, ui) {
										jQuery(this).css('background-color','#cfffcf');
									},
									out: function(event, ui) {
										jQuery(this).css('background-color','inherit');
									},
									drop: function(event, ui) {
										switch (jQuery(this).attr('id')) {
											<?php
												foreach ($image_selectors as $selector) {
											?>
											case '<?php echo $selector['id']; ?>_droppable':
												jQuery(this).find('label').html('<?php echo $selector['name']; ?>');
												break;
											<?php
												}
											?>
						
										}
										jQuery(this).find('label').html('<?php _e('Slideshow background image','frontpage-slideshow')?>');
										jQuery(this).find('div').css('background-image','url('+ui.draggable.attr('src')+')');
										if (jQuery(this).attr('id') == 'fs_loader_image_droppable') {
											jQuery(this).find('div').css('background-repeat', 'no-repeat');
											jQuery(this).find('div').css('background-position', 'center center');
										}
										if (jQuery(this).find('div').css('display') == 'none') jQuery(this).find('div').show('clip');
										jQuery(this).find('input').val(ui.draggable.attr('src'));
										jQuery(this).css('background-color','inherit');
									}
								});
							});
							//]]>
						</script>
						<p><input type="submit" name="fs_preview" class="button-primary" value="<?php  _e('Preview'); ?>" /></p>
					</div>
				</div>
				<div class="postbox closed">
					<div class="handlediv" title="<?php _e('Click to open/close','frontpage-slideshow')?>"><br /></div>
					<h3><span><?php _e('Reset preview or plugin','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><label for="fs_reset_preview"><?php _e('Use this button to reset the preview to the actual active configuration.','frontpage-slideshow')?> <input type="submit" id="fs_reset_preview" name="fs_reset_preview" class="button-primary" value="<?php  _e('Reset preview','frontpage-slideshow'); ?>" /></label></p>
						<p><label for="fs_reset"><?php _e('Use this button to reset the plugin to its default configuration.','frontpage-slideshow')?> <input type="submit" id="fs_reset" name="fs_reset" class="button-primary" value="<?php  _e('Reset the plugin','frontpage-slideshow'); ?>" onclick="if(!confirm('<?php _e('There will be no way back !!!','frontpage-slideshow')?>')) return false;" onkeypress="if(!confirm('<?php _e('There will be no way back !!!','frontpage-slideshow')?>')) return false;" /></label></p>
					</div>
				</div>
				<p><label for="fs_submit"><?php _e('When you are satified by the settings, you can press this button :','frontpage-slideshow')?>
				<input type="submit" id="fs_submit" name="fs_submit" class="button-primary" value="<?php  _e('Save the settings and apply them immediately','frontpage-slideshow'); ?>" onclick="if(!confirm('<?php _e('The changes will be seen immediately !','frontpage-slideshow')?>')) return false;" onkeypress="if(!confirm('<?php _e('The changes will be seen immediately !','frontpage-slideshow')?>')) return false;" /></label></p>
			</div>
		</form>
	</div>
	<div id="CLCP" class="CLCP"></div>
 	<script type="text/javascript">
		// <![CDATA[
		jQuery('.postbox h3').prepend('<a class="togbox">+</a> ');
		jQuery('.postbox div.handlediv').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
		jQuery('.postbox h3').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
		jQuery('.postbox.close-me').each(function(){
			jQuery(this).addClass("closed");
		});
		//]]>
	</script>

	<?php 
}
/******************************************************************************/
/* Meta box displayed on the edit post page				      */
/******************************************************************************/
$frontpageSlideshow_meta_boxes = array(
					'fs-title' => array(
								'name' => 'fs-title',
								'title' => __('Slide title','frontpage-slideshow'),
								'description' => __('The title of the slide : if none is given, the post title is used','frontpage-slideshow'),
								),
					'fs-picture' => array(
								'name' => 'fs-picture',
								'type' => 'picture',
								'title' => __('Slide picture','frontpage-slideshow'),
								'description' => __('You can also use the <em>Add an Image</em> button, upload an image and paste the URL here, or use an external picture.<br />If you leave this blank, the first image found into the post content will be used.','frontpage-slideshow'),
								),
					'fs-comment' => array(
								'name' => 'fs-comment',
								'title' => __('Slide comment','frontpage-slideshow'),
								'description' => __('This comment will be displayed onto the picture. Leave blank to dont display a comment.','frontpage-slideshow'),
								),
					'fs-button-comment' => array(
								'name' => 'fs-button-comment',
								'title' => __('Slide button-comment','frontpage-slideshow'),
								'description' => __('This comment will be displayed into the button, right under the title.','frontpage-slideshow'),
								),
					'fs-link' => array(
								'name' => 'fs-link',
								'title' => __('Slide link','frontpage-slideshow'),
								'description' => __('When the user is clicking onto the picture, this URI is used. Leave blank to set this post link as the slide link (if this option is activated into <a href="options-general.php?page=frontpage-slideshow">the plugin admin page</a>)','frontpage-slideshow'),
								),
					);
function frontpageSlideshow_meta_boxes() {
	global $post, $frontpageSlideshow_meta_boxes;
	
?>
		<p><?php _e('All those options will be savend when you will save the changes made on this post');?></p>
		<?php echo '<input type="hidden" name="'.$meta_box['name'].'_noncename" id="'.$meta_box['name'].'_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />'; ?>
		<table class="widefat" cellspacing="0" width="100%" id="inactive-plugins-table">
		
			<tbody class="plugins">
<?php
	
			foreach ($frontpageSlideshow_meta_boxes as $meta_box) {
				$meta_box_value = get_post_meta($post->ID, $pre.'_value', true);
				
				if ($meta_box_value == "")
					$meta_box_value = $meta_box['std'];
				
?><tr>
					<td width="100" align="center" style="border-bottom: 1px solid #dfdfdf; overflow: auto;">
<?php
					echo '<h2>'.$meta_box['title'].'</h2>';
?>	</td>
					<td style="border-bottom: 1px solid #dfdfdf;">
<?php
					if ($meta_box['type'] == 'picture') {
						$attachments = get_children(array(
									'post_type'		=> 'attachment',
									'post_mime_type' 	=> 'image',
									'post_status' 		=> null,
									'post_parent'		=> null,
								));
						$pics = '';
						if (is_array($attachments) && count($attachments)) {
							foreach ($attachments as $attachment) {
								$pics.= '<img style="border: 1px solid transparent; cursor: pointer;';
								if (get_post_meta($post->ID, $meta_box['name'], true) == $attachment->guid) $pics.= ' border-color: red;';
								$pics.= '" onclick="jQuery(\'#'.$meta_box['name'].'\').val(this.src); this.style.borderColor=\'red\';" onmouseout="if (jQuery(\'#'.$meta_box['name'].'\').val() != this.src) this.style.borderColor=\'transparent\';" onmouseover="if (jQuery(\'#'.$meta_box['name'].'\').val() != this.src) this.style.borderColor=\'cyan\';" src="'.$attachment->guid.'" width="100" /> ';
							}
						} else {
							$pics = __('No images found.','frontpage-slideshow');
						}
						echo '<p>'.__('Click on one of the following image to choose the slide picture.').'</p>';
						echo '<textarea id="fs-pictures-chooser-keeper" style="display: none!important;">'.$pics.'</textarea><div style="display: block; overflow:hidden; overflow-x: hidden; overflow-y: auto; height: 100px; width: 100%; border: 1px solid #dfdfdf;"><a href="#" onclick="jQuery(this).parent().html(jQuery(\'#fs-pictures-chooser-keeper\').val()); return false;" onkeypress="jQuery(this).parent().html(jQuery(\'#fs-pictures-chooser-keeper\').val()); return false;">'.__('Click here to load the image selector and choose an image.').'</a></div>';
						echo '<p><a href="#" target="_blank" onclick="if (jQuery(\'#'.$meta_box['name'].'\').val() !=\'\') { this.href=jQuery(\'#'.$meta_box['name'].'\').val(); this.title=jQuery(\'#'.$meta_box['name'].'\').val(); } else { alert(\''.__('No explicitely specified picture for this post, no preview...\nThe first picture inserted in this post will be used.').'\'); this.href=\'#\'; return false; }">'.__('Preview the current picture if one is explicitely specified in th field below.').'</a></p>';
					}
					if ($meta_box['name'] == 'fs-link') {
						$attachments = array_merge(
								get_children(array(
									'post_type' 	=> 'page',
									'post_parent'		=> null,
								)),
								get_children(array(
									'post_type' 	=> 'post',
									'post_parent'		=> null,
								))
								);
						$posts = '';
						foreach ($attachments as $attachment) {
							$permalink = get_permalink($attachment->ID);
							$posts .= '<option value="'.$permalink.'"';
							if (get_post_meta($post->ID, $meta_box['name'], true) != '' && (get_post_meta($post->ID, $meta_box['name'], true) == $attachment->guid || get_post_meta($post->ID, $meta_box['name'], true) == $permalink)) $posts.= ' selected="selected"';
							$posts .= '>'.$attachment->post_title.'</option>';
						}
						if (get_post_meta($post->ID, $meta_box['name'], true) == '') 
							$posts = '<option value="" disabled="disabled" selected="selected">'.__('Choose a page on this blog').'</option>'.$posts;
						echo '<select onchange="document.getElementById(\''.$meta_box['name'].'\').value = this.options[this.selectedIndex].value">'.$posts.'</select>';
					}
					echo '<input type="text" name="'.$meta_box['name'].'" id="'.$meta_box['name'].'" value="'.str_replace('"','\"',get_post_meta($post->ID, $meta_box['name'], true)).'" style="width: 100%" /><br />';
					echo '<p><label for="'.$meta_box['name'].'">'.$meta_box['description'].'</label></p>';
?>	</td>
				</tr>
<?php
			}
?>
			</tbody>
		</table>
<?php	
}

function frontpageSlideshow_create_meta_box() {
	if ( function_exists('add_meta_box') ) {
		add_meta_box( 'fs-metabox', __('Frontpage-Slideshow Options'), 'frontpageSlideshow_meta_boxes', 'post', 'normal', 'high' );
	}
}

function frontpageSlideshow_save_postdata( $post_id ) {
	global $post,$frontpageSlideshow_meta_boxes;
	
	foreach($frontpageSlideshow_meta_boxes as $meta_box) {
		// Verify
		if ( !wp_verify_nonce( $_POST[$meta_box['name'].'_noncename'], plugin_basename(__FILE__) )) {
			return $post_id;
		}
	
		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ))
				return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ))
				return $post_id;
		}
	
		$data = $_POST[$meta_box['name']];
		
		if(get_post_meta($post_id, $meta_box['name']) == '')
			add_post_meta($post_id, $meta_box['name'], $data, true);
		elseif($data != get_post_meta($post_id, $meta_box['name'], true))
			update_post_meta($post_id, $meta_box['name'], $data);
		elseif($data == "")
			delete_post_meta($post_id, $meta_box['name'], get_post_meta($post_id, $meta_box['name'], true));
	}
}



/******************************************************************************/
/*	Registering stuff						      */
/******************************************************************************/

if (frontpageSlideshow_get_options(false,'fs_insert') == 'shortcode') {
	add_shortcode(frontpageSlideshow_get_options(false,'fs_shortcode'), 'frontpageSlideshow_dedicated_shortcode');
	if (function_exists('add_action')) {
		add_filter('init', 'frontpageSlideshow_init',1);
		add_filter('wp_head', 'frontpageSlideshow_header',1);
	}
} else {
	if (function_exists('add_action')) {
		add_filter('the_content', 'frontpageSlideshow');
		add_filter('init', 'frontpageSlideshow_init',1);
		add_filter('wp_head', 'frontpageSlideshow_header',1);
	}
}
if (function_exists('add_action')) {
	add_action('admin_menu', 'frontpageSlideshow_admin_menu');
	add_action('admin_init', 'frontpageSlideshow_admin_init');
	add_action('admin_menu', 'frontpageSlideshow_create_meta_box');
	add_action('save_post', 'frontpageSlideshow_save_postdata');
}
function frontpageSlideshow_Widget_init() {
	register_widget('frontpageSlideshow_Widget');
}
add_action('widgets_init', 'frontpageSlideshow_Widget_init');
?>
