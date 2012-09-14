<?php
require_once 'functions.php';

$smarty = new Smarty;
$smarty->compile_dir = '../templates_c/';

switch($_GET['act']) {
	
//Questions
	case 'showquestion':
	
		die(showquestion());
		break;

	case 'listquestion':
		die(listquestion(0));
		break;
//Users
	case 'showlogin':
		die(showlogin());
		break;
	case 'checklogin':
		checklogin();
		break;
	default:
		die($_GET['act']);
		break;
}
