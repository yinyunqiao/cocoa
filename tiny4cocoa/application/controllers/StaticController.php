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
    ob_start();
    $offset = 60 * 60 * 24;
    $expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
    header ($expire);
    $path=$this->_pathinfo['base'].'/public/js';
    $base = $this->_pathinfo['base'];
    $path = $base.'/public/js';
    $bootstrap = $this->_pathinfo['base']."/public/bootstrap/js/";
    $framework = $this->_pathinfo['tinyAppLib'];
    //include("$path/jquery/jquery.min.js");
    include("$framework/jquery/jquery-1.10.2.min.js");
    include("$path/jquery/jquery.validate.min.js");
    include("$path/jquery/jquery.form.js");
    include("$path/upload/upload.js");
    include("$bootstrap/bootstrap.min.js");
    include("$framework/jquery.hotkeys/jquery.hotkeys.js");
    include("$framework/flot/jquery.flot.js");
    include("$framework/flot/jquery.flot.categories.js");
    include("$framework/Jcrop/js/jquery.Jcrop.min.js");
    include("$framework/jsvote/src/jquery.jsvote.js");
    include("$path/base.js");
  }
	
  public function wmdjsAction() {
		
    header ("content-type: application/x-javascript; charset: utf-8");
    header('Pragma: ');
    header ("cache-control: max-age=600");
    ob_start();
    $offset = 60 * 60 * 24;
    $expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
    header ($expire);
    $framework = $this->_pathinfo['tinyAppLib'];
    readfile("$framework/wmd/wmd.js");
    readfile("$framework/wmd/showdown.js");
  }
  
  public function cssAction() {
		
    header ("content-type: text/css; charset: utf-8");
    header('Pragma: ');
    header ("cache-control: max-age=600");
    $offset = 60 * 60 * 24;
    $expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
    header ($expire);
    $base = $this->_pathinfo['base'];
    $path = $base.'/public/css';
    $bootstrap = $this->_pathinfo['base']."/public/bootstrap/css";
    $framework = $this->_pathinfo['tinyAppLib'];
    
    include("$path/base.css");
    include("$path/thread.css");
    include("$framework/wmd/wmd.css");
    include("$bootstrap/bootstrap.min.css");
    include("$bootstrap/bootstrap-responsive.min.css");
    include("$framework/Jcrop/css/jquery.Jcrop.min.css");
    include("$framework/jsvote/src/jsvote.css");
    include("$path/site.css");
  }
}

