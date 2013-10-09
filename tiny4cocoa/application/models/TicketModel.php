<?php
class TicketModel extends baseDbModel {
  
  
  public function newTicket($userid) {
    
    $data["createtime"] = time();
    $data["ticket"] = $data["createtime"] . rand(100000,999999);
    $data["userid"] = $userid;
    $this->select("tickets")->insert($data);
    return $data;
  }
  
  public function isTicketExistedAndVaild($ticket) {
    
    $sql = "SELECT * FROM `tickets` WHERE ticket = '$ticket';";
    $result = $this->fetchArray($sql);
    if(!$result)
      return NULL;
    $data = $result[0];
    $time = time();
    if(($time-$data["createtime"])>60*60*1) {
    
      $this->removeTicket($ticket);
      return NULL;
    }
    return $data;
  }
  
  public function removeTicket($ticket) {
    
    $sql = "DELETE FROM  `tickets` WHERE  ticket = '$ticket'";
    $this->run($sql);
  }
}



