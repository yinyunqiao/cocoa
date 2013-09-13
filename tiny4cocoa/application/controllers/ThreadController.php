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
    
    $threadModel = new ThreadModel();
    $threadPageSize = 40;
    $threadCount = $threadModel->threadCount();
    $threads = $threadModel->threads($page,$threadPageSize);
		$pageControl = ToolModel::pageControl($page,$threadCount,$threadPageSize,"<a href='/thread/index/#page#/'>");
      
    $this->_mainContent->assign("threads",$threads);
    $this->_mainContent->assign("pageControl",$pageControl);
    
    
    $newthreads = $threadModel->threads(1,20);
    $this->_mainContent->assign("newthreads",$newthreads);
    
    $toplistModel = new ToplistModel();
    $toplist = $toplistModel->toplist();
    $this->_mainContent->assign("toplist",$toplist);
    
    if($page==1)
      $this->setTitle("讨论区");
    else
      $this->setTitle("讨论区 第 $page 页");
    
    $this->display();
  }
  
  public function showAction() {
    
    $id = $this->intVal(3);
    $threadModel = new ThreadModel();
    
    if($_POST){
      
      $userModel = new UserModel();
      if($this->userid>0)
        if(strlen($_POST["content"])>0) {
        
          $data = array();
          $time = time();
          $data["threadid"] = $id;
          $data["name"] = $this->username;
          $data["userid"] = $this->userid;
          $data["content"] = $_POST["content"];
          $data["createdate"] = $time;
          $data["updatedate"] = $time;
          $replys = $threadModel->newReply($data);
          if($replys) {
            $thread = array();
            $thread["id"] = $id;
            $thread["replys"] = $replys;
            $thread["updatedate"] = $time;
            $thread["lastreply"] = $data["name"];
            $thread["lastreplyid"] = $data["userid"];
            $threadModel->updateThread($thread);
          }
          header("location: /thread/show/$id/");
          die();
        }
    }
    $thread = $threadModel->threadById($id);
    $replysCount = $threadModel->replysCountById($id);
    $replys = $threadModel->replysById($id);
    
    $threads = $threadModel->threads(1,20);
    $this->_mainContent->assign("threads",$threads);
    
    $this->_mainContent->assign("userid",$this->userid);
    $this->_mainContent->assign("thread",$thread);
    $this->_mainContent->assign("replysCount",$replysCount);
    $this->_mainContent->assign("replys",$replys);
    
    $toplistModel = new ToplistModel();
    $toplist = $toplistModel->toplist();
    $this->_mainContent->assign("toplist",$toplist);
    
    $this->setTitle($thread["title"]);
    $this->display();
  }
 
  public function newAction() {
    
    $userModel = new UserModel();
    if($this->userid==0) {
      header("location: /user/login/");
      die();
    }
    if($_POST){
      
      $data = array();
      $time = time();
      $data["title"] = $_POST["title"];
      $data["content"] = $_POST["content"];
      $data["createby"] = $this->username;
      $data["createbyid"] = $this->userid;
      $data["createdate"] = $time;
      $data["updatedate"] = $time;
      if(strlen($_POST["title"])>0)
        if(strlen($_POST["content"])>0) {
          $threadModel = new ThreadModel();
          $threadid = $threadModel->newThread($data);
          if($threadid==-1) {
            
            $this->viewFile="Thread/duplicate.html";
            $this->display();
            die();
          }
          header("location: /thread/show/$threadid/");
          die();
      }
    }
    $this->display();
  }

  public function editThreadAction() {
    
    $id = $this->intVal(3);
    $threadModel = new ThreadModel();
    
    if($this->userid==0)
      header("location: /thread/show/$id/");
    if($_POST &&
      strlen($_POST["threadid"])>0 &&
      strlen($_POST["title"])>0 && 
      strlen($_POST["content"])>0){
        
        $data = array();
        $data["id"] = $_POST["threadid"];
        $data["modifydate"] = time();
        $data["title"] = $_POST["title"];
        $data["content"] = $_POST["content"];
        $threadModel->updateThread($data);
        header("location: /thread/show/$data[id]/");
        die();
    }
    
    $thread = $threadModel->threadById($id,0);
    if($thread["createbyid"]!=$this->userid)
      header("location: /thread/show/$id/");
    $this->_mainContent->assign("userid",$this->userid);
    $this->_mainContent->assign("thread",$thread);
    $this->setTitle($thread["title"]);
    $this->display();
  }
  
  public function editReplyAction() {
    
    $id = $this->intVal(3);
    $threadModel = new ThreadModel();
    $reply = $threadModel->replyByReplyId($id,0);
    
    if($this->userid==0)
      header("location: /thread/show/$reply[threadid]/");
    if($reply["userid"]!=$this->userid)
      header("location: /thread/show/$reply[threadid]/");
    
    if($_POST &&
      strlen($_POST["replyid"])>0 &&
      strlen($_POST["content"])>0){
      
        $reply = $threadModel->replyByReplyId($_POST["replyid"],0);
        if($reply["userid"]!=$this->userid) {
          header("location: /thread/show/$reply[threadid]/");
          die();
        }
        $data = array();
        $data["id"] = $_POST["replyid"];
        $data["updatedate"] = time();
        $data["content"] = $_POST["content"];
        $threadModel->updateReply($data);
        header("location: /thread/show/$_POST[threadid]/");
        die();
    }
    
    $this->_mainContent->assign("reply",$reply);
    $this->_mainContent->assign("userid",$this->userid);
    $this->display();
  }
  
}






