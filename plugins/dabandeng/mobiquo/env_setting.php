<?php

defined('IN_MOBIQUO') or exit;

@include(FROOT.'forumdata/cache/plugin_dabandeng.php');
$mobiquo_config = $_DPLUGIN['dabandeng']['vars'];
$mobiquo_config['pluginid'] = $_DPLUGIN['dabandeng']['pluginid'];
$mobiquo_config['is_open'] = $_DPLUGIN['dabandeng']['available'];
$mobiquo_config['home_data'] = preg_grep("/^\d+$/", preg_split('/\s*,\s*/', $mobiquo_config['home_data']));
$mobiquo_config['hide_forum_id'] = unserialize($mobiquo_config['hide_forum_id']);
if (!isset($mobiquo_config['hide_forum_id'][0])) {
    $mobiquo_config['hide_forum_id'] = array();
}

$request = file_get_contents('php://input');
$parsers = php_xmlrpc_decode_xml($request);

if (!$parsers) {
    get_error('Request error');
}

$request_name   = $parsers->methodname;
$request_params = php_xmlrpc_decode(new xmlrpcval($parsers->params,'array'));
$params_num = count($request_params);
$error_code = 0;

switch ($request_name) {
    case 'attach_image':
        if ($params_num >= 3) {
            $_GET['action']    = 'swfupload';
            $_GET['operation'] = 'upload';
            $_GET['fid'] = $request_params[3];
            $_POST['Filename'] = $request_params[1];
            $_POST['Upload']   = 'Submit Query';

            $fp = tmpfile();
            fwrite($fp, $request_params[0]);
            $file_info = stream_get_meta_data($fp);
            $tmp_name = $file_info['uri'];
            $filesize = @filesize($tmp_name);

            $_FILES['Filedata'] = array(
                'name'      => $request_params[1],
                'type'      => $request_params[2] == 'JPG' ? 'image/jpeg' : 'image/png',
                'tmp_name'  => $tmp_name,
                'error'     => 0,
                'size'      => $filesize ? $filesize : strlen($request_params[0])
            );
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'authorize_user':
        if ($params_num == 2) {
            $_POST['username'] = $request_params[0];
            $_POST['password'] = $request_params[1];
            $_POST['loginfield'] = 'username';
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'get_bookmarked_topic': 
        $start_num = intval(isset($request_params[0]) ? $request_params[0] : '0');
        $end_num = intval(isset($request_params[1]) ? $request_params[1] : '19');
        if ($start_num > $end_num) {
            get_error('Line: '.__LINE__);
        } elseif ($end_num - $start_num >= 50) {
            $end_num = $start_num + 49;
        }
        $limit_num = $end_num - $start_num + 1;
        break;
    case 'create_message':
        if ($params_num == 3 || $params_num == 5) {
            $_POST['msgto'] = $request_params[0][0];
            $_POST['message'] = $request_params[2];
            $_GET['action'] = 'send';
            $_GET['pmsubmit'] = 'yes';
            $_GET['infloat'] = 'yes';
            if ($params_num == 5 && $request_params[3] == 1) {
                $_POST['handlekey'] = 'pmreply';
            } else {
                $_POST['pmsubmit'] = true;
                $_GET['sendnew'] = 'yes';
            }
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'create_topic':
        if ($params_num >= 4) {
            $_GET['action'] = 'newthread';
            $_GET['fid'] = intval($request_params[0]);
            $_GET['topicsubmit'] = 'yes';
            $_POST['posttime'] = time();
            $_POST['subject'] = $request_params[1];
            $_POST['checkbox'] = 0;
            $_POST['message'] = $request_params[3];
            $_POST['attention_add'] = 1;
            if(isset($request_params[4])) $_POST['attachnew'] = array( $request_params[4] => array( 'description'=> ''));
            if(isset($request_params[5])) $_POST['typeid'] = $request_params[5];
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'delete_message':
        if ($params_num == 1) {
            $msg_id = intval($request_params[0]);
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'get_board_stat': break;
    case 'get_box':
        if ($params_num >= 1) {
            $_GET['filter'] = $request_params[0];
            $start_num = intval(isset($request_params[1]) ? $request_params[1] : '0');
            $end_num = intval(isset($request_params[2]) ? $request_params[2] : '19');
            if ($start_num > $end_num) {
                get_error('Line: '.__LINE__);
            } elseif ($end_num - $start_num >= 50) {
                $end_num = $start_num + 49;
            }
            $limit_num = $end_num - $start_num + 1;
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'get_box_info': break;
    case 'get_config': break;
    case 'get_forum': break;
    case 'get_inbox_stat': break;
    case 'get_home_data':
        if ($params_num == 1) {
            $home_data_id = intval($request_params[0]);
        }
        break;
    case 'get_message':
        if ($params_num == 1) {
            $msg_id = intval($request_params[0]);
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'get_new_topic':
        $start_num = intval(isset($request_params[0]) ? $request_params[0] : '0');
        $end_num = intval(isset($request_params[1]) ? $request_params[1] : '19');
        if ($start_num > $end_num) {
            get_error('Line: '.__LINE__);
        } elseif ($end_num - $start_num >= 50) {
            $end_num = $start_num + 49;
        }
        $limit_num = $end_num - $start_num + 1;
        $_POST['st'] = 'on';
        $_POST['srchtxt'] = '';
        $_POST['srchtype'] = 'title';
        $_POST['srchuname'] = '';
        $_POST['srchfilter'] = 'all';
        $_POST['srchfrom'] = 0;
        $_POST['orderby'] = 'lastpost';
        $_POST['ascdesc'] = 'desc';
        $_POST['srchfid'] = array('all');
        $_POST['searchsubmit'] = true;
        break;
    case 'get_online_users': break;
    case 'get_raw_post':
        if ($params_num == 1) {
            $_GET['pid'] = intval($request_params[0]);
            $_GET['action'] = 'edit';
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'get_subscribed_topic': 
        $start_num = intval(isset($request_params[0]) ? $request_params[0] : '0');
        $end_num = intval(isset($request_params[1]) ? $request_params[1] : '19');
        if ($start_num > $end_num) {
            get_error('Line: '.__LINE__);
        } elseif ($end_num - $start_num >= 50) {
            $end_num = $start_num + 49;
        }
        $limit_num = $end_num - $start_num + 1;
        break;
    case 'get_thread':
        if ($params_num >= 1) {
            $_GET['tid'] = intval($request_params[0]);
            $start_num = intval(isset($request_params[1]) ? $request_params[1] : '0');
            $end_num = intval(isset($request_params[2]) ? $request_params[2] : '19');
            $mode = isset($request_params[3]) ? $request_params[3] : '';
            if ($start_num > $end_num) {
                get_error('Line: '.__LINE__);
            } elseif ($end_num - $start_num >= 50) {
                $end_num = $start_num + 49;
            }
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'get_topic':
        if ($params_num >= 1) {
            $_GET['fid'] = intval($request_params[0]);
            $start_num = intval(isset($request_params[1]) ? $request_params[1] : '0');
            $end_num = intval(isset($request_params[2]) ? $request_params[2] : '19');
            $mode = isset($request_params[3]) ? $request_params[3] : '';
            if ($start_num > $end_num) {
                get_error('Line: '.__LINE__);
            } elseif ($end_num - $start_num >= 50) {
                $end_num = $start_num + 49;
            }
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'login_forum':
        if ($params_num == 2) {
            $_GET['fid'] = intval($request_params[0]);
            $_GET['action'] = 'pwverify';
            $_POST['pw'] = $request_params[1];
            $_POST['loginsubmit'] = true;
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'get_user_info':
        if ($params_num == 1) {
            $_GET['username'] = $request_params[0];
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'get_user_reply_post':
        if ($params_num == 1) {
            $username = $request_params[0];
        } else {
            get_error('Line: '.__LINE__);
        }
        $_POST['st'] = 'on';
        $_POST['srchtxt'] = '';
        $_POST['srchtype'] = 'title';
        $_POST['srchuname'] = $username;
        $_POST['srchfilter'] = 'all';
        $_POST['srchfrom'] = 0;
        $_POST['orderby'] = 'lastpost';
        $_POST['ascdesc'] = 'desc';
        $_POST['srchfid'] = array('all');
        $_POST['searchsubmit'] = true;
        break;
    case 'get_user_topic':
        if ($params_num == 1) {
            $username = $request_params[0];
        } else {
            get_error('Line: '.__LINE__);
        }
        $_POST['st'] = 'on';
        $_POST['srchtxt'] = '';
        $_POST['srchtype'] = 'title';
        $_POST['srchuname'] = $username;
        $_POST['srchfilter'] = 'all';
        $_POST['srchfrom'] = 0;
        $_POST['orderby'] = 'lastpost';
        $_POST['ascdesc'] = 'desc';
        $_POST['srchfid'] = array('all');
        $_POST['searchsubmit'] = true;
        break;
    case 'logout_user': break;
    case 'reply_topic':
        if ($params_num >= 4) {
            $_GET['tid'] = intval($request_params[0]);
            $image = $request_params[1]; // not use
            $_POST['message'] = $request_params[2];
            $_POST['subject'] = $request_params[3];
            if(isset($request_params[4])) $_POST['attachnew'] = array( $request_params[4] => array( 'description'=> ''));
            $_GET['action'] = 'reply';
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'save_raw_post':
        if ($params_num == 3) {
            $_GET['editsubmit'] = 'yes';
            $_GET['action'] = 'edit';
            $_POST['pid'] = $request_params[0];
            $_POST['subject'] = $request_params[1];
            $_POST['message'] = $request_params[2];
            $_POST['posttime'] = time();
            $_POST['editsubmit'] = 'true';
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'subscribe_topic':
        if ($params_num == 1) {
            $_GET['tid'] = intval($request_params[0]);
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'unsubscribe_topic':
        if ($params_num == 1) {
            $_GET['tid'] = intval($request_params[0]);
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'bookmark_topic':
        if ($params_num == 1) {
            $_GET['tid'] = ($request_params[0]);
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'unbookmark_topic':
        if ($params_num == 1) {
            $_GET['tid'] = intval($request_params[0]);
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'search_topic':
        if ($params_num >= 1) {
            $_POST['srchtype'] = 'title';
            $_POST['srchfilter'] = 'all';
            $_POST['orderby'] = 'lastpost';
            $_POST['srchfid'] = array('all');
            $_POST['st'] = 'on';
            $_POST['searchsubmit'] = true;
            $_POST['srchtxt'] = $request_params[0];
            
            $start_num = intval(isset($request_params[1]) ? $request_params[1] : '0');
            $end_num = intval(isset($request_params[2]) ? $request_params[2] : '19');
            if ($start_num > $end_num) {
                get_error('Line: '.__LINE__);
            } elseif ($end_num - $start_num >= 50) {
                $end_num = $start_num + 49;
            }
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
    case 'search':
        if ($params_num == 1 || $params_num == 3) {
            $search_field = array(
                'srchtxt',      // search key words
                'srchtype',     // 'title' or 'fulltext'
                'srchuname',    // search author name
                'srchfilter',   // 'all', 'digest', or 'top'
                'special',      // special topic array of 1,2,3,4,5 (array)
                'srchfrom',     // search from time
                'before',       // before search from time or after
                'orderby',      // 'lastpost', 'dateline', 'replies', 'views'
                'ascdesc',      // 'asc' or 'desc'
                'srchfid',      // search forum id (array)
                'searchid',     // search id
            );
            
            $_POST['srchtype'] = 'title';
            $_POST['srchfilter'] = 'all';
            $_POST['orderby'] = 'lastpost';
            $_POST['srchfid'] = array('all');
            $_POST['st'] = 'on';
            $_POST['searchsubmit'] = true; 
            
            if (is_array($request_params[0]))
            {
                foreach($request_params[0] as $field)
                {
                    if (!in_array($field['name'], $search_field)) continue;
                    
                    $_POST[$field['name']] = $field['value'];
                }
            } else {
                $search_filters = explode(';', $request_params[0]);
                foreach($search_filters as $search_filter){
                    $search_key_value = preg_split('/=/', $search_filter, 2);
                    if (!in_array(trim($search_key_value[0]), $search_field)) continue;
                    
                    $_POST[trim($search_key_value[0])] = trim($search_key_value[1]);
                }
            }
            
            $start_num = intval(isset($request_params[1]) ? $request_params[1] : '0');
            $end_num = intval(isset($request_params[2]) ? $request_params[2] : '19');
            if ($start_num > $end_num) {
                get_error('Line: '.__LINE__);
            } elseif ($end_num - $start_num >= 50) {
                $end_num = $start_num + 49;
            }
        } else {
            get_error('Line: '.__LINE__);
        }
        break;
}

?>