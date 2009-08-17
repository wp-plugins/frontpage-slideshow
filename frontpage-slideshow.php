<?php

/*
Plugin Name: Frontpage-Slideshow
Plugin URI: http://www.modulaweb.fr/blog/wp-plugins-en/frontside-slideshow-en/
Description: Frontpage Slideshow provides a slide show like you can see in linux.com front page
Version: 0.1
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

function frontpageSlideshow($content) {
	
	// modify this var to change the slideshow category
	$fscategory='fs-cat';
	// make sur to change this var on the other funtion below

	if (!is_feed() && is_front_page()) { // the slideshow is only displayed on frontpage
		// get the 4th newer posts
		$fsposts = get_posts('category_name='.$fscategory.'&orderby=ID&numberposts=4&order=DESC');
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
				$fscontent .= '<img id="fs-entry-img-'.$id.'" class="skip" src="'.$entry['image'].'"';
				if ($id == $fslast) $fscontent .= ' onload="fsDoSlide()"'; // put this to makeanother loop after the last image
				$fscontent .= ' />';
				$fscontent .= '<span id="fs-entry-comment-'.$id.'" class="skip">'.$entry['comment'].'</span>';
				$fscontent .= '<span id="fs-entry-link-'.$id.'" class="skip">'.$entry['link'].'</span>';
				$fscontent .= '</li>';
			}
			$fscontent .= '</ul></div>';
		}
		return "\n<!-- Frontpage Slideshow begin -->\n".$fscontent."\n<!-- Frontpage Slideshow end -->\n".$content;
	} else {
		return $content;
	}
}
function frontpageSlideshow_header() {
	// modify this var to change the slideshow category
	$fscategory='fs-cat';
	// make sur to change this var on the other funtion before
	$fsposts = get_posts('category_name='.$fscategory.'&orderby=ID&numberposts=4');
	$fslast = count($fsposts) - 1;
	?>
<? /* 	If the Prototype javascript framework is already loaded before, you could comment this line */ ?>
<script type="text/javascript" src="<? bloginfo('url') ?>/wp-includes/js/prototype.js"></script>
<? /*	If the Scriptaculous javascript framework is already loaded before, you could comment this line */ ?>
<script type="text/javascript" src="<? bloginfo('url') ?>/wp-content/plugins/frontpage-slideshow/js/scriptaculous.js?load=effects"></script>
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
	frontpageSidebar();
}
function fsDoSlide() {
	if (fsid>-1) $('fs-entry-'+fsid).removeClassName('fs-current');
	fsid++;
	if (fsid>fslast) fsid = 0; // new loop !
	fsChangeSlide(fsid);
}
function frontpageSidebar() {
	fsinterval = window.setInterval('fsDoSlide()',5000);
}
/* ]]> */
</script>
<?
/*
	Here comes the CSS ruleset
	You can, of course, edit it to fit your needs
	Maybe later, a configuration page will come and allow to tweak the css rules in a more flexible way
	
*/
?>
<style type="text/css">
/* <![CDATA[ */
#fs-main {
	width: 100%;
	height: 260px;
	border: 1px solid #444;
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	overflow: hidden;
	background-color: black;
	color: white;
}
#fs-slide {
	float: left;
	width: 80%;
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
	background-image: url(<? bloginfo('url') ?>/wp-content/plugins/frontpage-slideshow/images/loading_black.gif);
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}
#fs-placeholder {
	height: 195px;
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
}
#fs-excerpt {
	font-size: 14px;
	padding-left: 10px;
}
.fs-comment {
	font-size: 8px;
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
	background-color: #444;
}
.fs-entry:hover {
	background-color: #333;
}
/* ]]> */
</style>
	
	<?
}

if (function_exists('add_action')) {
	add_filter('the_content', 'frontpageSlideshow');
	add_filter('wp_head', 'frontpageSlideshow_header');
}
?>
