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
  
  public function recentRegUsersTrendAll($day) {
    $sql =
      "SELECT DATE_FORMAT(FROM_UNIXTIME(regdate),'%Y-%m-%d') as `regd`,
      count(`uid`) as `c`
      FROM `cocoabbs_uc_members` 
      WHERE `regdate`>unix_timestamp(SUBDATE(now(), INTERVAL $day DAY))
      GROUP BY `regd`";
    $ret = $this->fetchArray($sql);
    if(count($ret)==0)
      return "";
    $indexs = array();
    $days = array();
    $counts = array();
    $i = 0;
    
    $jsdata = array();
    foreach($ret as $record) {
      $indexs[] = $i;
      $days[] = $record["regd"];
      $counts[] = $record["c"];
      $jsdata[] = '["' . substr($record["regd"],-2) . '",' . $record["c"] . ']';
      $i++;
    }
    $retArray["data"] =  join(",",$counts);
    $retArray["jsdata"] =  join(",",$jsdata);
    return $retArray; 
  }
  
  public function recentRegUsersTrend($day) {
    $sql =
      "SELECT DATE_FORMAT(FROM_UNIXTIME(regdate),'%Y-%m-%d') as `regd`,
      count(`uid`) as `c`
      FROM `cocoabbs_uc_members` 
      WHERE `validated` = 1 AND `regdate`>unix_timestamp(SUBDATE(now(), INTERVAL $day DAY))
      GROUP BY `regd`";
    $ret = $this->fetchArray($sql);
    if(count($ret)==0)
      return "";
    $indexs = array();
    $days = array();
    $counts = array();
    $i = 0;
    
    $jsdata = array();
    foreach($ret as $record) {
      $indexs[] = $i;
      $days[] = $record["regd"];
      $counts[] = $record["c"];
      $jsdata[] = '["' . substr($record["regd"],-2) . '",' . $record["c"] . ']';
      $i++;
    }
    $retArray["data"] =  join(",",$counts);
    $retArray["jsdata"] =  join(",",$jsdata);
    return $retArray; 
  }
  
  public function recentThreadTrend($day) {
    
    $sql =
      "SELECT DATE_FORMAT(FROM_UNIXTIME(createdate),'%Y-%m-%d') as `regd`,
      count(`id`) as `c`
      FROM `threads` 
      WHERE `createdate`>unix_timestamp(SUBDATE(now(), INTERVAL $day DAY))
      GROUP BY `regd`";
    $ret = $this->fetchArray($sql);
    if(count($ret)==0)
      return "";
    $indexs = array();
    $days = array();
    $counts = array();
    $jsdata = array();
    $i = 0;
    foreach($ret as $record) {
      $indexs[] = $i;
      $days[] = $record["regd"];
      $counts[] = $record["c"];
      $jsdata[] = '["' . substr($record["regd"],-2) . '",' . $record["c"] . ']';
      $i++;
    }
    $retArray["data"] =  join(",",$counts);
    $retArray["jsdata"] =  join(",",$jsdata);
    return $retArray; 
  }
  
  
  public function threadTimerTrend() {
    
    $sql =
      "SELECT DATE_FORMAT(FROM_UNIXTIME(createdate),'%H') as `hour`,
      count(`id`) as `c`
      FROM `threads` 
      GROUP BY `hour`";
    $ret = $this->fetchArray($sql);
    if(count($ret)==0)
      return "";
    $indexs = array();
    $days = array();
    $counts = array();
    $jsdata = array();
    $i = 0;
    foreach($ret as $record) {
      $indexs[] = $i;
      $days[] = $record["regd"];
      $counts[] = $record["c"];
      $jsdata[] = '["' . $record["hour"] . '点",' . $record["c"] . ']';
      $i++;
    }
    $retArray["data"] =  join(",",$counts);
    $retArray["jsdata"] =  join(",",$jsdata);
    return $retArray; 
  }
  public function recentReplysTrend($day)  {
    $sql =
      "      SELECT DATE_FORMAT(FROM_UNIXTIME(createdate),'%Y-%m-%d') as `regd`,
      count(`id`) as `c`
      FROM `thread_replys` 
      WHERE `createdate`>unix_timestamp(SUBDATE(now(), INTERVAL $day DAY))
      GROUP BY `regd`";
    $ret = $this->fetchArray($sql);
    if(count($ret)==0)
      return "";
    $indexs = array();
    $days = array();
    $counts = array();
    $jsdata = array();
    $i = 0;
    foreach($ret as $record) {
      $indexs[] = $i;
      $days[] = $record["regd"];
      $counts[] = $record["c"];
      $jsdata[] = '["' . substr($record["regd"],-2) . '",' . $record["c"] . ']';
      $i++;
    }
    $retArray["data"] =  join(",",$counts);
    $retArray["jsdata"] =  join(",",$jsdata);
    return $retArray; 
  }
  public function replysTimerTrend() {
    
    $sql =
      "SELECT DATE_FORMAT(FROM_UNIXTIME(createdate),'%H') as `hour`,
      count(`id`) as `c`
      FROM `thread_replys` 
      GROUP BY `hour`";
    $ret = $this->fetchArray($sql);
    if(count($ret)==0)
      return "";
    $indexs = array();
    $days = array();
    $counts = array();
    $jsdata = array();
    $i = 0;
    foreach($ret as $record) {
      $indexs[] = $i;
      $days[] = $record["regd"];
      $counts[] = $record["c"];
      $jsdata[] = '["' . $record["hour"] . '点",' . $record["c"] . ']';
      $i++;
    }
    $retArray["data"] =  join(",",$counts);
    $retArray["jsdata"] =  join(",",$jsdata);
    return $retArray; 
  }
  public function stats() {
    
    $data = array();
    
    $sql = "SELECT count(*) as c FROM `cocoabbs_uc_members` WHERE `validated` = 1;";
    $ret = $this->fetchArray($sql);
    $data["users"] = $ret[0]["c"];
    
    $sql = "SELECT count(*) as c FROM `threads`;";
    $ret = $this->fetchArray($sql);
    $data["threads"] = $ret[0]["c"];
    
    $sql = "SELECT count(*) as c FROM `thread_replys`;";
    $ret = $this->fetchArray($sql);
    $data["replys"] = $ret[0]["c"];
    return $data;
  }
}