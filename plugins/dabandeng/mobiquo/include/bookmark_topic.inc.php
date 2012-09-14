<?php

defined('IN_MOBIQUO') or exit;
define('NOROBOT', TRUE);
define('CURSCRIPT', 'my');
require_once FROOT.'include/common.inc.php';

if(!$discuz_uid) {
    get_error('not_loggedin');
}

if(!$db->result_first("SELECT tid FROM {$tablepre}favorites WHERE uid='$discuz_uid' AND tid='$tid' LIMIT 1")) {
    $db->query("INSERT INTO {$tablepre}favorites (uid, tid) VALUES ('$discuz_uid', '$tid')");
}

?>