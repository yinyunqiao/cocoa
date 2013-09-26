<?php
class LogModel extends baseDbModel {
  
	public function __construct() {
    
  }
  
  public function slowRequest($time) {
    
    $data = array();
    $data["url"] = $_SERVER["REQUEST_URI"];
    $data["time"] = $time;
    $data["userip"] = ToolModel::getRealIpAddr();
    $data["date"] = time();
    $this->select("slowrequest")->insert($data);
  }
}