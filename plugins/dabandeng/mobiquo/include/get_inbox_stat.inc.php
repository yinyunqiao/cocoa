<?php

defined('IN_MOBIQUO') or exit;
define('CURSCRIPT', 'pm');
define('NOROBOT', TRUE);

require_once FROOT.'include/common.inc.php';

$discuz_action = 101;
if(!$discuz_uid) {
    get_error('not_loggedin');
}

include_once DISCUZ_ROOT.'./uc_client/client.php';

$ucnewpm = uc_pm_checknew($discuz_uid);

?>