<?php
class ThreadController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $this->_view->assign("active","thread");
  }
 
  public function indexAction() {
    
    $page = $this->intVal(3);
    if($page==1) {
      
      header("HTTP/1.1 301 Moved Permanently");
      header("location: /thread/");
      die();
    }
    if($page==0)
      $page=1;
    
    $thread = new ThreadModel();
    $threadPageSize = 20;
    $threadCount = $thread->threadCount();
    $threads = $thread->threads($page,$threadPageSize);
		$pageControl = ToolModel::pageControl($page,$threadCount,$threadPageSize,"<a href='/thread/index/#page#/'>");
      
    $this->_mainContent->assign("threads",$threads);
    $this->_mainContent->assign("pageControl",$pageControl);
    if($page==1)
      $this->setTitle("讨论区");
    else
      $this->setTitle("讨论区 第 $page 页");
    $this->display();
  }
  
  public function showAction() {
    
    $id = $this->intVal(3);
    $threadModel = new ThreadModel();
    $discuz = new DiscuzModel();
    $userid = $discuz->checklogin();
    
    if($_POST){
      
      $userModel = new UserModel();
      if($userid>0)
        if(strlen($_POST["content"])>0) {
        
          $data = array();
          $time = time();
          $data["threadid"] = $id;
          $data["name"] = $userModel->username($userid);
          $data["userid"] = $userid;
          $data["content"] = $_POST["content"];
          $data["createdate"] = $time;
          $data["updatedate"] = $time;
          $replys = $threadModel->newReply($data);
          $thread = array();
          $thread["id"] = $id;
          $thread["replys"] = $replys;
          $thread["updatedate"] = $time;
          $thread["lastreply"] = $data["name"];
          $thread["lastreplyid"] = $data["userid"];
          $threadModel->updateThread($thread);
          header("location: /thread/show/$id/");
          die();
        }
    }
    $thread = $threadModel->threadById($id);
    $replysCount = $threadModel->replysCountById($id);
    $replys = $threadModel->replysById($id);
    
    $threads = $threadModel->threads(1,20);
    
    $this->_mainContent->assign("userid",$userid);
    $this->_mainContent->assign("thread",$thread);
    $this->_mainContent->assign("replysCount",$replysCount);
    $this->_mainContent->assign("replys",$replys);
    $this->_mainContent->assign("threads",$threads);
    
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
      $time = time();
      $data["title"] = $_POST["title"];
      $data["content"] = $_POST["content"];
      $data["createby"] = $username;
      $data["createbyid"] = $userid;
      $data["createdate"] = $time;
      $data["updatedate"] = $time;
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






