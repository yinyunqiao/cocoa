<?php
class UserController extends baseController
{
  public function __construct($pathinfo,$controller) {
		
    parent::__construct($pathinfo,$controller);
    $this->_view->assign("active","user");
  }
 
  public function indexAction() {
    
    $this->display();
  }
  
  public function showAction() {
    
    $id = $this->intVal(3);
    $userModel = new UserModel();
    $threadModel = new ThreadModel();
    $userinfo["id"] = $id;
    $userinfo["name"] = $userModel->username($id);
    $userinfo["image"] = DiscuzModel::get_avatar($id,"middle");
    $userinfo["threadscreate"] = $threadModel->threadsByUserid($id);
    $userinfo["threadsreply"] = $threadModel->threadsReplyByUserid($id);
    $this->_mainContent->assign("user",$userinfo);
    $this->display();
  }
  
}
