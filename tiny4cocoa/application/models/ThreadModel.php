<?php
class ThreadModel extends baseDbModel {
  
  public function threadCount() {
    
    $sql = 
    "SELECT count(*) as `c` FROM `threads`;";
    $result =  $this->fetchArray($sql);
    return $result[0]["c"];
  }
  
  public function threads($page,$pageSize) {
    
    $start = ($page-1)*$pageSize;
    $sql = 
    "SELECT * FROM `threads`
    ORDER BY `updatedate` DESC 
    limit $start,$pageSize;";
    $result =  $this->fetchArray($sql);
    $ret = array();
    if(count($result)==0)
      return $ret;
    foreach($result as $item) {
      $item["createtime"] = ToolModel::countTime($item["createdate"]);
      $item["updatetime"] = ToolModel::countTime($item["updatedate"]);
      $item["image"] = DiscuzModel::get_avatar($item["createbyid"],"small");
      $ret[] = $item;
    } 
    return $ret;
  }

  public function threadById($id) {
    
    $sql = "SELECT * FROM `threads` where id = $id;";
    $ret = $this->fetchArray($sql);
    $thread = $ret[0];
    $thread["content"] = Markdown(stripslashes($thread["content"]));
    $thread["createtime"] = ToolModel::countTime($thread["createdate"]);
    $thread["updatetime"] = ToolModel::countTime($thread["updatedate"]);
    $thread["image"] = DiscuzModel::get_avatar($thread["createbyid"],"small");
    return $thread;
  }
  
  public function threadsByUserid($userid) {
    
    $sql = "SELECT * FROM `threads` where `createbyid` = $userid;";
    $result = $this->fetchArray($sql);
    $ret = array();
    if(count($result)==0)
      return $ret;
    foreach($result as $item) {
      $item["createtime"] = ToolModel::countTime($item["createdate"]);
      $item["updatetime"] = ToolModel::countTime($item["updatedate"]);
      $item["image"] = DiscuzModel::get_avatar($item["createbyid"],"small");
      $ret[] = $item;
    } 
    return $ret;
  }
  
  public function threadsReplyByUserid($userid) {
    
    $sql = "SELECT * FROM `threads` where `createbyid` <> $userid AND `id` in (SELECT `threadid` FROM `thread_replys` WHERE `userid` = $userid GROUP BY `threadid`);";
    $result = $this->fetchArray($sql);
    $ret = array();
    if(count($result)==0)
      return $ret;
    foreach($result as $item) {
      $item["createtime"] = ToolModel::countTime($item["createdate"]);
      $item["updatetime"] = ToolModel::countTime($item["updatedate"]);
      $item["image"] = DiscuzModel::get_avatar($item["createbyid"],"small");
      $ret[] = $item;
    } 
    return $ret;
  }
  
  public function replysCountById($id) {
    
    $sql = 
    "SELECT count(*) as `c` FROM `thread_replys` where threadid = $id;";
    $result =  $this->fetchArray($sql);
    return $result[0]["c"];
  }
  
  public function replysById($id) {
    
    $sql = "SELECT * FROM `thread_replys` where threadid = $id ORDER BY `id`;";
    $result = $this->fetchArray($sql);
    $ret = array();
    if(count($result)==0)
      return $ret;
    foreach($result as $item) {
      
      $item["content"] = Markdown(stripslashes($item["content"]));
      $item["createtime"] = ToolModel::countTime($item["createdate"]);
      $item["updatetime"] = ToolModel::countTime($item["updatedate"]);
      $item["image"] = DiscuzModel::get_avatar($item["userid"],"small");
      $ret[] = $item;
    } 
    return $ret;
  }
  
  
  public function newThread($data) {
    
    return $this->select("threads")->insert($data);
  }
  
  public function newReply($data) {
    
    $this->select("thread_replys")->insert($data);
    $sql = "SELECT count(`id`) as c FROM `thread_replys` WHERE threadid = $data[threadid];";
    $result = $this->fetchArray($sql);
    $c = $result[0]["c"];
    return $c;
  }
  
  public function updateThread($data) {
    
    $this->select("threads")->where("id = $data[id]")->update($data);
  }
}