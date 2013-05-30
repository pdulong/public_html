<?php
class No404EmailLogRoute extends CEmailLogRoute
{
	protected function formatLogMessage($message,$level,$category,$time)
	{
		$message = '<div style="line-height:1.6em;">'.trim(nl2br($message)).'</div>';
		return @date('d-m-Y H:i:s',$time)."<br/><br/>[<strong>$level</strong> $category]<br/><br/>$message";
	}

	protected function processLogs($logs)
	{
		$message='';
		foreach($logs as $log)
		{
			if ($log[2] != 'exception.CHttpException.404')
				$message.=$this->formatLogMessage($log[0],$log[1],$log[2],$log[3]);
		}
		if ($message == '')
			return;
		$message .= "\n\n".'User: '.Yii::app()->user->name;
		$message .= "\n".'Referer: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '--');
		
		foreach($this->getEmails() as $email)
			$this->sendEmail($email, $this->getSubject(), $message);
	}

	protected function sendEmail($email,$subject,$message)
	{
		$subject = $subject ? 'Yii Application Debug Message' : '';
		if(($from=$this->getSentFrom())!=='')
			mail($email,$subject,$message,"From:{$from}\r\n".'MIME-Version: 1.0' . "\r\n".'Content-type: text/html; charset=iso-8859-1'."\r\n");
		else
			mail($email,$subject,$message,'MIME-Version: 1.0' . "\r\n".'Content-type: text/html; charset=iso-8859-1'."\r\n");
	}
}