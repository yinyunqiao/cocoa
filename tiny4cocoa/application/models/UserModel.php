<?php
class UserModel extends baseDbModel {
  
  public function username($userid) {
    
    $sql = "SELECT username FROM `cocoabbs_members` WHERE uid = $userid;";
    $result =  $this->fetchArray($sql);
    return $result[0]["username"];
  }
  
}