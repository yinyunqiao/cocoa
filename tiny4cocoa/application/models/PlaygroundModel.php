<?php

class PlaygroundModel extends baseDbModel {
  
  public function __construct() {
    
    parent::__construct();
  }
  
  public function save($data) {

    $data["createtime"] = time();
    $this->select("playground_apply")->insert($data);
  }
}