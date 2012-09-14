<?php

defined('IN_MOBIQUO') or exit;
define('CURSCRIPT', 'post');
define('NOROBOT', TRUE);

require_once FROOT.'include/common.inc.php';
require_once DISCUZ_ROOT.'./include/post.func.php';

$_DTYPE = $checkoption = $optionlist = array();
if($sortid) {
    threadsort_checkoption();
}

if(($forum['simple'] & 1) || $forum['redirect']) {
    get_error('forum_disablepost');
}

require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

$thread = '';
if(!empty($cedit)) {
    unset($inajax, $infloat, $ajaxtarget, $handlekey);
}

if($action == 'edit' || $action == 'reply') {
    if($thread = $db->fetch_first("SELECT * FROM {$tablepre}threads WHERE tid='$tid'".($auditstatuson ? '' : " AND displayorder>='0'"))) {
        if($thread['readperm'] && $thread['readperm'] > $readaccess && !$forum['ismoderator'] && $thread['authorid'] != $discuz_uid) {
            get_error('thread_nopermission');
        }

        $fid = $thread['fid'];
        $special = $thread['special'];

    } else {
        get_error('thread_nonexistence');
    }

    if($action == 'reply' && ($thread['closed'] == 1) && !$forum['ismoderator']) {
        get_error('post_thread_closed');
    }
}

periodscheck('postbanperiods');

if($forum['password'] && $forum['password'] != $_DCOOKIE['fidpw'.$fid]) {
    get_error('forum_passwd');
}

if(empty($forum['allowview'])) {
    if(!$forum['viewperm'] && !$readaccess) {
        get_error('group_nopermission');
    } elseif($forum['viewperm'] && !forumperm($forum['viewperm'])) {
        get_error('viewperm_none_nopermission');
    }
} elseif($forum['allowview'] == -1) {
    get_error('forum_access_view_disallow');
}

if($adminid != 1) {
    formulaperm($forum['formulaperm']);
}

if(!$adminid && $newbiespan && (!$lastpost || $timestamp - $lastpost < $newbiespan * 3600)) {
    if($timestamp - ($db->result_first("SELECT regdate FROM {$tablepre}members WHERE uid='$discuz_uid'")) < $newbiespan * 3600) {
        get_error('post_newbie_span');
    }
}

$special = $special > 0 && $special < 7 || $special == 127 ? intval($special) : 0;

$allowpostattach = $forum['allowpostattach'] != -1 && ($forum['allowpostattach'] == 1 || (!$forum['postattachperm'] && $allowpostattach) || ($forum['postattachperm'] && forumperm($forum['postattachperm'])));
$attachextensions = $forum['attachextensions'] ? $forum['attachextensions'] : $attachextensions;
if($attachextensions) {
    $imgexts = explode(',', str_replace(' ', '', $attachextensions));
    $imgexts = array_intersect(array('jpg','jpeg','gif','png','bmp'), $imgexts);
    $imgexts = implode(', ', $imgexts);
} else {
    $imgexts = 'jpg, jpeg, gif, png, bmp';
}
$allowuploadnum = TRUE;
if($allowpostattach) {
    if($maxattachnum) {
        $allowuploadnum = $maxattachnum - $db->result_first("SELECT count(*) FROM {$tablepre}attachments WHERE uid='$discuz_uid' AND dateline>'$timestamp'-86400");
        $allowuploadnum = $allowuploadnum < 0 ? 0 : $allowuploadnum;
    }
    if($maxsizeperday) {
        $allowuploadsize = $maxsizeperday - intval($db->result_first("SELECT SUM(filesize) FROM {$tablepre}attachments WHERE uid='$discuz_uid' AND dateline>'$timestamp'-86400"));
        $allowuploadsize = $allowuploadsize < 0 ? 0 : $allowuploadsize;
        $allowuploadsize = $allowuploadsize / 1048576 >= 1 ? round(($allowuploadsize / 1048576), 1).'MB' : round(($allowuploadsize / 1024)).'KB';
    }
}

$allowpostimg = $allowpostattach && $imgexts;
$enctype = $allowpostattach ? 'enctype="multipart/form-data"' : '';
$maxattachsize_mb = $maxattachsize / 1048576 >= 1 ? round(($maxattachsize / 1048576), 1).'MB' : round(($maxattachsize / 1024)).'KB';

$postcredits = $forum['postcredits'] ? $forum['postcredits'] : $creditspolicy['post'];
$replycredits = $forum['replycredits'] ? $forum['replycredits'] : $creditspolicy['reply'];
$digestcredits = $forum['digestcredits'] ? $forum['digestcredits'] : $creditspolicy['digest'];
$postattachcredits = $forum['postattachcredits'] ? $forum['postattachcredits'] : $creditspolicy['postattach'];

$maxprice = isset($extcredits[$creditstrans]) ? $maxprice : 0;

$extra = rawurlencode($extra);
$notifycheck = empty($emailnotify) ? '' : 'checked="checked"';
$stickcheck = empty($sticktopic) ? '' : 'checked="checked"';
$digestcheck = empty($addtodigest) ? '' : 'checked="checked"';

$subject = to_local($subject);
$message = to_local($message);
$message = preg_replace('/\[quote=.*?\]/si', '[quote]', $message);

$subject = isset($subject) ? dhtmlspecialchars(censor(trim($subject))) : '';
$subject = !empty($subject) ? str_replace("\t", ' ', $subject) : $subject;
$message = isset($message) ? censor(trim($message)) : '';
$polloptions = isset($polloptions) ? censor(trim($polloptions)) : '';
$readperm = isset($readperm) ? intval($readperm) : 0;
$price = isset($price) ? intval($price) : 0;
$tagstatus = $tagstatus && $forum['allowtag'] ? ($tagstatus == 2 ? 2 : $forum['allowtag']) : 0;

if(empty($bbcodeoff) && !$allowhidecode && !empty($message) && preg_match("/\[hide=?\d*\].+?\[\/hide\]/is", preg_replace("/(\[code\](.+?)\[\/code\])/is", ' ', $message))) {
    get_error('post_hide_nopermission');
}

if(periodscheck('postmodperiods', 0)) {
    $modnewthreads = $modnewreplies = 1;
} else {
    $censormod = censormod($subject."\t".$message);
    $modnewthreads = (!$allowdirectpost || $allowdirectpost == 1) && $forum['modnewposts'] || $censormod ? 1 : 0;
    $modnewreplies = (!$allowdirectpost || $allowdirectpost == 2) && $forum['modnewposts'] == 2 || $censormod ? 1 : 0;
}

if($allowposturl < 3 && $message) {
    $urllist = get_url_list($message);
    if(is_array($urllist[1])) foreach($urllist[1] as $key => $val) {
        if(!$val = trim($val)) continue;
        if(!iswhitelist($val)) {
            if($allowposturl == 0) {
                get_error('post_url_nopermission');
            } elseif($allowposturl == 1) {
                $modnewthreads = $modnewreplies = 1;
                break;
            } elseif($allowposturl == 2) {
                if ($version == '7.1') {
                    $message = str_replace(array('[url='.$urllist[0][$key].']', '[url]'.$urllist[0][$key].'[/url]'), $urllist[0][$key], $message);
                } else {
                    $message = str_replace('[url]'.$urllist[0][$key].'[/url]', $urllist[0][$key], $message);
                    $message = preg_replace("@\[url={$urllist[0][$key]}\](.*?)\[/url\]@i", '\\1', $message);
                }
            }
        }
    }
}

$urloffcheck = $usesigcheck = $smileyoffcheck = $codeoffcheck = $htmloncheck = $emailcheck = '';
if($discuz_uid) {
    if($db->result_first("SELECT COUNT(*) FROM {$tablepre}favoritethreads WHERE tid='$tid' AND uid='$discuz_uid'")) {
        $has_attention = true;
    }
}

$seccodecheck = ($seccodestatus & 4) && (!$seccodedata['minposts'] || $posts < $seccodedata['minposts']);
$secqaacheck = $secqaa['status'][2] && (!$secqaa['minposts'] || $posts < $secqaa['minposts']);

$allowpostpoll = $allowpost && $allowpostpoll && ($forum['allowpostspecial'] & 1);
$allowposttrade = $allowpost && $allowposttrade && ($forum['allowpostspecial'] & 2);
$allowpostreward = $allowpost && $allowpostreward && ($forum['allowpostspecial'] & 4) && isset($extcredits[$creditstrans]);
$allowpostactivity = $allowpost && $allowpostactivity && ($forum['allowpostspecial'] & 8);
$allowpostdebate = $allowpost && $allowpostdebate && ($forum['allowpostspecial'] & 16);
$usesigcheck = $discuz_uid && $sigstatus ? 'checked="checked"' : '';
//$ordertypecheck = getstatus($thread['status'], 4) ? 'checked="checked"' : '';

if($specialextra && $allowpost && $threadplugins && (!array_key_exists($specialextra, $threadplugins) || !@in_array($specialextra, unserialize($forum['threadplugin'])) || !@in_array($specialextra, $allowthreadplugin))) {
    $specialextra = '';
}

$allowanonymous = $forum['allowanonymous'] || $allowanonymous ? 1 : 0;

$editorid = 'e';
$editoroptions = str_pad(decbin($editoroptions), 2, 0, STR_PAD_LEFT);
$editormode = $editormode == 2 ? $editoroptions{0} : $editormode;
$allowswitcheditor = $editoroptions{1};
if($specialextra) {
    $special = 127;
    if(@in_array($specialextra, $pluginlangs)) {
        @include_once DISCUZ_ROOT.'./forumdata/cache/cache_scriptlang.php';
    }
}

$policykey = 'reply';

if($policykey) {
    $postcredits = $forum[$policykey.'credits'] ? $forum[$policykey.'credits'] : $creditspolicy[$policykey];
}

if($forum['allowreply'] == -1) {
    get_error('forum_access_disallow');
}


$discuz_action = 12;

if(!$discuz_uid && !((!$forum['replyperm'] && $allowreply) || ($forum['replyperm'] && forumperm($forum['replyperm'])))) {
    get_error('replyperm_login_nopermission');
} elseif(empty($forum['allowreply'])) {
    if(!$forum['replyperm'] && !$allowreply) {
        get_error('replyperm_none_nopermission');
    } elseif($forum['replyperm'] && !forumperm($forum['replyperm'])) {
        get_error('replyperm_none_nopermission');
    }
} elseif($forum['allowreply'] == -1) {
    get_error('post_forum_newreply_nopermission');
}

if(empty($thread)) {
    get_error('thread_nonexistence');
} elseif($thread['price'] > 0 && $thread['special'] == 0 && !$discuz_uid) {
    get_error('group_nopermission');
}

checklowerlimit($replycredits);

if($special == 127) {
    $postinfo = $db->fetch_first("SELECT message FROM {$tablepre}posts WHERE tid='$tid' AND first='1'");
    $sppos = strrpos($postinfo['message'], chr(0).chr(0).chr(0));
    $specialextra = substr($postinfo['message'], $sppos + 3);
    if(!array_key_exists($specialextra, $threadplugins) || !in_array($specialextra, unserialize($forum['threadplugin'])) || !in_array($specialextra, $allowthreadplugin)) {
        $special = 0;
        $specialextra = '';
    }
}


require_once DISCUZ_ROOT.'./include/forum.func.php';

if($subject == '' && $message == '' && $thread['special'] != 2) {
    get_error('post_sm_isnull');
} elseif($thread['closed'] && !$forum['ismoderator']) {
    get_error('post_thread_closed');
} elseif($post_autoclose = checkautoclose()) {
    get_error($post_autoclose);
} elseif($post_invalid = checkpost($special == 2 && $allowposttrade)) {
    get_error($post_invalid);
} elseif(checkflood()) {
    get_error('post_flood_ctrl');
}


$attentionon = empty($attention_add) ? 0 : 1;
$attentionoff = empty($attention_remove) ? 0 : 1;

if($thread['lastposter'] != $discuz_userss) {
    $userreplies = $db->result_first("SELECT COUNT(*) FROM {$tablepre}posts WHERE tid='$tid' AND first='0' AND authorid='$discuz_uid'");
    $thread['heats'] += round($heatthread['reply'] * pow(0.8, $userreplies));
    $heatbefore = $thread['heats'];
    $db->query("UPDATE {$tablepre}threads SET heats='$thread[heats]' WHERE tid='$tid'", 'UNBUFFERED');
}

$bbcodeoff = checkbbcodes($message, !empty($bbcodeoff));
$smileyoff = checksmilies($message, !empty($smileyoff));
$parseurloff = !empty($parseurloff);
$htmlon = $allowhtml && !empty($htmlon) ? 1 : 0;
$usesig = !empty($usesig) ? 1 : 0;

$isanonymous = $allowanonymous && !empty($isanonymous)? 1 : 0;
$author = empty($isanonymous) ? $discuz_user : '';

$pinvisible = $modnewreplies ? -2 : 0;
$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);

$db->query("INSERT INTO {$tablepre}posts (fid, tid, first, author, authorid, subject, dateline, message, useip, invisible, anonymous, usesig, htmlon, bbcodeoff, smileyoff, parseurloff, attachment)
        VALUES ('$fid', '$tid', '0', '$discuz_user', '$discuz_uid', '$subject', '$timestamp', '$message', '$onlineip', '$pinvisible', '$isanonymous', '$usesig', '$htmlon', '$bbcodeoff', '$smileyoff', '$parseurloff', '0')");
$pid = $db->insert_id();

if ($version != '7.1') {
    $cacheposition = @getstatus($thread['status'], 1);
    if($pid && $cacheposition) {
        savepostposition($tid, $pid);
    }
}

$nauthorid = 0;
if(!empty($noticeauthor) && !$isanonymous) {
    list($ac, $nauthorid, $nauthor) = explode('|', $noticeauthor);
    if($nauthorid != $discuz_uid) {
        $postmsg = messagecutstr(str_replace($noticetrimstr, '', $message), 100);
        if($ac == 'q') {
            sendnotice($nauthorid, 'repquote_noticeauthor', 'threads');
        } elseif($ac == 'r') {
            sendnotice($nauthorid, 'reppost_noticeauthor', 'threads');
        }
    }
}

$uidarray = array();
$query = $db->query("SELECT uid FROM {$tablepre}favoritethreads WHERE tid='$tid'");
while($favthread = $db->fetch_array($query)) {
    if($favthread['uid'] !== $discuz_uid && (!$nauthorid || $nauthorid != $favthread['uid'])) {
        $uidarray[] = $favthread['uid'];
    }
}
if($discuz_uid && !empty($uidarray)) {
    sendnotice(implode(',', $uidarray), 'favoritethreads_notice', 'threads', $tid, array('user' => (!$isanonymous ? $discuz_userss : '<i>Anonymous</i>'), 'maxusers' => 5));
    $db->query("UPDATE {$tablepre}favoritethreads SET newreplies=newreplies+1, dateline='$timestamp' WHERE uid IN (".implodeids($uidarray).") AND tid='$tid'", 'UNBUFFERED');
}
if($discuz_uid) {
    $stataction = '';
    if($attentionon) {
        $stataction = 'attentionon';
        $db->query("REPLACE INTO {$tablepre}favoritethreads (tid, uid, dateline) VALUES ('$tid', '$discuz_uid', '$timestamp')", 'UNBUFFERED');
    }
    if($attentionoff) {
        $stataction = 'attentionoff';
        $db->query("DELETE FROM {$tablepre}favoritethreads WHERE tid='$tid' AND uid='$discuz_uid'", 'UNBUFFERED');
    }
    if($stataction) {
        write_statlog('', 'item=attention&action=newreply_'.$stataction, '', '', 'my.php');
    }
}

$allowpostattach && ($attachnew || $attachdel || $special == 2 && $tradeaid) && updateattach();

//$replymessage = 'post_reply_succeed';

if($specialextra) {

    @include_once DISCUZ_ROOT.'./plugins/'.$threadplugins[$specialextra]['module'].'.class.php';
    $classname = 'threadplugin_'.$specialextra;
    if(method_exists($classname, 'newreply_submit_end')) {
        $threadpluginclass = new $classname;
        $threadpluginclass->newreply_submit_end($fid, $tid);
    }

}

$forum['threadcaches'] && deletethreadcaches($tid);

if($modnewreplies) {
    $db->query("UPDATE {$tablepre}forums SET todayposts=todayposts+1 WHERE fid='$fid'", 'UNBUFFERED');
    get_error('post_reply_mod_succeed');
} else {

    $db->query("UPDATE {$tablepre}threads SET lastposter='$author', lastpost='$timestamp', replies=replies+1 WHERE tid='$tid'", 'UNBUFFERED');

    updatepostcredits('+', $discuz_uid, $replycredits);

    $lastpost = "$thread[tid]\t".addslashes($thread['subject'])."\t$timestamp\t$author";
    $db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost', posts=posts+1, todayposts=todayposts+1 WHERE fid='$fid'", 'UNBUFFERED');
    if($forum['type'] == 'sub') {
        $db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost' WHERE fid='$forum[fup]'", 'UNBUFFERED');
    }

    $feed = array();
    if($addfeed && $forum['allowfeed'] && $thread['authorid'] != $discuz_uid && !$isanonymous) {
        $feed['icon'] = 'post';
        $feed['title_template'] = 'feed_reply_title';
        $feed['title_data'] = array(
            'subject' => "<a href=\"{$boardurl}viewthread.php?tid=$tid\">$thread[subject]</a>",
            'author' => "<a href=\"space.php?uid=$thread[authorid]\">$thread[author]</a>"
        );
        postfeed($feed);
    }

    if(is_array($dzfeed_limit['thread_replies']) && in_array(($thread['replies'] + 1), $dzfeed_limit['thread_replies'])) {
        $arg = $data = array();
        $arg['type'] = 'thread_replies';
        $arg['fid'] = $thread['fid'];
        $arg['typeid'] = $thread['typeid'];
        $arg['sortid'] = $thread['sortid'];
        $arg['uid'] = $thread['authorid'];
        $arg['username'] = addslashes($thread['author']);
        $data['title']['actor'] = $thread['authorid'] ? "<a href=\"space.php?uid={$thread[authorid]}\" target=\"_blank\">{$thread[author]}</a>" : $thread['author'];
        $data['title']['forum'] = "<a href=\"forumdisplay.php?fid={$thread[fid]}\" target=\"_blank\">".$forum['name'].'</a>';
        $data['title']['count'] = $thread['replies'] + 1;
        $data['title']['subject'] = "<a href=\"viewthread.php?tid={$thread[tid]}\" target=\"_blank\">{$thread[subject]}</a>";
        add_feed($arg, $data);
    }

    if(is_array($dzfeed_limit['user_posts']) && in_array(($posts + 1), $dzfeed_limit['user_posts'])) {
        $arg = $data = array();
        $arg['type'] = 'user_posts';
        $arg['uid'] = $discuz_uid;
        $arg['username'] = $discuz_userss;
        $data['title']['actor'] = "<a href=\"space.php?uid={$discuz_uid}\" target=\"_blank\">{$discuz_user}</a>";
        $data['title']['count'] = $posts + 1;
        add_feed($arg, $data);
    }
    
    //$page = getstatus($thread['status'], 4) ? 1 : @ceil(($thread['special'] ? $thread['replies'] + 1 : $thread['replies'] + 2) / $ppp);
    //get_error($replymessage);
}

?>