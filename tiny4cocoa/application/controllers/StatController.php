<?php
class StatController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $this->_view->assign("active","stat");
  }
 
  public function indexAction() {
    
    
    $statModel = new StatModel();
    
    $regUsersTrend = $statModel->recentRegUsersTrend();
    $this->_mainContent->assign("regUsersTrend",$regUsersTrend);
    
    $threadTrend = $statModel->recentThreadTrend();
    $this->_mainContent->assign("threadTrend",$threadTrend);
    
    $replyTrend = $statModel->recentReplysTrend();
    $this->_mainContent->assign("replyTrend",$replyTrend);
    
    
    $this->display();
  }
  
  
}
