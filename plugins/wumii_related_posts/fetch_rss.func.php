<?php 
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once DISCUZ_ROOT.'include/chinese.class.php';

function wumii_fetch_rss($tid, $title, $num = 4) {
    global $_DCACHE, $boardurl;
    static $lastRssRequestFailedTime;
    
    // Don't do anything for 30 seconds if last request failed.
    if ($lastRssRequestFailedTime && time() - $lastRssRequestFailedTime < 30) {
        return '';
    }
    
    // Discuz has several options to convert url to static url in different pages.
    // And discuz core uses ($GLOBALS['rewritestatus'] & 2) to test if topic page is setted to use static url.
    if($GLOBALS['rewritestatus'] & 2) {
        // fake static url format
        $url = $boardurl . "thread-$tid-1-1.html";
    } else {
        $url = $boardurl . "viewthread.php?tid=$tid";
    }
    
    $encodedUrl = urlencode($url);
    $encodedTitle = urlencode($title);
    $encodedSitePrefix = urlencode($boardurl);
    $version = '1.0.0.2';
    $platform = 'Discuz' . $_DCACHE['settings']['version'];
    
    $requestUrl = "http://www.wumii.com/ext/relatedItemsRss.htm?type=1&url=$encodedUrl&title=$encodedTitle&num=$num&sitePrefix=$encodedSitePrefix&mode=3&v=$version&pf=$platform";
    
    // dfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE)
    //XXX: $bysocket is useless. We need to change the parameter if discuz fix it.
    $relatedItemsHtml = dfopen($requestUrl, 0, '', '', false, '', 3);
    if ($relatedItemsHtml) {
        // discuz output the rss using platform charset, we need to convert the returning content.
        global $charset;
        if(strtoupper($charset) != 'UTF-8') {
            $chs = new Chinese('UTF-8', $charset);
            $relatedItemsHtml = $chs->Convert($relatedItemsHtml);
        }
        return $relatedItemsHtml;
    } else {
        $lastRssRequestFailedTime = time();
        return '';
    }
}
?>