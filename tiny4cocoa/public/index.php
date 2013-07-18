<?php
  putenv("TZ=Asia/Shanghai");
  date_default_timezone_set("Asia/Shanghai");

	$basePath = realpath(dirname(__FILE__) . '/../');
	$TA_PathInfo = array (
						'base' => $basePath,
						'tinyAppLib' => realpath($basePath . '/../../framework'),
						'application' => realpath($basePath . '/application'),
						'controllers' => realpath($basePath . '/application/controllers'),
						'models' => realpath($basePath . '/application/models'),
						'layouts' => realpath($basePath . '/application/layouts'),
						'views' => realpath($basePath . '/application/views'),
						'compile' => realpath($basePath . '/templates_c'),
						'cache' => realpath($basePath . '/cache'),
		);
	
	require_once $TA_PathInfo['tinyAppLib'] . "/tinyApp/Application.php";
	require_once $TA_PathInfo['tinyAppLib'] . "/tinyApp/Controller.php";
	require_once $TA_PathInfo['tinyAppLib'] . "/tinyApp/View.php";
	require_once $TA_PathInfo['tinyAppLib'] . "/Smarty/Smarty.class.php";
	require_once $TA_PathInfo['tinyAppLib'] . "/PHPMailer/class.phpmailer.php";
	require_once $TA_PathInfo['tinyAppLib'] . "/Akismet/Akismet.php";
	require_once $TA_PathInfo['tinyAppLib'] . "/markdown/markdown.php";
  
  define('MAGPIE_CACHE_AGE',60*33);
  define('MAGPIE_CACHE_DIR',"../rsscache");
	require_once $TA_PathInfo['tinyAppLib'] . "/magpierss/rss_fetch.php";
  mb_internal_encoding("UTF-8");
  
	$dblog = "/var/www/iapp/log/db.log";
	$application = new tinyApp_Application($TA_PathInfo);
	$application->dispatch();
