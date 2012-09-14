<?php

defined('IN_MOBIQUO') or exit;
define('CURSCRIPT', 'member');
define('NOROBOT', TRUE);
require_once FROOT.'include/common.inc.php';

$num = $db->result_first("SELECT COUNT(*) FROM {$tablepre}sessions");
$guestnum = $db->result_first("SELECT COUNT(*) FROM {$tablepre}sessions WHERE uid = '0'");
$usernum = $num - $guestnum;

$onlinelist = array();
$query = $db->query("SELECT uid,username FROM {$tablepre}sessions WHERE invisible='0' AND uid > '0'
                     ORDER BY lastactivity DESC LIMIT 100");

while($online = $db->fetch_array($query)) {
    $online['url'] = discuz_uc_avatar($online['uid'], '', true);
    $onlinelist[] = $online;
}

?>