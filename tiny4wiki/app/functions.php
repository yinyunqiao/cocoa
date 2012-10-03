<?
require_once 'pagefunctions.php';
require_once 'lib/Smarty/Smarty.class.php';

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

function showpage($pagename) {

	$smarty = new Smarty;
	$page = getPageContent($pagename);
	if(!$page)
	{
		$page = array();
		$page['url'] = $pagename;
		$page['title'] = "暂无标题";
		$page['content'] = "暂无内容";
	}
	$smarty->assign("page",$page);
	$smarty->assign("title",$page['title']);
	if($_COOKIE['TINY4WIKI_AUTH']=='tinyfool')
		$smarty->assign("havechangeright",1);
	$maincontent=$smarty->fetch('pageDetail.html');
	$smarty->assign("maincontent",$maincontent);
	return $smarty->fetch('page.html');
}

function showeditpage($pagename) {
	
	$extentjs='<script src="/tiny4wiki/lib/tiny_mce/tiny_mce_gzip.js" type="text/javascript"></script>
	<script src="/tiny4wiki/lib/js/loadtinymce.js" type="text/javascript"></script>';
	
	$smarty = new Smarty;
	$page = getPageContent($pagename);
	if(!$page)
	{
		$page = array();
		$page['url'] = $pagename;
		$page['title'] = "暂无标题";
		$page['content'] = "暂无内容";
	}
	$smarty->assign("extentjs",$extentjs);
	$smarty->assign("page",$page);
	$maincontent=$smarty->fetch('pageEdit.html');
	$smarty->assign("maincontent",$maincontent);
	return $smarty->fetch('page.html');
}