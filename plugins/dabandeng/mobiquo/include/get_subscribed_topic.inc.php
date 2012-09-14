<?php

defined('IN_MOBIQUO') or exit;
define('NOROBOT', TRUE);
define('CURSCRIPT', 'my');
require_once FROOT.'include/common.inc.php';
require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

if(!$discuz_uid) {
    get_error('not_loggedin');
}
    
$num = $db->result_first("SELECT COUNT(*) FROM {$tablepre}favoritethreads ft WHERE ft.uid='$discuz_uid'");
$query = $db->query("SELECT t.tid AS t_tid, t.fid, t.subject, t.replies, t.lastpost, t.lastposter, t.closed, ft.* FROM {$tablepre}favoritethreads ft LEFT JOIN {$tablepre}threads t ON ft.tid=t.tid WHERE ft.uid='$discuz_uid' ORDER BY t.lastpost DESC LIMIT $start_num, $limit_num");
$attentionlist = array();
while($attention = $db->fetch_array($query)) {
    if(!$attention['t_tid']) {
        $db->query("DELETE FROM {$tablepre}favoritethreads WHERE tid='$attention[tid]' AND uid='$discuz_uid'", 'UNBUFFERED');
        continue;
    }
    $attention['name'] = $_DCACHE['forums'][$attention['fid']]['name'];
    if($attention['closed']) {
        $attention['new'] = 0;
    } else {
        if($lastvisit < $attention['lastpost'] && (empty($_DCOOKIE['oldtopics']) || strpos($_DCOOKIE['oldtopics'], 'D'.$attention['tid'].'D') === FALSE)) {
            $attention['new'] = 1;
        } else {
            $attention['new'] = 0;
        }
    }
    $attentionlist[] = $attention;
}

foreach($attentionlist as $key => $fav) {
    $post_info = $db->fetch_first("SELECT p.authorid, p.message FROM {$tablepre}posts p
                                   WHERE p.author='$fav[lastposter]' AND p.tid=$fav[tid] AND p.dateline=$fav[lastpost] LIMIT 1");
    $attentionlist[$key]['authorid'] = $post_info['authorid'];
    $attentionlist[$key]['message'] = $post_info['message'];
}

?>