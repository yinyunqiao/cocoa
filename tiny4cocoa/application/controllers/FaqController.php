<?php
class FaqController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $this->_view->assign("active","faq");
    
    $toplistModel = new ToplistModel();
    $toplist = $toplistModel->toplist();
    $this->_mainContent->assign("toplist",$toplist);
    
    $threadModel = new ThreadModel();
    $newthreads = $threadModel->threads(1,20);
    $this->_mainContent->assign("newthreads",$newthreads);
    
    $newsModel = new NewsModel();
    $tags = $newsModel->hotTags(500);
    $this->_mainContent->assign("tags",$tags);
    
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
    
    $faqModel = new FaqModel();
    $threadPageSize = 40;
    $threadCount = $faqModel->threadCount();
    $threads = $faqModel->threads($page,$threadPageSize);
		$pageControl = ToolModel::pageControl($page,$threadCount,$threadPageSize,"<a href='/faq/index/#page#/'>",0);
      
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
    $faqModel = new FaqModel();
    

    $thread = $faqModel->threadById($id);
    $replysCount = $faqModel->replysCountById($id);
    $replys = $faqModel->replysById($id);
    
    $threads = $faqModel->threads(1,20);
    $this->_mainContent->assign("threads",$threads);
    
    $this->_mainContent->assign("thread",$thread);
    $this->_mainContent->assign("replysCount",$replysCount);
    $this->_mainContent->assign("replys",$replys);
    
    $toplistModel = new ToplistModel();
    $toplist = $toplistModel->toplist();
    $this->_mainContent->assign("toplist",$toplist);
    
    $this->setTitle($thread["title"]);
    $this->display();
  }
 
  function tagAction() {
    
    $tag = urldecode($this->strVal(3));       
    $this->_mainContent->assign("tag",$tag);

    $newsModel = new NewsModel();
    $threads = $newsModel->tagThreads($tag);
    $this->_mainContent->assign("threads",$threads);
    $this->display();
  }
}






