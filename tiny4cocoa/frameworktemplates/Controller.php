<?php
class [name]Controller extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
  }
 
  public function indexAction() {
    
    $this->display();
  }
}



