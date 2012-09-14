<?
require_once 'userfun.php';
require_once 'questionfun.php';
require_once '../../lib/Smarty/Smarty.class.php';
require_once 'openid.php';

function checkCookie() {
	
	$tiny4wiki_user = $_COOKIE['tiny4wiki_user'];
	if(empty($tiny4wiki_user))
		header("Location:index.php?act=showlogin");
}

function createLoginBox($title,$retUrl,$reg=0,$noindex=0) 
{
	if($retUrl=='')
		$retUrl='/';
	$smarty = new Smarty;
	if($noindex==1)
	{
		$smarty->assign("extmeta",'<meta name="robots" content="noindex" />');
	}
	$smarty->assign("boxTitle",$title);
	$smarty->assign("retUrl",$retUrl);
	$smarty->assign("reg",$reg);
	$boxContent=$smarty->fetch('login.html');
	$smarty->assign("boxContent",$boxContent);
	return $smarty->fetch('msgbox.html');
}

function listquestion($unanswered) {
	
	$tab = $_GET['tab'];
    global $smarty;
	if($tab=="new" || $tab=="hot" || $tab=="week" || $tab=="month") {
		
	}
	else
		$tab = "active";
	$questions = getTopQuestion($tab,$unanswered);
	$smarty->assign("activetab",$tab);
	$smarty->assign("questions",$questions);
	$mainContent = $smarty->fetch("questionlist.html");
	$smarty->assign("mainContent",$mainContent);
	return $smarty->fetch('all.html');
}

function showquestion() {
	
	global $smarty;
	$question = getQuestionById($_GET['id']);
	$smarty->assign("question",$question);
	$mainContent = $smarty->fetch("question.html");
	$smarty->assign("mainContent",$mainContent);
	return $smarty->fetch('all.html');
}

function showlogin() {
	
	global $smarty;
	$mainContent = $smarty->fetch("login.html");
	$smarty->assign("mainContent",$mainContent);
	return $smarty->fetch('all.html');
}

function checklogin(){
	try {
	    $openid = new LightOpenID;
	    if(!$openid->mode) {
	        if(isset($_GET['login'])) {
	            $openid->identity = 'https://www.google.com/accounts/o8/id';
				$openid->required = array('contact/email');
	            header('Location: ' . $openid->authUrl());
	   		}
		}
		elseif($openid->mode == 'cancel') {
	        echo 'User has canceled authentication!';
	    } else {
	    	$openid->validate();
	 		$info = $openid->getAttributes();
            header('Location: /users/login');
	    }
	} catch(ErrorException $e) {
	    echo $e->getMessage();
	}
}




