<?php
require_once  dirname(dirname(dirname(__FILE__))) . '/lib/recaptcha/recaptchalib.php';
class HomeController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $this->_view->assign("active","home");
  }
  
  public function indexAction() {
    
    $allModel = new AllModel();
    $newsModel = new NewsModel();
    $discuz = new DiscuzModel();
    $this->userid = $discuz->checklogin();
    $page = $this->intVal(3);
    if($page==0)
      $page=1;
    $size = 30;
    
    $newscenter = new NewscenterModel();
    $count = $newscenter->count("apple");
    $newscount = $newscenter->count("unmarked");
    $spamcount = $newsModel->spamCount();
    $applenews = $newscenter->news(1,28,"apple");
    $napplenews = array();
    foreach($applenews as $item) {
      
      $item["time"] = ToolModel::countTime($item["pubdate"]);
      $item["elink"] = urlencode($item["link"]);
      
      $napplenews[] = $item;
    }
    
    $applenews = $napplenews;
    
    $size = 24;
    $count = $newsModel->newsCount();
    $news = $newsModel->news(1,$size);
		$pageControl = ToolModel::pageControl($page,$count,$size,"<a href='/home/news/#page#/'>");
    
    $thread = new ThreadModel();
    $threadCount = $thread->threadCount();
    $threadPageSize = 40;
    $threads = $thread->threads(1,$threadPageSize);
		$pageControl = ToolModel::pageControl(1,$threadCount,$threadPageSize,"<a href='/thread/index/#page#/'>");
    
    $toplistModel = new ToplistModel();
    $toplist = $toplistModel->toplist();
    $this->_mainContent->assign("toplist",$toplist);
    
    $this->_mainContent->assign("pageControl",$pageControl);
    $this->_mainContent->assign("threads",$threads);
    $this->_mainContent->assign("news",$news);
    $this->_mainContent->assign("applenews",$applenews);
    $this->_mainContent->assign("userid",$this->userid);
    $this->_mainContent->assign("spamcount",$spamcount);
    $this->_mainContent->assign("newscount",$newscount);
    
    $this->viewFile="Home/index.html";
    if($page>1)
      $this->setTitle("本站新闻 第".$page."页");
    $this->display();
  }
  public function linkAction() {
    
    $this->_view->assign("active","link");
    $this->display();
  }
  public function newsAction() {
    
    $page = $this->intVal(3);
    if($page<=1){
      
      header("HTTP/1.1 301 Moved Permanently");
      header("Location: /home/");
      header("Connection: close");
      die();
    }
    $this->indexAction();
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
  
  public function checkRecaptchaAction(){
    $privatekey = "6LcGEuMSAAAAAAohpDLjBTKW9WhcoIdrnopcBzgY";
    
    if ($_POST["recaptcha_response_field"]) {
            $resp = recaptcha_check_answer ($privatekey,
                                            $_SERVER["REMOTE_ADDR"],
                                            $_POST["recaptcha_challenge_field"],
                                            $_POST["recaptcha_response_field"]);

            if ($resp->is_valid) {
                    echo "ok";
                    $newsModel = new NewsModel();
                    $newsModel->saveComment();
            } else {
                    $error = $resp->error;
                    echo $error;
            }
    }
    else
      echo "error";
    
  }
  
  public function sAction() {
    
    $index = $this->intVal(3);
    $other = $this->strVal(4);
    if(count($this->__uriparts)!=5 || !empty($other)) {
      
      header ('HTTP/1.1 301 Moved Permanently');
      header("location: /home/s/$index/");
    }
    
    $discuz = new DiscuzModel();
    $allModel = new AllModel();
    $newsModel = new NewsModel();
    $tongji = new TongjiModel();
    
    
    $dataall = $tongji->data("all");
    $hotnews = $tongji->hotnews(10);
      
    $threads = $allModel->allThreads(1,10);
    $userid = $discuz->checklogin();
    $username = $newsModel->usernameById($userid);
    $news = $newsModel->oneNews($index);
    $comments = $newsModel->commentsByNewsId($index);
    
    $nonamename = $_COOKIE["nonamename"];
    if(empty($nonamename)) {
      
      $nonamename = "匿名用户" . rand(0,10000);
      setcookie("nonamename", $nonamename, time()+3600*24*7*2);
    }
    
    $this->_mainContent->assign("threads",$threads);
    $this->_mainContent->assign("news",$news);
    $this->_mainContent->assign("comments",$comments);
    $this->_mainContent->assign("userid",$userid);
    $this->_mainContent->assign("username",$username);
    $this->_mainContent->assign("nonamename",$nonamename);
    $this->_mainContent->assign("pageview",$dataall[$index]);
    $this->_mainContent->assign("hotnews",$hotnews);
    
    
    $publickey = "6LcGEuMSAAAAAOKJbYerrUqiotwH4wHyYr5E0Y-w";
    $recaptcha =  recaptcha_get_html($publickey);
    $this->_mainContent->assign("recaptcha",$recaptcha);
    
    $this->setTitle($news["title"]);
    $this->display();
  }

  public function sitemapAction() {
    
    $newsModel = new NewsModel();
    $news = $newsModel->news(1,10000);
    $this->_mainContent->assign("news",$news);
    $this->_layout = "empty";
    $this->display();
  }

  public function rssAction() {
    
    $newsModel = new NewsModel();
    $news = $newsModel->news(1,30);
    $this->_mainContent->assign("allnews",$news);
    $this->_layout = "empty";
    $this->display();
  }

  public function savecommentAction() {
    
    if(empty($_POST["content"])) {
      header ('HTTP/1.1 301 Moved Permanently');
      header("location: /home/");
    }
    // $newsModel = new NewsModel();
    // $newsModel->saveComment();
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
  
  public function tagsAction() {
    
    $size = 15;
    $page = $this->intVal(3);
    if($page==1){
      
      header("HTTP/1.1 301 Moved Permanently");
      header("Location: /home/tags/");
      header("Connection: close");
      die();
    }
    if($page==0)
      $page=1;
    
    $newsModel = new NewsModel();
    $tags = $newsModel->hotTags();
    $alltags = $newsModel->tags($page,$size);
    $count = $newsModel->tagsCount();
    
		$pageControl = ToolModel::pageControl($page,$count,$size,"<a href='/home/tags/#page#/'>");
    $this->_mainContent->assign("pageControl",$pageControl);
    
    $this->_mainContent->assign("tags",$tags);
    $this->_mainContent->assign("alltags",$alltags);
    $this->setTitle("Tags 第 $page 页");
    $this->_view->assign("active","tags");
    $this->display();
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
  
  public function newnewsAction() {
    
    $newscenter = new NewscenterModel();
    $ids = $newscenter->appleNewsIdsDays(3);
    $idStr = join(",",$ids);
    echo $idStr;
  }
  
  public function testallAction() {
    
    $newscenter = new NewscenterModel();
    $ids = $newscenter->uncheckedIds();
    $apples = array();
    foreach($ids as $id) {
      $url = "http://tiny4cocoa.com:9090/api/news/?id=$id";
      $ret = ToolModel::getUrl($url);
      var_dump($ret);
      $newscenter->markApple($id,$ret,0);
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
}


