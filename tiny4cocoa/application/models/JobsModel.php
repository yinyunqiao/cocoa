<?php
class JobsModel extends baseDbModel {
  
  function update() {
    
    $source["from"] = "拉勾网";
    $source["url"] = "http://www.lagou.com/joinNov/ex_list_ios";
    $sources[] = $source;
    foreach($sources as $source) {
      
       $content = ToolModel::getUrl($source["url"]);
       $data = json_decode($content,true);
       foreach($data["result"] as $job) {
         
         $job = $this->lagou2job($job);
         $this->select("jobs")->insert($job);
       }
    }
  }
  
  function newJobs() {
    
    $sql = "SELECT `id`,`department`,`salary`,`position`,`city` 
        FROM `jobs`
        WHERE `ban` == 0 
        ORDER BY `id` DESC
        LIMIT 0,10;";
    $jobs = $this->fetchArray($sql);
    return $jobs;
  }
  
  function jobById($id) {
    
    $sql = "SELECT * FROM `jobs` WHERE `id` = $id";
    $result = $this->fetchArray($sql);
    return $result[0];
  }
  
  function lagou2job($lagou) {
    
    $job = $lagou;
    $job["createdate"] = time();
    $job["fromid"] = $job["id"];
    $job["from"] = "拉手网";
    if($job["position"]=="iOS")
      $job["position"] = "iOS开发工程师";
    unset($job["id"]);
    return $job;
  }
}