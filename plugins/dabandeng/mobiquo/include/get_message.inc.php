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

$pm = uc_pm_viewnode($discuz_uid, 0, $msg_id);

if (!$pm) {
    get_error('pm_nonexistence');
}

$pm['msgto'] = get_user_name_by_id($pm['msgtoid']);
$pm['icon_url'] = discuz_uc_avatar(($pm['folder'] == 'inbox') ? $pm['msgfromid'] : $pm['msgtoid'], '', true);

?>