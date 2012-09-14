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

if(!empty($special)) {
    $addfeedcheck = $customaddfeed & 2 ? 'checked="checked"': '';
} else {
    $addfeedcheck = $customaddfeed & 1 ? 'checked="checked"': '';
}

$thread = '';
if(!empty($cedit)) {
    unset($inajax, $infloat, $ajaxtarget, $handlekey);
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

if($action == 'newthread' && $forum['allowspecialonly'] && !$special) {
    if($allowpostpoll) {
        $special = 1;
    } elseif($allowposttrade) {
        $special = 2;
    } elseif($allowpostreward) {
        $special = 3;
    } elseif($allowpostactivity) {
        $special = 4;
    } elseif($allowpostdebate) {
        $special = 5;
    } elseif($allowpost && $threadplugins && $allowthreadplugin && ($forum['threadplugin'] = unserialize($forum['threadplugin']))) {
        $threadpluginary = array_intersect($allowthreadplugin, $forum['threadplugin']);
        $specialextra = $threadpluginary[0] ? $threadpluginary[0] : '';
    }

    if(!$special && !$specialextra) {
        get_error('undefined_action');
    }
}

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

$policykey = 'post';

if($policykey) {
    $postcredits = $forum[$policykey.'credits'] ? $forum[$policykey.'credits'] : $creditspolicy[$policykey];
}

if ($forum['allowpost'] == -1) {
    get_error('forum_access_disallow');
}

// ========================================================================================

$discuz_action = 11;

if(empty($forum['fid']) || $forum['type'] == 'group') {
    get_error('forum_nonexistence');
}

if(($special == 1 && !$allowpostpoll) || ($special == 2 && !$allowposttrade) || ($special == 3 && !$allowpostreward) || ($special == 4 && !$allowpostactivity) || ($special == 5 && !$allowpostdebate)) {
    get_error('group_nopermission');
}

if(!$discuz_uid && !((!$forum['postperm'] && $allowpost) || ($forum['postperm'] && forumperm($forum['postperm'])))) {
    get_error('postperm_login_nopermission');
} elseif(empty($forum['allowpost'])) {
    if(!$forum['postperm'] && !$allowpost) {
        get_error('postperm_none_nopermission');
    } elseif($forum['postperm'] && !forumperm($forum['postperm'])) {
        get_error('postperm_none_nopermission');
    }
} elseif($forum['allowpost'] == -1) {
    get_error('post_forum_newthread_nopermission');
}

//if($url && !empty($qihoo['relate']['webnum'])) {
//    $from = in_array($from, array('direct', 'iframe')) ? $from : '';
//    if($data = @implode('', file("http://search.qihoo.com/sint/content.html?surl=$url&md5=$md5&ocs=$charset&ics=$charset&from=$from"))) {
//        preg_match_all("/(\w+):([^\>]+)/i", $data, $data);
//        if(!$data[2][1]) {
//            $subject = trim($data[2][3]);
//            $message = !$editormode ? str_replace('[br]', "\n", trim($data[2][4])) : str_replace('[br]', '<br />', trim($data[2][4]));
//        } else {
//            showmessage('reprint_invalid');
//        }
//    }
//}

checklowerlimit($postcredits);

if($subject == '') {
    get_error('post_sm_isnull');
}

if(!$sortid && !$special && $message == '') {
    get_error('post_sm_isnull');
}

if($post_invalid = checkpost($special)) {
    get_error($post_invalid);
}

if(checkflood()) {
    get_error('post_flood_ctrl');
}

if($discuz_uid) {
    $attentionon = empty($attention_add) ? 0 : 1;
}

$typeid = isset($typeid) && isset($forum['threadtypes']['types'][$typeid]) ? $typeid : 0;
$iconid = !empty($iconid) && isset($_DCACHE['icons'][$iconid]) ? $iconid : 0;
$displayorder = $modnewthreads ? -2 : (($forum['ismoderator'] && !empty($sticktopic)) ? 1 : 0);
$digest = ($forum['ismoderator'] && !empty($addtodigest)) ? 1 : 0;
$readperm = $allowsetreadperm ? $readperm : 0;
$isanonymous = $isanonymous && $allowanonymous ? 1 : 0;
$price = intval($price);
$price = $maxprice && !$special ? ($price <= $maxprice ? $price : $maxprice) : 0;

if(!$typeid && $forum['threadtypes']['required'] && !$special) {
    get_error('post_type_isnull');
}

if(!$sortid && $forum['threadsorts']['required'] && !$special) {
    get_error('post_sort_isnull');
}

if($price > 0 && floor($price * (1 - $creditstax)) == 0) {
    get_error('post_net_price_iszero');
}

$sortid = $special && $forum['threadsorts']['types'][$sortid] ? 0 : $sortid;
$typeexpiration = intval($typeexpiration);

if($forum['threadsorts']['expiration'][$typeid] && !$typeexpiration) {
    get_error('threadtype_expiration_invalid');
}

$optiondata = array();
if($forum['threadsorts']['types'][$sortid] && !$forum['allowspecialonly']) {
    $optiondata = threadsort_validator($typeoption);
}

$author = !$isanonymous ? $discuz_user : '';

$moderated = $digest || $displayorder > 0 ? 1 : 0;

if ($version == '7.1') {
    $db->query("INSERT INTO {$tablepre}threads (fid, readperm, price, iconid, typeid, sortid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, special, attachment, moderated)
        VALUES ('$fid', '$readperm', '$price', '$iconid', '$typeid', '$sortid', '$author', '$discuz_uid', '$subject', '$timestamp', '$timestamp', '$author', '$displayorder', '$digest', '$special', '0', '$moderated')");
} else {

    $thread['status'] = 0;
    
    $ordertype && $thread['status'] = setstatus(4, 1, $thread['status']);
    
    $hiddenreplies && $thread['status'] = setstatus(2, 1, $thread['status']);
    
    if($allowpostrushreply && $rushreply) {
        $thread['status'] = setstatus(3, 1, $thread['status']);
        $thread['status'] = setstatus(1, 1, $thread['status']);
    }
    
    $db->query("INSERT INTO {$tablepre}threads (fid, readperm, price, iconid, typeid, sortid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, special, attachment, moderated, status)
        VALUES ('$fid', '$readperm', '$price', '$iconid', '$typeid', '$sortid', '$author', '$discuz_uid', '$subject', '$timestamp', '$timestamp', '$author', '$displayorder', '$digest', '$special', '0', '$moderated', '$thread[status]')");
}
$tid = $db->insert_id();

if($discuz_uid) {
    $stataction = '';
    if($attentionon) {
        $stataction = 'attentionon';
        $db->query("REPLACE INTO {$tablepre}favoritethreads (tid, uid, dateline) VALUES ('$tid', '$discuz_uid', '$timestamp')", 'UNBUFFERED');
    }
    if($stataction) {
        write_statlog('', 'item=attention&action=newthread_'.$stataction, '', '', 'my.php');
    }
    $db->query("UPDATE {$tablepre}favoriteforums SET newthreads=newthreads+1 WHERE fid='$fid' AND uid<>'$discuz_uid'", 'UNBUFFERED');
}

if($moderated) {
    updatemodlog($tid, ($displayorder > 0 ? 'STK' : 'DIG'));
    updatemodworks(($displayorder > 0 ? 'STK' : 'DIG'), 1);
}

if($forum['threadsorts']['types'][$sortid] && !empty($optiondata) && is_array($optiondata)) {
    $filedname = $valuelist = $separator = '';
    foreach($optiondata as $optionid => $value) {
        if(($_DTYPE[$optionid]['search'] || in_array($_DTYPE[$optionid]['type'], array('radio', 'select', 'number'))) && $value && $version != '7.1') {
            $filedname .= $separator.$_DTYPE[$optionid]['identifier'];
            $valuelist .= $separator."'$value'";
            $separator = ' ,';
        }
        $db->query("INSERT INTO {$tablepre}typeoptionvars (sortid, tid, optionid, value, expiration)
            VALUES ('$sortid', '$tid', '$optionid', '$value', '".($typeexpiration ? $timestamp + $typeexpiration : 0)."')");
    }
    
    if($filedname && $valuelist && $version != '7.1') {
        $db->query("INSERT INTO {$tablepre}optionvalue$sortid ($filedname, tid, fid) VALUES ($valuelist, '$tid', '$fid')");
    }
}

$bbcodeoff = checkbbcodes($message, !empty($bbcodeoff));
$smileyoff = checksmilies($message, !empty($smileyoff));
$parseurloff = !empty($parseurloff);
$htmlon = bindec(($tagstatus && !empty($tagoff) ? 1 : 0).($allowhtml && !empty($htmlon) ? 1 : 0));

$pinvisible = $modnewthreads ? -2 : 0;
$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
$db->query("INSERT INTO {$tablepre}posts (fid, tid, first, author, authorid, subject, dateline, message, useip, invisible, anonymous, usesig, htmlon, bbcodeoff, smileyoff, parseurloff, attachment)
    VALUES ('$fid', '$tid', '1', '$discuz_user', '$discuz_uid', '$subject', '$timestamp', '$message', '$onlineip', '$pinvisible', '$isanonymous', '$usesig', '$htmlon', '$bbcodeoff', '$smileyoff', '$parseurloff', '0')");
$pid = $db->insert_id();

if($version != '7.1' && $pid && @getstatus($thread['status'], 1)) {
    savepostposition($tid, $pid);
}


$allowpostattach && ($attachnew || $attachdel || $sortid) && updateattach();

if($modnewthreads) {
    $db->query("UPDATE {$tablepre}forums SET todayposts=todayposts+1 WHERE fid='$fid'", 'UNBUFFERED');
    get_error('post_newthread_mod_succeed');
} else {

    $feed = array(
        'icon' => '',
        'title_template' => '',
        'title_data' => array(),
        'body_template' => '',
        'body_data' => array(),
        'title_data'=>array(),
        'images'=>array()
    );
    if($addfeed && $forum['allowfeed'] && !$isanonymous) {
        if($special == 0) {
            $feed['icon'] = 'thread';
            $feed['title_template'] = 'feed_thread_title';
            $feed['body_template'] = 'feed_thread_message';
            $feed['body_data'] = array(
                'subject' => "<a href=\"{$boardurl}viewthread.php?tid=$tid\">$subject</a>",
                'message' => cutstr(strip_tags(preg_replace(array("/\[hide=?\d*\].+?\[\/hide\]/is", "/\[.+?\]/is"), array('', ''), $message)), 150)
            );
        }

        if($feed) {
            postfeed($feed);
        }
    }

    if($specialextra) {

        $classname = 'threadplugin_'.$specialextra;
        if(method_exists($classname, 'newthread_submit_end')) {
            $threadpluginclass = new $classname;
            $threadpluginclass->newthread_submit_end($fid);
        }

    }
    if($digest) {
        foreach($digestcredits as $id => $addcredits) {
            $postcredits[$id] = (isset($postcredits[$id]) ? $postcredits[$id] : 0) + $addcredits;
        }
    }
    updatepostcredits('+', $discuz_uid, $postcredits);
    $db->query("UPDATE {$tablepre}members SET threads=threads+1 WHERE uid='$discuz_uid'");

    if(is_array($dzfeed_limit['user_threads']) && in_array(($threads + 1), $dzfeed_limit['user_threads'])) {
        $arg = $data = array();
        $arg['type'] = 'user_threads';
        $arg['uid'] = $discuz_uid;
        $arg['username'] = $discuz_userss;
        $data['title']['actor'] = "<a href=\"space.php?uid={$discuz_uid}\" target=\"_blank\">{$discuz_user}</a>";
        $data['title']['count'] = $threads + 1;
        add_feed($arg, $data);
    }

    $subject = str_replace("\t", ' ', $subject);
    $lastpost = "$tid\t$subject\t$timestamp\t$author";
    $db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost', threads=threads+1, posts=posts+1, todayposts=todayposts+1 WHERE fid='$fid'", 'UNBUFFERED');
    if($forum['type'] == 'sub') {
        $db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost' WHERE fid='$forum[fup]'", 'UNBUFFERED');
    }

    //get_error('post_newthread_succeed');
}

?>