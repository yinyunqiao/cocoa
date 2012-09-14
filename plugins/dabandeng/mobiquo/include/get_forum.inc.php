<?php

defined('IN_MOBIQUO') or exit;

require_once FROOT.'include/common.inc.php';
require_once DISCUZ_ROOT.'./include/forum.func.php';

$hide_forums = '';
if (!empty($mobiquo_config['hide_forum_id']))
{
    $fids = join(',', $mobiquo_config['hide_forum_id']);
    if($fids){
        $hide_forums = "AND f.fid NOT IN ($fids) ";
    }
}

$sql = !empty($accessmasks) ?
            "SELECT f.fid, f.name, f.fup, f.type, ff.description, ff.icon, ff.password, ff.viewperm, a.allowview FROM {$tablepre}forums f
                LEFT JOIN {$tablepre}forumfields ff ON ff.fid=f.fid
                LEFT JOIN {$tablepre}access a ON a.uid='$discuz_uid' AND a.fid=f.fid
                WHERE f.status>0 $hide_forums ORDER BY f.type, f.displayorder"
            : "SELECT f.fid, f.name, f.fup, f.type, ff.description, ff.icon, ff.password, ff.viewperm FROM {$tablepre}forums f
                LEFT JOIN {$tablepre}forumfields ff USING(fid)
                WHERE f.status>0 $hide_forums ORDER BY f.type, f.displayorder";

$query = $db->query($sql);
$forum_root = array(0 => array('fid' => 0, 'child' => array()));
$forum_g = $froum_f = $forum_s = array();
while($forum = $db->fetch_array($query)) {

    switch ($forum['type'])
    {
        case   'sub': $forum_s[] = $forum; break;
        case 'group': $forum_g[] = $forum; break;
        case 'forum': 
            $forum_icon = $forum['icon'];
            if(forum($forum)) {
                $forum['icon'] = $forum_icon;
                $froum_f[] = $forum;
            }
            break;
    }
}

foreach($forum_s as $s_forum) {
    insert_forum($froum_f, $s_forum);
}

foreach($froum_f as $f_forum) {
    insert_forum($forum_g, $f_forum);
}

foreach($forum_g as $g_forum) {
    if ($g_forum['child']) {
        insert_forum($forum_root, $g_forum);
    }
}

$forum_tree = $forum_root[0]['child'];


function insert_forum(&$forum_ups, $forum)
{
    global $boardurl;
    $board_url = substr($boardurl, 0, -26);
    $url_parse = parse_url($board_url);
    $site_url = $url_parse['scheme'].'://'.$url_parse['host'].(isset($url_parse['port']) && $url_parse['port'] ? ":$url_parse[port]" : '');
    
    foreach($forum_ups as $id => $forum_up)
    {
        if ($forum_up['fid'] == $forum['fup'])
        {
            $forum_id = $forum['fid'];
            $logo_url = '';
            if (file_exists("./forum_icons/$forum_id.png"))
            {
                $logo_url = $boardurl."forum_icons/$forum_id.png";
            }
            else if (file_exists("./forum_icons/$forum_id.jpg"))
            {
                $logo_url = $boardurl."forum_icons/$forum_id.jpg";
            }
            else if (file_exists("./forum_icons/default.png"))
            {
                $logo_url = $boardurl."forum_icons/default.png";
            }
            else if ($forum['icon'])
            {
                if (preg_match('/^http/', $forum['icon']))
                {
                    $logo_url = $forum['icon'];
                }
                else if (preg_match('/^\//', $forum['icon']))
                {
                    $logo_url = $site_url.$forum['icon'];
                }
                else
                {
                    $logo_url = $board_url.$forum['icon'];
                }
            }

            $xmlrpc_forum = new xmlrpcval(array(
                'forum_id'      => new xmlrpcval($forum['fid'], 'string'),
                'forum_name'    => new xmlrpcval(basic_clean($forum['name']), 'base64'),
                'description'   => new xmlrpcval(basic_clean($forum['description']), 'base64'),
                'parent_id'     => new xmlrpcval($forum['fup'], 'string'),
                'logo_url'      => new xmlrpcval($logo_url, 'string'),
                'is_protected'  => new xmlrpcval($forum['password'] ? true : false, 'boolean'),
                'url'           => new xmlrpcval('', 'string'),
                'sub_only'      => new xmlrpcval(($forum['type'] == 'group') ? true : false, 'boolean'),
             ), 'struct');

            if (isset($forum['child']))
            {
                $xmlrpc_forum->addStruct(array('child' => new xmlrpcval($forum['child'], 'array')));
            }

            $forum_ups[$id]['child'][] = $xmlrpc_forum;
            continue;
        }
    }
}

?>