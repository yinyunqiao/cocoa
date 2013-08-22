<?php
class ToplistModel extends baseDbModel {


  public function toplist() {
    $time = time();
    $result = 
      $this ->select("toplinks")
            ->orderby("weight DESC")
            ->where("timelimit = -1 OR  $time <= `createtime`+`timelimit`")
            ->fetchAll();
    return $result;
  }
}






