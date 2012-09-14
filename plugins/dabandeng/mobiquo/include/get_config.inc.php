<?php

defined('IN_MOBIQUO') or exit;
require_once FROOT.'include/common.inc.php';

$mobiquo_config['version'] = 'dz72';
if (is_numeric($mobiquo_config['pluginid'])) {
    $dabandeng_version = $db->result_first("SELECT version FROM {$tablepre}plugins WHERE pluginid='$mobiquo_config[pluginid]' LIMIT 1");
    $mobiquo_config['version'] = 'dz72_'.$dabandeng_version;
}

?>