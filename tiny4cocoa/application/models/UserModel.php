<?php
class UserModel extends baseDbModel {
  
  public function username($userid) {
    
    $sql = "SELECT username FROM `cocoabbs_members` WHERE uid = $userid;";
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
      FROM `cocoabbs_members`
      WHERE `groupid` <> 4 
	    AND `groupid` <> 5 
	    AND `groupid` <> 6 
	    AND `groupid` <> 8
      AND `email` not in (SELECT `email` FROM `nonews`);";
    $result = $this->fetchArray($sql);
    return $result;
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
}