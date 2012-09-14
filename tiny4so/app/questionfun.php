<?php
require_once 'db.php';
require_once 'discuz.php';

function getTopQuestion($tab,$unanswered) {

	$count = 30;
	$now = time();
	switch($tab){
			
		case "new":
			$sqlStr = "SELECT * from cocoabbs_threads order by dateline DESC limit 0,$count";
			break;
		case "active":
			$sqlStr = "SELECT * from cocoabbs_threads order by lastpost DESC limit 0,$count";
			break;
		case "hot":
			$sqlStr = "SELECT *,(replies*20+views) as score from cocoabbs_threads where ($now-lastpost)<3600*24 order by score DESC limit 0,$count";
			break;
		case "week":
			$sqlStr = "SELECT *,(replies*20+views) as score from cocoabbs_threads where ($now-lastpost)<3600*24*7 order by score DESC limit 0,$count";
			break;
		case "month":
			$sqlStr = "SELECT *,(replies*20+views) as score from cocoabbs_threads where ($now-lastpost)<3600*24*30 order by score DESC limit 0,$count";
	}
	
	$result = mysql_query($sqlStr);
	$datas = array();
	while($row = mysql_fetch_array($result)){
		
		$row['updatetime']= countTime($row['lastpost']);
		if($row['views']<1000) {
			
			$row['viewcount'] = $row['views'];
		}else if($row['views']<10000) {
			
			$row['viewcount'] = floor($row['views']/1000) . "K";
		}else {
			
			$row['viewcount'] = floor($row['views']/10000) . "万";
		}
		
		
		$datas[] = $row;
	}
	return $datas;
}

function getQuestionById($id) {
	
	$sqlStr = "SELECT * from cocoabbs_threads where tid = $id";
	$result = mysql_query($sqlStr);
	$question = mysql_fetch_array($result);
	if($question) {
		
		$sqlStr = "SELECT * from cocoabbs_posts where tid = $id";
		$result = mysql_query($sqlStr);
		$posts = array();
		while($post = mysql_fetch_array($result)){
			
			$post['message'] = discuzCode($post['message']);
			$post['avatar'] = get_avatar($post['authorid'],"small");
			$post['posttime']= countTime($post['dateline']);
			if($post['first']==1)
			{
				$question['question'] = $post;
			}
			else
				$posts[] = $post;
		}
		$question['updatetime']= countTime($question['lastpost']);
		$question['asktime']= countTime($question['dateline']);
		$question['answers'] = $posts;
		$question['avatar'] = get_avatar($question['authorid'],"small");
	}
	return $question;
}

function countTime($time){
	
	$diff = time() - $time;
	if($diff<60)
	{
		$ret = $diff . "秒前";
	}
	else if($diff<3600){
		
		$ret = floor($diff/60) . "分钟前";
	}
	else if($diff<3600*24) {
		$ret = floor($diff/3600) . "小时前";
	}
	else
		$ret = date("Y年m月d日",$time);
	return $ret;
}



