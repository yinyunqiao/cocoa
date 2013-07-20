<?php
class SendmailController {
  
    public function sendmailqueueAction(){
        $mailModel = new MailModel();
        $queue = $mailModel->getMailQueue(20);
        if(count($queue)){
            foreach($queue as $item){
                $mailModel->sendMailItem($item);
            }
        }else{
            $logger = new LoggerModel("/var/log/mp/mail.log");
            $logger->log("empty");
        }
    }
}