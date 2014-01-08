<?php
class QuestionController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $this->_view->assign("active","question");
  }
 
  public function indexAction() {
    
    $this->baseThreadIndex("index","`score` DESC");
  }
  
  public function hotAction() {
    
    $this->baseThreadIndex("hot","`replys` DESC","最热帖");
  }
  
  public function coldAction() {
    
    $this->baseThreadIndex("cold","`replys`,id DESC","最冷贴");
  }
  
  function baseThreadIndex($action,$order,$title="") {
    
    $page = $this->intVal(3);
    if($page==1) {
      
      header("HTTP/1.1 301 Moved Permanently");
      header("location: /question/");
      die();
    }
    if($page==0)
      $page=1;
    
    $threadModel = new ThreadModel();
    $quesitonPageSize = 40;
    $questionCount = $threadModel->questionCount();
    $questions = $threadModel->questions($page,$quesitonPageSize,$order);
		$pageControl = ToolModel::pageControl($page,$questionCount,$quesitonPageSize,"<a href='/question/$action/#page#/'>");
      
    $this->_mainContent->assign("questions",$questions);
    $this->_mainContent->assign("pageControl",$pageControl);
    
    $newthreads = $threadModel->questions(1,20);
    $this->_mainContent->assign("newthreads",$newthreads);
    
    $toplistModel = new ToplistModel();
    $toplist = $toplistModel->toplist();
    $this->_mainContent->assign("toplist",$toplist);
    
    $this->_mainContent->assign("threadtitle",$title);
    
    if($page==1)
      $this->setTitle("提问区 $title");
    else
      $this->setTitle("提问区 $title 第 $page 页");
    
    $this->viewFile="Question/index.html";
    $this->display();
  }
  
  public function showAction() {
    
    $id = $this->intVal(3);
    $threadModel = new ThreadModel();
    $userModel = new UserModel();
    $reputation = $userModel->reputation($this->userid);
    
    if($_POST){
      
      
      if(!$this->isEmailValidated) {
        header("location: /home/");
        die();
      }
      if($reputation<0) {
        header("location: /home/");
        die();
      }
      
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
          header("location: /question/show/$id/");
          die();
        }
    }
    
    $thread = $threadModel->threadById($id);
    if(!$thread || $thread["del"]==1){
      
      header("HTTP/1.1 301 Moved Permanently");
      header("location: /home/");
      die();
    }
    $replysCount = $threadModel->replysCountById($id);
    $replys = $threadModel->replysById($id);
    $threads = $threadModel->questions(1,20);
    $userVote = $threadModel->userVote($id,$this->userid);
      
    $this->_mainContent->assign("threads",$threads);
    
    if(!$this->userid)
      $this->_mainContent->assign("userid",0);
    else
      $this->_mainContent->assign("userid",$this->userid);
    
    if($this->userid) {
      
      $userModel = new UserModel();
      $userInfo = $userModel->userInfo($this->userid);
      $reputation = $userInfo["reputation"];
    }else 
      $reputation = 0;
    $this->_mainContent->assign("reputation",$reputation);
    
    $this->_mainContent->assign("thread",$thread);
    $this->_mainContent->assign("replysCount",$replysCount);
    $this->_mainContent->assign("replys",$replys);
    
    $this->_mainContent->assign("voteInfo",$voteInfo);
    $this->_mainContent->assign("userVote",$userVote);
    
    $toplistModel = new ToplistModel();
    $toplist = $toplistModel->toplist();

    $this->_mainContent->assign("toplist",$toplist);
    $this->_mainContent->assign("isEmailValidated",$this->isEmailValidated);
    $this->_mainContent->assign("reputation",$reputation);
  
    $this->setTitle($thread["title"]);
    $this->display();
  }
 
  public function newAction() {
    
    $userModel = new UserModel();
    $reputation = $userModel->reputation($this->userid);
    if($this->userid==0) {
      header("location: /user/login/");
      die();
    }
    if($_POST){
      
      if(!$this->isEmailValidated) {
        header("location: /home/");
        die();
      }
      if($reputation<0) {
        header("location: /home/");
        die();
      }
      $data = array();
      $time = time();
      $data["title"] = $_POST["title"];
      $data["content"] = $_POST["content"];
      $data["createby"] = $this->username;
      $data["createbyid"] = $this->userid;
      $data["createdate"] = $time;
      $data["updatedate"] = $time;
      $data["score"] = $time;
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
    $this->_mainContent->assign("reputation",$reputation);
    $this->_mainContent->assign("isEmailValidated",$this->isEmailValidated);
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
    $this->_mainContent->assign("isEmailValidated",$this->isEmailValidated);
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
    $this->_mainContent->assign("isEmailValidated",$this->isEmailValidated);
    $this->display();
  }
  
  public function voteAction() {
    
    if($this->userid==0)
      die("no_login");
    
    $userModel = new UserModel();
    $userInfo = $userModel->userInfo($this->userid);
    $reputation = $userInfo["reputation"];
    if($reputation<20)
      die("reputation_too_low");
    
    $threadid = $_POST["threadid"];
    $vote = $_POST["vote"]; 
    $threadModel = new ThreadModel();
    $result = $threadModel->vote($threadid, $this->userid,$vote);
    echo json_encode($result);
  }
}






