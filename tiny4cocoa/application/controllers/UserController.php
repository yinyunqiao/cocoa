<?php
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
    $this->display();
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
  
}
