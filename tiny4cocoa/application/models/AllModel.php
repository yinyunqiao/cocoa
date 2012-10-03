<?php
class AllModel extends baseDbModel {
  
  public function allThreads($page,$pageSize) {
    
    $start = ($page-1)*$pageSize;
    $sql = "SELECT * FROM `cocoabbs_threads` ORDER BY `lastpost` DESC limit $start,$pageSize;";
    return $this->fetchArray($sql);
  }
  
  public function newThreads($page,$pageSize) {
    
    $start = ($page-1)*$pageSize;
    $sql = "SELECT * FROM `cocoabbs_threads` ORDER BY `dateline` DESC limit $start,$pageSize;";
    return $this->fetchArray($sql);
  }
  
}