<?
	$db=mysql_connect("localhost","root","123456");
	mysql_select_db("tiny4cocoa",$db);
	mysql_query("SET NAMES 'utf8'");
	putenv("TZ=Asia/Shanghai");