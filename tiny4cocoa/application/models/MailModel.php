<?php

class MailModel extends baseDbModel {

    private $mail;
    public function begin() {
      
      mb_internal_encoding('UTF-8'); 
  		$this->mail                = new PHPMailer();
      $this->mail->CharSet = 'utf-8';
  		$this->mail->IsSMTP();
  		$this->mail->Host          = "smtp.sendgrid.net";
  		$this->mail->SMTPAuth      = true;
  		$this->mail->SMTPKeepAlive = true;
  		$this->mail->Port          = 25;
  		$this->mail->Username      = "tinyfool";
  		$this->mail->Password      = "vid0443";
  		$this->mail->CharSet = 'utf-8';
      $this->mail->IsHTML(true);
      $this->mail->Encoding = "base64"; 
    }

    public function send($mailData) {

        $this->mail->SetFrom($mailData["fromMail"],$mailData["from"]);
        if($mailData["replyMail"])
            $this->mail->AddReplyTo($mailData["replyMail"],$mailData["reply"]);
        $this->mail->AddAddress($mailData["toMail"],$mailData["to"]);
        if($mailData["bccMail"])
            $this->mail->AddBCC($mailData["bccMail"],$mailData["bcc"]);
        $this->mail->Subject = mb_encode_mimeheader($mailData["subject"]);
        $this->mail->AltBody = $mailData["altBody"];
        $this->mail->MsgHTML($mailData["msgHTML"]);
        return $this->mail->Send();
    }

    public function end(){

        $this->mail->ClearAddresses();
        $this->mail->ClearAttachments();
    }

    public function getErrorInfo(){
        return $this->mail->ErrorInfo;
    }

    public function generateMail($email, $frommail, $subject, $content){
        $item = array();
        $item['email'] = $email;
        $item['frommail'] = $frommail;
        $item['subject'] = $subject;
        $item['content'] = $content;
        $this->select("mail_queue")->insert($item);
    }

    public function getMailQueue($limit = 20){
        $sql = "SELECT * FROM mail_queue WHERE status = 0 ORDER BY rand() ASC LIMIT $limit";
        return $this->fetchArray($sql);
    }

    public function sendMailItem($queueItem){
        $fromMail = "noreply@tiny4.org";
        if($queueItem['frommail'] != ""){
            $fromMail = $queueItem['frommail'];
        }
        // $queueItem['content'] = str_replace(array("\\r\\n", "\\r", "\\n", "\r\n", "\n", "\r"), "<br />", $queueItem['content']);
        $mailItem = array(
            "toMail" => $queueItem['email'],
            "to" => $queueItem['email'],
            "fromMail" => $fromMail,
            "msgHTML" => $queueItem['content'],
            "altBody" => $queueItem['content'],
            "subject" => $queueItem['subject']
        );
        $sql = "";
        try{
            $this->begin();
            if(!$this->send($mailItem)){
                $errorInfo = $this->getErrorInfo();
            }

            $logger = new LoggerModel("/var/log/mp/mail.log");
            if(isset($errorInfo)){
                $sql = "UPDATE mail_queue SET status = 2, errorMessage = '$errorInfo' WHERE id = ". $queueItem['id'];
                $logger->log($queueItem['id']. ", fail, $errorInfo");
            }else{
                $sql = "UPDATE mail_queue SET status = 1 WHERE id = ". $queueItem['id'];
                $logger->log($queueItem['id']. ", success");
            }
            $this->end();
         }catch(Exception $e){
            $errorInfo = $e->getMessage();
            $sql = "UPDATE mail_queue SET status = 2, errorMessage = '$errorInfo' WHERE id = ". $queueItem['id'];
        }

        $this->run($sql);
    }

    public function mailCountOfStatus($status){
        $sql = "SELECT count(*) as c FROM mail_queue WHERE status = $status";
        $ret = $this->fetchArray($sql);
        return intval($ret[0]['c']);
    }
}