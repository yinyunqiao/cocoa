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
  
  function lagou2job($lagou) {
    
    $job = $lagou;
    $job["createdate"] = time();
    $job["fromid"] = $job["id"];
    $job["from"] = "拉手网";
    unset($job["id"]);
    return $job;
  }
}