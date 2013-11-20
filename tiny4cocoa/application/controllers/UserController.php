<?php
require_once  dirname(dirname(dirname(__FILE__))) . '/lib/recaptcha/recaptchalib.php';

class UserController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $this->_view->assign("active","user");
  }
 
  public function indexAction() {

    $page = $this->intVal(3);
    if($page==0)
      $page=1;
    $size = 30;
    
    $userModel = new UserModel();
    $count = $userModel->count();
    $users = $userModel->users($page,$size);
    $this->_mainContent->assign("users",$users);
    
		$pageControl = ToolModel::pageControl($page,$count,$size,"<a href='/user/index/#page#/'>",0);
    $this->_mainContent->assign("pageControl",$pageControl);
    
    $this->display();
  }
  
  public function recentAction() {
    
    $page = $this->intVal(3);
    if($page==0)
      $page=1;
    $size = 30;
    
    $userModel = new UserModel();
    $count = $userModel->count();
    $users = $userModel->users($page,$size,"date");
    $this->_mainContent->assign("users",$users);
    
		$pageControl = ToolModel::pageControl($page,$count,$size,"<a href='/user/recent/#page#/'>",0);
    $this->_mainContent->assign("pageControl",$pageControl);
    
    $this->display();
  }
  
  public function nameAction() {
    
    $name = urldecode($this->strVal(3));
    $userModel = new UserModel();
    $userid = $userModel->useridByName($name);
    header ('HTTP/1.1 301 Moved Permanently');
    header("location:/user/show/$userid/$name/");
  }
  
  
  public function showAction() {
    
    $id = $this->intVal(3);
    if($id==0) {
      header ('HTTP/1.1 301 Moved Permanently');
      header("location:/user/show/0/");
    }
    $userModel = new UserModel();
    $threadModel = new ThreadModel();
    $userinfo["id"] = $id;
    $userinfo["name"] = $userModel->username($id);
    $userinfo["image"] = DiscuzModel::get_avatar($id,"middle");
    $userinfo["threadscreate"] = $threadModel->threadsByUserid($id);
    $userinfo["threadsreply"] = $threadModel->threadsReplyByUserid($id);
    $this->_mainContent->assign("user",$userinfo);
    $this->display();
  }
  
  public function unsubscribeAction() {
    
    $userModel = new UserModel();
    if(strlen($_POST["email"])>3) {

      $userModel->sendUnsubscribeMail($_POST["email"]);
      $this->viewFile="User/unsubscribe_mailsent.html";
      $this->display();
      die();
    }
    if(strlen($_GET["v"])>0) {
      
      $code = md5($_GET["mail"]."3.141592654");
      if($code==$_GET["v"]) {
        
        $userModel->addUnsubscribeMail($_GET["mail"]);
        $this->viewFile="User/unsubscribe_ok.html";
        $this->display();
        die();
      }
    }
    
    $email = $_GET["mail"];
    $this->_mainContent->assign("email",$email);
    $this->display();
  }
  
  public function regAction() {
    
    if($_POST) {
      
      if(!$_POST["name"] || !$_POST["password"] || !$_POST["email"]) {
        
        header("location: /");
        die();
      }
      $userModel = new UserModel();
      $userid = $userModel->reg($_POST);
      $userModel->sendValidateMail($userid);
      $this->viewFile="User/regok.html";
      $this->display();
      die();
    }
    $this->display();
  }
  
  public function resendvalidateemailAction() {
    
    $userModel = new UserModel();
    $userid = $userModel->checklogin();
    $userModel->sendValidateMail($userid);
    $this->display();
  }
  
  public function validateAction() {
    
    $userModel = new UserModel();
    $ret = $userModel->validateMail($_GET["user"],$_GET["v"]);
    if($ret==1)
      $this->viewFile="User/validateok.html";
    else
      $this->viewFile="User/validateerror.html";
    $this->display();
  }
  
  public function loginAction() {
    
    if($_POST){
      
      $userModel = new UserModel();
      $userid = $userModel->login($_POST);
      if($userid>0)
        header("location: /");
      else
        header("location: /user/login/");
      die();
    }
    $this->display();
  }
  
  public function logoutAction() {
    
    $userModel = new UserModel();
    $userModel->logout();
  }
  
  public function avatarAction() {
    
    if($this->userid==0){
    
      header("location: /");
      die();
    }
    
    if(isset($_FILES['ImageFile'])) {
    
      $this->uploadFile();
      die();
    }
    
    if($_POST) {
      
      $this->saveAvatar();          
      die();
    }
    $avatar = DiscuzModel::get_avatar($this->userid,"small");
    $this->_mainContent->assign("avatar",$avatar);
    $this->display();
  }
  
  private function saveAvatar() {
    
    $discuzPath = dirname(dirname(dirname(dirname(__FILE__))));
    $uploaderModel = new UploaderModel();
    $filename =  $discuzPath.$_POST["fileurl"];
    $image = ToolModel::createImageFromFile($filename);
    unlink($filename);
    $imagex = imagesx($image);
    $imagey = imagesy($image);
    $cx = $_POST["cx"]*$imagex/$_POST["iw"];
    $cy = $_POST["cy"]*$imagey/$_POST["ih"];
    $cw = $_POST["cw"]*$imagex/$_POST["iw"];
    $ch = $_POST["ch"]*$imagey/$_POST["ih"];
    
    $image1 = $uploaderModel->crop($image,
                      $cx,
                      $cy,
                      $cw,
                      $ch,
                      180,
                      180);
    $image2 = $uploaderModel->crop($image,
                      $cx,
                      $cy,
                      $cw,
                      $ch,
                      80,
                      80);
    $image3 = $uploaderModel->crop($image,
                      $cx,
                      $cy,
                      $cw,
                      $ch,
                      48,
                      48);
                      
    $userModel = new UserModel();    
    $userid =  $userModel->checklogin();
    $avpath = DiscuzModel::get_avatar_path($userid,"big");
    $path = dirname($avpath);
    ToolModel::makeDeepDir($path);
    imagejpeg($image1,$avpath,85);                  
    $avpath = DiscuzModel::get_avatar_path($userid,"middle");
    imagejpeg($image2,$avpath,85);                  
    $avpath = DiscuzModel::get_avatar_path($userid,"small");
    imagejpeg($image3,$avpath,85);
  }
  private function uploadFile() {
    
  	if(!isset($_FILES['ImageFile']) || !is_uploaded_file($_FILES['ImageFile']['tmp_name']))
      die("ERROR");
    $discuzPath = dirname(dirname(dirname(dirname(__FILE__))));
    $savepath = "$discuzPath/uploadtemp/";
    $uploaderModel = new UploaderModel();
    $filename = $uploaderModel->savetoTempDir("ImageFile",$savepath);
    echo $filename;
  }
  
  public function emailsettingAction() {
    
    if($this->userid==0) {
      header("location: /user/login/");
      die();
    }
    $userModel = new UserModel();
    
    if($_POST) {
      
      if($_POST["atnotify"]==1)
        $emailatnotification = 1;
      else
        $emailatnotification = 0;
      
      if($_POST["dailynews"]==1)
        $emaildailynotification = 1;
      else
        $emaildailynotification = 0;
      
      if($_POST["weeklynews"]==1)
        $emailweeklynotification = 1;
      else
        $emailweeklynotification = 0;
      $userModel->updateEmailSetting(
        $this->userid,
        $emailatnotification,
        $emaildailynotification,
        $emailweeklynotification);
      $this->_mainContent->assign("warnning","设置成功更新");
    }
    $userInfo = $userModel->userInfo($this->userid);
    $this->_mainContent->
      assign("emailatnotification",$userInfo["emailatnotification"]);
    $this->_mainContent->
      assign("emaildailynotification",$userInfo["emaildailynotification"]);
    $this->_mainContent->
      assign("emailweeklynotification",$userInfo["emailweeklynotification"]);

    $this->display();
  }
  
  
  public function ajaxcheckAction(){
    
    $action = $this->strVal(3);
    $userModel = new UserModel();
    switch($action) {
      
      case "name":
        if($userModel->isUserExisted($_GET["name"]))
          echo "false";
        else
          echo "true";
        break;
      case "email":
        if($userModel->isEmailExisted($_GET["email"]))
          echo "false";
        else
          echo "true";
        break;
    }
  }
  
  public function weiboAction() {
    
    if($this->userid==0) {
      header("location: /user/login/");
      die();
    }
    
    $weiboModel = new WeiboModel();
    $token = $weiboModel->token($this->userid);
    if($token)
      $this->_mainContent->assign("hastoken",1);
    else {
      $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
      $code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );
      $this->_mainContent->assign("url",$code_url);
    }
    $this->display();
  }
    
  public function weibocallbackAction() {
    
    $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

    if (isset($_REQUEST['code'])) {
    	$keys = array();
    	$keys['code'] = $_REQUEST['code'];
    	$keys['redirect_uri'] = WB_CALLBACK_URL;
    	try {
    		$token = $o->getAccessToken( 'code', $keys ) ;
    	} catch (OAuthException $e) {
    	}
    }
    if ($token) {
      $weiboModel = new WeiboModel();
      $weiboModel->setToken($token["access_token"]);
      header("location:/user/weibo/");
    }else {      
      echo '绑定失败！<a hrer="/user/weibo/">请重试</a>';
    }
  }
  
  public function sendweiboAction() {
    
    if(!$_POST)
      die();
    $weiboModel = new WeiboModel();
    $token = $weiboModel->token($this->userid);
    if(!$token)
      die("notoken");
    $c = new SaeTClientV2( WB_AKEY , WB_SKEY , $token);
    $ret = $c->update($_POST["weibocontent"]);
    if($ret)
      return "ok";
  }
  
  public function passwordAction() {
    
    if($_POST){
      
      $passwordmd5 = md5($_POST["oldpassword"]);
      $userModel = new UserModel();
      if(!$userModel->passwordMatch($this->userid,$passwordmd5)){
        
        $this->message("密码修改失败","老密码不对<p><a href='/user/password/'>返回修改</a></p>");
        die();
      }
      $userModel = new UserModel();
      $userModel->changePassword($this->userid,$_POST["newpassword"]);
      $this->message("密码修改成功","密码修改成功");
      die();
    }
    $this->display();
  }
  
  public function resetpasswordAction() {
    
    if($_POST) {
      
      $ticket = $_POST["ticket"];
      $ticketModel = new TicketModel();
      $data = $ticketModel->isTicketExistedAndVaild($ticket);
      if(!$data){
      
        $this->message("请求过期","你的重置密码请求已经过期，请重新申请。");
        die();
      }
      $this->message("修改成功","你的密码已经修改成功了。");
      $userModel = new UserModel();
      $userModel->changePassword($data["userid"],$_POST["password"]);
      $ticketModel->removeTicket($ticket);
      die();
    }
    $ticket = $_GET["ticket"];
    if($ticket=="") {
      $this->message("这里一片荒芜","你走到了无人之地");
      die();
    }
    $ticketModel = new TicketModel();
    $data = $ticketModel->isTicketExistedAndVaild($ticket);
    if(!$data){
      
      $this->message("请求过期","你的重置密码请求已经过期，请重新申请。");
      die();
    }
    $this->_mainContent->assign("ticket",$ticket);
    $this->display();
  }
  public function forgotpasswordAction() {
    if($_POST) {
      
      $privatekey = "6LcGEuMSAAAAAAohpDLjBTKW9WhcoIdrnopcBzgY";
      if ($_POST["recaptcha_response_field"]) {
          
        $resp = recaptcha_check_answer ($privatekey,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);
        if ($resp->is_valid) {
          $userModel = new UserModel();
          $userid = $userModel->resetPassword($_POST["name"]);
          if($userid==0)
            echo "user_not_found";
          else
            echo "ok";
        } else {
          $error = $resp->error;
          echo $error;
        }
      }
      else
        echo "error";
      die();
    }
    
    
    $flag = $this->strVal(3);
    if($flag=="ok"){
      
      $this->message(
            "成功",
            "重置密码的邮件已经发送到你的注册邮箱，请耐心等待，然后按照邮件提示进行。");
      die();
    }

    $this->display();
  }
}
