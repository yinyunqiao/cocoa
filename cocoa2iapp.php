<?php
  
  $db=mysql_connect("localhost","root","cid04587");
  mysql_select_db("tiny4cocoa",$db);
  mysql_query("SET NAMES 'utf8'");
  putenv("TZ=Asia/Shanghai");
  $sql = "select  authorid from cocoabbs_posts  where tid =370 group by authorid";
  $result=mysql_query($sql);
	if(!$result)
		die("error");
	$userid = array();
	while ($row = mysql_fetch_assoc($result)) {
		$userid[] = $row["authorid"];
	}
	$ids = join($userid,",");
	$sql = "SELECT  uid,username,email,extcredits1 FROM `cocoabbs_members`  where uid in ($ids) order by extcredits1 desc"; 
	$result=mysql_query($sql);
  if(!$result)
		die("error");
	echo "<table align='center'>";
	while ($row = mysql_fetch_assoc($result)) {
		echo "<tr><td>". $row["username"] . "</td><td>" . $row["email"] . "</td><td>" . $row["extcredits1"] . "</td></tr>";
	}
	echo "</table>";
	
	