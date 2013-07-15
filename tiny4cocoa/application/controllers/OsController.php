<?php
class OsController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $this->_view->assign("active","os");
  }
 
  public function indexAction() {
    
    $os = new OsModel();
    $libs = $os->libs();
    $this->_mainContent->assign("libs",$libs);
    $this->display();
  }
  
  public function libAction() {
    
    $id = $this->intVal(3);
    $os = new OsModel();
    $lib = $os->lib($id);
    $this->_mainContent->assign("lib",$lib);
    $this->display();
  }
  
}



