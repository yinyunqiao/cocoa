<?php

defined('IN_MOBIQUO') or exit;
define('NOROBOT', TRUE);
define('CURSCRIPT', 'logging');

require_once FROOT.'include/common.inc.php';
require_once DISCUZ_ROOT.'./include/misc.func.php';
require_once DISCUZ_ROOT.'./include/login.func.php';
require_once DISCUZ_ROOT.'./uc_client/client.php';

if(!$discuz_uid) {
    $field = 'username';
    $username = to_local($username);
    $password = to_local($password);
    
    if(!($loginperm = logincheck())) {
        get_error('login_strike');
    }
    
    $discuz_uid = 0;
    $discuz_user = $discuz_pw = $discuz_secques = '';    
    $result = userlogin();
    
    if($result > 0) {
        $ucsynlogin = $allowsynlogin ? uc_user_synlogin($discuz_uid) : '';
        if($groupid == 8) {
            get_error('login_succeed_inactive_member');
        }
    } elseif($result == -1) {
        $ucresult['username'] = addslashes($ucresult['username']);
        $auth = authcode("$ucresult[username]\t".FORMHASH, 'ENCODE');
        get_error('login_activation');
    } else {
        $password = preg_replace("/^(.{".round(strlen($password) / 4)."})(.+?)(.{".round(strlen($password) / 6)."})$/s", "\\1***\\3", $password);
        $errorlog = dhtmlspecialchars(
            $timestamp."\t".
            ($ucresult['username'] ? $ucresult['username'] : stripslashes($username))."\t".
            $password."\t".
            ($secques ? "Ques #".intval($questionid) : '')."\t".
            $onlineip);
        writelog('illegallog', $errorlog);
        loginfailed($loginperm);
        $fmsg = $ucresult['uid'] == '-3' ? (empty($questionid) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';
        get_error($fmsg);
    }
    
    updatesession();
}

?>