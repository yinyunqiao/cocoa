<?php

defined('IN_MOBIQUO') or exit;
define('NOROBOT', TRUE);
define('CURSCRIPT', 'search');

require_once FROOT.'include/common.inc.php';
require_once DISCUZ_ROOT.'./include/forum.func.php';
require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';
require_once DISCUZ_ROOT.'./forumdata/cache/cache_icons.php';

$discuz_action = 111;

$cachelife_time = 300;        // Life span for cache of searching in specified range of time
$cachelife_text = 3600;        // Life span for cache of text searching

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

$disabled = array();
$disabled['title'] = !$allowsearch ? 'disabled' : '';
$disabled['fulltext'] = $allowsearch != 2 ? 'disabled' : '';


if(!$allowsearch) {
    get_error('group_nopermission');
}

$orderby = in_array($orderby, array('dateline', 'replies', 'views')) ? $orderby : 'lastpost';
$ascdesc = isset($ascdesc) && $ascdesc == 'asc' ? 'asc' : 'desc';


//!($exempt & 2) && checklowerlimit($creditspolicy['search'], -1);

$srchuname = isset($srchuname) ? trim($srchuname) : '';

if($allowsearch == 2 && $srchtype == 'fulltext') {
    //periodscheck('searchbanperiods');
} elseif($srchtype != 'title') {
    $srchtype = 'title';
}

$fids_array = array();
foreach($_DCACHE['forums'] as $fid => $forum) {
    if($forum['type'] != 'group' && (!$forum['viewperm'] && $readaccess) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
        $fids_array[] = $fid;
    }
}

if (isset($mobiquo_config['hide_forum_id']))
{
    $fids_array = array_diff($fids_array, $mobiquo_config['hide_forum_id']);
}

$fids = join(',', $fids_array);

if($threadplugins && $specialplugin) {
    $specialpluginstr = implode("','", $specialplugin);
    $special[] = 127;
} else {
    $specialpluginstr = '';
}
$specials = $special ? implode(',', $special) : '';
$srchfilter = in_array($srchfilter, array('all', 'digest', 'top')) ? $srchfilter : 'all';

$searchstring = 'topic|'.$srchtype.'|'.addslashes($srchtxt).'|'.intval($srchuid).'|'.$srchuname.'|'.addslashes($fids).'|'.intval($srchfrom).'|'.intval($before).'|'.$srchfilter.'|'.$specials.'|'.$specialpluginstr;
$searchindex = array('id' => 0, 'dateline' => '0');

$query = $sdb->query("SELECT searchid, dateline,
    ('$searchctrl'<>'0' AND ".(empty($discuz_uid) ? "useip='$onlineip'" : "uid='$discuz_uid'")." AND $timestamp-dateline<$searchctrl) AS flood,
    (searchstring='$searchstring' AND expiration>'$timestamp') AS indexvalid
    FROM {$tablepre}searchindex
    WHERE ('$searchctrl'<>'0' AND ".(empty($discuz_uid) ? "useip='$onlineip'" : "uid='$discuz_uid'")." AND $timestamp-dateline<$searchctrl) OR (searchstring='$searchstring' AND expiration>'$timestamp')
    ORDER BY flood");

while($index = $sdb->fetch_array($query)) {
    if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
        $searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
        break;
    } elseif($adminid != '1' && $index['flood']) {
        //get_error('search_ctrl');
    }
}

if($searchindex['id']) {

    $searchid = $searchindex['id'];

} else {

    if(!$fids) {
        get_error('group_nopermission');
    }

//    if($adminid != '1' && $maxspm) {
//        if(($sdb->result_first("SELECT COUNT(*) FROM {$tablepre}searchindex WHERE dateline>'$timestamp'-60")) >= $maxspm) {
//            get_error('search_toomany');
//        }
//    }

    $digestltd = $srchfilter == 'digest' ? "t.digest>'0' AND" : '';
    $topltd = $srchfilter == 'top' ? "AND t.displayorder>'0'" : "AND t.displayorder>='0'";

    $sqlsrch = "FROM {$tablepre}threads t WHERE $digestltd t.fid IN ($fids) $topltd";

    if($srchuname) {
        $srchuid = $comma = '';
        $srchuname = str_replace('*', '%', addcslashes($srchuname, '%_'));
        $query = $db->query("SELECT uid FROM {$tablepre}members WHERE username LIKE '".str_replace('_', '\_', $srchuname)."' LIMIT 50");
        while($member = $db->fetch_array($query)) {
            $srchuid .= "$comma'$member[uid]'";
            $comma = ', ';
        }
        if(!$srchuid) {
            $sqlsrch .= ' AND 0';
        }
    } elseif($srchuid) {
        $srchuid = "'$srchuid'";
    }


    if($srchuid) {
        $sqlsrch .= " AND t.authorid IN ($srchuid)";
    }

    if(!empty($specialpluginstr)) {
        $sqlsrch .=  " AND iconid IN (".implodeids($specialplugin).")";
    }

    $keywords = str_replace('%', '+', $srchtxt).(trim($srchuname) ? '+'.str_replace('%', '+', $srchuname) : '');
    $expiration = $timestamp + $cachelife_text;

    $threads = $tids = 0;
    $maxsearchresults = $maxsearchresults ? intval($maxsearchresults) : 500;
    $query = $sdb->query("SELECT t.tid, t.closed, t.author $sqlsrch ORDER BY tid DESC LIMIT $maxsearchresults");
    while($thread = $sdb->fetch_array($query)) {
        if($thread['closed'] <= 1 && $thread['author']) {
            $tids .= ','.$thread['tid'];
            $threads++;
        }
    }
    $db->free_result($query);

    $db->query("INSERT INTO {$tablepre}searchindex (keywords, searchstring, useip, uid, dateline, expiration, threads, tids)
            VALUES ('$keywords', '$searchstring', '$onlineip', '$discuz_uid', '$timestamp', '$expiration', '$threads', '$tids')");
    $searchid = $db->insert_id();

    //!($exempt & 2) && updatecredits($discuz_uid, $creditspolicy['search'], -1);

}


require_once DISCUZ_ROOT.'./include/misc.func.php';

$index = $sdb->fetch_first("SELECT searchstring, keywords, threads, tids FROM {$tablepre}searchindex WHERE searchid='$searchid'");
if(!$index) {
    get_error('search_id_invalid');
}

$keyword = htmlspecialchars($index['keywords']);
$keyword = $keyword != '' ? str_replace('+', ' ', $keyword) : '';

$index['keywords'] = rawurlencode($index['keywords']);
$index['searchtype'] = preg_replace("/^([a-z]+)\|.*/", "\\1", $index['searchstring']);

$threadlist = array();
$query = $sdb->query("SELECT t.*, p.message FROM {$tablepre}threads t, {$tablepre}posts p WHERE t.tid=p.tid AND p.first='1' AND t.tid IN ($index[tids]) AND t.displayorder>='0' ORDER BY t.$orderby $ascdesc LIMIT 20");
while($thread = $sdb->fetch_array($query)) {
    $thread['dblastpost'] = $thread['lastpost'];
    $threadlist[] = procthread($thread);
}

if($prompts['newbietask'] && $newbietaskid && $newbietasks[$newbietaskid]['scriptname'] == 'search'){
    require_once DISCUZ_ROOT.'./include/task.func.php';
    task_newbie_complete();
}

?>