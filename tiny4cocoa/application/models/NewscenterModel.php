<?php
class NewscenterModel extends baseDbModel {
  
  public function news($page,$size,$filter) {
    
    if($page<1)
      $page = 1;
    $start = ($page-1)*$size;
    
    if($filter=="marked")
      $where = " WHERE `checked` = 1 ";
    elseif($filter=="unmarked")
      $where = " WHERE `checked` = 0 ";
    elseif($filter=="apple")
      $where = " WHERE `apple` = 1 ";
    
    $sql = "SELECT `newscenter_items`.*,
      `newscenter_sources`.`name`,
      `newscenter_sources`.`url`
       FROM `newscenter_items`
       LEFT JOIN `newscenter_sources`
       ON `newscenter_items`.`sid` = `newscenter_sources`.`id`
       $where ORDER BY `pubdate` DESC limit $start,$size;";
    $news = $this->fetchArray($sql);
    $news = $this->makeDateFlag($news);
    return $news;
  }
  
  private function makeDateFlag($news){
    
    if(count($news)==0)
      return $news;
    $nnews = array();
    $today = date("Y年m月d日");
    
    foreach($news as $n) {
      
      if($nowdate!=date("Y年m月d日",$n["pubdate"])){
        
        $nowdate = date("Y年m月d日",$n["pubdate"]);
        if($nowdate == $today)
          $n["dateflag"] = "今天";
        else
          $n["dateflag"] = $nowdate;
      }
      $nnews[] = $n;
    }
    $news = $nnews;
    return $news;
  }
  public function data($id) {
    
    $sql = "SELECT * FROM `newscenter_items` WHERE `id` = $id;";
    $ret = $this->fetchArray($sql);
    return $ret[0];
  }
  
  public function newsids(){
    
    $sql = "SELECT `id` FROM `newscenter_items` WHERE `checked` = 1;";
    $ret = $this->fetchArray($sql);
    $ids = array();
    if(count($ret)>0)
      foreach($ret as $line){
        $ids[] = $line["id"];
      }
    return $ids;
  }
  
  public function appleNewsIdsDays($day){
    
    $now = time();
    $timediff = $now- 60*60*24*$day;
    $sql = "SELECT `id` FROM `newscenter_items` WHERE `checked` = 1 AND `apple` =1 AND `pubdate`> $timediff ;";
    var_dump($sql);
    $ret = $this->fetchArray($sql);
    $ids = array();
    if(count($ret)>0)
      foreach($ret as $line){
        $ids[] = $line["id"];
      }
    return $ids;
  }
  
  public function uncheckedIds(){
    
    $sql = "SELECT `id` FROM `newscenter_items` WHERE `checked` = 0;";
    $ret = $this->fetchArray($sql);
    $ids = array();
    if(count($ret)>0)
      foreach($ret as $line){
        $ids[] = $line["id"];
      }
    return $ids;
  }
  
  
  public function count($filter) {
    
    if($filter=="marked")
      $where = " WHERE checked = 1 ";
    elseif($filter=="unmarked")
      $where = " WHERE checked = 0 ";
    elseif($filter=="apple")
      $where = " WHERE apple = 1 ";
    
    $sql = "SELECT count(*) as c FROM `newscenter_items` $where;";
    $ret = $this->fetchArray($sql);
    return $ret[0]["c"];
  }
  
  public function checked($ids) {
    
    $idStr = join(",",$ids);
    $sql = "UPDATE `newscenter_items` set `checked`=1 WHERE `id` in ($idStr); ";
    $this->run($sql);
  }
  
  public function markApple($id,$apple){
    
    $sql = "UPDATE `newscenter_items` set `checked`=1,`apple`=$apple WHERE `id` = $id; ";
    $this->run($sql);
  }
  public function update() {
    
    $sql = "SELECT `id`,`rss`,`url`,`name` FROM `newscenter_sources`;";
    $items = $this->fetchArray($sql);
    
    foreach($items as $site) {
      
      echo "<br/>".$site["name"];
      $result = fetch_rss($site["rss"]);
  		foreach($result->items as $rss) {
        $data = array();
        $data["title"] = $rss["title"];
        $data["link"] = $rss["link"];
        
        $data["content"] = $rss["atom_content"];
        if($data["content"] == "")
          $data["content"] = $rss["content"]["encoded"];
        if($data["content"] == "")
          $data["content"] = $rss["description"];
        $imgRegx="/<img[^<>]*?src=\"(.*?)\"/";
        if(preg_match($imgRegx,$data["content"],$match)) {
          
          $data["image"] = $match["1"];
        }
        $data["content"] = mysql_escape_string($data["content"]);
        
        $data["author"] = $rss["dc"]["creator"];
        if($data["author"]=="")
          $data["author"] = $rss["author"];
        if($data["author"]=="")
          $data["author"] = $site["name"];
        
  			$pubdate = $rss["pubdate"];
  			if($pubdate == "")
  				$pubdate = $rss["updated"];
  			$data["pubdate"] = strtotime($pubdate);
        $data["sid"] = $site["id"];
        $data["urlmd5"] = md5($data["link"]);
        $this->select("newscenter_items")->insert($data);
      }
    }
  }
}