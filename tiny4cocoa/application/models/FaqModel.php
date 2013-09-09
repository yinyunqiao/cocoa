<?php
class FaqModel extends baseDbModel {
  
  public function threadCount() {
    
    $sql = 
    "SELECT count(*) as `c` FROM `cocoabbs_threads`;";
    $result =  $this->fetchArray($sql);
    return $result[0]["c"];
  }
  
  
  public function threads($page,$pageSize) {
    
    $start = ($page-1)*$pageSize;
    $sql = 
    "SELECT * FROM `cocoabbs_threads`
    ORDER BY `lastpost` DESC 
    limit $start,$pageSize;";
    $result =  $this->fetchArray($sql);
    $ret = array();
    if(count($result)==0)
      return $ret;
    foreach($result as $item) {
      
      $item["id"] = $item["tid"];
      $item["createtime"] = ToolModel::countTime($item["dateline"]);
      $item["updatetime"] = ToolModel::countTime($item["lastpost"]);
      $item["image"] = DiscuzModel::get_avatar($item["authorid"],"small");
      $item["title"] = stripslashes($item["subject"]);
      $item["createby"] = $item["author"];
      $item["createbyid"] = $item["authorid"];
      $item["lastreply"] = $item["lastposter"];
      $item["replys"] = $item["replies"];
      $ret[] = $item;
    } 
    return $ret;
  }


  public function parseContent($content) {
    
    $html = DiscuzModel::discuzcode(stripslashes($content));
    return $html;
  }

  public function threadById($id) {
    
    $sql = "SELECT * FROM `cocoabbs_posts` where `tid` = $id AND `first` = 1;";
    $ret = $this->fetchArray($sql);
    $thread = $ret[0];
    $thread["content"] = $this->parseContent($thread["message"]);
    $thread["title"] = stripslashes($thread["subject"]);
    $thread["createtime"] = ToolModel::countTime($thread["dateline"]);
    $thread["image"] = DiscuzModel::get_avatar($thread["authorid"],"small");
    $thread["createby"] = $thread["author"];
    $thread["createbyid"] = $thread["authorid"];
    return $thread;
  }
    
  public function replysCountById($id) {
    
    $sql = 
    "SELECT count(*) as `c` FROM `cocoabbs_posts` where `tid` = $id AND `first` !=1; ";
    $result =  $this->fetchArray($sql);
    return $result[0]["c"];
  }
  
  public function replysById($id) {
    
    $sql = "SELECT * FROM `cocoabbs_posts` where `tid` = $id AND `first` !=1 ORDER BY `dateline`;";
    $result = $this->fetchArray($sql);
    $ret = array();
    if(count($result)==0)
      return $ret;
    foreach($result as $item) {
      
      $item["content"] = $this->parseContent($item["message"]);
      $item["createtime"] = ToolModel::countTime($item["dateline"]);
      $item["image"] = DiscuzModel::get_avatar($item["authorid"],"small");
      $item["name"] = $item["author"];
      $item["userid"] = $item["authorid"];
      
      $ret[] = $item;
    } 
    return $ret;
  }
  

}






