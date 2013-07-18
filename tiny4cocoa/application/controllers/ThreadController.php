<?php
class ThreadController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $this->_view->assign("active","thread");
  }
 
  public function indexAction() {
    
    $this->setTitle("讨论区");
    $this->display();
  }
  
  public function showAction() {
    
    $id = $this->intVal(3);
    $threadModel = new ThreadModel();
    $thread = $threadModel->threadById($id);
    $replysCount = $threadModel->replysCountById($id);
    $replys = $threadModel->replysById($id);
    $this->_mainContent->assign("thread",$thread);
    $this->_mainContent->assign("replysCount",$replysCount);
    $this->_mainContent->assign("replys",$replys);
    $this->setTitle($thread["title"]);
    $this->display();
  }
 
  public function newAction() {
    
    $discuz = new DiscuzModel();
    $userModel = new UserModel();
    $userid = $discuz->checklogin();
    $username = $userModel->username($userid);
    if($userid==0) {
      header("location: /logging.php?action=login");
      die();
    }
    if($_POST){
      
      $data = array();
      $data["title"] = $_POST["title"];
      $data["content"] = $_POST["content"];
      $data["createby"] = $username;
      $data["createbyid"] = $userid;
      $data["createdate"] = time();
      if(strlen($_POST["title"])>0)
        if(strlen($_POST["content"])>0) {
          $threadModel = new ThreadModel();
          $threadid = $threadModel->newThread($data);
          header("location: /thread/show/$threadid/");
          die();
      }
    }
    $this->display();
  }
}






