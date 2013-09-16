<?php
	class baseController extends tinyApp_Controller
	{
		
		protected $cas;
		protected $breadCrumb;
		protected $title;
		protected $viewFile;
		protected $userinfo;	// 用户基础信息
		protected $userid;
    protected $username;
    
		public function __construct($pathinfo,$controller) {
			
			$this->sitename = "Tiny4Cocoa";
			parent::__construct($pathinfo,$controller);
			$this->begintime = microtime(true);
			$this->_layout="index";
			$this->cas[]=array('name'=>'index','title'=>'首页');
			$this->cas[]=array('name'=>'app','title'=>'应用列表');
			$this->cas[]=array('name'=>'user','title'=>'用户');
			$this->cas[]=array('name'=>'group','title'=>'群组');
			
			$mainMenu = $this->createMainMenu();
			$title= $this->findTitle();
      if($title)
        $this->title = "$title - $this->sitename";
      else
        $this->title = $this->sitename;
        
			$controller=$this->_controller['name'];
			
			if($controller=='index')
				$this->breadCrumb = '';
			else
				$this->breadCrumb = "<a href='/'>首页</a>-&gt;<a href='/$controller/'>$title</a>";
			
			$this->_view->assign('mainMenu',$mainMenu);
			$this->_view->assign('title',$this->title);
			$this->_view->assign('breadCrumb',$this->breadCrumb);
			
			$controller=ucwords($this->_controller['name']);
			$action=$this->_controller['action'];
			$mainContentFile="$controller/$action.html";
			
			if(file_exists($this->_pathinfo['views'].'/'.$mainContentFile))  {
				$this->viewFile=$mainContentFile;
			}
			
			$this->_mainContent->assign("retUrl",$_SERVER['REQUEST_URI']);
			$this->_view->assign("retUrl",$_SERVER['REQUEST_URI']);
			$this->_view->assign("navsel",$controller);

      $userModel = new UserModel();
      $this->userid = $userModel->checklogin();
      $this->username = $userModel->username($this->userid);
      $this->isEmailValidated = $userModel->isEmailValidated($this->userid);
			$this->_view->assign("userid",$this->userid);
			$this->_view->assign("username",$this->username);
			$this->_view->assign("isEmailValidated",$this->isEmailValidated);

			$iPhone = ToolModel::is_iPhone();
			$this->_view->assign("iPhone",$iPhone);
			$this->_mainContent->assign("iPhone",$iPhone);
		}
		
		public function display($viewfile="") 
		{
			$this->endtime = microtime(true);
			$this->_view->assign("pagetime",round(($this->endtime-$this->begintime)*1000.0,3));
			
			if (!empty($viewfile)) {
				$this->_mainContent->assign("retUrl",$_SERVER['REQUEST_URI']);
				$this->_view->assign("retUrl",$_SERVER['REQUEST_URI']);
				$mainContent=$this->_mainContent->fetch($viewfile);
				$this->_view->assign('mainContent',$mainContent);
			} else if ($this->viewFile!="") {
				$this->_mainContent->assign("retUrl",$_SERVER['REQUEST_URI']);
				$this->_view->assign("retUrl",$_SERVER['REQUEST_URI']);
				$mainContent=$this->_mainContent->fetch($this->viewFile);
				$this->_view->assign('mainContent',$mainContent);
			}
			if($_SESSION['id']) {
			  $notify = new NotificationModel();
			  $notifyCount = $notify->unreadCount($_SESSION['id']);
			  $this->_view->assign("notifyCount",$notifyCount);
			}
			parent::display();
		}
		
		public function findTitle()
		{
			foreach($this->cas as $ca)
			{
				if($this->_controller['name']==$ca['name'])
					return $ca['title'];
			}
		}
		
		public function createMainMenu() {
			$ret='';
			foreach($this->cas as $ca)
			{
				
				if($ca['name']!='index')
					$url='/'.$ca['name'].'/';
				else
					$url='/';
				$ret .= "<li><a href='$url'";
				if($this->_controller['name']==$ca['name'])
					$ret.=" class='sel'";
				$ret .=">$ca[title]</a></li>";
			}
			return $ret;
		}
		
		public function createMainBlock($title,$content,$class) {			
			$ret=	"<div class='main_block $class'><h3 class='title'>$title</h3>$content</div>";
			return $ret;
		}
		public function initSidebar1() {
			$classModel=new ClassModel();
			$data=$classModel->getClassesAndCounts(1);
			$sb.=$this->createSbBlock("编程语言和开发工具",$this->makeClassList($data));
			$data=$classModel->getClassesAndCounts(2);
			$sb.=$this->createSbBlock("操作系统和平台",$this->makeClassList($data));
			$data=$classModel->getClassesAndCounts(3);
			$sb.=$this->createSbBlock("垂直技术和领域",$this->makeClassList($data));

			return $sb;
		}
		
		public function makeClassList($data)
		{
			$ret="<ul>";
			if(count($data)>0)
			foreach($data as $d)
				$ret.="<li><a href='/class/show/$d[id]/'>$d[name]($d[c])</a></li>";
			$ret.="</ul>";
			return $ret;
		}
		
		public function createSbBlock($title,$content)  {
			$ret=	"<div class='sb_block'><h3 class='title'>$title</h3>$content</div>";
			return $ret;
		}
	
    public function setTitle($title) {
      
      $this->_view->assign("title","$title - $this->sitename");
    }
	}
	
