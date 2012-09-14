<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$sql = "DELETE FROM cdb_request WHERE variable like 'dabandeng_%'";
runquery($sql);

$finish = TRUE;

?>