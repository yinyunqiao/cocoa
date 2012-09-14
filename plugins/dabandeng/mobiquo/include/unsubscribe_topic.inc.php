<?php

defined('IN_MOBIQUO') or exit;
define('NOROBOT', TRUE);
define('CURSCRIPT', 'my');
require_once FROOT.'include/common.inc.php';

if(!$discuz_uid) {
    get_error('not_loggedin');
}

$db->query("DELETE FROM {$tablepre}favoritethreads WHERE tid='$tid' AND uid='$discuz_uid'");

?>