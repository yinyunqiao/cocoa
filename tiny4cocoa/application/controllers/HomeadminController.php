<?php
class HomeadminController extends baseController
{
  public $userid;
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $discuz = new DiscuzModel();
    $this->userid = $discuz->checklogin();
    if($this->userid!=2) {
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
}
