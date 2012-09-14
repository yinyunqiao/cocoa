<?php

defined('IN_MOBIQUO') or exit;
define('CURSCRIPT', 'pm');
define('NOROBOT', TRUE);

require_once FROOT.'include/common.inc.php';

$msgto = to_local($msgto);

$discuz_action = 101;
if(!$discuz_uid) {
	get_error('not_loggedin');
}

include_once DISCUZ_ROOT.'./uc_client/client.php';

if(!$allowsendpm) {
	get_error('pm_send_disable');
}

$message = preg_replace('/\[QUOTE\](.*?)\[\/QUOTE\]/s', '[quote]$1[/quote]', to_local($message));

if(!$adminid && $newbiespan && (!$lastpost || $timestamp - $lastpost < $newbiespan * 3600)) {
	$query = $db->query("SELECT regdate FROM {$tablepre}members WHERE uid='$discuz_uid'");
	if($timestamp - ($db->result($query, 0)) < $newbiespan * 3600) {
		get_error('pm_newbie_span');
	}
}

!($exempt & 1) && checklowerlimit($creditspolicy['sendpm'], -1);

if(!empty($uid)) {
	$msgto = intval($uid);
} else {
	if(!empty($msgtos)) {
		$buddynum = uc_friend_totalnum($discuz_uid, 3);
		$buddyarray = uc_friend_ls($discuz_uid, 1, $buddynum, $buddynum, 3);
		$uids = array();
		foreach($buddyarray as $buddy) {
			$uids[] = $buddy['friendid'];
		}
		$msgto = $p = '';
		foreach($msgtos as $uid) {
			$msgto .= in_array($uid, $uids) ? $p.$uid : '';
			$p = ',';
		}
		if(!$msgto) {
			get_error('pm_send_nonexistence');
		}
	} else {
		if(!($uid = $db->result_first("SELECT uid FROM {$tablepre}members WHERE username='$msgto'"))) {
			get_error('pm_send_nonexistence');
		}
		$msgto = $uid;
	}
}
if($discuz_uid == $msgto) {
	get_error('pm_send_self_ignore');
}
if(trim($message) === '') {
	get_error('pm_send_empty');
}
include_once DISCUZ_ROOT.'./forumdata/cache/cache_bbcodes.php';
foreach($_DCACHE['smilies']['replacearray'] AS $key => $smiley) {
	$_DCACHE['smilies']['replacearray'][$key] = '[img]'.$boardurl.'images/smilies/'.$_DCACHE['smileytypes'][$_DCACHE['smilies']['typearray'][$key]]['directory'].'/'.$smiley.'[/img]';
}

$message = preg_replace($_DCACHE['smilies']['searcharray'], $_DCACHE['smilies']['replacearray'], $message);

$pmid = uc_pm_send($discuz_uid, $msgto, '', $message, 1, 0, 0);
if($pmid > 0) {
	!($exempt & 1) && updatecredits($discuz_uid, $creditspolicy['sendpm'], -1);
	if(!empty($sendnew)) {
		if($prompts['newbietask'] && $newbietaskid && $newbietasks[$newbietaskid]['scriptname'] == 'sendpm') {
			require_once DISCUZ_ROOT.'./include/task.func.php';
			task_newbie_complete();
		}
		//get_error('pm_send_succeed');
	}
} elseif($pmid == -1) {
	get_error('pm_send_limit1day_error');
} elseif($pmid == -2) {
	get_error('pm_send_floodctrl_error');
} elseif($pmid == -3) {
	get_error('pm_send_batnotfriend_error');
} elseif($pmid == -4) {
	get_error('pm_send_pmsendregdays_error');
} else {
	get_error('pm_send_invalid');
}

?>