<?
	$db=mysql_connect("localhost","root","123456");
	mysql_select_db("cocoa_doc",$db);
	mysql_query("SET NAMES 'utf8'");
	putenv("TZ=Asia/Shanghai");
