<?php
class HomeadminController extends baseController
{
 
	public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $discuz = new DiscuzModel();
    $userid = $discuz->checklogin();
    if($userid!=2) {
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
  
  public function newimageuploadAction() {
    
    if(!isset($_POST))
      die();
  	if(!isset($_FILES['ImageFile']) || !is_uploaded_file($_FILES['ImageFile']['tmp_name']))
  	{
  			die('Something went wrong with Upload!');
  	}
  	$ImageName 		= str_replace(' ','-',strtolower($_FILES['ImageFile']['name']));
  	$ImageSize 		= $_FILES['ImageFile']['size'];
  	$TempSrc	 	= $_FILES['ImageFile']['tmp_name']; 
  	$ImageType	 	= $_FILES['ImageFile']['type'];
  	switch(strtolower($ImageType))
  	{
  		case 'image/png':
  			$CreatedImage =  imagecreatefrompng($_FILES['ImageFile']['tmp_name']);
  			break;
  		case 'image/gif':
  			$CreatedImage =  imagecreatefromgif($_FILES['ImageFile']['tmp_name']);
  			break;			
  		case 'image/jpeg':
  		case 'image/pjpeg':
  			$CreatedImage = imagecreatefromjpeg($_FILES['ImageFile']['tmp_name']);
  			break;
  		default:
  			die('Unsupported File!'); //output error and exit
  	}
    
    var_dump($CreatedImage);
  }
}
