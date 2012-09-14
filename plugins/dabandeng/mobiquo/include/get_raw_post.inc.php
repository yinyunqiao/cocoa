<?php

defined('IN_MOBIQUO') or exit;
define('CURSCRIPT', 'post');
define('NOROBOT', TRUE);

require_once FROOT.'include/common.inc.php';
require_once DISCUZ_ROOT.'./include/post.func.php';

$postinfo = $db->fetch_first("SELECT * FROM {$tablepre}posts WHERE pid='$pid'");
$fid = $postinfo['fid'];
$tid = $postinfo['tid'];

if(!empty($tid) || !empty($fid)) {
    if(empty($tid)) {
        $forum = $db->fetch_first("SELECT f.fid, f.*, ff.* $accessadd1 $modadd1, f.fid AS fid
            FROM {$tablepre}forums f
            LEFT JOIN {$tablepre}forumfields ff ON ff.fid=f.fid $accessadd2 $modadd2
            WHERE f.fid='$fid'");
    } else {
        $forum = $db->fetch_first("SELECT t.tid, t.closed,".(defined('SQL_ADD_THREAD') ? SQL_ADD_THREAD : '')." f.*, ff.* $accessadd1 $modadd1, f.fid AS fid
            FROM {$tablepre}threads t
            INNER JOIN {$tablepre}forums f ON f.fid=t.fid
            LEFT JOIN {$tablepre}forumfields ff ON ff.fid=f.fid $accessadd2 $modadd2
            WHERE t.tid='$tid'".($auditstatuson ? '' : " AND t.displayorder>='0'")." LIMIT 1");
        $tid = $forum['tid'];
    }

    if($forum) {
        $fid = $forum['fid'];
        $forum['ismoderator'] = !empty($forum['ismoderator']) || $adminid == 1 || $adminid == 2 ? 1 : 0;
        foreach(array('postcredits', 'replycredits', 'threadtypes', 'threadsorts', 'digestcredits', 'postattachcredits', 'getattachcredits', 'modrecommend') as $key) {
            $forum[$key] = !empty($forum[$key]) ? unserialize($forum[$key]) : array();
        }
    } else {
        $fid = 0;
    }
}

$_DTYPE = $checkoption = $optionlist = array();

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


if($forum['type'] == 'sub') {
    $fup = $db->fetch_first("SELECT name, fid FROM {$tablepre}forums WHERE fid='$forum[fup]'");
}

periodscheck('postbanperiods');

if($forum['password'] && $forum['password'] != $_DCOOKIE['fidpw'.$fid]) {
    get_error('forum_passwd');
}

if(empty($forum['allowview'])) {
    if(!$forum['viewperm'] && !$readaccess) {
        get_error('group_nopermission');
    } elseif($forum['viewperm'] && !forumperm($forum['viewperm'])) {
        get_error('viewperm');
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


($forum['allowpost'] == -1) && get_error('forum_access_disallow');



$discuz_action = 13;

$orig = $db->fetch_first("SELECT m.adminid, p.first, p.authorid, p.author, p.dateline, p.anonymous, p.invisible, p.htmlon FROM {$tablepre}posts p
    LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
    WHERE pid='$pid' AND tid='$tid' AND fid='$fid'");

if($magicstatus) {
    $magicid = $db->result_first("SELECT magicid FROM {$tablepre}threadsmod WHERE tid='$tid' AND magicid='10'");
    $allowanonymous = $allowanonymous || $magicid ? 1 : $allowanonymous;
}

$isfirstpost = $orig['first'] ? 1 : 0;
$isorigauthor = $discuz_uid && $discuz_uid == $orig['authorid'];
$isanonymous = $isanonymous && $allowanonymous ? 1 : 0;
$audit = $orig['invisible'] == -2 || $thread['displayorder'] == -2 ? $audit : 0;

if(empty($orig)) {
    get_error('undefined_action');
} elseif((!$forum['ismoderator'] || !$alloweditpost || (in_array($orig['adminid'], array(1, 2, 3)) && $adminid > $orig['adminid'])) && !($forum['alloweditpost'] && $isorigauthor)) {
    get_error('post_edit_nopermission');
} elseif($isorigauthor && !$forum['ismoderator']) {
    if($edittimelimit && $timestamp - $orig['dateline'] > $edittimelimit * 60) {
        get_error('post_edit_timelimit');
    } elseif((($isfirstpost && $modnewthreads) || (!$isfirstpost && $modnewreplies)) && $version == '7.1') {
        get_error('post_edit_moderate');
    }
}

$thread['pricedisplay'] = $thread['price'] == -1 ? 0 : $thread['price'];

if($tagstatus && $isfirstpost) {
    $query = $db->query("SELECT tagname FROM {$tablepre}threadtags WHERE tid='$tid'");
    $threadtagary = array();
    while($tagname = $db->fetch_array($query)) {
        $threadtagary[] = $tagname['tagname'];
    }
    $threadtags = dhtmlspecialchars(implode(' ',$threadtagary));
}

if($special == 5) {
    $debate = array_merge($thread, daddslashes($db->fetch_first("SELECT * FROM {$tablepre}debates WHERE tid='$tid'"), 1));
    $firststand = $db->result_first("SELECT stand FROM {$tablepre}debateposts WHERE tid='$tid' AND uid='$discuz_uid' AND stand<>'0' ORDER BY dateline LIMIT 1");

    if(!$isfirstpost && $debate['endtime'] && $debate['endtime'] < $timestamp && !$forum['ismoderator']) {
        get_error('debate_end');
    }
    if($isfirstpost && $debate['umpirepoint'] && !$forum['ismoderator']) {
        get_error('debate_umpire_comment_invalid');
    }
}

//$rushreply = getstatus($thread['status'], 3);

//$savepostposition = getstatus($thread['status'], 1);

//$hiddenreplies = getstatus($thread['status'], 2);

$icons = '';
if(!$special && is_array($_DCACHE['icons']) && $isfirstpost) {
    $key = 1;
    foreach($_DCACHE['icons'] as $id => $icon) {
        $icons .= ' <input class="radio" type="radio" name="iconid" value="'.$id.'" '.($thread['iconid'] == $id ? 'checked="checked"' : '').' /><img src="images/icons/'.$icon.'" alt="" />';
        $icons .= !(++$key % 10) ? '<br />' : '';
    }
}

$usesigcheck = $postinfo['usesig'] ? 'checked="checked"' : '';
$urloffcheck = $postinfo['parseurloff'] ? 'checked="checked"' : '';
$smileyoffcheck = $postinfo['smileyoff'] == 1 ? 'checked="checked"' : '';
$codeoffcheck = $postinfo['bbcodeoff'] == 1 ? 'checked="checked"' : '';
$tagoffcheck = $postinfo['htmlon'] & 2 ? 'checked="checked"' : '';
$htmloncheck = $postinfo['htmlon'] & 1 ? 'checked="checked"' : '';
$showthreadsorts = ($thread['sortid'] || !empty($sortid)) && $isfirstpost;
$sortid = empty($sortid) ? $thread['sortid'] : $sortid;

$poll = $temppoll = '';
if($isfirstpost) {
    if($special == 127) {
        $sppos = strrpos($postinfo['message'], chr(0).chr(0).chr(0));
        $specialextra = substr($postinfo['message'], $sppos + 3);
        if($specialextra && array_key_exists($specialextra, $threadplugins) && in_array($specialextra, unserialize($forum['threadplugin'])) && in_array($specialextra, $allowthreadplugin)) {
            $postinfo['message'] = substr($postinfo['message'], 0, $sppos);
        } else {
            $special = 0;
            $specialextra = '';
        }
    }
    $thread['freecharge'] = $maxchargespan && $timestamp - $thread['dateline'] >= $maxchargespan * 3600 ? 1 : 0;
    $freechargehours = !$thread['freecharge'] ? $maxchargespan - intval(($timestamp - $thread['dateline']) / 3600) : 0;
    if($thread['special'] == 1 && ($alloweditpoll || $thread['authorid'] == $discuz_uid)) {
        $query = $db->query("SELECT polloptionid, displayorder, polloption, multiple, visible, maxchoices, expiration, overt FROM {$tablepre}polloptions AS polloptions LEFT JOIN {$tablepre}polls AS polls ON polloptions.tid=polls.tid WHERE polls.tid ='$tid' ORDER BY displayorder");
        while($temppoll = $db->fetch_array($query)) {
            $poll['multiple'] = $temppoll['multiple'];
            $poll['visible'] = $temppoll['visible'];
            $poll['maxchoices'] = $temppoll['maxchoices'];
            $poll['expiration'] = $temppoll['expiration'];
            $poll['overt'] = $temppoll['overt'];
            $poll['polloptionid'][] = $temppoll['polloptionid'];
            $poll['displayorder'][] = $temppoll['displayorder'];
            $poll['polloption'][] = stripslashes($temppoll['polloption']);
        }
        $maxpolloptions = $maxpolloptions - $db->num_rows($query);
    } elseif($thread['special'] == 3) {
        $rewardprice = abs($thread['price']);
    } elseif($thread['special'] == 4) {
        $activitytypelist = $activitytype ? explode("\n", trim($activitytype)) : '';
        $activity = $db->fetch_first("SELECT * FROM {$tablepre}activities WHERE tid='$tid'");
        $activity['starttimefrom'] = gmdate("Y-m-d H:i", $activity['starttimefrom'] + $timeoffset * 3600);
        $activity['starttimeto'] = $activity['starttimeto'] ? gmdate("Y-m-d H:i", $activity['starttimeto'] + $timeoffset * 3600) : '';
        $activity['expiration'] = $activity['expiration'] ? gmdate("Y-m-d H:i", $activity['expiration'] + $timeoffset * 3600) : '';
    } elseif($thread['special'] == 5 ) {
        $debate['endtime'] = $debate['endtime'] ? gmdate("Y-m-d H:i", $debate['endtime'] + $timeoffset * 3600) : '';
    }
}

if($thread['special'] == 2 && (($thread['authorid'] == $discuz_uid && $allowposttrade && $version != '7.1') || $allowedittrade)) {
    $query = $db->query("SELECT * FROM {$tablepre}trades WHERE pid='$pid'");
    $tradetypeselect = '';
    if($db->num_rows($query)) {
        $trade = $db->fetch_array($query);
        $trade['expiration'] = $trade['expiration'] ? date('Y-m-d', $trade['expiration']) : '';
        $trade['costprice'] = $trade['costprice'] > 0 ? $trade['costprice'] : '';
        $trade['message'] = dhtmlspecialchars($trade['message']);
        $tradetypeid = $trade['typeid'];
        $forum['tradetypes'] = $forum['tradetypes'] == '' ? -1 : unserialize($forum['tradetypes']);
        if((!$tradetypeid || !isset($tradetypes[$tradetypeid]) && !empty($forum['tradetypes']))) {
            $tradetypeselect = '<select name="tradetypeid" onchange="ajaxget(\'post.php?action=threadsorts&tradetype=yes&sortid=\'+this.options[this.selectedIndex].value+\'&sid='.$sid.'\', \'threadtypes\', \'threadtypeswait\')"><option value="0">&nbsp;</option>';
            foreach($tradetypes as $typeid => $name) {
                if($forum['tradetypes'] == -1 || @in_array($typeid, $forum['tradetypes'])) {
                    $tradetypeselect .= '<option value="'.$typeid.'">'.strip_tags($name).'</option>';
                }
            }
            $tradetypeselect .= '</select><span id="threadtypeswait"></span>';
        } else {
            $tradetypeselect = '<select disabled><option>'.$tradetypes[$trade['typeid']].'</option></select>';
        }
        $expiration_7days = date('Y-m-d', $timestamp + 86400 * 7);
        $expiration_14days = date('Y-m-d', $timestamp + 86400 * 14);
        $expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
        $expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
        $expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
        $expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));
    } else {
        $tradetypeid = $special = 0;
        $trade = array();
    }
}

if($isfirstpost && $specialextra) {
    @include_once DISCUZ_ROOT.'./plugins/'.$threadplugins[$specialextra]['module'].'.class.php';
    $classname = 'threadplugin_'.$specialextra;
    if(method_exists($classname, 'editpost')) {
        $threadpluginclass = new $classname;
        $threadplughtml = $threadpluginclass->editpost($fid, $tid);
    }
}

//$postinfo['subject'] = str_replace('"', '&quot;', $postinfo['subject']);
//$postinfo['message'] = dhtmlspecialchars($postinfo['message']);
include_once language('misc');
//$postinfo['message'] = preg_replace($language['post_edit_regexp'], '', $postinfo['message']);

if($special == 5) {
    $standselected = array($firststand => 'selected="selected"');
}

if($allowpostattach) {
    $attachlist = getattach();
    $attachs = $attachlist['attachs'];
    $imgattachs = $attachlist['imgattachs'];
    unset($attachlist);
    $attachfind = $attachreplace = array();
    if($attachs['used']) {
        foreach($attachs['used'] as $attach) {
            if($attach['isimage']) {
                $attachfind[] = "/\[attach\]$attach[aid]\[\/attach\]/i";
                $attachreplace[] = '[attachimg]'.$attach['aid'].'[/attachimg]';
            }
        }
    }
    if($imgattachs['used']) {
        foreach($imgattachs['used'] as $attach) {
            $attachfind[] = "/\[attach\]$attach[aid]\[\/attach\]/i";
            $attachreplace[] = '[attachimg]'.$attach['aid'].'[/attachimg]';
        }
    }
    //$attachfind && $postinfo['message'] = preg_replace($attachfind, $attachreplace, $postinfo['message']);
}
if($special == 2 && $trade['aid'] && !empty($imgattachs['used']) && is_array($imgattachs['used'])) {
    foreach($imgattachs['used'] as $k => $tradeattach) {
        if($tradeattach['aid'] == $trade['aid']) {
            unset($imgattachs['used'][$k]);
            break;
        }
    }
}

?>