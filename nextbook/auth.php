<?php
$user["developerID"] = "chyhfj";
$user["developerKey"] = "f2fe1124af14a9c8592a210eeb287b2a";
$users[] = $user;
$user["developerID"] = "yarshure1";
$user["developerKey"] = "07a0914041403c09bcef7b491a49c1f6";
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