<?
require_once 'db.php';

function getPageContent($url) {
	
	$sqlStr="SELECT url,title,content,updatetime,id FROM page WHERE url='$url'";
	$result=mysql_query($sqlStr);
	if(!($row = mysql_fetch_array($result)))
	{
		return NULL;
	}
	return $row;
}

function updatePage($page) {
	
	$url = $page['url'];
	$now = time();
	$sqlStr = "SELECT url FROM page WHERE url = '$url'";
	$result = mysql_query($sqlStr);
	if($row = mysql_fetch_array($result))
		$sqlStr = "UPDATE page SET title='$page[title]',content='$page[content]',updatetime=$now WHERE url='$url'";
	else
		$sqlStr = "INSERT INTO page (url,title,content,updatetime) VALUES ('$url','$page[title]','$page[content]',$now)";
	$result=mysql_query($sqlStr);
}
