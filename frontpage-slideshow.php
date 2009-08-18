<?php

/*
Plugin Name: Frontpage-Slideshow
Plugin URI: http://www.modulaweb.fr/blog/wp-plugins/frontside-slideshow/en/
Description: Frontpage Slideshow provides a slide show like you can see on <a href="http://linux.com">linux.com</a> or <a href="http://modulaweb.fr/">modulaweb.fr</a> front page. <a href="options-general.php?page=frontpage-slideshow">Configuration Page</a>
Version: 0.3
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

function frontpageSlideshow($content,$admin_page=false) {
	
	$options = frontpageSlideshow_get_options();

	$fscategory = join(',',$options['values']['fs_cats']);

	if ((!is_feed() && is_front_page()) || $admin_page) { // the slideshow is only displayed on frontpage
		// get the 4th newer posts
		$fsposts = get_posts('category_name='.$fscategory.'&orderby=ID&numberposts='.$options['values']['fs_slides'].'&order=DESC');
		// put post in more logical order
		$fsposts = array_reverse($fsposts);
		$fsentries = array();
		foreach ($fsposts as $fspost) {
			// format informations
			$title = $fspost->post_title;
			$comment = get_post_meta($fspost->ID,'fs-comment',true);
			$buttoncomment = get_post_meta($fspost->ID,'fs-button-comment',true);
			// handles https for the link
			(!is_ssl()) ? $link = str_replace('https://','http://',get_post_meta($fspost->ID,'fs-link',true)) : $link = str_replace('http://','https://',get_post_meta($fspost->ID,'fs-link',true));
			$image = $fspost->post_content;
			preg_match('/<img.*src="([^"]*)"/',$image,$matches);
			$image = $matches[1];
			// handles https for image
			(!is_ssl()) ? $image = str_replace('https://','http://',$image) : $image = str_replace('http://','https://',$image);
			// put infos into an array
			$fsentries[] = array('title' => $title.'&nbsp;', 'image' => $image, 'comment' => $comment.'&nbsp;', 'button-comment' => $buttoncomment.'&nbsp;', 'link' => $link);
		}
		// construct the slider
		$fscontent = '';
		$fslast = count($fsentries) -1;
		if (count($fsentries)) {
			$fscontent = '<div id="fs-main"><div id="fs-slide"><div id="fs-image"><div id="fs-placeholder"><a href="#top" onclick="if (fsid>-1) { if ($(\'fs-entry-link-\'+fsid).innerHTML != \'\') { this.href=$(\'fs-entry-link-\'+fsid).innerHTML; } }">&nbsp;</a></div><div id="fs-text"><div id="fs-title">&nbsp;</div><div id="fs-excerpt">&nbsp;</div></div></div></div><ul>';
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

function frontpageSlideshow_header($admin_page=false) {
	$options = frontpageSlideshow_get_options();

	$fscategory = join(',',$options['values']['fs_cats']);
	if ((!is_feed() && is_front_page()) || $admin_page) { // the slideshow is only displayed on frontpage
		$fsposts = get_posts('category_name='.$fscategory.'&orderby=ID&numberposts='.$options['values']['fs_slides']);
		$fslast = count($fsposts) - 1;
	
		frontpageSlideshow_JS($options,$fslast);
		frontpageSlideshow_CSS($options);
	}
}

function frontpageSlideshow_JS($options,$fslast) {
?>
<? /* 	If the Prototype javascript framework is already loaded before, you could comment this line */ ?>
<script type="text/javascript" src="<? (is_ssl()) ? $url = str_replace('http://','https://',get_bloginfo('url')) : $url = str_replace('https://','http://',get_bloginfo('url')); echo $url  ?>/wp-includes/js/prototype.js"></script>
<? /*	If the Scriptaculous javascript framework is already loaded before, you could comment this line */ ?>
<script type="text/javascript" src="<? (is_ssl()) ? $url = str_replace('http://','https://',get_bloginfo('url')) : $url = str_replace('https://','http://',get_bloginfo('url')); echo $url  ?>/wp-content/plugins/frontpage-slideshow/js/scriptaculous.js?load=effects"></script>
<?/* 	Please, do not edit the javascript code below  */ ?>
<script type="text/javascript">
/* <![CDATA[ */
var fslast = <?=$fslast?>; // # of last slide (if less than 4)
var fsid = -1; // the current slide
var fsinterval = 0; //  the setInterval var
function fsChangeSlide(id) {
	$('fs-entry-'+fsid).removeClassName('fs-current');
	fsid=id;
	window.clearInterval(fsinterval);
	new Effect.Opacity('fs-slide',{ duration: 0.5, from: 1, to: 0, afterFinish: fsChangeSlide2 });
}
function fsChangeSlide2() {
	$('fs-image').style.backgroundImage='url('+$('fs-entry-img-'+fsid).src+')';
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
<?
}

function frontpageSlideshow_CSS($options) {
/*
	Here comes the CSS ruleset
	You can, of course, edit it to fit your needs
	Maybe later, a configuration page will come and allow to tweak the css rules in a more flexible way
	
*/
?>
<style type="text/css">
/* <![CDATA[ */
#fs-main {
	width: <?=$options['values']['fs_main_width']?>;
	height: <?=$options['values']['fs_main_height']?>;
	border: 1px solid #444;
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	overflow: hidden;
	background-color: black;
	color: white;
	font-family: Verdana, Sans, Helvetica, Arial, sans-serif;

}
#fs-slide {
	float: left;
	width: <? if ($options['values']['fs_slide_width']) echo $options['values']['fs_slide_width']; else echo '100%'; ?>;
	height: 100%;
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}
#fs-image {
	width: 100%;
	height: 100%;
	background-position: center center;
	background-repeat: no-repeat;
	background-image: url(<? (is_ssl()) ? $url = str_replace('http://','https://',get_bloginfo('url')) : $url = str_replace('https://','http://',get_bloginfo('url')); echo $url  ?>/wp-content/plugins/frontpage-slideshow/images/loading_black.gif);
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}
#fs-placeholder {
	height: <?=$options['values']['fs_placeholder_height']?>;
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
	opacity: 0.75;
	background-color: black;
	margin-top: 10px;
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
	font-size: 14px;
}
.fs-title {
	font-weight: bold;
	font-size: 11px;
	line-height: 1.4em;
}
#fs-excerpt {
	font-size: 14px;
	padding-left: 10px;
}
.fs-comment {
	font-size: 8px;
	line-height: 1.2em;
}
#fs-main ul {
	display: block;
	margin: 0;
	padding: 0;
	width: 20%;
	height: 100%;
	float: right;
	list-style: none;
	clear: none;
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}
#fs-main li {
	display: block
	width: 100%;
	height: 55px;
	-moz-border-radius: 3px;
	-khtml-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	cursor: pointer;
	padding: 5px;
}
.fs-current {
	background-color: #444!important;
}
.fs-entry {
	background-color: #000;
	margin: 0;
	overflow: hidden;
}
.fs-entry:hover {
	background-color: #333;
}
.fs-skip {
	position: absolute;
	top: -300000px;
}
/* ]]> */
</style>
<?
}

function frontpageSlideshow_get_options() {
	$options = get_option('frontpage-slideshow',array(
								'values' => array(
									'fs_cats' 		=> array('fs-cat'),
									'fs_slides' 		=> 4,
									'fs_show_buttons' 	=> 1,
									'fs_main_width'		=> '732px',
									'fs_main_height'	=> '260px',
									'fs_slide_width'	=> '80%',
									'fs_placeholder_height'	=> '195px',
									
								),
								'types' => array(
									'fs_cats' 		=> 'array of cats',
									'fs_slides' 		=> 'integer',
									'fs_show_buttons' 	=> 'bool',
									'fs_main_width'		=> 'css-size',
									'fs_main_height'	=> 'css-size',
									'fs_slide_width'	=> 'css-size',
									'fs_placeholder_height'	=> 'css-size',
								),
								'names' => array(
									'fs_cats' 		=> __('The categories','frontpage-slideshow'),
									'fs_slides' 		=> __('The number of slides to show','frontpage-slideshow'),
									'fs_show_buttons' 	=> __('The "Show buttons" option','frontpage-slideshow'),
									'fs_main_width'		=> __('The slideshow width','frontpage-slideshow'),
									'fs_main_height'	=> __('The slideshow height','frontpage-slideshow'),
									'fs_slide_width'	=> __('The image width','frontpage-slideshow'),
									'fs_placeholder_height'	=> __('The main text top','frontpage-slideshow'),
								),
							  ));
	return $options;
}

/******************************************************************************/
/*	Administration page						      */
/******************************************************************************/

function frontpageSlideshow_admin_menu() {
	add_options_page('Frontpage Slideshow', 'Frontpage Slideshow', 8, 'frontpage-slideshow', 'frontpageSlideshow_admin_options');
}

function frontpageSlideshow_admin_options() {
	$options = frontpageSlideshow_get_options();
	if($_POST["fs_submit"]) {
		$bad_values = array();
		foreach($_POST as $key => $val) {
			if($key != "frontpage-slideshow_submit") {
				$value_ok = false;
				switch ($options['types'][$key]) {
					case 'array of cats': 
						if (!is_array($val)) {
							$bad_values[] = $options['names'][$key];
						} else {
							$cats = get_categories('hide_empty=0&depth=1');
							$cats_ = array();
							foreach($cats as $c) {
								$cats_[] = $c;
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
						if (!is_int($val)) {
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
				}
				if ($value_ok) {
					if ( is_array($val) ) {
						$options[$key] = $val;
					} else {
						$options[$key] = stripslashes($val);
					}
				}
			}
		}
		if (count($bad_values)) {
			$message = __('The ptions have NOT been updated !','frontpage-slideshow').'<br />'.__('The following values got to be corrected before to save something : ','frontpage-slideshow').'<ul>';
			foreach($bad_values as $b) {
				$message .= '<li>'.$b.'</li>';
			}
			$message .= '</ul>';
		} else {
			update_option('frontpage-slideshow', $options);
			$message = __('The options have been updated.', 'frontpage-slideshow');
		}
	}
	?>
	<div class="wrap">
		<div id="icon-plugins" class="icon32"><br/></div>
		<h2>Frontpage Slideshow – <?_e('Option page','frontpage-slideshow')?></h2><p></p>
			<div id="poststuff" class="meta-box-sortables">
				<div class="postbox">
					<h3><span><?_e('Preview','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<?
							frontpageSlideshow_header(true);
							echo frontpageSlideshow('',true);						
						?>
					</div>
				</div>
				<div class="postbox closed">
					<div class="handlediv" title="Cliquez pour ouvrir/fermer"><br /></div>
					<h3><span><?_e('Categories','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><?_e('Frontpage Slideshow will look for posts to display as slides into these categories : ','frontpage-slideshow')?></p>
						<ul style="list-style: none">
						<?
							$cats = get_categories('hide_empty=0&depth=1');
							$count=1;
							echo '<li><label for="fs_cats_'.$count.'"><input type="checkbox" disabled="disabled" checked="checked" id="fs_cats_'.$count.'" name="fs_cats[]" value="fs-cat"> fs-cat</label></li>';
							foreach ($cats as $c) {
								if ($c->cat_name!='fs-cat') {
									echo '<li><label for="fs_cats_'.$count.'"><input type="checkbox" id="fs_cats_'.$count.'" name="fs_cats[]" value="' . $c->cat_name . '"';
									if (in_array($c->cat_name,$options['values']['fs_cats'])) echo ' selected="selected"';
									echo '> ' . $c->cat_name . '</label></li>';
									$count++;
								}
							}
				?>
						</ul>
					</div>
				</div>
				<div class="postbox closed">
					<h3><span><?_e('About slides and buttons','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><label for="fs_slides"><?_e('How many slides to show ?','frontpage-slideshow')?> <input type="text" id="fs_slides" name="fs_slides" size="2" maxlength="2" value="<?=$options['values']['fs_slides']?>" /></label></p>
						<p><label for="fs_show_buttons"><input type="checkbox" id="fs_show_buttons" name="fs_show_buttons" value="1"<? if ($options['values']['fs_slides']) echo ' checked="checked"'?> /> <?_e('Show buttons','frontpage-slideshow')?></label></p>
					</div>
				</div>
				<div class="postbox closed">
					<h3><span><?_e('About sizes and styles','frontpage-slideshow')?></span></h3>
					<div class="inside" style="padding: 5px;">
						<p><label for="fs_main_width"><?_e('Slideshow width :','frontpage-slideshow')?> <input type="text" id="fs_main_width" name="fs_main_width" size="5" value="<?=$options['values']['fs_main_width']?>" /></label></p>
						<p><label for="fs_main_height"><?_e('Slideshow height :','frontpage-slideshow')?> <input type="text" id="fs_main_height" name="fs_main_height" size="5" value="<?=$options['values']['fs_main_height']?>" /></label></p>
						<p><label for="fs_slide_width"><?_e('Image width :','frontpage-slideshow')?> <input type="text" id="fs_slide_width" name="fs_slide_width" size="5" value="<?=$options['values']['fs_slide_width']?>" /></label></p>
						<p><label for="fs_placeholder_height"><?_e('Main text top :','frontpage-slideshow')?> <input type="text" id="fs_placeholder_height" name="fs_placeholder_height" size="5" value="<?=$options['values']['fs_placeholder_height']?>" /></label></p>
					</div>
				</div>
			</div>
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

	<?
}






if (function_exists('add_action')) {
	add_filter('the_content', 'frontpageSlideshow');
	add_filter('wp_head', 'frontpageSlideshow_header');
	add_action('admin_menu', 'frontpageSlideshow_admin_menu');
}
?>
