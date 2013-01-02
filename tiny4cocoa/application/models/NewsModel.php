<?php
class NewsModel extends baseDbModel {
  
  public function newsCount() {
    
    $sql = 
    "SELECT count(*) as `c` FROM `cocoacms_news`;";
    $result =  $this->fetchArray($sql);
    return $result[0]["c"];
  }
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
  function hotTags() {
    
    $sql = "SELECT * FROM `cocoabbs_tags` ORDER BY `total` DESC LIMIT 0,20;";
    $ret = $this->fetchArray($sql);
    return $ret;
  }
  
  public function spamCount() {
    
    $sql = "SELECT count(*) as `spamcount` FROM `cocoacms_comments` WHERE `spam`=1;";
    $ret = $this->fetchArray($sql);
    return $ret[0]["spamcount"];
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
  
  public function emptySpam() {
    
    $sql = "DELETE  FROM `cocoacms_comments` WHERE `spam`=1;";
    $this->run($sql);
  }
  public function commentById($id){
    
    $sql =
      "SELECT * FROM `cocoacms_comments` WHERE `id` =$id;";
    $ret = $this->fetchArray($sql);
    if(count($ret)>0)
      return $ret[0];
    return NULL;
  }
  
  public function comments($page,$size){
    
    if($page < 1)
      $page = 1;
    $start = ($page-1)*$size;
    $sql =
      "SELECT * FROM `cocoacms_comments` ORDER BY `id` DESC limit $start,$size;";
    $ret = $this->fetchArray($sql);
    return $ret;
  }
  
  
  public function commentsByNewsId($id) {
    
    $sql =
      "SELECT * FROM `cocoacms_comments`
      WHERE `newsid` = $id AND `spam` = 0
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
  
  //算法来自 http://stackoverflow.com/questions/5037592/how-to-add-rel-nofollow-to-links-with-preg-replace
  function linkAddNofollow($content) {
      $content =
      preg_replace_callback('~<(a\s[^>]+)>~isU', "NewsModel::cb2", $content);
      return $content;
  }

  function cb2($match) { 
      list($original, $tag) = $match;   // regex match groups

      // $my_folder =  "/hostgator";       // re-add quirky config here
      $blog_url = "http://tiny4cocoa.com";

      if (strpos($tag, "nofollow")) {
          return $original;
      }
      elseif (strpos($tag, $blog_url) 
//      && (!$my_folder || !strpos($tag, $my_folder))
      ) {
          return $original;
      }
      else {
          return "<$tag rel='nofollow'>";
      }
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
    $id = $this->select("cocoacms_comments")->insert($data);
    $akismet = new Akismet();
    $comment = $data;
    $comment["id"] = $id;
    $data = array('blog' => 'http://tiny4cocoa.org/home/',
                  'user_ip' => $comment["ip"],
                  'user_agent' => $comment["useragent"],
                  'referrer' => $comment["referrer"],
                  'permalink' => "http://tiny4cocoa.org/home/s/$comment[newsid]",
                  'comment_type' => 'comment',
                  'comment_author' => $comment["poster"],
                  'comment_author_email' => '',
                  'comment_author_url' => '',
                  'comment_content' => $comment["content"]);
    $ret = $akismet->commentCheck($data);
    if($ret) {
      $this->markSpam($comment["id"],1);
    }
    $this->updateCommentsCount($comment["newsid"]);
    header("location:/home/s/$comment[newsid]/");
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
    $content = $this->linkAddNofollow($content);
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