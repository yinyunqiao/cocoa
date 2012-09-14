<?php

defined('IN_MOBIQUO') or exit;
define('CURSCRIPT', 'member');
define('NOROBOT', TRUE);
require_once FROOT.'include/common.inc.php';

if($discuz_user) {
	$db->query("UPDATE {$tablepre}members SET lastvisit='$timestamp' WHERE uid='$discuz_uid'");
}

?>
