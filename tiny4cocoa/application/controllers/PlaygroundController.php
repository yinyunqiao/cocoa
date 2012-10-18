<?php
class PlaygroundController extends baseController
{
 
  public function indexAction() {
    
    $this->display();
  }
  
  public function joinAction() {
    
    if($_GET["app"]!="footprint")
      header("location:/playground/");
    
    $this->_mainContent->assign("app",$_GET["app"]);
    $this->display();
  }
  
  public function saverequestAction() {
    
    // $db = new PlaygroundModel();
    // $db->save($_POST);
    //header("location:/playground/joinok/");
  }
  
  public function joinokAction() {
    
    $this->display();
  }
  
  public function feedbackAction() {
    
    $data = $_POST["feedback"];
    $data = ToolModel::getRealIpAddr() . "/" . $data;
    $feedback = explode("/",$data);
    // $fp = fopen('/root/log/feedback.log', 'a');
    // fwrite($fp,$data."\r\n");
    // fclose($fp);
    $db = new PlaygroundModel();
    $sql = "INSERT INTO `playground_feedback` (`ip`,`uuid`,`foursquare`,`instagram`,`feedback`) VALUES('$feedback[0]','$feedback[0]','$feedback[0]','$feedback[0]','$feedback[0]');";
    $db->run($sql);
    echo "done";
  }
}


