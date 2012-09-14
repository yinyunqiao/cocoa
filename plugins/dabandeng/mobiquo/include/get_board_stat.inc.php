<?php

defined('IN_MOBIQUO') or exit;
define('CURSCRIPT', 'member');
define('NOROBOT', TRUE);
require_once FROOT.'include/common.inc.php';

$onlinenum = $db->result_first("SELECT COUNT(*) FROM {$tablepre}sessions");
$guestnum  = $db->result_first("SELECT COUNT(*) FROM {$tablepre}sessions WHERE uid = '0'");
@extract($db->fetch_first("SELECT SUM(threads) AS threads, SUM(posts) AS posts FROM {$tablepre}forums WHERE status='1'"));

?>
