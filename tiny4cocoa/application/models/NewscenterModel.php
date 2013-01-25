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
  
  public function appleNews24() {
    
    $time = time();
    $oneday = 86400;
    $sql = "SELECT `newscenter_items`.*,
      `newscenter_sources`.`name`,
      `newscenter_sources`.`url`
       FROM `newscenter_items`
       LEFT JOIN `newscenter_sources`
       ON `newscenter_items`.`sid` = `newscenter_sources`.`id`
       WHERE `apple` = 1  AND $time <`pubdate`+$oneday ORDER BY `pubdate` DESC";
    $news = $this->fetchArray($sql);
    return $news;
  }
  
  public function appleNewsFromDay($day) {
    
    $sql = "SELECT `newscenter_items`.*,
      `newscenter_sources`.`name`,
      `newscenter_sources`.`url`
      FROM `newscenter_items`
      LEFT JOIN `newscenter_sources`
      ON `newscenter_items`.`sid` = `newscenter_sources`.`id`
      WHERE `apple` = 1 
      AND 
      DATE_FORMAT(FROM_UNIXTIME(`pubdate`),'%Y-%m-%d') = \"$day\"
      ORDER BY `pubdate` DESC";
    $news = $this->fetchArray($sql);
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
  
  public function removeTail($content){
    
    $tails = array();
    $tails[]= "<div id='jiathis_style_24x24'>"; //雷锋网
    $tails[]= "<a href=\"http://www.ifanr.com\">爱范儿 · Beats"; //爱范儿
    $tails[]= "<img width='1' height='1' src='http://tech2ipo.feedsportal.com/"; //Tech2iPO
    $tails[] = "<p class=\"sourcelink\">（若无特别注明"; //雷锋网引用
    
    foreach($tails as $tail) {
      $pos = strpos($content,$tail);
      if($pos!=0)
        $content = substr($content,0,$pos);
    }
    return $content;
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
  
  public function markApple($id,$apple,$mark=1){
    
    $sql = "UPDATE `newscenter_items` set `checked`=1,`apple`=$apple WHERE `id` = $id; ";
    $this->run($sql);
    if($mark!=1)
      return;
    $host = "http://74.207.248.39:9090";
    ToolModel::getUrl(
      "$host/api/amend/?id=$id&isTarget=$apple");
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
  
  public function newsById($id) {
    
    $sql = "SELECT *
       FROM `newscenter_items`
       WHERE `id` = $id;";
    $news = $this->fetchArray($sql);
    $news[0]["elink"] = urlencode($news[0]["link"]);
    return $news[0];
  }
  
  public function makeHomeCluster($applenews) {
    
    $ret = $this->kv_get("homecluster");
    $homecluster = unserialize($ret["v"]);
    $homecluster = json_decode($homecluster,true);
    if(count($applenews)==0)
      return $applenews;
    $napplenews = array();
    $supportnewsids = array();
    foreach($applenews as $news) {
      
      $id = $news["id"];
      if(count($homecluster[$id])>0){
        
        $snews = array();
        foreach($homecluster[$id] as $sid) {
          
          $supportnewsids[] = $sid;
          $snews[] = $this->newsById($sid);
        }
        $news["snews"] = $snews;
      }
      if(!in_array($id,$supportnewsids))
        $napplenews[] = $news;
    }
    return $napplenews;
  }
  
  public function kv_get($key) {
    
    $ret = $this->select("cocoacoms_kv")->fields("v,updatetime")->where("k = '$key'")->fetchOne();
    return $ret;
  } 
  
  public function kv_set($key,$value) {
    
    $this->kv_clear($key);
    $value = mysql_real_escape_string($value);
    $time = time();
    $sql = "INSERT INTO `cocoacoms_kv` (`k`,`v`,`updatetime`) 
      VALUES ('$key','$value','$time');";
    $this->run($sql);
  }
  
  public function kv_clear($key) {
    
    $sql = "DELETE FROM `cocoacoms_kv` WHERE k = '$key';";
    $this->run($sql);
  }
}