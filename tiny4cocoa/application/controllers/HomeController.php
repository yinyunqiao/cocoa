<?php
class HomeController extends baseController
{
 
  public function indexAction() {
    
    $allModel = new AllModel();
    $newsModel = new NewsModel();
    
    $threads = $allModel->allThreads(1,10);
    $newThreads = $allModel->newThreads(1,10);
    
    $news = $newsModel->news(1,10);
    $this->_mainContent->assign("threads",$threads);
    $this->_mainContent->assign("newThreads",$newThreads);
    $this->_mainContent->assign("news",$news);
    $this->display();
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
      $newscenter->markApple($id,$ret);
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
}


