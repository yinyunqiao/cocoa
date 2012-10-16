<?php
class PlaygroundController extends baseController
{
 
  public function indexAction() {
    
    $this->display();
  }
  
  public function joinAction() {
    
    if($_GET["app"]!="footprint")
      header("location:/playground/");
    
    $this->_mainContent->assign("app",$_GET["app"]);
    $this->display();
  }
  
  public function saverequestAction() {
    
    $db = new PlaygroundModel();
    $db->save($_POST);
    header("location:/playground/joinok/");
  }
  
  public function joinokAction() {
    
    $this->display();
  }
}


