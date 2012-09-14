<?php

defined('IN_MOBIQUO') or exit;
define('NOROBOT', TRUE);
define('CURSCRIPT', 'logging');

require_once FROOT.'include/common.inc.php';
require_once DISCUZ_ROOT.'./uc_client/client.php';

$ucsynlogout = $allowsynlogin ? uc_user_synlogout() : '';

clearcookies();
$discuz_uid = 0;
$discuz_user = $discuz_pw = '';
$styleid = $_DCACHE['settings']['styleid'];
updatesession();

?>