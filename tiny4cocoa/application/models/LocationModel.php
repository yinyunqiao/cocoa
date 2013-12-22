<?php
class LocationModel {
  
	public function __construct() {
    
  }
  
  public static function city($ip) {
    
    $url = "http://ip.taobao.com/service/getIpInfo.php?ip=$ip";
    $data = ToolModel::getUrl($url);
    $object = json_decode($data);
    return $object->data->city;
  }
  

}