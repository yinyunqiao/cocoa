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
    
    $this->display();
  }
  
  public function loginAction() {
    
    if($_POST){
      
      $userModel = new UserModel();
      $uid = $userModel->login($_POST);
      var_dump($uid);
      die();
    }
    $this->display();
  }
  public function logoutAction() {
    $discuz = new DiscuzModel();
    $discuz->logout();
  }
  
}
