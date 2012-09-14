<?php

defined('IN_MOBIQUO') or exit;
define('NOROBOT', TRUE);
define('CURSCRIPT', 'my');
require_once FROOT.'include/common.inc.php';
require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

if(!$discuz_uid) {
    get_error('not_loggedin');
}

$favlist = array();

$num = $db->result_first("SELECT COUNT(*) FROM {$tablepre}favorites fav, {$tablepre}threads t
    WHERE fav.uid = '$discuz_uid' AND fav.tid=t.tid AND t.displayorder>='0'");

$query = $db->query("SELECT t.tid, t.fid, t.subject, t.replies, t.lastpost, t.lastposter, t.closed, f.name
    FROM {$tablepre}favorites fav, {$tablepre}threads t, {$tablepre}forums f
    WHERE fav.tid=t.tid AND t.displayorder>='0' AND fav.uid='$discuz_uid' AND t.fid=f.fid
    ORDER BY t.lastpost DESC LIMIT $start_num, $limit_num");

while($fav = $db->fetch_array($query)) {
    if($fav['closed']) {
        $fav['new'] = 0;
    } else {
        if($lastvisit < $fav['lastpost'] && (empty($_DCOOKIE['oldtopics']) || strpos($_DCOOKIE['oldtopics'], 'D'.$fav['tid'].'D') === FALSE)) {
            $fav['new'] = 1;
        } else {
            $fav['new'] = 0;
        }
    }
    
    $favlist[] = $fav;
}

foreach($favlist as $key => $fav) {
    $post_info = $db->fetch_first("SELECT p.authorid, p.message FROM {$tablepre}posts p
                                   WHERE p.author='$fav[lastposter]' AND p.tid=$fav[tid] AND p.dateline=$fav[lastpost] LIMIT 1");
    $favlist[$key]['authorid'] = $post_info['authorid'];
    $favlist[$key]['message'] = $post_info['message'];
}

?>