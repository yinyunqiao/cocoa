<?php
class FaqModel extends baseDbModel {
  
  public function threadCount() {
    
    $sql = 
    "SELECT count(*) as `c` FROM `cocoabbs_threads`;";
    $result =  $this->fetchArray($sql);
    return $result[0]["c"];
  }
  
  
  public function threads($page,$pageSize) {
    
    $start = ($page-1)*$pageSize;
    $sql = 
    "SELECT * FROM `cocoabbs_threads`
    ORDER BY `lastpost` DESC 
    limit $start,$pageSize;";
    $result =  $this->fetchArray($sql);
    $ret = array();
    if(count($result)==0)
      return $ret;
    foreach($result as $item) {
      
      $item["id"] = $item["tid"];
      $item["createtime"] = ToolModel::countTime($item["dateline"]);
      $item["updatetime"] = ToolModel::countTime($item["lastpost"]);
      $item["image"] = DiscuzModel::get_avatar($item["authorid"],"small");
      $item["title"] = stripslashes($item["subject"]);
      $item["createby"] = $item["author"];
      $item["createbyid"] = $item["authorid"];
      $item["lastreply"] = $item["lastposter"];
      
      $ret[] = $item;
    } 
    return $ret;
  }


  public function parseContent($content) {
    
    $html = Markdown(stripslashes($content));
    $html = ToolModel::youkuInsert($html);
    $html = ToolModel::autoDetect($html);
    
    return $html;
  }

  public function threadById($id,$html=1) {
    
    $sql = "SELECT * FROM `threads` where id = $id;";
    $ret = $this->fetchArray($sql);
    $thread = $ret[0];
    if($html==1)
      $thread["content"] = $this->parseContent($thread["content"]);
    else
      $thread["content"] = stripslashes($thread["content"]);
    $thread["title"] = stripslashes($thread["title"]);
    $thread["createtime"] = ToolModel::countTime($thread["createdate"]);
    $thread["updatetime"] = ToolModel::countTime($thread["updatedate"]);
    $thread["image"] = DiscuzModel::get_avatar($thread["createbyid"],"small");
    return $thread;
  }
  
  public function threadsByUserid($userid) {
    
    $sql = "SELECT * FROM `threads` where `createbyid` = $userid;";
    $result = $this->fetchArray($sql);
    $ret = array();
    if(count($result)==0)
      return $ret;
    foreach($result as $item) {
      $item["createtime"] = ToolModel::countTime($item["createdate"]);
      $item["updatetime"] = ToolModel::countTime($item["updatedate"]);
      $item["image"] = DiscuzModel::get_avatar($item["createbyid"],"small");
      $ret[] = $item;
    } 
    return $ret;
  }
  
  public function threadsReplyByUserid($userid) {
    
    $sql = "SELECT * FROM `threads` where `createbyid` <> $userid AND `id` in (SELECT `threadid` FROM `thread_replys` WHERE `userid` = $userid GROUP BY `threadid`);";
    $result = $this->fetchArray($sql);
    $ret = array();
    if(count($result)==0)
      return $ret;
    foreach($result as $item) {
      $item["createtime"] = ToolModel::countTime($item["createdate"]);
      $item["updatetime"] = ToolModel::countTime($item["updatedate"]);
      $item["image"] = DiscuzModel::get_avatar($item["createbyid"],"small");
      $ret[] = $item;
    } 
    return $ret;
  }
  
  public function replysCountById($id) {
    
    $sql = 
    "SELECT count(*) as `c` FROM `thread_replys` where threadid = $id;";
    $result =  $this->fetchArray($sql);
    return $result[0]["c"];
  }
  
  public function replysById($id) {
    
    $sql = "SELECT * FROM `thread_replys` where threadid = $id ORDER BY `id`;";
    $result = $this->fetchArray($sql);
    $ret = array();
    if(count($result)==0)
      return $ret;
    foreach($result as $item) {
      
      $item["content"] = $this->parseContent($item["content"]);
      $item["createtime"] = ToolModel::countTime($item["createdate"]);
      $item["updatetime"] = ToolModel::countTime($item["updatedate"]);
      $item["image"] = DiscuzModel::get_avatar($item["userid"],"small");
      $ret[] = $item;
    } 
    return $ret;
  }
  
  public function replyByReplyId($id,$html=1) {
    
    $sql = "SELECT * FROM `thread_replys` where id = $id;";
    $result = $this->fetchArray($sql);
    $item = $result[0];
    if($html==1)
      $item["content"] = $this->parseContent($item["content"]);
    else
      $item["content"] = stripslashes($item["content"]);
    $item["createtime"] = ToolModel::countTime($item["createdate"]);
    $item["updatetime"] = ToolModel::countTime($item["updatedate"]);
    return $item;
  }
  
  public function updateReply($data) {
    
    $this->select("thread_replys")->where("id = $data[id]")->update($data);
  }
  
  public function newThread($data) {
    
    return $this->select("threads")->insert($data);
  }
  
  public function newReply($data) {
    
    $this->select("thread_replys")->insert($data);
    $sql = "SELECT count(`id`) as c FROM `thread_replys` WHERE threadid = $data[threadid];";
    $result = $this->fetchArray($sql);
    $c = $result[0]["c"];
    $this->replyNotify($data);
    return $c;
  }
  
  public function replyNotify($data) {
    
    $users = $this->getThreadUsers($data["threadid"]);
    if(($key = array_search($data["userid"], $users)) !== false) {
        unset($users[$key]);
    }
    if(count($users)==0)
      return;
    $usersStr = join(",",$users);
    $sql = "SELECT `username`,`email` FROM `cocoabbs_members` WHERE `uid` in ($usersStr);";
    $result = $this->fetchArray($sql);
    $thread = $this->threadById($data["threadid"]);
    foreach($result as $user) {
      
      $this->replyNotifyMail($user["username"], $user["email"], $data["name"], $data["content"],$thread["title"],$data["threadid"]);
    }
  }
  
  private function replyNotifyMail($username, $email, $replyuser, $content, $threadname, $threadid) {
    
    
    $subject = "您参与的帖子《".$threadname."》有了新回复";
    $mailContent = "您参与的帖子《".$threadname."》有了新回复<br/>";
    $mailContent .= "<p><a href=http://tiny4cocoa.com/thread/show/$threadid/>http://tiny4cocoa.com/thread/show/$threadid/</a></p>";
    $mailContent .= "<p> $replyuser 刚刚回复说:</p>";
    $mailContent .= Markdown(stripslashes($content));
    $mailContent .= "<p><a href=http://tiny4cocoa.com/thread/show/$threadid/>http://tiny4cocoa.com/thread/show/$threadid/</a></p>";
    $mail = new MailModel();
    $mail->generateMail(
            $email,
             "admin@tiny4.org", 
            $subject, 
            $mailContent);
  }
  
  
  public function getThreadUsers($threadid){
    
    $sql = "SELECT `userid` FROM `thread_replys` WHERE `threadid` = $threadid GROUP BY `userid`;";
    $result = $this->fetchArray($sql);
    $users = array();
    if(count($result)>0) {
      
      foreach($result as $user) {
        
        $users[] = $user["userid"];
      }
    }
    $sql = "SELECT `createbyid` FROM `threads` WHERE `id` = $threadid;";
    $result = $this->fetchArray($sql);
    if(count($result)>0) {
        $users[] = $result[0]["createbyid"];
    }
    $users = array_unique($users);
    return $users;
  }
  
  public function updateThread($data) {
    
    $this->select("threads")->where("id = $data[id]")->update($data);
  }
  
  
  public function topThreadsFrom($n,$time){
    
    $sql =
      "SELECT `thread_replys`.`threadid`,count(*) as `replyscount`,
      `threads`.`title`,`threads`.`replys`
      FROM `thread_replys`
      LEFT JOIN `threads`
      ON `threads`.`id` = `thread_replys`.`threadid`
      WHERE `thread_replys`.`createdate` > $time
      GROUP BY `thread_replys`.`threadid`
      ORDER BY `replyscount` DESC
      LIMIT 0,$n;
      ";
    $result = $this->fetchArray($sql);
    return $result;
  }
  
  
  public function topBbsHero($n,$time){
    
    $sql =
      "SELECT name,`thread_replys`.`userid`,count(*) as `replyscount`
      FROM `thread_replys`
      WHERE `thread_replys`.`createdate` > $time 
      GROUP BY `thread_replys`.`userid` 
      ORDER BY `replyscount` DESC
      LIMIT 0,10;";
    $result = $this->fetchArray($sql);
    return $result;
  }
  
  public function isWeekmailSent() {
    
    $now = time();
    $day = date("Y-m-d", $now);
    $sql = "SELECT * FROM `mailsent` WHERE `weekdate`='$day';";;
    $result = $this->fetchArray($sql);
    if(count($result)>0)
      return 1;
    else 
      return 0;
  }
  
}






