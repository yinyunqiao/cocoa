<?php
require_once 'functions.php';
$wiki = strtolower($_GET['wiki']);

switch($_GET['act']) {
	
	case 'showlogin':
		die(createLoginBox('登录',$_GET['retUrl']));
		break;
		
	case 'login':
		$result = uc_user_login($_POST['username'],$_POST['password']);
		$username = $result[1];
		setcookie("TINY4WIKI_AUTH",$username,time()+60*60*24*30,"/");
		setcookie("TINY4WIKI_AUTH",$username,time()+60*60*24*30);
		header("Location:/doc/");
		break;
	
	case 'edit':
		if($_COOKIE['TINY4WIKI_AUTH']=='tinyfool')	
			die(showeditpage($wiki));
		else
			header("Location:/doc/$wiki");
		break;
	
	case 'save':
		updatePage($_POST);
		header("Location:/doc/$_POST[url]");
		break;
		
	case 'show':
		die(showpage($wiki));
		break;
	
	case 'logout':
		setcookie("TINY4WIKI_AUTH",'0',time()-3600,"/");
		setcookie("TINY4WIKI_AUTH",'0',time()-3600);
		header("Location:/doc/");
		break;
		
	default:
		die(showpage('index'));
		break;
}
