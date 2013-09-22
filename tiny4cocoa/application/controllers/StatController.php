<?php
class StatController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $this->_view->assign("active","stat");
  }
 
  public function indexAction() {
    
    
    $statModel = new StatModel();
    
    $day = 10;
    $regUsersTrend = $statModel->recentRegUsersTrend($day);
    $this->_mainContent->assign("regUsersTrend",$regUsersTrend);
    
    $recentRegUsersTrendAll = $statModel->recentRegUsersTrendAll($day);
    $this->_mainContent->assign("recentRegUsersTrendAll",$recentRegUsersTrendAll);
    
    $activeUsersTrend = $statModel->activeUsersTrend($day);
    $this->_mainContent->assign("activeUsersTrend",$activeUsersTrend);
    
    $threadTrend = $statModel->recentThreadTrend($day);
    $this->_mainContent->assign("threadTrend",$threadTrend);
    
    $replyTrend = $statModel->recentReplysTrend($day);
    $this->_mainContent->assign("replyTrend",$replyTrend);
    
    
    $TrendTimer = $statModel->threadTimerTrend($day);
    $this->_mainContent->assign("TrendTimer",$TrendTimer);
    
    $replyTimer = $statModel->replysTimerTrend($day);
    $this->_mainContent->assign("replyTimer",$replyTimer);
    
    $stats = $statModel->stats();
    $this->_mainContent->assign("stats",$stats);
    
    $this->display();
  }
  
  
}
