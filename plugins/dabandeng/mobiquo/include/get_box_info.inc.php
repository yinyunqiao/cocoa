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
require_once UC_ROOT.'lib/db.class.php';

if (class_exists(ucclient_db)) {
    $uc_db = new ucclient_db();
} else {
    $uc_db = new db();
}
$uc_db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, '', UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);

$sql = 'SELECT COUNT(*) FROM '.UC_DBTABLEPRE.'pms pm WHERE ';
$filteradd = "pm.msgtoid='$discuz_uid' AND pm.related='1' AND pm.msgfromid>'0' AND pm.delstatus IN (0,1) AND pm.folder='inbox'";
$meg_count_inbox = $uc_db->result_first($sql.$filteradd);
$unread_count_inbox = $uc_db->result_first($sql.$filteradd." AND pm.new='1'" );

$filteradd = "pm.msgfromid='$discuz_uid' AND pm.related='1' AND pm.msgfromid>'0' AND pm.delstatus IN (0,2) AND pm.folder='inbox'";
$meg_count_outbox = $uc_db->result_first($sql.$filteradd);

$box_info = array(
                    array('box_id'       => 'inbox',
                          'box_name'     => 'Inbox',
                          'msg_count'    => $meg_count_inbox,
                          'unread_count' => $unread_count_inbox,
                          'box_type'     => 'INBOX'
                         ),
                    array('box_id'       => 'outbox',
                          'box_name'     => 'Outbox',
                          'msg_count'    => $meg_count_outbox,
                          'unread_count' => 0,
                          'box_type'     => 'SENT'
                         )
                 );

?>