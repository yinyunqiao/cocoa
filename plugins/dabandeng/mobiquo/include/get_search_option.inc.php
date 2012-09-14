<?php

defined('IN_MOBIQUO') or exit;
define('NOROBOT', TRUE);
define('CURSCRIPT', 'search');

require_once FROOT.'include/common.inc.php';
require_once DISCUZ_ROOT.'./include/forum.func.php';
require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';
require_once DISCUZ_ROOT.'./forumdata/cache/cache_icons.php';

$discuz_action = 111;

$cachelife_time = 300;  // Life span for cache of searching in specified range of time
$cachelife_text = 3600; // Life span for cache of text searching

$sdb = loadmultiserver('search');

$srchtype = empty($srchtype) ? '' : trim($srchtype);
$checkarray = array('posts' => '', 'trade' => '', 'qihoo' => '', 'threadsort' => '');

$searchid = isset($searchid) ? intval($searchid) : 0;

if($srchtype == 'trade' || $srchtype == 'threadsort' || $srchtype == 'qihoo') {
    $checkarray[$srchtype] = 'checked';
} elseif($srchtype == 'title' || $srchtype == 'fulltext') {
    $checkarray['posts'] = 'checked';
} else {
    $srchtype = '';
    $checkarray['posts'] = 'checked';
}

$keyword = isset($srchtxt) ? htmlspecialchars(trim($srchtxt)) : '';

$threadsorts = '';
if($srchtype == 'threadsort') {
    $query = $db->query("SELECT * FROM {$tablepre}threadtypes WHERE special='1' ORDER BY displayorder");
    while($type = $db->fetch_array($query)) {
        $threadsorts .= '<option value="'.$type['typeid'].'" '.($type['typeid'] == intval($sortid) ? 'selected=selected' : '').'>'.$type['name'].'</option>';
    }
}

$forumselect = forumselect('', '', '', TRUE);
preg_match_all('/<option value="(\d+)">(.*?)<\/option>/s', $aa, $forums, PREG_SET_ORDER);

$disabled = array();
$disabled['title'] = !$allowsearch ? 'disabled' : '';
$disabled['fulltext'] = $allowsearch != 2 ? 'disabled' : '';

include language('templates');

?>