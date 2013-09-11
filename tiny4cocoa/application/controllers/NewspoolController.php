<?php
class NewspoolController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
  }
  
  public function indexAction() {
    
    $newsModel = new NewsModel();
    $page = $this->intVal(3);
    if($page==0)
      $page=1;
    $size = 40;
    
    $newscenter = new NewscenterModel();
    $count = $newscenter->count("all");
    $applenews = $newscenter->news(($page-1)*$size,$size,"all");
    $napplenews = array();
    foreach($applenews as $item) {
      
      $item["time"] = ToolModel::countTime($item["pubdate"]);
      $item["elink"] = urlencode($item["link"]);
      
      $napplenews[] = $item;
    }
    
    $applenews = $napplenews;
    
		$pageControl = ToolModel::pageControl($page,$count,$size,"<a href='/newspool/index/#page#/'>",0);
    
    
    $toplistModel = new ToplistModel();
    $toplist = $toplistModel->toplist();
    $this->_mainContent->assign("toplist",$toplist);
    
    $this->_mainContent->assign("pageControl",$pageControl);
    $this->_mainContent->assign("applenews",$applenews);
    
    if($page>1)
      $this->setTitle("本站新闻 第".$page."页");
    $this->display();
  }

}


