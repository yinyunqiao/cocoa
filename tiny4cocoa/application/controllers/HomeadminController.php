<?php
class HomeadminController extends baseController
{
 
	public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $discuz = new DiscuzModel();
    $userid = $discuz->checklogin();
    if($userid==0) {
      header ('HTTP/1.1 301 Moved Permanently');
      header('location: /home/');
    }
  }
  
  public function indexAction() {
    
    $this->display();
  }
  
  public function newarticleAction() {
    
    $this->display();
  }
  
  public function articlesAction() {
    
    $this->display();
  }  
}
