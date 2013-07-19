<?php
class OsController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $this->_view->assign("active","user");
  }
 
  public function indexAction() {
    
    $this->display();
  }
  
  public function showAction() {
    
    $id = $this->intVal(3);
    $os = new OsModel();
    $lib = $os->lib($id);
    $this->_mainContent->assign("lib",$lib);
    $this->setTitle($lib["name"]);
    $this->display();
  }
  
}
