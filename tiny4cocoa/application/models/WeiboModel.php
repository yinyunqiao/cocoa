<?php
class WeiboModel extends baseDbModel {
  
  public function token($userid) {
    
    $sql = "SELECT `token` FROM `weibotoken` WHERE id = $userid;";
    $result = $this->fetchArray($sql);
    return $result[0]["token"];
  }

  public function setToken($token) {
    
    $userModel = new UserModel();
    $userid = $userModel->checklogin();
    $data = array();
    $data["id"] =  $userid;
    $data["token"] = $token;
    $sql = "DELETE  FROM `weibotoken` WHERE id = $userid;";
    $this->run($sql);
    $this->select("weibotoken")->insert($data);
  }
}



