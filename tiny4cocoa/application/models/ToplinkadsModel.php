<?php
class ToplinkadsModel extends baseDbModel {

  public function links() {

    $result = 
      $this ->select("topadlink")
            ->orderby("expiretime")
            ->fetchAll();
    return $result;
  }
  
  public function toplink() {
    
    $time = time();
    $sql = "
      SELECT * FROM `topadlink` 
      WHERE (`expiretime` > $time  OR `expiretime`=-1 ) AND `valid` = 1
      ORDER BY rand();";
    
    $result = $this->fetchArray($sql);
    if(count($result)==0)
      return NULL;
    else
      return $result[0];
  }
  
  public function add($data) {
    
    if($data["expire"]==-1)
      $data["expiretime"] = -1;
    else
      $data["expiretime"] = time() + $data["expire"]*60*60;
    unset($data["expire"]);
    $this->select("topadlink")->insert($data);
  }
}






