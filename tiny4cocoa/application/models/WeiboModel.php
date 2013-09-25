<?php
class WeiboModel extends baseDbModel {
  
  public function token($userid) {
    
    $sql = "SELECT `token` FROM `weibotoken` WHERE id = $userid;";
    $result = $this->fetchArray($sql);
    return $result[0]["token"];
  }

  public function setToken($token) {
    
    
  }
}



