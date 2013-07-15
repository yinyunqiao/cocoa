<?php
class OsModel extends baseDbModel {
  
  public function libs() {
    
    $sql = 
    "SELECT * FROM `cocoaos_main` order by `createdate` limit 0,10;";
    $result =  $this->fetchArray($sql);
    return $result;
  }
  
  public function lib($id) {
    
    $sql = 
    "SELECT * FROM `cocoaos_main` where id = $id;";
    $result =  $this->fetchArray($sql);
    return $result[0];
  }
  
}