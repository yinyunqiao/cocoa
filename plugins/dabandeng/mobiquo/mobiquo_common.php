<?php

defined('IN_MOBIQUO') or exit;

function to_utf8($str)
{
    global $charset;
    
    $str = function_exists('mb_convert_encoding') ? @mb_convert_encoding($str, 'UTF-8', $charset) : iconv($charset, 'utf-8', $str);
    
    return unescape_htmlentitles($str);
}

function unescape_htmlentitles($str) {
    preg_match_all("/(?:%u.{4})|.{4};|&#\d+;|.+|\\r|\\n/U",$str,$r);
    $ar = $r[0];
    
    foreach($ar as $k=>$v) {
        if(substr($v,0,2) == "&#") {
            $ar[$k] =@html_entity_decode($v,ENT_QUOTES, 'UTF-8');
        }
    }
    return join("",$ar);
}

function to_local($str)
{
    global $charset;
    return iconv('utf-8', $charset, $str);
}

function mobiquo_iso8601_encode($timet)
{
    global $timeoffset;
    
    $t = gmdate("Ymd\TH:i:s", $timet + $timeoffset * 3600);      
    $t .= sprintf("%+03d:%02d", intval($timeoffset), abs($timeoffset - intval($timeoffset)) * 60); 
    
    return $t;
}

function format_time($time)
{    
    return mobiquo_iso8601_encode(strtotime($time));
}

function get_message($message)
{
    $message = preg_replace('/\[\/?code\]|\[\/?b\]/', '', $message);
    $message = preg_replace('/\[img\].*?\/images\/smilies\/.*?\[\/img\]/', '', $message);

    return basic_clean($message);
}

function get_short_content($message, $length = 100)
{
    $message = preg_replace('/\[url.*?\].*?\[\/url\]/si', '###url###', $message);
    $message = preg_replace('/\[img.*?\].*?\[\/img\]/si', '###img###', $message);
    $message = preg_replace('/\[attach.*?\].*?\[\/attach\]/si', '###attach###', $message);
    $message = preg_replace('/\[(i|code|quote|free|media|audio|flash|hide|swf).*?\].*?\[\/\\1\]/si', '', $message);
    $message = preg_replace('/\[.*?\]/si', '', $message);
    $message = preg_replace('/###(url|img|attach)###/si', '[$1]', $message);
    $message = preg_replace('/^\s*|\s*$/', '', $message);
    $message = preg_replace('/[\n\r\t]+/', ' ', $message);
    $message = preg_replace('/\s+/', ' ', $message);
    $message = cutstr($message, $length);
    $message = basic_clean($message);

    return $message;
}

function get_error($err_key)
{
    global $discuz_uid;
    
    header('Mobiquo_is_login:'.($discuz_uid ? 'true' : 'false'));
    //@require_once(DISCUZ_ROOT.'./templates/default/messages.lang.php');
    $err_id = $discuz_uid ? 18 : 20;

    $err_str = isset($language[$err_key]) ? $language[$err_key] : $err_key;
    $err_str = basic_clean($err_str);
    //$err_str = preg_replace('/\(.*?\)|“.*?”|\{.*?\}/', '', $err_str);
    //$err_str = preg_replace('/\$\S+\s*/', '', $err_str);
    
    $r = new xmlrpcresp('', $err_id, $err_str);
    echo $r->serialize('UTF-8');
    exit;
}

function log_it($log_data)
{
    global $mobiquo_config;

    if(!$mobiquo_config['keep_log'] || !$log_data)
    {
        return;
    }

    $log_file = './log/'.date('Ymd_H').'.log';

    file_put_contents($log_file, print_r($log_data, true), FILE_APPEND);
}

function post_html_clean($str)
{
    $search = array(
        '/<a .*?href="(.*?)".*?>(.*?)<\/a>/si',
        '/<img .*?src="(.*?)".*?\/?>/sei',
        '/<blockquote.*?>(.*?)<\/blockquote>/si',
        '/<br\s*\/?>|<\/cite>|<li>|<\/em>|<em.*?>/si',
        '/&nbsp;/si',
    );

    $replace = array(
        '[url=$1]$2[/url]',
        "'[img]'.url_encode('$1').'[/img]'",
        '[quote]$1[/quote]',
        "\n",
        ' ',
    );

    $str = preg_replace('/\n|\r/si', '', $str);
    if (preg_match('/^(.*?<blockquote.*?>)(.*)(<\/blockquote>.*)$/si', $str, $match_first))
    {
        if (preg_match('/^(.*?)<blockquote.*?>.*<\/blockquote>(.*)$/si', $match_first[2], $match_second))
        {
            $str = $match_first[1].$match_second[1].$match_second[2].$match_first[3];
        }
    }
    $str = preg_replace('/<i class="pstatus".*?>.*?<\/i>(<br\s*\/>){0,2}/', '', $str);
    $str = preg_replace('/<script.*?>.*?<\/script>/', '', $str);
    $str = preg_replace($search, $replace, $str);

    // remove link on img
    $str = preg_replace('/\[url=.*?\](\[img\].*?\[\/img\])\[\/url\]/', '$1', $str);
    // remove reply link
    $str = preg_replace('/\[url=[^\]]*?redirect\.php\?goto=findpost.*?\](.*?)\[\/url\]/', '$1', $str);
    // Currently, we don't display smiles and system image
    $str = preg_replace('/\[img\]images\/(smilies|default)\/.*?\[\/img\]/si', '', $str);
    // Currently, we don't display back image
    $str = preg_replace('/\[img\].*?back\.gif\[\/img\]/si', '', $str);

    return basic_clean($str);
}

function url_encode($url)
{
    $url = rawurlencode($url);
    
    $from = array('/%3A/', '/%2F/', '/%3F/', '/%2C/', '/%3D/', '/%26/', '/%25/', '/%23/', '/%2B/', '/%3B/');
    $to   = array(':',     '/',     '?',     ',',     '=',     '&',     '%',     '#',     '+',     ';');
    $url = preg_replace($from, $to, $url);
    
    return htmlspecialchars_decode($url);
}

function basic_clean($str)
{
    $str = strip_tags($str);
    $str = to_utf8($str);
    return html_entity_decode($str, ENT_QUOTES, 'UTF-8');
}

function get_user_id_by_name($username)
{
    if (!$username)
    {
        return false;
    }

    $var = "my_get_name_$username";
    if(!isset($GLOBALS[$var])) {
        if($username == $GLOBALS['member']['username']) {
            $GLOBALS[$var] = $GLOBALS['member']['uid'];
        } else {
            global $db, $tablepre;
            $GLOBALS[$var] = $db->result_first("SELECT uid FROM {$tablepre}members WHERE username='$username'");
        }
    }
    return $GLOBALS[$var];
}

function get_user_name_by_id($uid)
{
    if (!$uid)
    {
        return false;
    }

    $var = "my_get_name_$uid";
    if(!isset($GLOBALS[$var])) {
        if($uid == $GLOBALS['member']['uid']) {
            $GLOBALS[$var] = $GLOBALS['member']['username'];
        } else {
            global $db, $tablepre;
            $GLOBALS[$var] = $db->result_first("SELECT username FROM {$tablepre}members WHERE uid='$uid'");
        }
    }
    return $GLOBALS[$var];
}

function post_reply_clean($str)
{
    $str = preg_replace("/\n|\r/s", '', $str);

    if (preg_match('/<div id="dps_preply_\d+" page="\d+">.*?<\/td>\s*<\/tr>\s*<\/table>\s*<\/div>\s*<\/div>/', $str, $matches)) {
        $str = preg_replace('/<div class="right">.*?<\/div>/s', " ]\n",  $matches[0]);
        $str = preg_replace('/<div class="left data">/s', "[ ",  $str);
        $str = preg_replace('/<div class="dps_preply_title"><div>(.*?)<\/div><\/div>/s', "\n\n========= $1 ============", $str);
        $str = preg_replace('/<\/?tr>/s', "\n", $str);
        $str = preg_replace('/<br\s*\/?>/s', "\n", $str);
        return basic_clean($str);
    }
    
    return '';
}

function get_language()
{
    global $charset;
    
    switch ($charset) {
        case 'utf-8':
            include('./lang/lang_utf8.php');
            break;
        case 'big5':
            include('./lang/lang_big5.php');
            break;
        default:
            include('./lang/lang_gbk.php');
    }
}

function is_subscribed($tid)
{
    global $discuz_uid, $tablepre, $db;
    
    if ($discuz_uid) {
        return ($db->result_first("SELECT COUNT(*) FROM {$tablepre}favoritethreads WHERE tid='$tid' AND uid='$discuz_uid'") ? true : false);
    }
    
    return false;
}

?>