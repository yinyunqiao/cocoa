
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
  
}






