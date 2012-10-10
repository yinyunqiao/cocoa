<?php
require_once  dirname(dirname(dirname(__FILE__))) . '/lib/gapc/src/Google_Client.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/gapc/src/contrib/Google_AnalyticsService.php';

class TongjiModel extends baseDbModel {
  
  private $callbackurl;
  private $client;
  private $service;
  
  public function __construct() {
    
    parent::__construct();
    $this->callbackurl = 'http://tiny4cocoa.com/homeadmin/settongji/';
    $client = new Google_Client();
    $client->setClientId('70232315343-0nikjc44hcpfk5qt93pe0e21sc2u3ntm.apps.googleusercontent.com');
    $client->setClientSecret('8I4c4toq6hYE6i3BhHhjRrIc');
    $client->setRedirectUri($this->callbackurl);
    $client->setDeveloperKey('AIzaSyBE9EKeqtgJntWuNbDekaPSNvu9ZalXFpE');
    $client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));
    $client->setUseObjects(true);
    $service = new Google_AnalyticsService($client);
    $this->client = $client;
    $this->service = $service;
  }
  public function check($code) {
    
    $client = $this->client;
    $service = $this->service;
    
    $k = "googletoken";
    $ret = $this->kv_get($k);
    if($ret)
      $token = $ret["v"];
    
    if (isset($_GET['logout'])) {
      $this->kv_clear($k);
      $token = NULL;
    }

    if (isset($code)) {
      $client->authenticate();
      $token = $client->getAccessToken();
      $this->kv_set($k,$token);
      header('Location: /homeadmin/settongji' );
    }
    
    if ($token) {
      try {
        $client->setAccessToken($token);
      }catch (Exception $e) {
        $this->kv_clear($k);
      }
    }
    
    if ($client->getAccessToken()) {
      $token = $client->getAccessToken();
      $this->kv_set($k,$token);
      echo "成功绑定";
      
    } else {
      $authUrl = $client->createAuthUrl();
      print "<a class='login' href='$authUrl'>Connect Me!</a>";
    }
  }
  
  public function data($range) {

    $key = "pageviews_".$range;
    $ret = $this->kv_get($key);
    if($ret) {
      $time = $ret["updatetime"];
      $data = unserialize($ret["v"]);
    }
    if(!$data || time() - $time>5*60) {
      
      $this->kv_clear($key);
      $data = $this->rawdata($range);
      $this->kv_set($key,serialize($data));
    }
    return $data;
  }
  
  public function hotnews($size) {
    
    $data7day = $this->data("7day");
    arsort($data7day);
    $ids = array_keys($data7day);
    $idsStr = join(",",$ids);
    $sql = "SELECT `id`,`title` FROM `cocoacms_news` WHERE `id` in ($idsStr);";
    $ret = $this->select("cocoacms_news")->fields("`id`,`title`")->where("`id` in ($idsStr)")->fetchAll();
    $hotnews = array();
    $news = array();
    foreach($ret as $line) {
        
      $id = $line["id"];
      $news[$id]= $line["title"];
    }
    foreach($ids as $id) {
      if($news[$id]) {
        
        $line["id"] = $id;
        $line["title"] = $news[$id];
        $hotnews[] = $line;
      }
    }
    return $hotnews;
  }
  
  public function rawdata($range) {
    
    $client = $this->client;
    $service = $this->service;

    $k = "googletoken";
    $ret = $this->kv_get($k);
    if($ret)
      $token = $ret["v"];

    if(!isset($token))
      return NULL;
    
    if ($token) {
      try {
        $client->setAccessToken($token);
      }catch (Exception $e) {      
        $this->kv_clear($k);
        return NULL;
      }
    }
    
    if ($client->getAccessToken()) {
      
      $token = $client->getAccessToken();
      $this->kv_set($k,$token);
      
      if($range == "all")
        $bdate = "2012-01-01";
      else if($range == "7day")
        $bdate = date("Y-m-d",time()-60*60*24*7);
      else
        $bdate = "2012-01-01";
      $date = date("Y-m-d",time()+60*60*24*2);
  	  $results = $service->data_ga->get(
          'ga:' . "39124819",
          $bdate,
          $date,
          'ga:pageviews',
          array(
              'dimensions' => 'ga:pagePathLevel3',
              'filters' => 'ga:pagePath=~^/home/s/*',
              'max-results' => '10000'));
      
      $data = array();
      if(count($results->rows)==0)
        return $data;
      foreach($results->rows as $line) {
        
        $index = str_replace("/","",$line[0]);
        if($index>0) {
          
          $data[$index] = $data[$index] + $line[1];
        }
      }
      return $data;
    } else {
      
      return NULL;
    }
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