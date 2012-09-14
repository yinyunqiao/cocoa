<?php

class MailModel extends baseDbModel {

	private $mail;
	public function begin() {
		
		$this->mail                = new PHPMailer();
		$this->mail->IsSMTP();
		$this->mail->Host          = "smtp.sendgrid.net";
		$this->mail->SMTPAuth      = true;
		$this->mail->SMTPKeepAlive = true;
		$this->mail->Port          = 25;
		$this->mail->Username      = "tinyfool";
		$this->mail->Password      = "vid0443";
		$this->mail->CharSet = 'utf-8';
	}
	
	public function send($mailData) {
		
		$this->mail->SetFrom($mailData["fromMail"],$mailData["from"]);
		if($mailData["replyMail"])
			$this->mail->AddReplyTo($mailData["replyMail"],$mailData["reply"]);
		$this->mail->AddAddress($mailData["toMail"],$mailData["to"]);
		if($mailData["bccMail"])
			$this->mail->AddBCC($mailData["bccMail"],$mailData["bcc"]);
		$this->mail->Subject = $mailData["subject"];
		$this->mail->AltBody = $mailData["altBody"];
	  $this->mail->MsgHTML($mailData["msgHTML"]);
		return $this->mail->Send();
	}
	
	public function end(){
		
		$this->mail->ClearAddresses();
	  $this->mail->ClearAttachments();
	}
}