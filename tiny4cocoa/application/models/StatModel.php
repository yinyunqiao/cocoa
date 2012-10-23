<?php

class StatModel extends baseDbModel {
  
  
  public function __construct() {
    
    parent::__construct();
  }
  
  public function days() {
    
    $sql = "SELECT `datename` FROM `stat_footprint` GROUP BY `datename` ORDER BY `datename` DESC;";
    $ret = $this->fetchArray($sql);
    return $ret;
  }
  
  public function data($index) {
    
    $sql = "SELECT * FROM `stat_footprint` WHERE `datename`='$index';";
    $ret = $this->fetchArray($sql);
    $data = array();
    foreach($ret as $item) {
      
      $type = $item["type"];
      $data[$type] = $item;
    }
    return $data;
  }
}