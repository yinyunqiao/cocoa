<?php
error_reporting(0);
define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

if(PHP_VERSION < '4.1.0') {
	$_GET = &$HTTP_GET_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
}

require_once DISCUZ_ROOT.'./config.inc.php';
require_once DISCUZ_ROOT.'./include/global.func.php';
require_once DISCUZ_ROOT.'./include/db_'.$database.'.class.php';
require_once DISCUZ_ROOT.'./forumdata/cache/cache_settings.php';
require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

$format = $_GET["format"];

$maxitemnum = 500;
$timestamp = time();
$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
$boardurl = 'http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF, 0, strrpos($PHP_SELF, '/') + 1);

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

if(!$_DCACHE['settings']['baidusitemap']) {
	exit('Baidu Sitemaps is closed!');
}

$xmlcontent = "";

	$groupid = 7;
	$extgroupids = '';
	$xmlfiletime = $timestamp - $_DCACHE['settings']['baidusitemap_life'] * 3600;
	$fidarray = array();

	foreach($_DCACHE['forums'] as $fid => $forum) {
		if(sitemapforumperm($forum)) {
			$fidarray[] = $fid;
		}
	}
	
	$query = $db->query("SELECT tid, subject 
		FROM {$tablepre}threads ORDER BY dateline DESC");

	if($format != "text")
	{
		$xmlcontent .= "<h2>全部帖子存档</h2><ul>";
	}
	while($thread = $db->fetch_array($query)) {
		if($format == "text")
		{
			$xmlcontent .=  "{$boardurl}thread-$thread[tid]-1-1.html\n";
			
		}
		else {
			$xmlcontent .=  "<li><a href=\"{$boardurl}thread-$thread[tid]-1-1.html\">";
			$xmlcontent .=  dhtmlspecialchars($thread['subject']);
			$xmlcontent .=  "</a></li>";
		}
	}
	
	if($format != "text")
	{
		$xmlcontent .= "</ul>";
	}
	
	echo $xmlcontent;

function sitemapforumperm($forum) {
	return $forum['type'] != 'group' && (!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm'])));
}
?>