<?php

defined('IN_MOBIQUO') or exit;

require_once FROOT.'include/common.inc.php';
get_language();

$dabandeng_request = array(
    1  => 'dabandeng_latest_picture',
    2  => 'dabandeng_forum_statistics',
    3  => 'dabandeng_latest_topic',
    4  => 'dabandeng_latest_reply',
    5  => 'dabandeng_latest_recommend',
    6  => 'dabandeng_latest_digest',
    7  => 'dabandeng_latest_stick',
    8  => 'dabandeng_most_reply',
    9  => 'dabandeng_most_view',
    10 => 'dabandeng_most_view_today',
    11 => 'dabandeng_most_view_week',
    12 => 'dabandeng_most_view_month',
    13 => 'dabandeng_hot',
);

include_once language('admincp');

if (isset($home_data_id) && $home_data_id) $mobiquo_config['home_data'] = array($home_data_id);

$home_array = array();
$board_url = substr($boardurl, 0, -26);
foreach($mobiquo_config['home_data'] as $id)
{
    if (isset($dabandeng_request[trim($id)])) {
        switch ($id) {
            case 1: 
                $data_type = 2;
                break;
            case 2: 
                $data_type = 3;
                break;
            default: $data_type = 1;
        }
        
        $data_title = $dabandeng_lang[$dabandeng_request[trim($id)]];
        
        $home_tab = request($dabandeng_request[trim($id)], 0, 0, 1);
        $data_array = preg_split('/<br *?\/?>/', $home_tab, -1, PREG_SPLIT_NO_EMPTY);

        $xmlrpc_data = array();
        
        foreach($data_array as $data)
        {
            if ($data_type == 2) {
                $fields = explode('|:|:|', $data);
                
                if (preg_match('/^http/i', $fields[0])) {
                    $url = $fields[0];
                } else {
                    $url = $board_url.$fields[0];
                }
                
                preg_match('/&ptid=(\d+)&pid=(\d+)/i', $fields[3], $matches);

                $xmlrpc_record = new xmlrpcval(array(
                    'url'           => new xmlrpcval(htmlspecialchars_decode($url), 'string'),
                    'description'   => new xmlrpcval(basic_clean($fields[1]), 'base64'),
                    'subject'       => new xmlrpcval(basic_clean($fields[2]), 'base64'),
                    'topic_title'   => new xmlrpcval(basic_clean($fields[2]), 'base64'),
                    'topic_id'      => new xmlrpcval($matches[1], 'string'),
                    'post_id'       => new xmlrpcval($matches[2], 'string')
                ), 'struct');
            } elseif ($data_type == 3) {
                $fields = explode(':', $data);
                $xmlrpc_record = new xmlrpcval(array(
                    'name'  => new xmlrpcval(basic_clean($lang[$fields[0]]), 'base64'),
                    'value' => new xmlrpcval($fields[1], 'base64'),
                ), 'struct');
            } else {
                $fields = explode('|:|:|', $data);
                preg_match('/<a href=\'(forumdisplay\.php\?fid=(\d+)|forum-(\d+)-.*?)\'.*?>(.*?)<\/a>/i', $fields[0], $matches);
                
                $xmlrpc_record =  new xmlrpcval(array(
                    'forum_id'          => new xmlrpcval($matches[2].$matches[3], 'string'),
                    'forum_name'        => new xmlrpcval(basic_clean($matches[4]), 'base64'),
                    'topic_title'       => new xmlrpcval(basic_clean($fields[1]), 'base64'),
                    'topic_author_name' => new xmlrpcval(basic_clean($fields[2]), 'base64'),
                    'first_time'        => new xmlrpcval($fields[3], 'string'),
                    'short_content'     => new xmlrpcval(basic_clean($fields[4]), 'base64'),
                    'post_author_name'  => new xmlrpcval(basic_clean($fields[5]), 'base64'),
                    'last_time'         => new xmlrpcval($fields[6], 'string'),
                    'topic_id'          => new xmlrpcval($fields[7], 'string'),
                    'reply_number'      => new xmlrpcval($fields[8], 'int'),
                    'view_number'       => new xmlrpcval($fields[9], 'int'),
                    'authhor'           => new xmlrpcval(basic_clean($id == 4 ? $fields[5] : $fields[2]), 'base64'),
                    'time'              => new xmlrpcval(format_time($fields[6]), 'dateTime.iso8601'),
                ), 'struct');
            }
            
            $xmlrpc_data[] = $xmlrpc_record;
        }
        
        $home_array[] = new xmlrpcval(array(
            'type'  => new xmlrpcval($data_type, 'int'),     
            'title' => new xmlrpcval(basic_clean($data_title), 'base64'),
            'data'  => new xmlrpcval($xmlrpc_data, 'array'),
        ), 'struct');
    }
}

?>