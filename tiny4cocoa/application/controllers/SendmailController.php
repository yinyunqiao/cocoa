<?php
class SendmailController {
  
    public function sendmailqueueAction(){
        $mailModel = new MailModel();
        $queue = $mailModel->getMailQueue(100);
        if(count($queue)){
            foreach($queue as $item){
                $mailModel->sendMailItem($item);
            }
        }else{
            $logger = new LoggerModel("/var/log/mp/mail-tiny4cocoa.log");
            $logger->log("empty");
        }
    }
}