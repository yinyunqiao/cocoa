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
             "Tiny4Cocoa论坛 <tiny4cocoa@tiny4.org>", 
            "Tiny4Cocoa每周精选", 
            $page);
    }
    echo "ok";
  }
  
  public function onedaysummaryAction() {
        
    $now = time();
    $onedaybefore = $now-60*60*24;
    
    $threadModel = new ThreadModel();
    $newThread = $threadModel->newThreadsFrom($onedaybefore);
    $topThread = $threadModel->topThreadsFrom(10,$onedaybefore);
    $bbsHero = $threadModel->topBbsHero(10,$onedaybefore);
    
    $newscenter = new NewscenterModel();
    $news = $newscenter->news(1,10,"apple");
    
    $data = array();
    $data["newThread"] = $newThread;
    $data["topThread"] = $topThread;
    $data["bbsHero"] = $bbsHero;
    $data["news"] = $news;
    
    
    $mail = new MailModel();
    $userModel = new UserModel();
    $users = $userModel->dailyNewsUser();
    
    foreach($users as $user) {
      
      $data["user"] = $user;
      $page = $this->makePage("MailTemplate","dailysummary",$data);
      $mail->generateMail(
            $user["email"],
             "Tiny4Cocoa论坛<tiny4cocoa@tiny4.org>", 
            "Tiny4Cocoa每日精选", 
            $page);
    }
    echo "ok";
  }
}