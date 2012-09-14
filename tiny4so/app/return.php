<?php
require_once("GoogleOpenID.php");	
error_reporting(0);

  $googleLogin = GoogleOpenID::getResponse();
  if($googleLogin->success()){
    $user_id = $googleLogin->identity();
  }
