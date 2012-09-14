<?php

defined('IN_MOBIQUO') or exit;

function attach_image_func()
{
    global $aid;

    if ($aid)
    {
        $xmlrpc_result = new xmlrpcval(array('attachment_id'  => new xmlrpcval($aid)), 'struct');
        return new xmlrpcresp($xmlrpc_result);
    }
    else
    {
        get_error('Line: '.__LINE__);
    }
}

function authorize_user_func()
{
    global $discuz_uid;
    
    $response = new xmlrpcval(array(
        'authorize_result' => new xmlrpcval(true, 'boolean'),
        'user_id' => new xmlrpcval($discuz_uid, 'string'),
    ), 'struct');

    return new xmlrpcresp($response);
}

function login_forum_func()
{
    global $login_status;
    
    $response = new xmlrpcval(
        array(
            'result'        => new xmlrpcval($login_status, 'boolean'),
            'result_text'   => new xmlrpcval($login_status ? '' : 'Password is wrong', 'base64'),
        ),
        'struct'
    );

    return new xmlrpcresp($response);
}

function create_topic_func()
{
    global $tid, $pinvisible;

    $xmlrpc_create_topic = new xmlrpcval(array(
        'result'    => new xmlrpcval(true, 'boolean'),
        'topic_id'  => new xmlrpcval($tid),
//      'state'     => new xmlrpcval($pinvisible == 0 ? 0 : 1)
    ), 'struct');

    return new xmlrpcresp($xmlrpc_create_topic);
}

function get_board_stat_func()
{
    global $onlinenum, $guestnum, $totalmembers, $threads, $posts;

    $board_stat = array(
        'total_threads' => new xmlrpcval($threads, 'int'),
        'total_posts'   => new xmlrpcval($posts, 'int'),
        'total_members' => new xmlrpcval($totalmembers, 'int'),
        'guest_online'  => new xmlrpcval($guestnum, 'int'),
        'total_online'  => new xmlrpcval($onlinenum, 'int')
    );

    $response = new xmlrpcval($board_stat, 'struct');

    return new xmlrpcresp($response);
}

function get_box_func()
{
    global $pmlist, $pmnum, $newpmnum;

    $pm_list = array();
    foreach ($pmlist as $pm)
    {
        $msg_to = array(new xmlrpcval(array('username' => new xmlrpcval(basic_clean($pm['msgto']), 'base64')), 'struct'));
        $pm_list[] = new xmlrpcval(array(
            'msg_id'        => new xmlrpcval($pm['pmid']),
            'msg_state'     => new xmlrpcval($pm['new'] ? 1 : 2, 'int'),
            'sent_date'     => new xmlrpcval(mobiquo_iso8601_encode($pm['dateline']),'dateTime.iso8601'),
            'msg_from'      => new xmlrpcval(basic_clean($pm['msgfrom']), 'base64'),
            'icon_url'      => new xmlrpcval(htmlspecialchars_decode($pm['icon_url'])),
            'msg_to'        => new xmlrpcval($msg_to, 'array'),
            'msg_subject'   => new xmlrpcval(basic_clean($pm['subject']), 'base64'),
            'short_content' => new xmlrpcval(get_short_content($pm['message']), 'base64')
        ), 'struct');
    }

    $result = new xmlrpcval(array(
        'total_message_count' => new xmlrpcval($pmnum, 'int'),
        'total_unread_count'  => new xmlrpcval($newpmnum, 'int'),
        'list'                => new xmlrpcval($pm_list, 'array')
    ), 'struct');

    return new xmlrpcresp($result);
}

function get_box_info_func()
{
    global $error_code, $box_info;

    $box_list = array();
    foreach($box_info as $box)
    {
        $box_list[] = new xmlrpcval(array(
            'box_id'        => new xmlrpcval($box['box_id'], 'string'),
            'box_name'      => new xmlrpcval(basic_clean($box['box_name']), 'base64'),
            'msg_count'     => new xmlrpcval($box['msg_count'], 'int'),
            'unread_count'  => new xmlrpcval($box['unread_count'], 'int'),
            'box_type'      => new xmlrpcval($box['box_type'], 'string')
        ), 'struct');
    }

    $result = new xmlrpcval(array(
        'message_room_count' => new xmlrpcval(100, 'int'),
        'list'               => new xmlrpcval($box_list, 'array')
    ), 'struct');

    return new xmlrpcresp($result);
}

function get_config_func()
{
    global $mobiquo_config, $bbname, $regname, $dabandeng_version;
    
    $config_list = array(
        'version'    => new xmlrpcval($mobiquo_config['version'], 'string'),
        'is_open'    => new xmlrpcval(true, 'boolean'),
        'guest_okay' => new xmlrpcval($mobiquo_config['guest_okay'] ? true : false, 'boolean'),
        'reg_url'    => new xmlrpcval($regname, 'string'),
        'forum_name' => new xmlrpcval(basic_clean($bbname), 'base64'),
        'forum_description' => new xmlrpcval('', 'base64'),
        'hide_forum_id'     => new xmlrpcval(join(',', $mobiquo_config['hide_forum_id']), 'base64'),
        'home_data'         => new xmlrpcval(join(',', $mobiquo_config['home_data']), 'base64'),
        'show_home_data'    => new xmlrpcval(count($mobiquo_config['home_data']) ? true : false, 'boolean'),
    );

    $response = new xmlrpcval($config_list, 'struct');

    return new xmlrpcresp($response);
}

function get_forum_func()
{
    global $forum_tree;

    $response = new xmlrpcval($forum_tree, 'array');

    return new xmlrpcresp($response);
}

function get_inbox_stat_func()
{
    global $ucnewpm;

    $result = new xmlrpcval(array(
        'inbox_unread_count' => new xmlrpcval($ucnewpm, 'int')
    ), 'struct');

    return new xmlrpcresp($result);
}

function get_message_func()
{
    global $pm;

    $msg_to = array(new xmlrpcval(array('username' => new xmlrpcval(basic_clean($pm['msgto']), 'base64')), 'struct'));
    $result = new xmlrpcval(array(
        'msg_from'      => new xmlrpcval(basic_clean($pm['msgfrom']), 'base64'),
        'msg_to'        => new xmlrpcval($msg_to, 'array'),
        'icon_url'      => new xmlrpcval(htmlspecialchars_decode($pm['icon_url'])),
        'sent_date'     => new xmlrpcval(mobiquo_iso8601_encode($pm['dateline']),'dateTime.iso8601'),
        'msg_subject'   => new xmlrpcval(basic_clean($pm['subject']), 'base64'),
        'text_body'     => new xmlrpcval(get_message($pm['message']), 'base64')
    ), 'struct');

    return new xmlrpcresp($result);
}

function get_new_topic_func()
{
    global $threadlist, $discuz_uid, $readaccess;

    $topic_list = array();
    foreach ($threadlist as $thread)
    {
        
        $short_content = true;
        if($thread['readperm'] && $thread['readperm'] > $readaccess && $thread['authorid'] != $discuz_uid) {
            $short_content = false;
        }
        
        $xmlrpc_topic = new xmlrpcval(array(
            'forum_id'          => new xmlrpcval($thread['fid']),
            'forum_name'        => new xmlrpcval(basic_clean($thread['forumname']), 'base64'),
            'topic_id'          => new xmlrpcval($thread['tid']),
            'topic_title'       => new xmlrpcval(basic_clean($thread['subject']), 'base64'),
            'reply_number'      => new xmlrpcval($thread['replies'], 'int'),
            'short_content'     => new xmlrpcval($short_content ? get_short_content($thread['message']) : '', 'base64'),
            'post_author_id'    => new xmlrpcval($thread['authorid']),
            'post_author_name'  => new xmlrpcval(basic_clean($thread['author']), 'base64'),
            'new_post'          => new xmlrpcval($thread['new'] ? true : false, 'boolean'),
            'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($thread['dblastpost']), 'dateTime.iso8601'),
            'icon_url'          => new xmlrpcval(htmlspecialchars_decode(discuz_uc_avatar($thread['authorid'], '', true))),
            
            'can_subscribe'     => new xmlrpcval($discuz_uid ? true : false, 'boolean'),
            'is_subscribed'     => new xmlrpcval(is_subscribed($thread['tid']), 'boolean'),
        ), 'struct');

        $topic_list[] = $xmlrpc_topic;
    }

    return new xmlrpcresp(new xmlrpcval($topic_list, 'array'));
}

function get_online_users_func()
{
    global $onlinelist, $usernum, $guestnum;

    $user_list = array();
    foreach($onlinelist as $user)
    {
        $user_list[] = new xmlrpcval(array(
            'user_name' => new xmlrpcval(basic_clean($user['username']), 'base64'),
            'icon_url'  => new xmlrpcval(htmlspecialchars_decode($user['url']))
        ), 'struct');
    }

    $online_users = array(
        'member_count' => new xmlrpcval($usernum, 'int'),
        'guest_count'  => new xmlrpcval($guestnum, 'int'),
        'list'         => new xmlrpcval($user_list, 'array')
    );

    $response = new xmlrpcval($online_users, 'struct');

    return new xmlrpcresp($response);
}

function get_raw_post_func()
{
    global $postinfo;
    
    $message = preg_replace('/^\[i=.*?\].*?\[\/i\]\s\s/si', '', $postinfo['message']);
    
    $response = new xmlrpcval(
        array(
            'post_id'       => new xmlrpcval($postinfo['pid']),
            'post_title'    => new xmlrpcval(basic_clean($postinfo['subject']), 'base64'),
            'post_content'  => new xmlrpcval(basic_clean($message), 'base64'),
        ),
        'struct'
    );
    
    return new xmlrpcresp($response);
}

function get_bookmarked_topic_func()
{
    global $favlist, $num, $discuz_uid;

    $topic_list = array();
    foreach ($favlist as $fav)
    {
        $xmlrpc_topic = new xmlrpcval(array(
            'forum_id'          => new xmlrpcval($fav['fid'], 'string'),
            'forum_name'        => new xmlrpcval(basic_clean($fav['name']), 'base64'),
            'topic_id'          => new xmlrpcval($fav['t_tid'], 'string'),
            'topic_title'       => new xmlrpcval(basic_clean($fav['subject']), 'base64'),
            'reply_number'      => new xmlrpcval($fav['replies'], 'int'),
            'short_content'     => new xmlrpcval(get_short_content($fav['message']), 'base64'),
            'icon_url'          => new xmlrpcval(htmlspecialchars_decode(discuz_uc_avatar($fav['authorid'], '', true))),
            'post_author_name'  => new xmlrpcval(basic_clean($fav['lastposter']), 'base64'),
            'new_post'          => new xmlrpcval($fav['new'] ? true : false, 'boolean'),
            'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($fav['lastpost']), 'dateTime.iso8601'),
            
            'can_subscribe'     => new xmlrpcval($discuz_uid ? true : false, 'boolean'),
            'is_subscribed'     => new xmlrpcval(is_subscribed($fav['t_tid']), 'boolean'),
        ), 'struct');

        $topic_list[] = $xmlrpc_topic;
    }

    $response = new xmlrpcval(
        array(
            'topic_num' => new xmlrpcval($$num, 'int'),
            'topics'    => new xmlrpcval($topic_list, 'array'),
        ),
        'struct'
    );

    return new xmlrpcresp($response);
}

function get_subscribed_topic_func()
{
    global $attentionlist, $num;

    $topic_list = array();
    foreach ($attentionlist as $fav)
    {
        $xmlrpc_topic = new xmlrpcval(array(
            'forum_id'          => new xmlrpcval($fav['fid'], 'string'),
            'forum_name'        => new xmlrpcval(basic_clean($fav['name']), 'base64'),
            'topic_id'          => new xmlrpcval($fav['tid'], 'string'),
            'topic_title'       => new xmlrpcval(basic_clean($fav['subject']), 'base64'),
            'reply_number'      => new xmlrpcval($fav['replies'], 'int'),
            'short_content'     => new xmlrpcval(get_short_content($fav['message']), 'base64'),
            'icon_url'          => new xmlrpcval(htmlspecialchars_decode(discuz_uc_avatar($fav['authorid'], '', true))),
            'post_author_name'  => new xmlrpcval(basic_clean($fav['lastposter']), 'base64'),
            'new_post'          => new xmlrpcval($fav['new'] ? true : false, 'boolean'),
            'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($fav['lastpost']), 'dateTime.iso8601')
        ), 'struct');

        $topic_list[] = $xmlrpc_topic;
    }

    $response = new xmlrpcval(
        array(
            'topic_num' => new xmlrpcval($num, 'int'),
            'topics'    => new xmlrpcval($topic_list, 'array'),
        ),
        'struct'
    );

    return new xmlrpcresp($response);
}

function get_thread_func()
{
    global $postlist, $attachedids, $thread, $ordertype, $boardurl, $readaccess, $discuz_uid, $pluginhooks, $bannedmessages, $adminid, $forum;
    $board_url = substr($boardurl, 0, -27);

    include_once language('templates');
    
    $rpc_post_list = array();
    $post_place = 0;

    foreach($postlist as $post) {
        
        // banned user, avatar may not display
        if ($bannedmessages & 2 && (($post['authorid'] && !$post['username']) || ($post['groupid'] == 4 || $post['groupid'] == 5) || ($post['status'] & 1))) {
            $post['avatar'] = '';
        }
        
        if ($adminid != 1 && $bannedmessages & 1 && (($post['authorid'] && !$post['username']) || ($post['groupid'] == 4 || $post['groupid'] == 5))) {
            $post_content = '['.basic_clean($language['message_banned']).']';
        } elseif ($adminid != 1 && $post['status'] & 1) {
            $post_content = '['.basic_clean($language['message_single_banned']).']';
        } elseif ($post['needhiddenreply']) {
            $post_content = '['.basic_clean($language['message_ishidden_hiddenreplies']).']';
        } elseif ($post['first'] && $threadpay) {
            $post_content = $thread['freemessage']."\n[".strip_tags('pay_comment').']';
        } else {
            $post_content = '';
            if ($bannedmessages & 1 && (($post['authorid'] && !$post['username']) || ($post['groupid'] == 4 || $post['groupid'] == 5))) {
                $post_content .= '['.basic_clean($language['admin_message_banned'])."]\n";
            } elseif ($post['status'] & 1) {
                $post_content .= '['.basic_clean($language['admin_message_single_banned'])."]\n";
            }
            
            $post_content .= post_html_clean($post['message']);
            
            $attachments = array();
            if(!$post['attachment']) {
                $attach_tags = $inside_aids = $attach_contents = array();
                if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $post['message'], $matchaids)) {
                    $attach_tags = $matchaids[0];
                    $inside_aids = $matchaids[1];
                }
                
                foreach($inside_aids as $aid){
                    $attach = $post['attachments'][$aid];
                    
                    if ($readaccess < $attach['readperm'] || ($attach['price'] && ($attach['unpayed'] || !$attach['payed']))) {
                        $attachicon = preg_replace('/^<img src="(.*?)" .*?$/si', '$1', $attach['attachicon']);
                        $filename = basic_clean($attach['filename']);
                        $attach_url = "[img]$board_url/$attachicon"."[/img]$filename ($attach[attachsize])\n[ 附件: 权限不足或为收费附件 ]";
                        unset($post['attachments'][$aid]);
                    } else {
                        if ($attach['isimage']) {
                            $attach_url = "[img]$board_url/attachment.php?aid=".aidencode($attach['aid'])."[/img]";
                            unset($post['attachments'][$aid]);
                        } else {
                            $filename = basic_clean($attach['filename']);
                            $attach_url = "[url=$board_url/attachment.php?aid=".aidencode($attach['aid'])."]$filename [/url] ($attach[attachsize])";
                        }
                    }
                    
                    $attach_contents[] = preg_replace('/http:.*?http:/', 'http:', $attach_url);
                }
    
                $post_content = str_replace($attach_tags, $attach_contents, $post_content);
                
                foreach($post['attachments'] as $attachment)
                {
                    if ($attachment['isimage'] && is_array($attachedids[$attachment['pid']]) && in_array($attachment['aid'], $attachedids[$attachment['pid']])) {
                        continue;
                    }
                    
                    if ($readaccess < $attachment['readperm'] || ($attachment['price'] && ($attachment['unpayed'] || !$attachment['payed']))) {
                        $attachicon = preg_replace('/^<img src="(.*?)" .*?$/si', '$1', $attachment['attachicon']);
                        $attach_url = "$board_url/$attachicon";
                    } else {
                        $attach_url = "$board_url/attachment.php?aid=".aidencode($attachment['aid']);
                    }
                    
                    $attach_url = preg_replace('/http:.*?http:/', 'http:', $attach_url);
                    
                    $xmlrpc_attachment = new xmlrpcval(array(
                        'filename'      => new xmlrpcval(basic_clean($attachment['filename']), 'base64'),
                        'filesize'      => new xmlrpcval($attachment['filesize'], 'int'),
                        'content_type'  => new xmlrpcval($attachment['isimage'] ? 'image' : 'others'),
                        'thumbnail_url' => new xmlrpcval(''),
                        'url'           => new xmlrpcval($attach_url)
                    ), 'struct');
                    $attachments[] = $xmlrpc_attachment;
                }
            } else {
                if ($discuz_uid) {
                    $post_content .= "\n\n[ 附件: 您所在的用户组无法查看附件 ]";
                } else {
                    $post_content .= "\n\n[ 附件: 您需要登录才可以查看附件 ]";
                }
            }
            
            if(is_array($pluginhooks['viewthread_postbottom'])) {
                $post_content .= post_reply_clean($pluginhooks['viewthread_postbottom'][$post_place]);
                $post_place++;
            }
        }

        $post_time = mobiquo_iso8601_encode($post['dbdateline']);

        $xmlrpc_post = new xmlrpcval(array(
            'topic_id'          => new xmlrpcval($post['tid']),
            'post_id'           => new xmlrpcval($post['pid']),
            'post_title'        => new xmlrpcval(basic_clean($post['subject']), 'base64'),
            'post_content'      => new xmlrpcval($post_content, 'base64'),
            'post_author_id'    => new xmlrpcval($post['authorid']),
            'post_author_name'  => new xmlrpcval(basic_clean($post['author']), 'base64'),
            'icon_url'          => new xmlrpcval(htmlspecialchars_decode($post['avatar'])),
            'post_time'         => new xmlrpcval($post_time, 'dateTime.iso8601'),
            'attachments'       => new xmlrpcval($attachments, 'array')
        ), 'struct');

        $rpc_post_list[] = $xmlrpc_post;
    }

    return new xmlrpcresp(
        new xmlrpcval(array(
                'total_post_num' => new xmlrpcval($thread['replies'] + 1, 'int'),
                'sort_order'     => new xmlrpcval(($ordertype != 1) ? 'ASC' : 'DESC'),
                'forum_id'       => new xmlrpcval($thread['fid']),
                'forum_name'     => new xmlrpcval(basic_clean($forum['name']), 'base64'),
                'topic_id'       => new xmlrpcval($thread['tid']),
                'topic_title'    => new xmlrpcval(basic_clean($thread['subject']), 'base64'),
                'posts'          => new xmlrpcval($rpc_post_list, 'array'),
                
                'can_subscribe'  => new xmlrpcval($discuz_uid ? true : false, 'boolean'),
                'is_subscribed'  => new xmlrpcval(is_subscribed($thread['tid']), 'boolean'),
            ),
            'struct'
        )
    );
}

function get_topic_func()
{
    global $threadlist, $total_topic_num, $prefixes, $forum, $readaccess, $discuz_uid;

    $prefix_list = array();
    foreach($prefixes as $prefix_id => $prefix_name) {
        $xmlrpc_prefix = new xmlrpcval(array(
            'prefix_id'           => new xmlrpcval($prefix_id, 'string'),
            'prefix_display_name' => new xmlrpcval(basic_clean($prefix_name), 'base64'),
        ), 'struct');

        $prefix_list[] = $xmlrpc_prefix;
    }
    
    $topic_list = array();
    foreach($threadlist as $thread) {
        $short_content = true;
        if($thread['readperm'] && $thread['readperm'] > $readaccess && !$forum['ismoderator'] && $thread['authorid'] != $discuz_uid) {
            $short_content = false;
        }
        
        $xmlrpc_topic = new xmlrpcval(array(
            'forum_id'          => new xmlrpcval($thread['fid'], 'string'),
            'topic_id'          => new xmlrpcval($thread['tid'], 'string'),
            'topic_title'       => new xmlrpcval(basic_clean($thread['subject']), 'base64'),
            'topic_author_id'   => new xmlrpcval($thread['authorid'], 'string'),
            'topic_author_name' => new xmlrpcval(basic_clean($thread['author']), 'base64'),
            'last_reply_time'   => new xmlrpcval(mobiquo_iso8601_encode($thread['dblastpost']),'dateTime.iso8601'),
            'reply_number'      => new xmlrpcval($thread['replies'], 'int'),
            'new_post'          => new xmlrpcval($thread['new'] ? true : false, 'boolean'),
            'view_number'       => new xmlrpcval($thread['views'], 'int'),
            'short_content'     => new xmlrpcval($short_content ? get_short_content($thread['message']) : '', 'base64'),
            'icon_url'          => new xmlrpcval(htmlspecialchars_decode(discuz_uc_avatar($thread['authorid'], '', true))),
            'attachment'        => new xmlrpcval($thread['attachment'], 'string'),
            
            'can_subscribe'     => new xmlrpcval($discuz_uid ? true : false, 'boolean'),
            'is_subscribed'     => new xmlrpcval(is_subscribed($thread['tid']), 'boolean'),
        ), 'struct');

        $topic_list[] = $xmlrpc_topic;
    }

    $response = new xmlrpcval(
        array(
            'total_topic_num' => new xmlrpcval($total_topic_num, 'int'),
            'topics'          => new xmlrpcval($topic_list, 'array'),
            'prefixes'        => new xmlrpcval($prefix_list, 'array')
        ),
        'struct'
    );

    return new xmlrpcresp($response);
}

function get_user_info_func()
{
    global $member;

    $xmlrpc_user_info = new xmlrpcval(array(
        'post_count'            => new xmlrpcval($member['posts'], 'int'),
        'reg_time'              => new xmlrpcval(mobiquo_iso8601_encode($member['regdate']), 'dateTime.iso8601'),
        'last_activity_time'    => new xmlrpcval(mobiquo_iso8601_encode($member['lastvisit']), 'dateTime.iso8601'),
        'thread_sort_order'     => new xmlrpcval('DATE_DESC'),
        'icon_url'              => new xmlrpcval(htmlspecialchars_decode(discuz_uc_avatar($member['uid'], '', true)))
    ), 'struct');

    return new xmlrpcresp($xmlrpc_user_info);
}

function get_user_reply_post_func()
{
    global $postlist;

    $post_list = array();
    foreach($postlist as $post)
    {
        $xmlrpc_post = new xmlrpcval(array(
            'forum_id'          => new xmlrpcval($post['fid']),
            'forum_name'        => new xmlrpcval(basic_clean($post['forumname']), 'base64'),
            'topic_id'          => new xmlrpcval($post['tid']),
            'topic_title'       => new xmlrpcval(basic_clean($post['t_subject']), 'base64'),
            'post_id'           => new xmlrpcval($post['pid']),
            'post_title'        => new xmlrpcval(basic_clean($post['p_subject']), 'base64'),
            'short_content'     => new xmlrpcval(get_short_content($post['message']), 'base64'),
            'icon_url'          => new xmlrpcval(htmlspecialchars_decode(discuz_uc_avatar($post['authorid'], '', true))),
            'reply_number'      => new xmlrpcval($post['replies'], 'int'),
            'post_position'     => new xmlrpcval($post['position'], 'int'),
            'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($post['dateline']), 'dateTime.iso8601'),
        ), 'struct');

        $post_list[] = $xmlrpc_post;
    }

    return new xmlrpcresp(new xmlrpcval($post_list, 'array'));
}

function get_user_topic_func()
{
    global $threadlist, $discuz_uid, $readaccess;

    $topic_list = array();
    foreach($threadlist as $thread)
    {
        $short_content = true;
        if($thread['readperm'] && $thread['readperm'] > $readaccess && $thread['authorid'] != $discuz_uid) {
            $short_content = false;
        }
        
        $xmlrpc_topic = new xmlrpcval(array(
            'forum_id'          => new xmlrpcval($thread['fid']),
            'forum_name'        => new xmlrpcval(basic_clean($thread['forumname']), 'base64'),
            'topic_id'          => new xmlrpcval($thread['tid']),
            'topic_title'       => new xmlrpcval(basic_clean($thread['subject']), 'base64'),
            'icon_url'          => new xmlrpcval(htmlspecialchars_decode(discuz_uc_avatar($thread['authorid'], '', true))),
            'topic_author_id'   => new xmlrpcval($thread['authorid']),
            'topic_author_name' => new xmlrpcval(basic_clean($thread['author']), 'base64'),
            'last_reply_time'   => new xmlrpcval(mobiquo_iso8601_encode($thread['dblastpost']), 'dateTime.iso8601'),
            'reply_number'      => new xmlrpcval($thread['replies'], 'int'),
            'view_number'       => new xmlrpcval($thread['views'], 'int'),
            'short_content'     => new xmlrpcval($short_content ? get_short_content($thread['message']) : '', 'base64'),
            
            'can_subscribe'     => new xmlrpcval($discuz_uid ? true : false, 'boolean'),
            'is_subscribed'     => new xmlrpcval(is_subscribed($thread['tid']), 'boolean'),
        ), 'struct');

        $topic_list[] = $xmlrpc_topic;
    }

    return new xmlrpcresp(new xmlrpcval($topic_list, 'array'));
}

function reply_topic_func()
{
    global $pid, $pinvisible;

    $xmlrpc_reply_topic = new xmlrpcval(array(
        'result'    => new xmlrpcval(true, 'boolean'),
        'post_id'   => new xmlrpcval($pid),
//      'state'     => new xmlrpcval($pinvisible == 0 ? 0 : 1)
    ), 'struct');

    return new xmlrpcresp($xmlrpc_reply_topic);
}

function save_raw_post_func()
{
    return new xmlrpcresp(new xmlrpcval(array('result' => new xmlrpcval(true, 'boolean')), 'struct'));
}

function get_search_option_func()
{
    global $disabled, $language, $forums;

    $field_list = array();
    
    // add option for search key field
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval('', 'base64'),
        'value'     => new xmlrpcval('', 'base64'),
    ), 'struct');
    
    $field = new xmlrpcval(array(
        'type'      => new xmlrpcval('text', 'base64'),
        'field'     => new xmlrpcval('srchtxt', 'base64'),
        'title'     => new xmlrpcval('', 'base64'),
        'option'    => new xmlrpcval(array($option), 'array'),
    ), 'struct');
    
    $field_list[] = $field;
    
    // add option for search type: title or fulltext
    $options = array();
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['title'], 'base64'),
        'value'     => new xmlrpcval('title', 'base64'),
        'default'   => new xmlrpcval(true, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    if (!$disabled['fulltext']) {
        $option = new xmlrpcval(array(
            'title'     => new xmlrpcval($language['fulltext'], 'base64'),
            'value'     => new xmlrpcval('fulltext', 'base64'),
            'default'   => new xmlrpcval(false, 'boolean'),
        ), 'struct');
        $options[] = $option;
    }
    
    $field = new xmlrpcval(array(
        'type'      => new xmlrpcval('radio', 'base64'),
        'field'     => new xmlrpcval('srchtype', 'base64'),
        'title'     => new xmlrpcval('', 'base64'),
        'option'    => new xmlrpcval($options, 'array'),
    ), 'struct');
    
    $field_list[] = $field;
    
    // add option for search by user name
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval('', 'base64'),
        'value'     => new xmlrpcval('', 'base64'),
    ), 'struct');
    
    $field = new xmlrpcval(array(
        'type'      => new xmlrpcval('text', 'base64'),
        'field'     => new xmlrpcval('srchuname', 'base64'),
        'title'     => new xmlrpcval($language['author'], 'base64'),
        'option'    => new xmlrpcval(array($option), 'array'),
    ), 'struct');
    
    $field_list[] = $field;
    
    // add option for search range: all, digest, or top      
    $options = array();
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['search_thread_range_all'], 'base64'),
        'value'     => new xmlrpcval('all', 'base64'),
        'default'   => new xmlrpcval(true, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['search_thread_range_digest'], 'base64'),
        'value'     => new xmlrpcval('digest', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['search_thread_range_top'], 'base64'),
        'value'     => new xmlrpcval('top', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $field = new xmlrpcval(array(
        'type'      => new xmlrpcval('radio', 'base64'),
        'field'     => new xmlrpcval('srchfilter', 'base64'),
        'title'     => new xmlrpcval($language['search_thread_range'], 'base64'),
        'option'    => new xmlrpcval($options, 'array'),
    ), 'struct');
    
    $field_list[] = $field;
    
    // add option for special thread search
    $options = array();
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['special_poll'], 'base64'),
        'value'     => new xmlrpcval('1', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['special_trade'], 'base64'),
        'value'     => new xmlrpcval('2', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['special_reward'], 'base64'),
        'value'     => new xmlrpcval('3', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['special_activity'], 'base64'),
        'value'     => new xmlrpcval('4', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['special_debate'], 'base64'),
        'value'     => new xmlrpcval('5', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $field = new xmlrpcval(array(
        'type'      => new xmlrpcval('checkbox', 'base64'),
        'field'     => new xmlrpcval('special', 'base64'),
        'title'     => new xmlrpcval($language['special_thread'], 'base64'),
        'option'    => new xmlrpcval($options, 'array'),
    ), 'struct');
    
    $field_list[] = $field;
    
    // add option for search time control
    $options = array();
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['search_any_date'], 'base64'),
        'value'     => new xmlrpcval('0', 'base64'),
        'default'   => new xmlrpcval(true, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['1_days_ago'], 'base64'),
        'value'     => new xmlrpcval('86400', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['2_days_ago'], 'base64'),
        'value'     => new xmlrpcval('172800', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['7_days_ago'], 'base64'),
        'value'     => new xmlrpcval('604800', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['30_days_ago'], 'base64'),
        'value'     => new xmlrpcval('2592000', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['90_days_ago'], 'base64'),
        'value'     => new xmlrpcval('7776000', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['180_days_ago'], 'base64'),
        'value'     => new xmlrpcval('15552000', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['365_days_ago'], 'base64'),
        'value'     => new xmlrpcval('31536000', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $field = new xmlrpcval(array(
        'type'      => new xmlrpcval('radio', 'base64'),
        'field'     => new xmlrpcval('srchfrom', 'base64'),
        'title'     => new xmlrpcval($language['search_time'], 'base64'),
        'option'    => new xmlrpcval($options, 'array'),
    ), 'struct');
    
    $field_list[] = $field;
    
    // add option for search time order
    $options = array();
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['search_newer'], 'base64'),
        'value'     => new xmlrpcval('', 'base64'),
        'default'   => new xmlrpcval(true, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    if (!$disabled['fulltext']) {
        $option = new xmlrpcval(array(
            'title'     => new xmlrpcval($language['search_older'], 'base64'),
            'value'     => new xmlrpcval('1', 'base64'),
            'default'   => new xmlrpcval(false, 'boolean'),
        ), 'struct');
        $options[] = $option;
    }
    
    $field = new xmlrpcval(array(
        'type'      => new xmlrpcval('radio', 'base64'),
        'field'     => new xmlrpcval('before', 'base64'),
        'title'     => new xmlrpcval('', 'base64'),
        'option'    => new xmlrpcval($options, 'array'),
    ), 'struct');
    
    $field_list[] = $field;
    
    // add option for search order control
    $options = array();
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['order_lastpost'], 'base64'),
        'value'     => new xmlrpcval('lastpost', 'base64'),
        'default'   => new xmlrpcval(true, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['order_starttime'], 'base64'),
        'value'     => new xmlrpcval('dateline', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['order_replies'], 'base64'),
        'value'     => new xmlrpcval('replies', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['order_views'], 'base64'),
        'value'     => new xmlrpcval('views', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    $field = new xmlrpcval(array(
        'type'      => new xmlrpcval('radio', 'base64'),
        'field'     => new xmlrpcval('orderby', 'base64'),
        'title'     => new xmlrpcval($language['search_orderby'], 'base64'),
        'option'    => new xmlrpcval($options, 'array'),
    ), 'struct');
    
    $field_list[] = $field;
    
    // add option for search time order: desc or asc
    $options = array();
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['order_asc'], 'base64'),
        'value'     => new xmlrpcval('asc', 'base64'),
        'default'   => new xmlrpcval(false, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    if (!$disabled['fulltext']) {
        $option = new xmlrpcval(array(
            'title'     => new xmlrpcval($language['order_desc'], 'base64'),
            'value'     => new xmlrpcval('desc', 'base64'),
            'default'   => new xmlrpcval(true, 'boolean'),
        ), 'struct');
        $options[] = $option;
    }
    
    $field = new xmlrpcval(array(
        'type'      => new xmlrpcval('radio', 'base64'),
        'field'     => new xmlrpcval('ascdesc', 'base64'),
        'title'     => new xmlrpcval('', 'base64'),
        'option'    => new xmlrpcval($options, 'array'),
    ), 'struct');
    
    $field_list[] = $field;
    
    // add option for search range
    $options = array();
    
    $option = new xmlrpcval(array(
        'title'     => new xmlrpcval($language['search_allforum'], 'base64'),
        'value'     => new xmlrpcval('all', 'base64'),
        'default'   => new xmlrpcval(true, 'boolean'),
    ), 'struct');
    
    $options[] = $option;
    
    
    foreach($forums as $forum) 
    {
        $option = new xmlrpcval(array(
            'title'     => new xmlrpcval($forum[2], 'base64'),
            'value'     => new xmlrpcval($forum[1], 'base64'),
            'default'   => new xmlrpcval(false, 'boolean'),
        ), 'struct');
        
        $options[] = $option;
    }
    
    $field = new xmlrpcval(array(
        'type'      => new xmlrpcval('checkbox', 'base64'),
        'field'     => new xmlrpcval('srchfid', 'base64'),
        'title'     => new xmlrpcval($language['search_range'], 'base64'),
        'option'    => new xmlrpcval($options, 'array'),
    ), 'struct');
    
    $field_list[] = $field;
    
    return new xmlrpcresp(new xmlrpcval($field_list, 'array'));
}

function search_func()
{
    global $threadlist, $discuz_uid, $readaccess, $searchid, $result_num;

    $topic_list = array();
    foreach($threadlist as $thread)
    {
        $short_content = true;
        if($thread['readperm'] && $thread['readperm'] > $readaccess && $thread['authorid'] != $discuz_uid) {
            $short_content = false;
        }
        
        $xmlrpc_topic = new xmlrpcval(array(
            'forum_id'          => new xmlrpcval($thread['fid']),
            'forum_name'        => new xmlrpcval(basic_clean($thread['forumname']), 'base64'),
            'topic_id'          => new xmlrpcval($thread['tid']),
            'topic_title'       => new xmlrpcval(basic_clean($thread['subject']), 'base64'),
            'icon_url'          => new xmlrpcval(htmlspecialchars_decode(discuz_uc_avatar($thread['authorid'], '', true))),
            'post_author_name'  => new xmlrpcval(basic_clean($thread['author']), 'base64'),
            'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($thread['dbdateline']), 'dateTime.iso8601'),
            'reply_number'      => new xmlrpcval($thread['replies'], 'int'),
            'view_number'       => new xmlrpcval($thread['views'], 'int'),
            'short_content'     => new xmlrpcval($short_content ? get_short_content($thread['message']) : '', 'base64'),
            'new_post'          => new xmlrpcval($thread['new'] ? true : false, 'boolean'),
            
            'can_subscribe'     => new xmlrpcval($discuz_uid ? true : false, 'boolean'),
            'is_subscribed'     => new xmlrpcval(is_subscribed($thread['tid']), 'boolean'),
        ), 'struct');

        $topic_list[] = $xmlrpc_topic;
    }

    $response = new xmlrpcval(
        array(
            'total_topic_num' => new xmlrpcval($result_num, 'int'),
            'searchid'        => new xmlrpcval($searchid, 'string'),
            'topics'          => new xmlrpcval($topic_list, 'array')
        ),
        'struct'
    );

    return new xmlrpcresp($response);
}

function xmlresptrue()
{
    return new xmlrpcresp(new xmlrpcval(true, 'boolean'));
}

function get_home_data()
{
    global $home_array;
    
    return new xmlrpcresp(new xmlrpcval($home_array, 'array'));
}

?>