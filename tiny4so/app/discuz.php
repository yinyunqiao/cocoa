<?php

function get_avatar($uid, $size = 'middle', $type = '') {
	
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$typeadd = $type == 'real' ? '_real' : '';
	$avpath = $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
	if(file_exists("/var/www/cocoa/uc_server/data/avatar/" . $avpath))
		$ret = "http://tiny4cocoa.com/uc_server/data/avatar/" . $avpath;
	else
		$ret =  "http://tiny4cocoa.com/uc_server/images/noavatar_$size.gif";
	return $ret;
}

function discuzCode($code) {

	$ret = preg_replace("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/|www\.|mailto:)([^\s\[\"']+?))?\](.+?)\[\/url\]/ies", "parseurl('\\1', '\\5')", $code);
	
	$ret = str_replace(array(
		'[/color]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]', '[s]', '[/s]', '[hr]', '[/p]',
		'[i=s]', '[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
		'[list=A]', '[*]', '[/list]', '[indent]', '[/indent]', '[/float]'
	), array(
		'</font>', '</font>', '</font>', '</p>', '<strong>', '</strong>', '<strike>', '</strike>', '<hr class="solidline" />', '</p>', '<i class="pstatus">', '<i>',
		'</i>', '<u>', '</u>', '<ul>', '<ul type="1" class="litype_1">', '<ul type="a" class="litype_2">',
		'<ul type="A" class="litype_3">', '<li>', '</ul>', '<blockquote>', '</blockquote>', '</span>'
	), preg_replace(array(
		"/\[color=([#\w]+?)\]/i",
		"/\[size=(\d+?)\]/i",
		"/\[size=(\d+(\.\d+)?(px|pt|in|cm|mm|pc|em|ex|%)+?)\]/i",
		"/\[font=([^\[\<]+?)\]/i",
		"/\[align=(left|center|right)\]/i",
		"/\[p=(\d{1,2}), (\d{1,2}), (left|center|right)\]/i",
		"/\[float=(left|right)\]/i"

	), array(
		"<font color=\"\\1\">",
		"<font size=\"\\1\">",
		"<font style=\"font-size: \\1\">",
		"<font face=\"\\1 \">",
		"<p align=\"\\1\">",
		"<p style=\"line-height: \\1px; text-indent: \\2em; text-align: \\3;\">",
		"<span style=\"float: \\1;\">"
	), $ret));
	$ret = str_replace("\r\n","<br/>",$ret);
	return $ret;
}

function parseurl($url, $text) {
	
	if(!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)) {
		$url = $matches[0];
		$length = 65;
		if(strlen($url) > $length) {
			$text = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
		}
		return '<a href="'.(substr(strtolower($url), 0, 4) == 'www.' ? 'http://'.$url : $url).'" target="_blank">'.$text.'</a>';
	} else {
		$url = substr($url, 1);
		if(substr(strtolower($url), 0, 4) == 'www.') {
			$url = 'http://'.$url;
		}
		return '<a href="'.$url.'" target="_blank">'.$text.'</a>';
	}
}

