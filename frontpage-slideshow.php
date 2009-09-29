<?php
/*
Plugin Name: Frontpage-Slideshow
Plugin URI: http://www.modulaweb.fr/blog/wp-plugins/frontside-slideshow/en/
Description: Frontpage Slideshow provides a slide show like you can see on <a href="http://linux.com">linux.com</a> or <a href="http://modulaweb.fr/">modulaweb.fr</a> front page. <a href="options-general.php?page=frontpage-slideshow">Configuration Page</a>
Version: 0.6.1
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
		$fsposts = get_posts('category='.$fscategories.'&orderby=ID&numberposts='.$options['values']['fs_slides'].'&order=DESC');
		// put post in more logical order
		$fsposts = array_reverse($fsposts);
		$fsentries = array();
		foreach ($fsposts as $fspost) {
			// format informations
			$title = $fspost->post_title;
			$comment = get_post_meta($fspost->ID,'fs-comment',true);
			$buttoncomment = get_post_meta($fspost->ID,'fs-button-comment',true);
			$link='';
			// if the option is on, uses the post permalink as slide link
			($options['values']['fs_default_link_to_page_link'] && get_post_meta($fspost->ID,'fs-link',true) == '') ? $link = get_permalink($fspost->ID) : $link = get_post_meta($fspost->ID,'fs-link',true);
			$image = get_post_meta($fspost->ID,'fs-picture',true);
			if ($image == '') { // if no image : use the first image on the post
				$image = $fspost->post_content;
				preg_match('/<img.*src="([^"]*)"/',$image,$matches);
				$image = $matches[1];
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
			$fscontent = '<div id="fs-main"><div id="fs-slide"><div id="fs-picture"><div id="fs-placeholder"><a href="#frontpage-slideshow" onclick="if (fsid>-1) { if ($(\'fs-entry-link-\'+fsid).innerHTML != \'\') { this.href=$(\'fs-entry-link-\'+fsid).innerHTML; } }">&nbsp;</a></div><div id="fs-text"><div id="fs-title">&nbsp;</div><div id="fs-excerpt">&nbsp;</div></div></div></div><ul>';
			foreach ($fsentries as $id=>$entry) {
				$fscontent .= '<li id="fs-entry-'.$id.'" class="fs-entry" onclick="window.clearInterval(fsinterval); fsChangeSlide('.$id.')">';
				$fscontent .= '<div id="fs-entry-title-'.$id.'" class="fs-title">'.$entry['title'].'</div>';
				$fscontent .= '<div id="fs-entry-button-comment-'.$id.'" class="fs-comment">'.$entry['button-comment'].'</div>';
				$fscontent .= '<img id="fs-entry-img-'.$id.'" class="fs-skip" src="'.$entry['image'].'"';
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
	wp_enqueue_script('scriptaculous-effects'); // will load PrototypeJS ans Scriptaculous + Scriptaculous Effects
}

function frontpageSlideshow_header($force_display=false,$options=array()) {
	if ((!is_feed() && is_front_page()) || $force_display) {
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
}

function frontpageSlideshow_JS($options,$fslast,$force_display=false) {
	if ((!is_feed() && is_front_page() && is_page()) || $force_display) {
?>
<script type="text/javascript">
/* <![CDATA[ */
var fslast = <?php echo $fslast?>; // # of last slide (if less than 4)
var fsid = -1; // the current slide
var fsinterval = 0; //  the setInterval var
function fsChangeSlide(id) {
	$('fs-entry-'+fsid).removeClassName('fs-current');
	fsid=id;
	window.clearInterval(fsinterval);
	new Effect.Opacity('fs-slide',{ duration: 0.5, from: 1, to: 0, afterFinish: fsChangeSlide2 });
}
function fsChangeSlide2() {
	$('fs-picture').style.backgroundImage='url('+$('fs-entry-img-'+fsid).src+')';
	$('fs-title').innerHTML=$('fs-entry-title-'+fsid).innerHTML;
	$('fs-excerpt').innerHTML=$('fs-entry-comment-'+fsid).innerHTML;
	new Effect.Opacity('fs-slide',{ duration: 0.5, from: 0, to: 1 });
	$('fs-entry-'+fsid).addClassName('fs-current');
	frontpageSlideshow();
}
function fsDoSlide() {
	if (fsid>-1) $('fs-entry-'+fsid).removeClassName('fs-current');
	fsid++;
	if (fsid>fslast) fsid = 0; // new loop !
	fsChangeSlide(fsid);
}
function frontpageSlideshow() {
	fsinterval = window.setInterval('fsDoSlide()',5000);
}
/* ]]> */
</script>
<?php 
	}
}

function frontpageSlideshow_CSS($options,$force_display=false) {
	if ((!is_feed() && is_front_page() && is_page()) || $force_display) {
/*
	Here comes the CSS ruleset
	You can, of course, edit it to fit your needs
	Maybe later, a configuration page will come and allow to tweak the css rules in a more flexible way

*/
?>
<!--[if IE]>
<style type="text/css">
#fs-text {
	filter: alpha(opacity=<?php echo str_replace('%','',$options['values']['fs_text_opacity'])?>);
}
</style>
<![endif]-->

<style type="text/css">
#fs-main {
	width: <?php echo $options['values']['fs_main_width']?>;
	height: <?php echo $options['values']['fs_main_height']?>;
	border: 1px solid <?php echo $options['values']['fs_main_border_color']?>;
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	overflow: hidden;
	background-color: <?php echo $options['values']['fs_main_color']?>;
	color: <?php echo $options['values']['fs_font_color']?>;
	font-family: Verdana, Sans, Helvetica, Arial, sans-serif!important;
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
	background-image: url(<?php  (is_ssl()) ? $url = str_replace('http://','https://',get_bloginfo('url')) : $url = str_replace('https://','http://',get_bloginfo('url')); echo $url  ?>/wp-content/plugins/frontpage-slideshow/images/loading_black.gif);
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
}
#fs-placeholder a:hover {
	text-decoration: none;
}
#fs-text {
	opacity: <?php  echo intval(str_replace('%','',$options['values']['fs_text_opacity'])) / 100; ?>;
	background-color: <?php echo $options['values']['fs_text_bgcolor']?>;
	/*margin-top: 10px;*/
	padding: 10px;
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

	width: <?php echo $options['values']['fs_buttons_width']?>!important;
	height: 100%;

	list-style: none!important;

	background-image: none!important;

	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}
#fs-main li {
	display: block!important;

	padding: 5px!important;
	margin: 0;

	width: 100%!important;
	height: 55px;

	background-image: none!important;

	-moz-border-radius: 3px;
	-khtml-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;

	cursor: pointer;
}
#fs-main li:before { content:""; }
#fs-main li:after { content:""; }

.fs-entry {
	background-color: <?php echo $options['values']['fs_button_normal_color']?>!important;
	margin: 0;
	overflow: hidden;
}
.fs-entry:hover {
	background-color: <?php echo $options['values']['fs_button_hover_color']?>!important;
}
.fs-current {
	background-color: <?php echo $options['values']['fs_button_current_color']?>!important;
}
.fs-skip {
	position: absolute!important;
	top: -300000px!important;
}
</style>
<?php 
	}
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
	frontpageSlideshow_header(true,$options);
	return frontpageSlideshow('',true,$options);
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
					'fs_text_bgcolor'		=> '#000',
					'fs_text_opacity'		=> '75%',
					'fs_main_color'			=> '#000',
					'fs_font_color'			=> '#fff',
					'fs_main_border_color'		=> '#444',
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
					'fs_text_bgcolor'		=> 'css-color',
					'fs_text_opacity'		=> 'percent',
					'fs_main_color'			=> 'css-color',
					'fs_font_color'			=> 'css-color',
					'fs_main_border_color'		=> 'css-color',
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
					'fs_text_bgcolor'		=> __('The main text background color','frontpage-slideshow'),
					'fs_text_opacity'		=> __('The main text opacity','frontpage-slideshow'),
					'fs_main_color'			=> __('The slideshow background color','frontpage-slideshow'),
					'fs_font_color'			=> __('The font color','frontpage-slideshow'),
					'fs_main_border_color'		=> __('The slideshow border color','frontpage-slideshow'),
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

function frontpageSlideshow_admin_options() {
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
			<div id="message" class="updated"><?php echo $message?></div>
			<?php  } ?>
			<div id="poststuff" class="meta-box-sortables">
				<div class="postbox">
					<h3><span><?php _e('Preview')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<?php 
							frontpageSlideshow_header(true,$options);
							echo frontpageSlideshow('',true,$options);
						?>
						<p><strong><?php _e('Important: ','frontpage-slideshow')?></strong> <?php _e('the slideshow may appear differently here and on your site due to the stylesheet of your theme.','frontpage-slideshow')?></p>
					</div>
				</div>
			<form method="post">
				<div class="postbox<?php  if ($options['values']['fs_is_activated']) echo ' closed' ?>">
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
					<div class="handlediv" title="Cliquez pour ouvrir/fermer"><br /></div>
					<h3><span><?php _e('About inserting the slideshow','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><?php _e('Where to insert the slideshow ?','frontpage-slideshow')?></p><?php  echo $options['values']['fs_insert']; ?>
						<ul style="list-style: none">
							<li><label for="fs_insert_1"><input type="radio" id="fs_insert_1" name="fs_insert" value="frontpage"<?php  if ($options['values']['fs_insert']=='frontpage') echo ' checked="checked"'; ?> /> <?php _e('On front-page','frontpage-slideshow')?></label><br />
								<label for="fs_insert_shortcode">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;→ <?php _e('The slideshow will appear only on the front page when it has been configured to display a static-page only.','frontpage-slideshow')?>
							</li>
							<li><label for="fs_insert_2"><input type="radio" id="fs_insert_2" name="fs_insert" value="shortcode"<?php  if ($options['values']['fs_insert']=='shortcode') echo ' checked="checked"'; ?> /> <?php _e('Everywhere on content post (using the dedicated shortcode)','frontpage-slideshow')?></label><br />
								<label for="fs_insert_shortcode">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;→ <?php _e('Shortcode','frontpage-slideshow')?> : <input id="fs_shortcode" name="fs_shortcode" value="<?php echo $options['values']['fs_shortcode']?>" /></label>
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
					<div class="handlediv" title="Cliquez pour ouvrir/fermer"><br /></div>
					<h3><span><?php _e('About categories','frontpage-slideshow')?></span></h3>
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
						<p><input type="submit" name="fs_preview" class="button-primary" value="<?php  _e('Preview'); ?>" /></p>
					</div>
				</div>
				<div class="postbox closed">
					<h3><span><?php _e('About slides and buttons','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><label for="fs_slides"><?php _e('How many slides to show ?','frontpage-slideshow')?> <input type="text" id="fs_slides" name="fs_slides" size="2" maxlength="2" value="<?php echo $options['values']['fs_slides']?>" /></label></p>
						<p><label for="fs_show_buttons"><select id="fs_show_buttons" name="fs_show_buttons">
							<option value="1"<?php  if ($options['values']['fs_show_buttons']) echo ' selected="selected"'?>><?php  _e('Show buttons','frontpage-slideshow'); ?></option>
							<option value="0"<?php  if (!$options['values']['fs_show_buttons']) echo ' selected="selected"'?>><?php  _e('Hide buttons','frontpage-slideshow'); ?></option>
						</select></p>
						<p><input type="submit" name="fs_preview" class="button-primary" value="<?php  _e('Preview'); ?>" /></p>
					</div>
				</div>
				<div class="postbox closed">
					<h3><span><?php _e('About default link','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><label for="fs_default_link_to_page_link"><select id="fs_default_link_to_page_link" name="fs_default_link_to_page_link">
							<option value="0"<?php  if ($options['values']['fs_default_link_to_page_link']) echo ' selected="selected"'?>><?php  _e('If no link is specidied : dont use the slide URL','frontpage-slideshow'); ?></option>
							<option value="1"<?php  if (!$options['values']['fs_default_link_to_page_link']) echo ' selected="selected"'?>><?php  _e('If no link is specidied : use the slide URL','frontpage-slideshow'); ?></option>
						</select></p>
						<p><input type="submit" name="fs_preview" class="button-primary" value="<?php  _e('Preview'); ?>" /></p>
					</div>
				</div>
				<div class="postbox closed">
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
					<h3><span><?php _e('About colors and opacities','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><label for="fs_main_color"><?php _e('Slideshow background color','frontpage-slideshow')?> <input type="text" id="fs_main_color" name="fs_main_color" size="15" value="<?php echo $options['values']['fs_main_color']?>" /></label></p>
						<p><label for="fs_main_border_color"><?php _e('Slideshow border color','frontpage-slideshow')?> <input type="text" id="fs_main_border_color" name="fs_main_border_color" size="15" value="<?php echo $options['values']['fs_main_border_color']?>" /></label></p>
						<p><label for="fs_font_color"><?php _e('Font color','frontpage-slideshow')?> <input type="text" id="fs_font_color" name="fs_font_color" size="15" value="<?php echo $options['values']['fs_font_color']?>" /></label></p>
						<p><label for="fs_button_normal_color"><?php _e('Buttons\' color (normal state)','frontpage-slideshow')?> <input type="text" id="fs_button_normal_color" name="fs_button_normal_color" size="15" value="<?php echo $options['values']['fs_button_normal_color']?>" /></label></p>
						<p><label for="fs_button_hover_color"><?php _e('Buttons\' color (hover)','frontpage-slideshow')?> <input type="text" id="fs_button_hover_color" name="fs_button_hover_color" size="15" value="<?php echo $options['values']['fs_button_hover_color']?>" /></label></p>
						<p><label for="fs_button_current_color"><?php _e('Buttons\' color (current)','frontpage-slideshow')?> <input type="text" id="fs_button_current_color" name="fs_button_current_color" size="15" value="<?php echo $options['values']['fs_button_current_color']?>" /></label></p>
						<p><label for="fs_text_bgcolor"><?php _e('Main text background color','frontpage-slideshow')?> <input type="text" id="fs_text_bgcolor" name="fs_text_bgcolor" size="15" value="<?php echo $options['values']['fs_text_bgcolor']?>" /></label></p>
						<p><label for="fs_text_opacity"><?php _e('Main text opacity','frontpage-slideshow')?> <input type="text" id="fs_text_opacity" name="fs_text_opacity" size="15" value="<?php echo $options['values']['fs_text_opacity']?>" /></label></p>
						<p><input type="submit" name="fs_preview" class="button-primary" value="<?php  _e('Preview'); ?>" /></p>
					</div>
				</div>
				<div class="postbox closed">
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
}

?>
