<?php
class CrontabController extends baseController {

  public function oneweeksummaryAction() {
    
    $now = time();
    $dw = date("w", $now);
    if($dw!="1")
      die();
    
    $threadModel = new ThreadModel();
    if($threadModel->isWeekmailSent()==1)
      die();
    $threadModel->setWeekmailSent();
    
    $oneweekbefore = $now-60*60*24*7;
    $topThread = $threadModel->topThreadsFrom(10,$oneweekbefore);
    $bbsHero = $threadModel->topBbsHero(10,$oneweekbefore);
    
    $newscenter = new NewscenterModel();
    $news = $newscenter->news(1,10,"apple");
    
    $data = array();
    $data["topThread"] = $topThread;
    $data["bbsHero"] = $bbsHero;
    $data["news"] = $news;
    
    
    $mail = new MailModel();
    $userModel = new UserModel();
    $users = $userModel->weeklyNewsUser();
    
    foreach($users as $user) {
      
      $data["user"] = $user;
      $page = $this->makePage("MailTemplate","weeksummary",$data);
      $mail->generateMail(
            $user["email"],
             "admin@tiny4.org", 
            "Tiny4Cocoa每周精选", 
            $page);
    }
    echo "ok";
  }
}