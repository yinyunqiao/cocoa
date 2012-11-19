<?php
class HomeadminController extends baseController
{
  public $userid;
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $discuz = new DiscuzModel();
    $this->userid = $discuz->checklogin();
    if($this->userid!=2 && $this->userid!=46) {
      header ('HTTP/1.1 301 Moved Permanently');
      header('location: /home/');
    }
  }
  
  public function indexAction() {
    
    $newsModel = new NewsModel();
    $news = $newsModel->news(1,10);
    $this->_mainContent->assign("news",$news);
    $this->display();
  }
  
  public function newarticleAction() {
    
    $this->display();
  }
  
  public function editarticleAction() {
    
    $index = $this->intVal(3);
    $newsModel = new NewsModel();
    $news = $newsModel->oneNews($index);
    $this->_mainContent->assign("threads",$threads);
    $this->_mainContent->assign("news",$news);
    
    $this->viewFile = "Homeadmin/newarticle.html";
    $this->display();
  }
  
  public function articlesAction() {
    
    $this->display();
  } 
  
  public function commentsAction() {
    
    $newModel = new NewsModel();
    $comments = $newModel->comments(1,20);
    $this->_mainContent->assign("comments",$comments);
    $this->display();
  }
   
  public function emptyspamAction() {
    
    $newsModel = new NewsModel();
    $newsModel->emptySpam();
    header("location:/homeadmin/comments/");
  }
  
  public function markspamAction(){
    
    $id = $this->intVal(3);
    if($id==0)
      header("location:/homeadmin/comments/");
    $newsModel = new NewsModel();
    $newsModel->markSpam($id,1);
    
    $akismet = new Akismet();
    $akismet->key = "5a3c4dc9f909";
    $akismet->blog = "http://tiny4cocoa.org/home/";
    if(!$akismet->verifyKey())
      die("akismet verify error");
    $comment = $newsModel->commentById($id);
    if(!$comment)
      die("can not find comment");
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
    $ret = $akismet->submitSpam($data);
    header("location:/homeadmin/comments/");
  }
  
  public function unmarkspamAction(){
    
    $id = $this->intVal(3);
    if($id==0)
      header("location:/homeadmin/comments/");
    $newsModel = new NewsModel();
    $newsModel->markSpam($id,0);
    
    $akismet = new Akismet();
    $akismet->key = "5a3c4dc9f909";
    $akismet->blog = "http://tiny4cocoa.org/home/";
    if(!$akismet->verifyKey())
      die("akismet verify error");
    $comment = $newsModel->commentById($id);
    if(!$comment)
      die("can not find comment");
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
    $ret = $akismet->submitHam($data);
    header("location:/homeadmin/comments/");
  }
  
  
  public function recheckSpamAction(){
    
    $id = $this->intVal(3);
    if($id==0)
      header("location:/homeadmin/comments/");
    $newsModel = new NewsModel();
    
    $akismet = new Akismet();
    $akismet->key = "5a3c4dc9f909";
    $akismet->blog = "http://tiny4cocoa.org/home/";
    if(!$akismet->verifyKey())
      die("akismet verify error");
    $comment = $newsModel->commentById($id);
    if(!$comment)
      die("can not find comment");
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
    $ret = $akismet->commentCheck($data);
    if($ret) {
      $newsModel->markSpam($comment["id"],1);
      //echo "comment # $comment[id] is spam!\r\n";
    }
    else {
      $newsModel->markSpam($comment["id"],0);
      //echo "comment # $comment[id] is not spam.\r\n";
    }
    header("location:/homeadmin/comments/");
  }
  public function savearticleAction() {
    
    if(empty($_POST))
      header("location:/homeadmin/");
    
    $news = new NewsModel();
    
    $data = $_POST;
    $data["createdate"] = time();
    $data["updatedate"] = time();
    $username = $news->usernameById($this->userid);
    $data["posterid"] = $this->userid;
    $data["poster"] = $username;
    $news->select("cocoacms_news")->insert($data);
    header("location:/homeadmin/");
  }
  
  public function newsimageuploadAction() {
    
    /**
      给图片起名字
      把图片转换成合适的尺寸/保存
      给出预览图片
    */
    $discuzPath = dirname(dirname(dirname(dirname(__FILE__))));
    $savepath = "$discuzPath/newsupload/";
    $upload = new UploadModel();
    $filename = $upload->filename;
    if(!$filename)
      die('上传失败，请稍后重试！');
    $sizes = array(
      array("s",220,146),
      array("m",300,-1)
    );
    $upload->cropAndSave($sizes,$savepath);
    $ret["filename"] = $filename;
    $ret["ext"] = $upload->ext;
    echo json_encode($ret);
  }
  
  public function settongjiAction() {
    
    $tongji = new TongjiModel();
    $tongji->check($_GET["code"]);
  }
  
  public function feedbackAction() {
    
    $db = new PlaygroundModel();
    $sql = "SELECT * FROM `playground_feedback` order by `id` DESC;";
    $ret = $db->fetchArray($sql);
    $feedbacks = array();
    foreach($ret as $line) {
      
      $line["feedback"] = urldecode($line["feedback"]);
      $line["feedback"] = ToolModel::toHtml($line["feedback"]);
      if($line["createtime"]!=0)
        $line["createtime"] = ToolModel::countTime($line["createtime"]);
      else
        $line["createtime"] = "";
      $feedbacks[] = $line;
    }
    $this->_mainContent->assign("feedbacks",$feedbacks);
    $this->display();
  }
  
  public function statAction(){
    
    $index = $this->strVal(3);
    $stat = new StatModel();
    $days = $stat->days();
    if(strlen($index)==0)
      $index = $days[0]["datename"];
    $data = $stat->data($index);
    $time = explode(",",$data["time"]["content"]);
    
    $ipdata = $data["ip"]["content"];
    $iparray = explode("\n",$ipdata);
    $ips = array();
    foreach($iparray as $ip) {
      $v = explode(" ",trim($ip));
      if(isset($v[0])&&isset($v[1])) {
        $ipline = array();
        $ipline["count"] = $v[0];
        $ipline["ip"] = $v[1];
        $ips[] = $ipline;
      }
    }
    
    $actiondata = $data["action"]["content"];
    $actionarray = explode("\n",$actiondata);
    $actions = array();
    foreach($actionarray as $action) {
      $v = explode(" ",trim($action));
      if(isset($v[0])&&isset($v[1])) {
        $ipline = array();
        $ipline["count"] = $v[0];
        $ipline["action"] = $v[1];
        $actions[] = $ipline;
      }
    }
    
    $userdata = $data["user"]["content"];
    $userarray = explode("\n",$userdata);
    $users = array();
    foreach($userarray as $user) {
      $v = explode(" ",trim($user));
      if(isset($v[0])&&isset($v[1])) {
        $ipline = array();
        $ipline["count"] = $v[0];
        $ipline["user"] = $v[1];
        $users[] = $ipline;
      }
    }
    
    $this->_mainContent->assign("time",$time);
    $this->_mainContent->assign("ips",$ips);
    $this->_mainContent->assign("actions",$actions);
    $this->_mainContent->assign("users",$users);
    $this->_mainContent->assign("index",$index);
    $this->_mainContent->assign("days",$days);
    $this->display();
  }
}
