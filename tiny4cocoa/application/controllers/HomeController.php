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
  
  public function sAction() {
  	
    
    $index = $this->intVal(3);
    $other = $this->strVal(4);
    if(count($this->__uriparts)!=5 || !empty($other)) {
      
      header ('HTTP/1.1 301 Moved Permanently');
      header("location: /home/s/$index/");
    }
      
    $allModel = new AllModel();
    $threads = $allModel->allThreads(1,10);
    
    $newsModel = new NewsModel();
    $news = $newsModel->oneNews($index);
    $this->_mainContent->assign("threads",$threads);
    $this->_mainContent->assign("news",$news);
    $this->setTitle($news["title"]);
    $this->display();
  }
}


