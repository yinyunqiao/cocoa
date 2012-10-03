<?php
class StaticController extends tinyApp_Controller
{
  public function __construct($pathinfo,$controller) {
    
    parent::__construct($pathinfo,$controller);
    $this->_useSession=false;
  }

  public function jsAction() {
		
    header ("content-type: application/x-javascript; charset: utf-8");
    header('Pragma: ');
    header ("cache-control: max-age=600");
    $offset = 60 * 60 * 24;
    $expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
    header ($expire);
    $path=$this->_pathinfo['base'].'/public/js';
		
		
    // include("$path/jquery/jquery-1.6.js");
    // include("$path/jquery/jquery.slides.js");
    // include("$path/jquery/jquery.validate.js");
    // include("$path/jquery_raty/jquery.raty.js");
    // include("$path/jquery/jquery.tipsy.js");
    // include("$path/jquery/jquery.masonry.js");
    // include("$path/jquery/jquery.masonry.stamp.js");
    // 
    // include("$path/bootstrap/bootstrap-alerts.js");
    // include("$path/bootstrap/bootstrap-dropdown.js");
    // include("$path/bootstrap/bootstrap-modal.js");
    // include("$path/bootstrap/bootstrap-scrollspy.js");
    // include("$path/bootstrap/bootstrap-tabs.js");
    // include("$path/bootstrap/bootstrap-twipsy.js");
    // include("$path/bootstrap/bootstrap-popover.js");
    // 
    // include("$path/tiny_suggest.js");
    // include("$path/guest.js");
    // include("$path/iappvote.js");
    // include("$path/load_more.js");
    // include("$path/iapp/iappbase.js");
    // include("$path/iapp/loadlistreply.js");
    // include("$path/main.js");
  }
	
  public function cssAction() {
		
    header ("content-type: text/css; charset: utf-8");
    header('Pragma: ');
    header ("cache-control: max-age=600");
    $offset = 60 * 60 * 24;
    $expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
    header ($expire);
    $path=$this->_pathinfo['base'].'/public/css';
    $home = "$path/home";
    include("$home/home.css");
  }
}

