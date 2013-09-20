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
  
  
  
  
  
  
  public function recentRegUsersTrend()
  {
    $sql =
      "SELECT DATE_FORMAT(FROM_UNIXTIME(regdate),'%Y-%m-%d') as `regd`,
      count(`uid`) as `c`
      FROM `cocoabbs_uc_members` 
      WHERE `regdate`>unix_timestamp(SUBDATE(now(), INTERVAL 20 DAY))
      GROUP BY `regd`";
    $ret = $this->fetchArray($sql);
    if(count($ret)==0)
      return "";
    $indexs = array();
    $days = array();
    $counts = array();
    $i = 0;
    foreach($ret as $record) {
      $indexs[] = $i;
      $days[] = $record["regd"];
      $counts[] = $record["c"];
      $i++;
    }
    $retArray["data"] =  join(",",$counts);
    return $retArray; 
  }
  
  public function recentThreadTrend()
  {
    $sql =
      "SELECT DATE_FORMAT(FROM_UNIXTIME(createdate),'%Y-%m-%d') as `regd`,
      count(`id`) as `c`
      FROM `threads` 
      WHERE `createdate`>unix_timestamp(SUBDATE(now(), INTERVAL 20 DAY))
      GROUP BY `regd`";
    $ret = $this->fetchArray($sql);
    if(count($ret)==0)
      return "";
    $indexs = array();
    $days = array();
    $counts = array();
    $i = 0;
    foreach($ret as $record) {
      $indexs[] = $i;
      $days[] = $record["regd"];
      $counts[] = $record["c"];
      $i++;
    }
    $retArray["data"] =  join(",",$counts);
    return $retArray; 
  }
  
  public function recentReplysTrend()
  {
    $sql =
      "      SELECT DATE_FORMAT(FROM_UNIXTIME(createdate),'%Y-%m-%d') as `regd`,
      count(`id`) as `c`
      FROM `thread_replys` 
      WHERE `createdate`>unix_timestamp(SUBDATE(now(), INTERVAL 20 DAY))
      GROUP BY `regd`";
    $ret = $this->fetchArray($sql);
    if(count($ret)==0)
      return "";
    $indexs = array();
    $days = array();
    $counts = array();
    $i = 0;
    foreach($ret as $record) {
      $indexs[] = $i;
      $days[] = $record["regd"];
      $counts[] = $record["c"];
      $i++;
    }
    $retArray["data"] =  join(",",$counts);
    return $retArray; 
  }
}