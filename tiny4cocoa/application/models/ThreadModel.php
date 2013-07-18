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
    ORDER BY `createdate` DESC 
    limit $start,$pageSize;";
    $result =  $this->fetchArray($sql);
    $ret = array();
    if(count($result)==0)
    return $ret;
    foreach($result as $item) {
      $item["createtime"] = ToolModel::countTime($item["createdate"]);
      $ret[] = $item;
    } 
    return $ret;
  }

  public function threadById($id) {
    
    $sql = "SELECT * FROM `threads` where id = $id;";
    $ret = $this->fetchArray($sql);
    $thread = $ret[0];
    $thread["content"] = Markdown($thread["content"]);
    $thread["createtime"] = ToolModel::countTime($thread["createdate"]);
    $thread["updatetime"] = ToolModel::countTime($thread["updatedate"]);
    return $thread;
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
      $item["createtime"] = ToolModel::countTime($item["createdate"]);
      $item["updatetime"] = ToolModel::countTime($item["updatedate"]);
      $ret[] = $item;
    } 
    return $ret;
  }
  
  
  public function newThread($data) {
    
    return $this->select("threads")->insert($data);
  }
}