<?php
require_once("GoogleOpenID.php");	
error_reporting(0);
$googleLogin = GoogleOpenID::createRequest("return.php");
$googleLogin->redirect();
