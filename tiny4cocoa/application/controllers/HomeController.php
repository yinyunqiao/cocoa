<?php
class HomeController extends baseController
{
 
  public function indexAction() {
    
    $allModel = new AllModel();
    $newsModel = new NewsModel();
    
    $threads = $allModel->allThreads(1,10);
    $newThreads = $allModel->newThreads(1,10);
    
    $news = $newsModel->news(1,10);
    $this->_mainContent->assign("threads",$threads);
    $this->_mainContent->assign("newThreads",$newThreads);
    $this->_mainContent->assign("news",$news);
    $this->display();
  }
  public function sitemapAction() {
    
    $newsModel = new NewsModel();
    $news = $newsModel->news(1,10);
    $this->_mainContent->assign("news",$news);
    $this->_layout = "empty";
    $this->display();
  }
  
  public function sAction() {
    
    $index = $this->intVal(3);
    $other = $this->strVal(4);
    if(count($this->__uriparts)!=5 || !empty($other)) {
      
      header ('HTTP/1.1 301 Moved Permanently');
      header("location: /home/s/$index/");
    }
    
    $discuz = new DiscuzModel();
    $allModel = new AllModel();
    $newsModel = new NewsModel();
    $tongji = new TongjiModel();
    
    
    $dataall = $tongji->data("all");
    $hotnews = $tongji->hotnews(10);
      
    $threads = $allModel->allThreads(1,10);
    $userid = $discuz->checklogin();
    $username = $newsModel->usernameById($userid);
    $news = $newsModel->oneNews($index);
    $comments = $newsModel->commentsByNewsId($index);
    
    $nonamename = $_COOKIE["nonamename"];
    if(empty($nonamename)) {
      
      $nonamename = "匿名用户" . rand(0,10000);
      setcookie("nonamename", $nonamename, time()+3600*24*7*2);
    }
    
    $this->_mainContent->assign("threads",$threads);
    $this->_mainContent->assign("news",$news);
    $this->_mainContent->assign("comments",$comments);
    $this->_mainContent->assign("userid",$userid);
    $this->_mainContent->assign("username",$username);
    $this->_mainContent->assign("nonamename",$nonamename);
    $this->_mainContent->assign("pageview",$dataall[$index]);
    $this->_mainContent->assign("hotnews",$hotnews);
    
    $this->setTitle($news["title"]);
    $this->display();
  }

  public function savecommentAction() {
    
    if(empty($_POST["content"])) {
      header ('HTTP/1.1 301 Moved Permanently');
      header("location: /home/");
    }
    $newsModel = new NewsModel();
    $newsModel->saveComment();
  }
  
  public function logAction() {
    
    $log = join(",",$_GET);
    $fp = fopen('/root/log/footprint.log', 'a');
    fwrite($log."\r\n");
    fclose($fp);
  }
}


