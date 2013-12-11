<?php
class UserModel extends baseDbModel {
  
  public function username($userid) {
    
    $sql = "SELECT username FROM `cocoabbs_uc_members` WHERE uid = $userid;";
    $result = $this->fetchArray($sql);
    return $result[0]["username"];
  }
  
  public function userInfo($userid) {
    
    $sql = "SELECT * FROM `cocoabbs_uc_members` WHERE uid = $userid;";
    $result = $this->fetchArray($sql);
    return $result[0];
  }
  
  public function weeklyNewsUser() {
    
    $sql="SELECT `username`,`email` 
      FROM `cocoabbs_uc_members`
      WHERE `validated` = 1 AND `emailweeklynotification` = 1;";
    $result = $this->fetchArray($sql);
    return $result;
  }
  
  public function dailyNewsUser() {
    
    $sql="SELECT `username`,`email` 
      FROM `cocoabbs_uc_members`
      WHERE `validated` = 1 AND `emaildailynotification` = 1;";
    $result = $this->fetchArray($sql);
    return $result;
  }
  
  public function useridByName($name) {
    
    $user = $this->select("cocoabbs_uc_members")
      ->fields("uid")
      ->where("username = '$name'")
      ->fetchOne();
    if(!$user)
      return 0;
    return $user["uid"];
  }
  
  public function validateMail($userid,$v) {
    
    $user = $this->select("cocoabbs_uc_members")
      ->where("uid = $userid")
      ->fetchOne();
    if(!$user)
      return 0;
    if($user["validated"]==1)
      return 1;
    $v1 = md5($user["username"].$user["salt"].$user["email"]);
    if($v==$v) {
      
      $sql = "UPDATE `cocoabbs_uc_members` set `validated` = 1 WHERE `uid` = $userid;";
      $this->run($sql);
      
      $time = time();
      $this->add_reputation($userid,10,"邮件验证成功",$time);
      $this->add_money($userid,10,"邮件验证成功",$time);
      $this->update_reputationAndMoney($userid);
      return 1;
    }
    return 0;
  }
  
  public function sendValidateMail($userid) {
    
    $user = $this->select("cocoabbs_uc_members")
      ->where("uid = $userid")
      ->fetchOne();

    if(!$user)
      return;
    
    $mail = $user["email"];
    $mailModel = new MailModel();
    $v = md5($user["username"].$user["salt"].$user["email"]);
    $page = "<p>你好，</p>
    <p>您收到这封邮件的原因是，有人使用这个邮箱地址注册了tiny4cocoa社区 http://tiny4cocoa.com ,id为 $user[username]。如果您确定这不是您自己的行为，请删除这封邮件。</p>
    
    <p>如果您可以确认是您自己的注册，请点击链接完成验证 <a href=http://tiny4cocoa.com/user/validate/?user=$user[uid]&v=$v>邮件验证</a></p>
    ";
     $mailModel->generateMail(
            $mail,
             "Tiny4Cocoa论坛 <tiny4cocoa@tiny4.org>", 
            "Tiny4Cocoa社区注册确认信", 
            $page);
    
  }
  
  
  public function sendUnsubscribeMail($mail) {
    
    $mailModel = new MailModel();
    $v = md5($mail."3.141592654");
    $page = "<p>你好</p>
    <p>如果你要退订Tiny4Cocoa社区的通知邮件，请点击下面的链接</p>
    
    <p><a href=http://tiny4cocoa.com/user/unsubscribe/?mail=$mail&v=$v>退订确定</a></p>
     <p>如果你不想退订，或者不知道为什么会收到这封邮件，那么你可以忽略这封邮件。</p>
    ";
     $mailModel->generateMail(
            $mail,
             "Tiny4Cocoa论坛 <tiny4cocoa@tiny4.org>", 
            "Tiny4Cocoa社区邮件退订确认信", 
            $page);
    
  }
  
  public function addUnsubscribeMail($mail) {
    
    $sql = "UPDATE `cocoabbs_uc_members` set `emailweeklynotification` = 0 WHERE email='$mail';";
    $this->run($sql);
  }
  
  public function users($page,$size,$order="shui") {
    
   $start = ($page-1)*$size;
   $users = $this
     ->select("cocoabbs_uc_members");
   if($order=="date") {
     
     $this->orderby("regdate DESC");
     
   }else {
     
     $this->fields("*,(posts*5+replys) as shui")->orderby("shui DESC");
   }
   $this->where("`validated` = 1")->limit("$start,$size");
   $users = $this->fetchAll();
   $ret = array();
   if(count($users)==0)
     return $ret;
   foreach($users as $item) {
     $item["regdate"] = ToolModel::countTime($item["regdate"]);
     if($item["lastlogintime"]!=0)
       $item["lastlogintime"] = ToolModel::countTime($item["lastlogintime"]);
     else
       $item["lastlogintime"] = "没有记录";
     $item["image"] = DiscuzModel::get_avatar($item["uid"],"small");
     $ret[] = $item;
   } 
   return $ret;
  }
  
  public function count() {
    
     $ret = $this
     ->select("cocoabbs_uc_members")
     ->fields("count(*) as c")
     ->fetchOne(); 
     return $ret["c"];
  }
  
  public function passwordMatch($userid,$passwordmd5) {
    
    $sql = "SELECT `uid` FROM `cocoabbs_uc_members` WHERE `uid` = '$userid' AND `password` = MD5(CONCAT('$passwordmd5',`salt`));";
    $result = $this->fetchArray($sql);
    if($result)
      return 1;
    else
      return 0;
  }
  
  public function changePassword($userid,$password) {
    
    $salt = rand(100000,999999);
    $passindb = md5(md5($password).$salt);
    $sql = 
      "UPDATE `cocoabbs_uc_members` 
      SET `salt` = '$salt',`password` = '$passindb' 
      WHERE `uid` = $userid
      ";
      
    $this->run($sql);
  }
  
  public function resetPassword($username) {
    
    $data["userid"] = $this->useridByName($username);
    if($data["userid"]==0)
      return 0;
    
    $ticketModel = new TicketModel();
    $data = $ticketModel->newTicket($data["userid"]);
    $userinfo = $this->userInfo($data["userid"]);
    
    $mail = $userinfo["email"];
    $mailModel = new MailModel();
    $page = "<p>你好，</p>
    <p>您收到这封邮件的原因是，有人请求重置 $username 在 tiny4cocoa社区的密码。如果您确定这不是您自己的行为，请删除这封邮件。</p>
    
    <p>如果您可以确认是您自己的行为，请点击链接重置密码 <a href=http://tiny4cocoa.com/user/resetpassword/?ticket=$data[ticket]>重置密码</a></p>";
     $mailModel->generateMail(
            $mail,
             "Tiny4Cocoa论坛 <tiny4cocoa@tiny4.org>", 
            "Tiny4Cocoa社区－重置密码邮件", 
            $page);
    return 1;
  }
  
  public function login($data) {
    
    $username = $data["name"];
    $passmd5 = md5($data["password"]);
    $sql = "SELECT `uid`,`salt` FROM `cocoabbs_uc_members` WHERE `username` = '$username' AND `password` = MD5(CONCAT('$passmd5',`salt`));";
    $result = $this->fetchArray($sql);
    if(!$result)
      return 0;
    else {
      $data["userid"] = $result[0]["uid"];
      $data["salt"] = $result[0]["salt"];
      $this->setSessionAndCookie($data);
      $this->updateLoginToDb($_SESSION["userid"]);
      return $result[0]["uid"];
    }
  }
  
  public function checklogin() {
    
    
    if($_SESSION["username"] && $_SESSION["userid"]) {
      
      $this->renewCookie();
      return $_SESSION["userid"];
    } else {
      
      $this->cookie2Session();
      if($_SESSION["userid"]){
        
        $this->updateLoginToDb($_SESSION["userid"]);
        return $_SESSION["userid"];
      }
    }
  }

  private function updateLoginToDb($userid) {
    
    $time = time();
    $ip = ToolModel::getRealIpAddr();
    $sql = "UPDATE `cocoabbs_uc_members` SET `lastloginip`='$ip', `lastlogintime`=$time WHERE `uid` = $userid;";
    $this->run($sql);
  }
  private function cookie2Session() {
    
    if(!$_COOKIE["TINY4COCOA_USERID"])
      return;
    $userid = $_COOKIE["TINY4COCOA_USERID"];
    $sql = "SELECT `uid`,`username`,`salt` 
      FROM `cocoabbs_uc_members`
      WHERE `uid` = $userid
      ;";
    $result = $this->fetchArray($sql);
    if(!$result)
      return;
    $user = $result[0];
    $tiny4cocoa_session = md5($user["username"].$user["uid"].$user["salt"]);
    if($tiny4cocoa_session==$_COOKIE["TINY4COCOA_SESSION"]){
      
      $_SESSION["username"] = $user["username"];
      $_SESSION["userid"] = $user["uid"];
    }
  }
  
  private function setSessionAndCookie($data) {
    
    $_SESSION["username"] = $data["name"];
    $_SESSION["userid"] = $data["userid"];
    $tiny4cocoa_session = md5($data["name"].$data["userid"].$data["salt"]);
    $time = time()+3600*24*7*2;
    setcookie("TINY4COCOA_USERID", $data["userid"] , $time,"/");
    setcookie("TINY4COCOA_SESSION", $tiny4cocoa_session , $time,"/");    
  }
  
  private function renewCookie() {
    
    $time = time()+3600*24*7*2;
    $userid = $_COOKIE["TINY4COCOA_USERID"];
    $session = $_COOKIE["TINY4COCOA_SESSION"];
    setcookie("TINY4COCOA_USERID", $userid , $time,"/");
    setcookie("TINY4COCOA_SESSION", $session , $time,"/");    
  }
  
  public function logout() {
    
    unset($_SESSION["username"]);
    unset($_SESSION["userid"]);
    setcookie("TINY4COCOA_USERID","",time()-3600*24,"/");
    setcookie("TINY4COCOA_SESSION","",time()-3600*24,"/");
    header("location:/");
  }

  public function username_valid($name) {
    
    preg_match("/^[_\-a-zA-Z0-9\x{4E00}-\x{9FFF}]+$/u",$name,$matches);
    if(count($matches)==0)
      return FALSE;
    if($this->isUserExisted($name))
      return FALSE;
    return TRUE;
  }

  public function email_valid($email) {
    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
      return FALSE; 
    if(!preg_match('/@.+\./', $email))
      return FALSE;
    if($this->isEmailExisted($email))
      return FALSE;
    return TRUE;
  }

  public function password_valid($password) {
    
    if(strlen($password)<3 || strlen($password)>32)
      return FALSE; 
    return TRUE;
  }
  
  public function isUserExisted($name) {
    
    $ret = $this->select("cocoabbs_uc_members")->where("username = '$name'")->fetchAll();
    if(count($ret)>0)
      return 1;
    else
      return 0;
  }
  
  public function isEmailExisted($email) {
    
    $ret = $this->select("cocoabbs_uc_members")->where("email = '$email'")->fetchAll();
    if(count($ret)>0)
      return 1;
    else
      return 0;
  }
  
  public function reg($username,$email,$password) {
    
    $user["username"] = $username;
    $user["salt"] = rand(100000,999999);
    $user["password"] = md5(md5($password).$user["salt"]);
    $user["email"] = $email;
    $user["regip"] = ToolModel::getRealIpAddr();
    $user["regdate"] = time();
    $user["validated"] = 0;
    $userid = $this->select("cocoabbs_uc_members")->insert($user);
    return $userid;
  }

  public function isEmailValidated($userid) {
    
    $ret = $this->select("cocoabbs_uc_members")
      ->fields("validated")
        ->where("`uid` = $userid")
          ->fetchOne();
    return $ret["validated"];
  }
  
  public function updateEmailSetting($userid,
        $emailatnotification,
        $emaildailynotification,
        $emailweeklynotification) {
          
    $sql="UPDATE `cocoabbs_uc_members` set 
           emailatnotification=$emailatnotification,
           emaildailynotification=$emaildailynotification,
           emailweeklynotification=$emailweeklynotification
           WHERE uid = $userid
           ;";
    $this->run($sql);      
  }
  
  public function updateUserStats() {
    
    $sql = "UPDATE `cocoabbs_uc_members`
        INNER JOIN 
          (SELECT count(*)   `c`,`createbyid` FROM `threads` GROUP BY `createbyid`) `tempt`
        ON `tempt`.`createbyid` = `cocoabbs_uc_members`.`uid` 
        SET `posts`=`c`;";
    $this->run($sql);
    
    $sql = "UPDATE `cocoabbs_uc_members`
            INNER JOIN 
              (SELECT count(*)   `c`,`userid` FROM `thread_replys` GROUP BY `userid`) `tempt`
            ON `tempt`.`userid` = `cocoabbs_uc_members`.`uid` 
            SET `replys`=`c`;";
    $this->run($sql);
  }
  
  public function add_reputation(
                        $userid,$amount,$message,$time,
                        $sourcename="",$sourceid=0,$sourceuserid=0) {
    
    $data["userid"] = $userid;
    $data["amount"] = $amount;
    $data["message"] = $message;
    $data["updatetime"] = $time;
    $data["sourcename"] = $sourcename;
    $data["sourceid"] = $sourceid;
    $data["sourceuserid"] = $sourceuserid;
    
    $this->select("reputation")->insert($data);
  }
  
  public function add_money(
                      $userid,$amount,$message,$time,
                      $sourcename="",$sourceid=0,$sourceuserid=0) {
    
    $data["userid"] = $userid;
    $data["amount"] = $amount;
    $data["message"] = $message;
    $data["updatetime"] = $time;
    $data["sourcename"] = $sourcename;
    $data["sourceid"] = $sourceid;
    $data["sourceuserid"] = $sourceuserid;
    
    $this->select("money")->insert($data);
  }
  
  public function update_reputationAndMoney($userid) {
    
    $sql = "UPDATE `cocoabbs_uc_members` 
            SET 
              `reputation` = (SELECT SUM(`amount`) FROM `reputation` WHERE `userid` = $userid),
              `money` = (SELECT SUM(`amount`) FROM `money` WHERE `userid` = $userid)
            WHERE `uid` = $userid;";
    $this->run($sql);
  }
  
  public function reputation_records($userid) {
    
    $records = $this->select("reputation")->where("userid = $userid")->orderby("`updatetime` DESC")->fetchAll();
    if(!$records)
       return array();
    $newRecords = array();
    foreach($records as $reputation) {
      
      $reputation["time"] = ToolModel::countTime($reputation["updatetime"]);
      $newRecords[] = $reputation;
    }
    return $newRecords;
  }
  
  public function money_records($userid) {
    
    $records = $this->select("money")->where("userid = $userid")->orderby("`updatetime` DESC")->fetchAll();
    
    if(!$records)
       return array();
    $newRecords = array();
    foreach($records as $money) {
      
      $money["time"] = ToolModel::countTime($money["updatetime"]);
      $newRecords[] = $money;
    }
    return $newRecords;
  }
  
  public function removeRepution($userid,$sourcename,$sourceid,$sourceUserid) {
    
    $sql = "DELETE FROM `reputation` 
                WHERE `userid` = $userid AND 
                      `sourcename` = '$sourcename' AND
                      `sourceid` = $sourceid AND
                      `sourceuserid` = $sourceUserid;
                      ";
    $this->run($sql);
    //var_dump($sql);
  }
  
  public function removeMoney($userid,$sourcename,$sourceid,$sourceUserid) {
    
    $sql = "DELETE FROM `money` 
                WHERE `userid` = $userid AND 
                      `sourcename` = '$sourcename' AND
                      `sourceid` = $sourceid AND
                      `sourceuserid` = $sourceUserid;
                      ";  
    $this->run($sql);
  }
  
}



