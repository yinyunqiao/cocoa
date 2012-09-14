<?php

defined('IN_MOBIQUO') or exit;
define('BINDDOMAIN', 'forumdisplay');
define('CURSCRIPT', 'forumdisplay');

require_once FROOT.'include/common.inc.php';
require_once DISCUZ_ROOT.'./include/forum.func.php';

$discuz_action = 2;

if($forum['redirect']) {
	get_error('Line: '.__LINE__);
} elseif($forum['type'] == 'group') {
	get_error('Line: '.__LINE__);
} elseif(empty($forum['fid'])) {
	get_error('Line: '.__LINE__);
}

$showoldetails = isset($showoldetails) ? $showoldetails : '';
switch($showoldetails) {
	case 'no': dsetcookie('onlineforum', 0, 86400 * 365); break;
	case 'yes': dsetcookie('onlineforum', 1, 86400 * 365); break;
}

$forum['name'] = strip_tags($forum['name']) ? strip_tags($forum['name']) : $forum['name'];
$forum['extra'] = unserialize($forum['extra']);
if(!is_array($forum['extra'])) {
	$forum['extra'] = array();
}

if($forum['type'] == 'forum') {
	$navigation = '&raquo; '.$forum['name'];
	$navtitle = $forum['name'];
} else {
	$forumup = $_DCACHE['forums'][$forum['fup']]['name'];
	$navigation = '&raquo; <a href="forumdisplay.php?fid='.$forum['fup'].'">'.$forumup.'</a> &raquo; '.$forum['name'];
	$navtitle = $forum['name'].' - '.strip_tags($forumup);
}

$rsshead = $rssstatus ? ('<link rel="alternate" type="application/rss+xml" title="'.$bbname.' - '.$navtitle.'" href="'.$boardurl.'rss.php?fid='.$fid.'&amp;auth='.$rssauth."\" />\n") : '';
$navtitle .= ' - ';
$metakeywords = !$forum['keywords'] ? $forum['name'] : $forum['keywords'];
$metadescription = !$forum['description'] ? $forum['name'] : strip_tags($forum['description']);

if($forum['viewperm'] && !forumperm($forum['viewperm']) && !$forum['allowview']) {
	get_error('No permission');
} elseif ($forum['formulaperm'] && $adminid != 1) {
	formulaperm($forum['formulaperm']);
}

$login_status = false;
if($forum['password']) {
	if($pw == $forum['password']) {
		dsetcookie('fidpw'.$fid, $pw);
		$login_status = true;
	}
}

?>