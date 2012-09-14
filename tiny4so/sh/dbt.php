<?php
	
	$db=mysql_connect("localhost","root","cid04587");
	mysql_select_db("tiny4cocoa",$db);
	mysql_query("SET NAMES 'utf8'");
	putenv("TZ=Asia/Shanghai");
	$sqlStr = "SELECT author,authorid,pid,fid,tid,subject,dateline,message from cocoabbs_post where first = 1";
	$result = mysql_query($sqlStr);
	$datas = array();
	while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
		
		$datas[] = $row;
	}
	
	mysql_select_db("cocoa_so",$db);
	mysql_query("SET NAMES 'utf8'");
	putenv("TZ=Asia/Shanghai");
	
	