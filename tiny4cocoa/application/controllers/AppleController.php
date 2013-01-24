<?php
class AppleController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $this->_view->assign("active","apple");
  }
 
  public function indexAction() {
    
    $allModel = new AllModel();
    $newsModel = new NewsModel();
    $newscenter = new NewscenterModel();
    $discuz = new DiscuzModel();
    
    $this->userid = $discuz->checklogin();
    $threads = $allModel->allThreads(1,10);
    
    $str = $this->strVal(3);
    $date = date_parse($str);
    $endtime = mktime(0,0,0,12,1,2012);
    
    if($date["year"] && $date["month"]&&$date["day"]) {
      
      $applenews = $newscenter->appleNewsFromDay($str);
      $title = "$date[year]年$date[month]月$date[day]日 苹果新闻";
      $time = mktime(0,0,0,$date["month"],$date["day"],$date["year"]);
      if($time<$endtime || $time>time()) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /apple/");
        header("Connection: close");
        die();
      }
      $today = date("Y-m-d",$time);
      $todayName = date("Y年m月d日",$time);
      if($today == date("Y-m-d")){
        
      }
      else {
        $prevDay = date("Y-m-d",$time+60*60*24);
        $prevDayName = date("Y年m月d日",$time+60*60*24);
      }
      if($endtime!=$time){
        
        $nextDay =  date("Y-m-d",$time-60*60*24);
        $nextDayName =  date("Y年m月d日",$time-60*60*24);  
      }
    }
    else {
      if($str!="") {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /apple/");
        header("Connection: close");
        die();
      }
      $news24 = 1;
      $applenews = $newscenter->news(1,10,"apple");
      $applenews = $newscenter->makeHomeCluster($applenews);
      $title = "最新苹果新闻";
      $nextDay =  date("Y-m-d");
      $nextDayName =  date("Y年m月d日");
    }
    $this->setTitle($title);
    $tags = $newsModel->hotTags();
    $count = $newscenter->count("apple");
    $newscount = $newscenter->count("unmarked");
    $spamcount = $newsModel->spamCount();
    $napplenews = array();
    if(count($applenews)>0) {
      foreach($applenews as $item) {
      
        $item["time"] = ToolModel::countTime($item["pubdate"]);
        $item["elink"] = urlencode($item["link"]);
        $item["desc"] = ToolModel::summary($item["content"],300);
        $napplenews[] = $item;
      }
      $applenews = $napplenews;
    }
    
		$pageControl = "<div class=\"pagination\"><ul>";
    if($news24) {
      $pageControl .= "<li class=\"disabled\"><a href=\"javascript:\">最近10条</a></li>";
    }else
    if($prevDay){
      $pageControl .= "<li><a href=\"/apple/index/$prevDay/\">« $prevDayName</a></li>";
    }else {
      $pageControl .= "<li><a href=\"/apple/\">最近10条</a></li>";
    }
    if($today) {
      $pageControl .= "<li class=\"disabled\"><a href=\"/apple/index/$today/\">$todayName</a></li>";
    }
    if($nextDay) {
      $pageControl .= "<li><a href=\"/apple/index/$nextDay/\">$nextDayName »</a></li>";
    }else {
    }
    $pageControl .= '</ul></div>';
    $this->_mainContent->assign("pageControl",$pageControl);
    $news = $newsModel->news(1,10);
    $this->_mainContent->assign("title",$title);
    $this->_mainContent->assign("threads",$threads);
    $this->_mainContent->assign("news",$news);
    $this->_mainContent->assign("applenews",$applenews);
    $this->_mainContent->assign("userid",$this->userid);
    $this->_mainContent->assign("spamcount",$spamcount);
    $this->_mainContent->assign("newscount",$newscount);
    $this->_mainContent->assign("tags",$tags);
    $this->display();
  }
  
  public function goAction() {
    
    $url = $_GET["url"];
    $data = array();
    $data[] = ToolModel::getRealIpAddr(); //ip
    $data[] = date("Y-m-d H:i:s"); //时间
    $data[] = $url;
    $ua = $_SERVER['HTTP_USER_AGENT']; //user agent
    $ua = str_replace(",",";",$ua);
    $data[] = $ua;
    $data[] = $_SERVER['HTTP_REFERER'];
    $line = join(",",$data);
    $fp = fopen('/root/log/cocoa_go.log', 'a');
    fwrite($fp,$line."\r\n");
    fclose($fp);
    header("location:$url");
  }
  
  
  public function homeclusterAction() {
    
    $newscenter = new NewscenterModel();
    $news1 = $newscenter->news(1,10,"apple");
    $news2 = $newscenter->news(1,60,"apple");
    
    $seed = array();
    foreach($news1 as $news) {
      $seed[] = $news["id"];
    }
    $data = array();
    foreach($news2 as $news) {
      $data[] = $news["id"];
    }
    $post["seed"] = join(",",$seed);
    $post["data"] = join(",",$data); 
    $ret = ToolModel::postJSON(
      "http://74.207.248.39:9090/api/cluster/",
       json_encode($post));
    $newscenter->kv_set("homecluster",serialize($ret));
  }

  public function sitemapAction() {
    
    $newsModel = new NewsModel();
    $news = $newsModel->news(1,10000);
    $this->_mainContent->assign("news",$news);
    $this->_layout = "empty";
    $this->display();
  }

  public function rssAction() {
    
    $newsModel = new NewscenterModel();
    $news = $newsModel->news(1,30,"apple");
    $napplenews = array();
    if(count($news)>0) {
      foreach($news as $item) {
      
        $item["time"] = ToolModel::countTime($item["pubdate"]);
        $item["elink"] = urlencode($item["link"]);
        $item["desc"] = ToolModel::summary($item["content"],300);
        $napplenews[] = $item;
      }
      $news = $napplenews;
    }
    $this->_mainContent->assign("allnews",$news);   

    $this->_layout = "empty";
    $this->display();
  }

  public function savecommentAction() {
    
    if(empty($_POST["content"])) {
      header ('HTTP/1.1 301 Moved Permanently');
      header("location: /home/");
    }
    $newsModel = new NewsModel();
    $newsModel->saveComment();
  }
  
  public function logAction() {
    
		putenv("TZ=Asia/Shanghai");
    $data = $_POST["log"];
    $logarray = explode("|",$data);
    $logarray[0] = date("Y-m-d H:i:s");
    $line = join(",",$logarray);
    $line = ToolModel::getRealIpAddr() . "," . $line;
    $fp = fopen('/root/log/footprint-' .date("Y-m-d") . '.log', 'a');
    fwrite($fp,$line."\r\n");
    fclose($fp);
  }
  
  public function updateFeedsAction() {
  	echo "<html>
  			<head>
  				<meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
  			</head>
  			<body>";
    $newsCenter = new NewscenterModel();
    $newsCenter->update();
  }
  
  public function anindexAction() {
    
    
    $newscenter = new NewscenterModel();
    $ids = $newscenter->newsids();
    $idStr = join(",",$ids);
    echo $idStr;
  }
  public function testallAction() {
    
    $newscenter = new NewscenterModel();
    $ids = $newscenter->uncheckedIds();
    $apples = array();
    foreach($ids as $id) {
      $news = $newscenter->data($id);
      $news["channel"] = $news["sid"];
      $ret = ToolModel::post("http://127.0.0.1:37210/isApple",$news);
      $newscenter->markApple($id,$ret,0);
      echo "$id = $ret<br/>";
    }
  }
  
  public function testNewsAction() {
    
    $id = $this->intVal(3);
    $newscenter = new NewscenterModel();
    $news = $newscenter->data($id);
    $news["channel"] = $news["sid"];
    $ret = ToolModel::post("http://127.0.0.1:37210/isApple",$news);
    var_dump($ret);
  }
  public function newsdataAction() {
    
    $id = $this->intVal(3);
    $newscenter = new NewscenterModel();
    $news = $newscenter->data($id);
    $news["content"] = strip_tags($news["content"]);
    //$news["content"] = str_replace("\n","",$news["content"]);
    $news["content"] = str_replace("\\n"," ",$news["content"]);
    $this->_mainContent->assign("news",$news);
    $this->_layout = "empty";
    $this->display();
  }
  
  public function checkSpamAction() {
    
    $newModel = new NewsModel();
    $comments = $newModel->commentToCheck();
    if(count($comments)==0)
      die("no comments");
    $akismet = new Akismet();
    $akismet->key = "5a3c4dc9f909";
    $akismet->blog = "http://tiny4cocoa.org/home/";
    if(!$akismet->verifyKey())
      die("akismet verify error");
    foreach($comments as $comment) {
      
      $data = array('blog' => 'http://tiny4cocoa.org/home/',
                    'user_ip' => $comment["ip"],
                    'user_agent' => $comment["useragent"],
                    'referrer' => $comment["referrer"],
                    'permalink' => "http://tiny4cocoa.org/home/s/$comment[newsid]",
                    'comment_type' => 'comment',
                    'comment_author' => $comment["poster"],
                    'comment_author_email' => '',
                    'comment_author_url' => '',
                    'comment_content' => $comment["content"]);
      //var_dump($data);
      $ret = $akismet->commentCheck($data);
      if($ret) {
        $newModel->markSpam($comment["id"],1);
        echo "comment # $comment[id] is spam!\r\n";
      }
      else {
        $newModel->markSpam($comment["id"],0);
        echo "comment # $comment[id] is not spam.\r\n";
      }
    }
  }

  public function snAction(){
    $id = $this->intVal(3);
    $newscenter = new NewscenterModel();
    $news = $newscenter->data($id);
    // $news["content"] = strip_tags($news["content"]);
    $news["content"] = str_replace("\\n"," ",$news["content"]);
    $news["content"] = str_replace("\\r"," ",$news["content"]);

    $news["content"] = stripslashes($news["content"]);
    $this->_mainContent->assign("content",$news["content"]);
    $this->_layout = "flat";
    $this->display();
  }
}



