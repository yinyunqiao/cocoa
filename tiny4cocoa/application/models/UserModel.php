<?php
class UserModel extends baseDbModel {
  
  public function username($userid) {
    
    $sql = "SELECT username FROM `cocoabbs_uc_members` WHERE uid = $userid;";
    $result = $this->fetchArray($sql);
    return $result[0]["username"];
  }
  
  public function normalUsers() {
    
    //groupid
    //8:等待验证
    //4:禁止发言
    //5:禁止访问
    //6:禁止IP
    $sql="SELECT `username`,`email` 
      FROM `cocoabbs_uc_members`
      WHERE `groupid` <> 4 
	    AND `groupid` <> 5 
	    AND `groupid` <> 6 
	    AND `groupid` <> 8
      AND `email` not in (SELECT `email` FROM `nonews`);";
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
             "admin@tiny4.org", 
            "Tiny4Cocoa社区邮件退订确认信", 
            $page);
    
  }
  
  public function addUnsubscribeMail($mail) {
    
    $sql = "INSERT INTO `nonews` (`email`)VALUES('$mail');";
    $this->run($sql);
  }
  
  public function users($page,$size) {
    
   $start = ($page-1)*$size;
   return $this
     ->select("cocoabbs_uc_members")
     ->orderby("regdate DESC")
     ->limit("$start,$size")
     ->fetchAll(); 
  }
  
  public function count() {
    
     $ret = $this
     ->select("cocoabbs_uc_members")
     ->fields("count(*) as c")
     ->fetchOne(); 
     return $ret["c"];
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
      return $result[0]["uid"];
    }
  }
  
  public function checklogin() {
    
    
    if($_SESSION["username"] && $_SESSION["userid"]) {
      
      $this->renewCookie();
      return $_SESSION["userid"];
    } else {
      
      $this->cookie2Session();
      if($_SESSION["userid"])
        return $_SESSION["userid"];
    }
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
  
  public function reg($data) {
    
    $user["username"] = $data["name"];
    $user["salt"] = rand(100000,999999);
    $user["password"] = md5(md5($data["password"]).$user["salt"]);
    $user["email"] = $data["email"];
    $user["regip"] = ToolModel::getRealIpAddr();
    $user["regdate"] = time();
    $user["validated"] = 0;
    $userid = $this->select("cocoabbs_uc_members")->insert($user);
    return $userid;
  }
}



