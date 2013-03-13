<?php
$user["developerID"] = "chyhfj";
$user["developerKey"] = "f2fe1124af14a9c8592a210eeb287b2a";
$users[] = $user;
$user["developerID"] = "yarshure";
$user["developerKey"] = "07a0914041403c09bcef7b491a49c1f6";
$users[] = $user;
$user["developerID"] = "WangLixiong";
$user["developerKey"] = "2d9f748d0ff34a99297f8876031b12fc";
$users[] = $user;
$user["developerID"] = "ETCBookInc";
$user["developerKey"] = "34df4ccae5476528f4c97b82879c48ff";
$users[] = $user;


$data = file_get_contents('php://input');
$array = json_decode($data,true);

foreach($users as $u) {
  
  if($array["developerID"]==$u["developerID"]) {
    
    if($array["developerKey"]==$u["developerKey"])
      die("ok");
  }
}
die("Denied");