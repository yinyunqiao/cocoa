<?php
/*======================================================================*\
|| #################################################################### ||
|| # Copyright &copy;2009 Quoord Systems Ltd. All Rights Reserved.    # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # This file is part of the Tapatalk package and should not be used # ||
|| # and distributed for any other purpose that is not approved by    # ||
|| # Quoord Systems Ltd.                                              # ||
|| # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
|| #################################################################### ||
\*======================================================================*/

define('IN_MOBIQUO', true);
define('FROOT', substr($_SERVER['SCRIPT_FILENAME'], 0, -37));

include('./lib/xmlrpc.inc');
include('./lib/xmlrpcs.inc');

error_reporting(0);

require('./error_code.php');
require('./mobiquo_common.php');
require('./server_define.php');
require('./env_setting.php');
require('./xmlrpcresp.php');

include('./include/'.$request_name.'.inc.php');

header('Mobiquo_is_login:'.($discuz_uid ? 'true' : 'false'));
ob_clean();

$rpcServer = new xmlrpc_server($server_param, false);
$rpcServer->setDebug(1);
$rpcServer->compress_response = true;
$rpcServer->response_charset_encoding = 'UTF-8';

$response = $rpcServer->service($request);

?>