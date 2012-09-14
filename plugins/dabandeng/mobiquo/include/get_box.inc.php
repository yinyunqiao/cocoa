<?php

defined('IN_MOBIQUO') or exit;
define('CURSCRIPT', 'pm');
define('NOROBOT', TRUE);

require_once FROOT.'include/common.inc.php';

$discuz_action = 101;
if (!$discuz_uid) {
    get_error('not_loggedin');
}

include_once DISCUZ_ROOT.'./uc_client/client.php';
require_once UC_ROOT.'lib/db.class.php';

if (class_exists(ucclient_db)) {
    $uc_db = new ucclient_db();
} else {
    $uc_db = new db();
}
$uc_db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, '', UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);

$select_count = 'SELECT COUNT(*)';
$select_all = 'SELECT *';
$sql = ' FROM '.UC_DBTABLEPRE."pms pm WHERE pm.related='1' AND pm.msgfromid>'0' AND pm.folder='inbox'";

if ($filter == 'inbox'){
    $sql .= " AND pm.msgtoid='$discuz_uid' AND pm.delstatus IN (0,1)";
} elseif ($filter == 'outbox'){
    $sql .= " AND pm.msgfromid='$discuz_uid' AND pm.delstatus IN (0,2)";
} else {
    get_error("undefined box id: $filter");
}

$order_limit = " ORDER BY pm.dateline DESC LIMIT $start_num, $limit_num";

$query = $uc_db->query($select_all.$sql.$order_limit);
$pmlist = array();
while($pm = $uc_db->fetch_array($query)) {
    $pm['msgto'] = get_user_name_by_id($pm['msgtoid']);
    $pm['icon_url'] = discuz_uc_avatar($filter == 'outbox' ? $pm['msgtoid'] : $pm['msgfromid'], '', true);
    if ($filter == 'outbox') {
        $pm['new'] = 0;
    }
    $pmlist[] = $pm;
}

$pmnum = $uc_db->result_first($select_count.$sql);
$newpmnum = $uc_db->result_first($select_count.$sql." AND pm.new='1'");

/*
$pmstatus = uc_pm_checknew($discuz_uid, 4);
$filter = !empty($filter) && in_array($filter, array('newpm', 'privatepm', 'announcepm')) ? $filter : ($pmstatus['newpm'] ? 'newpm' : 'privatepm');
$ucdata = uc_pm_list($discuz_uid, $page, $ppp, !isset($search) ? 'inbox' : 'searchbox', !isset($search) ? $filter : $srchtxt, 200);
if(!empty($search) && $srchtxt !== '') {
    $filter = '';
    $srchtxtinput = htmlspecialchars(stripslashes($srchtxt));
    $srchtxtenc = rawurlencode($srchtxt);
} else {
    $multipage = multi($ucdata['count'], $ppp, $page, 'pm.php?filter='.$filter);
}
$_COOKIE['checkpm'] && setcookie('checkpm', '', -86400 * 365);

$pmlist = array();
$today = $timestamp - ($timestamp + $timeoffset * 3600) % 86400;
foreach($ucdata['data'] as $pm) {
    $pm['msgto'] = get_user_name_by_id($pm['msgtoid']);
    $pmlist[] = $pm;
}

if($prompts['pm']['new']) {
    updateprompt('pm', $discuz_uid, 0);
}

$sql = "SELECT count(*) FROM ".UC_DBTABLEPRE."pms WHERE msgtoid='$discuz_uid' AND related='0' AND msgfromid>'0' AND folder='inbox'";
$pmnum = $db->result_first($sql);
$sql = "SELECT count(*) FROM ".UC_DBTABLEPRE."pms WHERE msgtoid='$discuz_uid' AND related='0' AND msgfromid>'0' AND folder='inbox' AND new='1'";
$newpmnum = $db->result_first($sql);
*/

?>