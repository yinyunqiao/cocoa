<?php
class NewsModel extends baseDbModel {
  
  public function news($page,$pageSize) {
    
    $start = ($page-1)*$pageSize;
    $sql = 
    "SELECT * FROM `cocoacms_news`
    ORDER BY `createdate` DESC 
    limit $start,$pageSize;";
    $result =  $this->fetchArray($sql);
    $ret = array();
    if(count($result)==0)
    return $ret;
    foreach($result as $item) {
		
      $item["createtime"] = $this->countTime($item["createdate"]);
      $ret[] = $item;
    } 
    return $ret;
  }
  
  public function oneNews($index) {
    
    $sql =
      "SELECT * FROM `cocoacms_news`
      WHERE `id` = $index;";
    $ret = $this->fetchArray($sql);
    $news = $ret[0];
    $news["createtime"] = $this->countTime($news["createdate"]);
    $news["content"] = $this->toHtml($news["content"]);
    return $news;
  }
  
  public function commentToCheck() {
    
    $sql = "SELECT * FROM `cocoacms_comments` WHERE `checked`=0 limit 0,3";
    $ret = $this->fetchArray($sql);
    return $ret;
  }
  
  public function markSpam($id,$spam) {
    
    $sql = "UPDATE `cocoacms_comments` set `checked`=1,`spam`=$spam WHERE `id` = $id";
    $ret = $this->run($sql);
  }
  
  public function commentsByNewsId($id) {
    
    $sql =
      "SELECT * FROM `cocoacms_comments`
      WHERE `newsid` = $id 
      ORDER BY `id`;";
    $ret = $this->fetchArray($sql);
    if(count($ret)==0)
      return $ret;
    $comments = array();
    foreach($ret as $comment) {
      
      $comment["createtime"] = $this->countTime($comment["createtime"]);
      $comment["content"] = $this->toHtml($comment["content"]);
      $comment["ip"] = ToolModel::MosaicIp($comment["ip"]);
      $comments[] = $comment;
    }
    return $comments;
  }
  
  public function saveComment() {

    $data["newsid"] = $_POST["newsid"];
    if(empty($_POST["nonamecheck"]))
      $data["hidename"] = 0;
    else
      $data["hidename"] = 1;
    $data["content"] = $_POST["content"];
    $data["posterid"] = $_POST["posterid"];
    $data["poster"] = $_POST["poster"];
    if($data["posterid"]>0 && $data["hidename"]==0) {
      $data["poster"] = $this->usernameById($data["posterid"]);
    }
    $data["createtime"] = time();
    $data["ip"] = ToolModel::getRealIpAddr();
    $data["useragent"] = $_SERVER['HTTP_USER_AGENT'];
    $data["referrer"] = $_SERVER['HTTP_REFERER'];
    $this->select("cocoacms_comments")->insert($data);
    $this->updateCommentsCount($data["newsid"]);
    header("location:/home/s/$data[newsid]/");
  }
  
  public function updateCommentsCount($newsid) {
    
    $sql = "UPDATE `cocoacms_news` set `commentscount` =(
SELECT count(*) FROM `cocoacms_comments` WHERE `newsid` = $newsid) WHERE `id` = $newsid;";
    $this->run($sql);
  }
  public function usernameById($id) {
    
    $user = $this->fetchArray("SELECT `username` FROM `cocoabbs_members` WHERE `uid`=$id");
    return $user[0]["username"];
  } 
  
  public function toHtml($content) {
    
    $content = stripslashes($content);
    $content = str_replace("\r\n","<br/>",$content);
    $content = str_replace("\n","<br/>",$content);
    $content = str_replace("\r","<br/>",$content);
    return $content;
  }
  
  public function countTime($time)
  {
    $diff = time() - $time;
    if($diff<0) {
      $ret = "（时间不确）";
    }else if($diff<60) {
      $ret = $diff . "秒前";
    } else if($diff<3600) {
      $ret = floor($diff/60) . "分钟前";
    } else if($diff<3600*24) {
      $ret = floor($diff/3600) . "小时前";
    } else if($diff<3600*24*7) {
      $ret = floor($diff/3600/24) . "天前";
    } else if($diff<3600*24*30) {
      $ret = floor($diff/3600/24/7) . "周前";
    } else if($diff<3600*24*30*12) {
      $ret = floor($diff/3600/24/30) . "月前";
    } else {
      $ret = date("Y年m月d日",$time);
    }
    return $ret;
  }
    
}