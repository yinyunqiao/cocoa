<?php
/*
Plugin Name: 无觅相关文章插件
Plugin URI: http://www.wumii.com/widget/relatedItems.htm
Author: Wumii Team
Author URI: http://www.wumii.com
Description: Automatically finds out related articles based on article content and user behavior.
 
Copyright 2010 wumii.com (email: team[at]wumii.com)

This plugin only for Discuz! 7.2 and Discuz! 7.1.
*/

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class plugin_wumii_related_posts {
    var $DEBUG = false;
    var $SERVER = 'http://widget.wumii.com';
    var $VERSION = '1.0.0.3';
    
    //options
    var $numPosts;
    var $enableCustomPos;
    var $scriptInFooter;
    
    // PHP 5 style constructor
    function __construct() {
        $this->plugin_wumii_related_posts();
    }
    
    // treated as regular method since PHP 5.3.3
    // PHP 4 style constructor
    function plugin_wumii_related_posts() {
        // all the plugin settings store in this file after install our plugin,
        // then we can access settings from $_DPLUGIN['wumii_related_posts'].
        @include DISCUZ_ROOT.'./forumdata/cache/plugin_wumii_related_posts.php';
        
        $this->numPosts = $_DPLUGIN['wumii_related_posts']['vars']['numPosts'];
        $this->enableCustomPos = $_DPLUGIN['wumii_related_posts']['vars']['enableCustomPos'];
        $this->scriptInFooter = $_DPLUGIN['wumii_related_posts']['vars']['scriptInFooter'];
    }
    
    function _getWumiiScript() {
        /**
         * $scriptlang
         *         We can access the words specified in the node id="language" in xml file from this global variable.
         * $_DCACHE
         *         Global variable defined by discuz.
         */
        global $_DCACHE, $scriptlang, $boardurl;
        
        $enableCustomPos = $this->enableCustomPos ? 'true' : 'false';
        $pluginName = $scriptlang['wumii_related_posts']['plugin_name'];
        $platform = 'Discuz' . $_DCACHE['settings']['version'];
        
        $script = <<<WUMII_SCRIPT

<p style="margin:0;padding:0;height:1px;">
    <script type="text/javascript"><!--
        var wumiiSitePrefix = "$boardurl";
        var wumiiEnableCustomPos = $enableCustomPos;
    //--></script>
    <script type="text/javascript" src="$this->SERVER/ext/relatedItemsWidget.htm?type=1&num=$this->numPosts&v=$this->VERSION&pf=$platform"></script>
    <a href="http://www.wumii.com/widget/relatedItems.htm" style="border:0;">
        <img src="http://static.wumii.com/images/pixel.png" alt="$pluginName" style="border:0;padding:0;margin:0;" />
    </a>
</p>
WUMII_SCRIPT;
        return $script;
    }
    
    // "global_" begin function is called before template output
    function global_footer() {
        if ($this->scriptInFooter) {
            return $this->_getWumiiScript();
        }
    }
    
    // hook on viewthread_postbottom, call before template output
    // viewthread_postbottom hook located in topic page, just after every post content.
    // the hook must return an array.
    function viewthread_postbottom_output() {
        /**
         * $postlist
         *     Represents all the posts in a topic page, including the topic post and its replys.
         *     If the page is the second subpage of a topic, $postlist will not contain the topic post.
         * $thread
         *     Respresents the topic page.
         */
        global $_DCACHE, $postlist, $db, $tablepre, $boardurl, $thread, $tid;
        
        // Generally, the pointer of $postlist isn't at the first for the discuz loop.
        // We need to reset the pointer to the first to test if the post is a topic post.
        $topicPost = reset($postlist);
        
        // $topicPost['first'] can be 1, 0 or null.
        // $topicPost['first'] is null only if $postlist also is null(e.g. the post refer to the tid not exist).
        if ($topicPost['first'] != 1) {    // not topic post.
            return array();
        }
        
        $url = $boardurl;
        // Discuz has several options to convert url to static url in different pages.
        // And discuz core uses ($GLOBALS['rewritestatus'] & 2) to test if topic page is setted to use static url.
        if($GLOBALS['rewritestatus'] & 2) {
            // fake static url format
            $url .= "thread-$tid-1-1.html";
        } else {
            $url .= "viewthread.php?tid=$tid";
        }
        
        // $thread['subject'] may contain classification information.
        // $thread['subjectenc'] is a parameter in a url request.
        // We use this variable to get the subject then we don't need to do a database call again.
        $subject = rawurldecode($thread['subjectenc']);
        
        $pic = $this->_extractImageSrc();
        
        $query = $db->query("SELECT tagname FROM {$tablepre}threadtags WHERE tid='$tid'");
        $tagsHtml = '';
        while($tags = $db->fetch_array($query)) {
            $tagsHtml .= "<a rel='tag'>$tags[tagname]</a>";
        }
        
        // Begin to build embed code.
        $embedCode = '';
        if ($this->DEBUG) {
            $embedCode .= "<script type='text/javascript'>var wumiiDebugServer = '$this->SERVER';</script>";
        }
        
        $embedCode .= <<<WUMII_HOOK

<div class="wumii-hook">
    <input type="hidden" name="wurl" value="$url" />
    <input type="hidden" name="wtitle" value="$subject" />
    <input type="hidden" name="wpic" value="$pic" />
</div>
WUMII_HOOK;
        
        // append invisible tag nodes.
        if ($tagsHtml) {
            $tagsHtml = '<span style="display:none;">' . $tagsHtml . '</span>';
            $embedCode .= $tagsHtml;
        }
        
        if (!$this->scriptInFooter) {
            $embedCode .= $this->_getWumiiScript();
        }
        
        return array($embedCode);
    }
    
    function _extractImageSrc() {
        global $sdb, $tablepre, $tid, $attachrefcheck, $ftp, $attachimgpost, $boardurl, $attachurl;
        $content = $sdb->result_first("SELECT message FROM {$tablepre}posts WHERE tid='$tid' AND first='1'");
        
        // extract referencing image
        preg_match('/' . preg_quote('[img]') . '(.+)' . preg_quote('[/img]', '/') . '/Ui', $content, $matches);
        // Discuz bug! Sometimes the value between "[img]" is not an absolute url and also it can't be shown corrently in the post content.
        if (strpos($matches[1], 'http') === 0) {
            return $matches[1];
        }
        
        preg_match_all('/' . preg_quote('[attach]') . '(\d+)' . preg_quote('[/attach]', '/') . '/Ui', $content, $matches);
        $attachAids = $matches[1];
        foreach ($attachAids as $aid) {
            $attach = $sdb->fetch_first("SELECT aid, width, attachment, isimage, remote FROM {$tablepre}attachments WHERE aid='$aid'");
            if (!$attach) {
                continue;
            }
            
            $finalAttach = $attach;
            // Set the first choice width limitation is 100px.
            if (intval($attach['width']) > 100) {
                break;
            }
        }
        
        if (!$finalAttach) {
            return '';
        }
        
        // Discuz 7.x use $refCheck to identify the different type of attachments.
        // The following code is copied from /templates/default/discuzcode.htm in function attachlist
        $refCheck = (!$finalAttach['remote'] && $attachrefcheck) || ($finalAttach['remote'] && ($ftp['hideurl'] || ($finalAttach['isimage'] && $attachimgpost && strtolower(substr($ftp['attachurl'], 0, 3)) == 'ftp')));
        
        if ($refCheck) {
            $aidEncode = aidencode($finalAttach['aid']);
            $src = "attachment.php?aid=$aidEncode&amp;noupdate=yes";
        } else {
            $attachBase = $finalAttach['remote'] ? $ftp['attachurl'] : $attachurl;
            $src = "$attachBase/$finalAttach[attachment]";
        }
        return $boardurl . $src;
    }
}
?>