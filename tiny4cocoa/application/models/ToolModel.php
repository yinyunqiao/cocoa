<?php
class ToolModel {
  
	public function __construct() {
    
  }
  
  
  public static function getRealIpAddr()
  {
      if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
      {
        $ip=$_SERVER['HTTP_CLIENT_IP'];
      }
      elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
      {
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
      }
      else
      {
        $ip=$_SERVER['REMOTE_ADDR'];
      }
      return $ip;
  }
  
  public static function toHtml($content) {
    
    $content = stripslashes($content);
    $content = str_replace("\r\n","<br/>",$content);
    $content = str_replace("\n","<br/>",$content);
    $content = str_replace("\r","<br/>",$content);
    return $content;
  }
  
  
}