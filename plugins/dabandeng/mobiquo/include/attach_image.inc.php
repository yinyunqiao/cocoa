<?php

defined('IN_MOBIQUO') or exit;
define('NOROBOT', TRUE);
define('CURSCRIPT', 'misc');

require_once FROOT.'include/common.inc.php';
require_once './lib/post.func.php';

$feed = array();

$uid = $discuz_uid;
$aid = 0;
$isimage = 0;
$simple = !empty($simple) ? $simple : 0;
$groupid = intval($db->result_first("SELECT groupid FROM {$tablepre}members WHERE uid='$uid'"));
@include DISCUZ_ROOT.'./forumdata/cache/usergroup_'.$groupid.'.php';

$statusid = -1;

if ($forum['allowpostattach'] != -1 && ($forum['allowpostattach'] == 1 || (!$forum['postattachperm'] && $allowpostattach) || ($forum['postattachperm'] && forumperm($forum['postattachperm'])))) {
    $attachments = attach_upload('Filedata');
    if($attachments) {
        if(is_array($attachments)) {
            $attach = $attachments[0];
            $isimage = $attach['isimage'];
            if(!$simple) {
                require_once DISCUZ_ROOT.'include/chinese.class.php';
                $c = new Chinese('utf8', $charset);
                $attach['name'] = addslashes($c->Convert(urldecode($attach['name'])));
                if($type != 'image' && $isimage) $isimage = -1;
            } elseif($simple == 1 && $type != 'image' && $isimage) {
                $isimage = -1;
            } elseif($simple == 2 && $type == 'image' && !$isimage) {
                dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
                //echo "DISCUZUPLOAD|1|4|0|0|";
                //exit;
                get_error('Line: '.__LINE__);
            }
            $db->query("INSERT INTO {$tablepre}attachments (tid, pid, dateline, readperm, price, filename, filetype, filesize, attachment, downloads, isimage, uid, thumb, remote, width)
                VALUES ('0', '0', '$timestamp', '0', '0', '$attach[name]', '$attach[type]', '$attach[size]', '$attach[attachment]', '0', '$isimage', '$uid', '$attach[thumb]', '$attach[remote]', '$attach[width]')");
            $aid = $db->insert_id();
            $statusid = 0;
            $uploadtag = 'upload';
            if(!$attachid) {
                $uploadtag = 'swfupload';
            }
            write_statlog('', 'action='.$uploadtag, '', '', 'forumstat.php');
        } else {
            //$statusid = $attachments;
            get_error('Line: '.__LINE__);
        }
    } else {
        //$statusid = 9;
        get_error('Line: '.__LINE__);
    }
} else {
    get_error('postattachperm_none_nopermission');
}

?>