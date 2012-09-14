<?php

defined('IN_MOBIQUO') or exit;
define('BINDDOMAIN', 'forumdisplay');
define('CURSCRIPT', 'forumdisplay');

require_once FROOT.'include/common.inc.php';
require_once DISCUZ_ROOT.'./include/forum.func.php';

$discuz_action = 2;

$start_limit = $start_num;
$tpp = $end_num - $start_num + 1;

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

if(($forum['viewperm'] && !forumperm($forum['viewperm']) && !$forum['allowview']) || $forum['redirect'] || in_array($fid, $mobiquo_config['hide_forum_id'])) {
    get_error('No permission');
} elseif($forum['formulaperm'] && $adminid != 1) {
    formulaperm($forum['formulaperm'], 0, TRUE);
}

if($forum['password']) {
    if($forum['password'] != $_DCOOKIE['fidpw'.$fid]) {
        get_error('Password protected forum');
    }
}

$sdb = loadmultiserver();

if($version != '7.1') {
    $threadids = array();
    if($forum['threadsorts']['defaultshow'] && $forum['threadsorts']['types'] && empty($sortid)) {
        $sortid = $forum['threadsorts']['defaultshow'];
    }
    
    if($sortid && $forum['threadsorts']['types'][$sortid]) {
        $sortid = intval($sortid);
        include_once DISCUZ_ROOT.'./forumdata/cache/threadsort_'.$sortid.'.php';
        require_once DISCUZ_ROOT.'./include/forumsort.func.php';
    
        $quicksearchlist = quicksearch();
    }
}

if($forum['autoclose']) {
    $closedby = $forum['autoclose'] > 0 ? 'dateline' : 'lastpost';
    $forum['autoclose'] = abs($forum['autoclose']) * 86400;
}

$filter = '';
$filteradd = '';
$selectadd = array();
$specialtype = array('poll' => 1, 'trade' => 2, 'reward' => 3, 'activity' => 4, 'debate' => 5);

if (!isset($orderby) || !in_array($orderby, array('lastpost', 'dateline', 'replies', 'views', 'recommends', 'heats'))) {
    $orderby = $_DCACHE['forums'][$fid]['orderby'] ? $_DCACHE['forums'][$fid]['orderby'] : 'lastpost';
}
if (!isset($ascdesc) || !in_array($ascdesc, array('ASC', 'DESC'))) {
    $ascdesc = $_DCACHE['forums'][$fid]['ascdesc'] ? $_DCACHE['forums'][$fid]['ascdesc'] : 'DESC';
}

$prefixes = array();
if ($forum['threadtypes']['required'] && !empty($forum['threadtypes']['prefix'])) {
    $prefixes = $forum['threadtypes']['types'];
}

if(isset($filter)) {
    if($filter == 'digest') {
        $filteradd = "AND digest>'0'";
    } elseif($recommendthread['status'] && $filter == 'recommend') {
        $filteradd = "AND recommends>'".intval($recommendthread['iconlevels'][0])."'";
    } elseif($filter == 'type' && $forum['threadtypes']['listable'] && $typeid && isset($forum['threadtypes']['types'][$typeid])) {
        $filteradd = "AND typeid='$typeid'";
        if($sortid) {
            $filteradd .= "AND sortid='$sortid'";
        }
    } elseif($filter == 'sort' && $sortid && isset($forum['threadsorts']['types'][$sortid])) {
        $filteradd = "AND sortid='$sortid'";
        if($typeid) {
            $filteradd .= "AND typeid='$typeid'";
        }
        if($version != '7.1') {
            $query_string = daddslashes($_SERVER['QUERY_STRING'], 1);
            if($query_string && $quicksearchlist['option']) {
                $query_string = substr($query_string, (strpos($query_string, "&") + 1));
                parse_str($query_string, $selectadd);
            }
        }
    } elseif($filter == 'special' && array_key_exists($extraid, $threadplugins)) {
        $filteradd = "AND iconid='{$threadplugins[$extraid][iconid]}'";
    } elseif(preg_match("/^\d+$/", $filter)) {
        $filteradd = $filter ? "AND lastpost>='".($timestamp - $filter)."'" : '';
        $orderby = $orderby != 'recommends' ? $orderby : 'heats';
    } elseif(isset($specialtype[$filter])) {
        $filteradd = "AND special='$specialtype[$filter]'";
    } elseif($orderby == 'lastpost') {
        $filter = '';
    } else {
        $filter = 2592000;
        $filteradd = "AND lastpost>='".($timestamp - 2592000)."'";
    }
} else {
    $filter = '';
}

if(empty($filter) && (empty($sortid) || $version == '7.1')) {
    $threadcount = $forum['threads'];
} else {
    $threadcount = $sdb->result_first("SELECT COUNT(*) FROM {$tablepre}threads WHERE fid='$fid' $filteradd AND displayorder>='0'");
}

$localstickycount = $sdb->result_first("SELECT COUNT(*) FROM {$tablepre}threads WHERE fid='$fid' $filteradd AND displayorder>'0'");
$normaltopiccount = $threadcount - $localstickycount;

$thisgid = $forum['type'] == 'forum' ? $forum['fup'] : $_DCACHE['forums'][$forum['fup']]['fup'];
if($globalstick && $forum['allowglobalstick']) {
    $stickytids = $_DCACHE['globalstick']['global']['tids'].(empty($_DCACHE['globalstick']['categories'][$thisgid]['count']) ? '' : ','.$_DCACHE['globalstick']['categories'][$thisgid]['tids']);
    $forumstickycount = 0;
    if($version != '7.1') {
        $forumstickytids = array();
        $_DCACHE['forumstick'][$fid] = is_array($_DCACHE['forumstick'][$fid]) ? $_DCACHE['forumstick'][$fid] : array();
        $forumstickycount = count($_DCACHE['forumstick'][$fid]);
        foreach($_DCACHE['forumstick'][$fid] as $forumstickthread) {
            $stickytids .= ", $forumstickytids";
        }
    
        $stickytids = trim($stickytids, ', ');
        if ($stickytids === ''){
            $stickytids = '0';
        }
    }
    $stickycount = $_DCACHE['globalstick']['global']['count'] + $_DCACHE['globalstick']['categories'][$thisgid]['count'] + $forumstickycount;
} else {
    $stickycount = $stickytids = 0;
}

$filterbool = !empty($filter) && in_array($filter, array('digest', 'recommend', 'type', 'activity', 'poll', 'trade', 'reward', 'debate'));

$threadlist = $threadids = array();

$order_add = ',4';
if ($version == '7.1') {
    $order_add = '';
}

if ($mode == 'TOP') {
    $query = $sdb->query("SELECT t.*, p.message FROM {$tablepre}threads t, {$tablepre}posts p
        WHERE t.tid=p.tid AND p.first='1' AND (t.fid='$fid' $filteradd AND t.displayorder='1' "
        . ($stickycount ? "OR (t.tid IN ($stickytids) AND t.displayorder IN (2, 3".$order_add."))" : '')
        . ") ORDER BY t.displayorder DESC, t.$orderby $ascdesc
        LIMIT $start_limit, $tpp");
    $total_topic_num = $localstickycount;
} else {
    $query = $sdb->query("SELECT t.*, p.message FROM {$tablepre}threads t, {$tablepre}posts p
        WHERE t.tid=p.tid AND p.first='1' AND t.fid='$fid' $filteradd AND t.displayorder='0'
        ORDER BY t.displayorder DESC, t.$orderby $ascdesc
        LIMIT $start_limit, ".($normaltopiccount - $start_limit < $tpp ? $normaltopiccount - $start_limit : $tpp));
    $total_topic_num = $normaltopiccount;
}

while($query && $thread = $sdb->fetch_array($query)) {
    if($thread['special'] != 127) {
        $thread['icon'] = isset($_DCACHE['icons'][$thread['iconid']]) ? 'images/icons/'.$_DCACHE['icons'][$thread['iconid']] : '';
    } else {
        $thread['icon'] = $threadplugins[$specialicon[$thread['iconid']]]['icon'];
    }

    $thread['typeid'] = $thread['typeid'] && !empty($forum['threadtypes']['prefix']) && isset($forum['threadtypes']['types'][$thread['typeid']]) ?
        '<em>[<a href="forumdisplay.php?fid='.$fid.'&amp;filter=type&amp;typeid='.$thread['typeid'].'">'.$forum['threadtypes']['types'][$thread['typeid']].'</a>]</em>' : '';

    $thread['sortid'] = $thread['sortid'] && !empty($forum['threadsorts']['prefix']) && isset($forum['threadsorts']['types'][$thread['sortid']]) ?
        '<em>[<a href="forumdisplay.php?fid='.$fid.'&amp;filter=sort&amp;sortid='.$thread['sortid'].'">'.$forum['threadsorts']['types'][$thread['sortid']].'</a>]</em>' : '';
    $thread['multipage'] = '';
    $topicposts = $thread['special'] ? $thread['replies'] : $thread['replies'] + 1;
    $thread['special'] == 3 && $thread['price'] < 0 && $thread['replies']--;

    $thread['moved'] = $thread['heatlevel'] = 0;
    if($thread['closed'] || ($forum['autoclose'] && $timestamp - $thread[$closedby] > $forum['autoclose'])) {
        $thread['new'] = 0;
        if($thread['closed'] > 1) {
            $thread['moved'] = $thread['tid'];
            $thread['tid'] = $thread['closed'];
            $thread['replies'] = '-';
            $thread['views'] = '-';
        }
        $thread['folder'] = 'lock';
    } else {
        $thread['folder'] = 'common';
        if($lastvisit < $thread['lastpost'] && (empty($_DCOOKIE['oldtopics']) || strpos($_DCOOKIE['oldtopics'], 'D'.$thread['tid'].'D') === FALSE)) {
            $thread['new'] = 1;
            $thread['folder'] = 'new';
        } else {
            $thread['new'] = 0;
        }
        if($thread['replies'] > $thread['views']) {
            $thread['views'] = $thread['replies'];
        }
        if($heatthread['iconlevels']) {
            foreach($heatthread['iconlevels'] as $k => $i) {
                if($thread['heats'] > $i) {
                    $thread['heatlevel'] = $k + 1;
                    break;
                }
            }
        }
    }

    $thread['dateline'] = $thread['dateline'] + $timeoffset * 3600;
    $thread['dblastpost'] = $thread['lastpost'];
    $thread['lastpost'] = $thread['lastpost'] + $timeoffset * 3600;

    if(in_array($thread['displayorder'], $version == '7.1' ? array(1, 2, 3) : array(1, 2, 3, 4))) {
        $thread['id'] = 'stickthread_'.$thread['tid'];
    } elseif(in_array($thread['displayorder'], array(4, 5)) && $version == '7.1') {
        $thread['id'] = 'floatthread_'.$thread['tid'];
    } else {
        $thread['id'] = 'normalthread_'.$thread['tid'];
    }
    
    $threadids[] = $thread['tid'];
    $threadlist[] = $thread;
    unset($thread);
}

if($sortid && $forum['threadsorts']['types'][$sortid] && $version != '7.1') {
    $sortlistarray = sortshowlist($searchoid, $searchvid, $threadids, $searchoption, $selectadd);
    $stemplate = $sortlistarray['stemplate'] ? $sortlistarray['stemplate'] : '';
    $threadlist = $sortlistarray['thread']['list'] ? $sortlistarray['thread']['list'] : $threadlist;
    $threadcount = !empty($sortlistarray['thread']['count']) ? $sortlistarray['thread']['count'] : $threadcount;
    $multipage = $sortlistarray['thread']['multipage'] ? $sortlistarray['thread']['multipage'] : $multipage;
    $sortthreadlist = $sortlistarray['sortthreadlist'] ? $sortlistarray['sortthreadlist'] : array();
}

$visitedforums = $visitedforums ? visitedforums() : '';

?>