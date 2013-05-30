<?php
class Email extends CComponent
{
	public $subject = '';
	public $from = '';
	public $returnPath;
	public $mimeVersion = '1.0';
	public $contentType = 'text/html';
	public $charset = 'utf-8';
	public $layout = 'main';
	public $lineLength = 74;
	
	private $_body;
	private $_bodyPlainText;
	private $_attachments;
	private $_mixedHash;
	private $_alternativeHash;
	private $_eol;
	private $_cc;
	private $_bcc;
	private $_tplData;
	
	/**
	* Constructor
	* @param string $eol End-of-line character(s) for the e-mail
	*/
	public function __construct($eol = "\n")
	{
		$this->_eol = $eol;
		$this->_init();
	}

	/**
	* Reset all variables for this class
	*/
	public function reset()
	{
		$this->_init();
	}

	private function _init()
	{
		$this->_body = $this->_bodyPlainText = $this->subject = $this->from = $this->returnPath = '';
		$this->_attachments = array();
		$this->_cc = array();
		$this->_bcc = array();
		$this->_mixedHash = md5(date('r', time()));
		$this->_alternativeHash = md5(date('r', time()));
	}
	
	/**
	* Set the subject of the e-mail
	* @param string $body The body of the e-mail
	*/
	public function setBody($view = '', $vars = array())
	{
		$this->_tplData=array('viewFile'=>$view, 'data'=>$vars);
		
		$vars = (array)$vars;
		$this->_body = $this->_bodyFromTemplate(
			'application.emails.html.layouts.'.$this->layout,
			'application.emails.html.'.$view,
			$vars
		);
		
		$plaintextLayout = Yii::getPathOfAlias('application.emails.plaintext.layouts.'.$this->layout);
		$plaintextView = Yii::getPathOfAlias('application.emails.plaintext.'.$view);
		if (file_exists($plaintextLayout.'.php') && !file_exists($plaintextView.'.php'))
		{
			$this->_bodyPlainText  = $this->html2text($this->_bodyFromTemplate(
				'application.emails.plaintext.layouts.'.$this->layout,
				'application.emails.html.'.$view,
				$vars
			));
		}
		else if (file_exists($plaintextLayout.'.php') && file_exists($plaintextView.'.php'))
		{
			$this->_bodyPlainText  = $this->_bodyFromTemplate(
				'application.emails.plaintext.layouts.'.$this->layout,
				'application.emails.plaintext.'.$view,
				$vars
			);
		}
		else
		{
			$this->_bodyPlainText = $this->html2text($this->_body);
		}
	}

	/**
	 * @ignore
	 */
	private function html2text($html)
	{
		require_once(dirname(__FILE__).'/Html2Text.php');
		$h2t = new Html2Text($html);
		$h2t->width=0; // We take care of Quoted Printable ourselves in this class
		if (isset($_SERVER) && isset($_SERVER['HTTP_HOST']))
			$h2t->set_base_url('http://'.$_SERVER['HTTP_HOST'].'/');
		return $h2t->get_text();
	}

	/**
	 * @ignore
	 */
	private function _bodyFromTemplate($layout, $view, $vars = array())
	{
		if ($view != '')
		{
			$view = $this->_template($view, $vars);
			if ($layout == '')
			{
				$message = $view;
			}
			else
			{
				$message = $this->_template($layout, array_merge( $vars, array('content'=>$view) ));
			}
		}
		else
		{
			$message = CVarDumper::dumpAsString($vars, 10, true);
		}
		return $message;
	}

	/**
	 * @ignore
	 */
	private function _template($view, $vars)
	{
		extract($vars); ob_start();
		include(Yii::getPathOfAlias($view).'.php');
		return ob_get_clean();
	}

	/**
	* Add CC address(es) for this email
	* @param array $cc An array of arrays in the form (email, name)
	*/
	public function addCC($cc)
	{
		$this->_cc = array_merge($this->_cc, (array)$cc);
	}

	/**
	* Add BCC address(es) for this email
	* @param array $bcc An array of arrays in the form (email, name)
	*/
	public function addBCC($bcc)
	{
		$this->_bcc = array_merge($this->_bcc, (array)$bcc);
	}

	/**
	* Send the e-mail
	* @param string $email The e-mail-adress to send the message to
	* @param string $name Name of the person the e-mail belongs to
	* @return bool Whether the e-mail-*sending* was successful (*NOT* whether receiving the e-mail was successful!)
	*/
	public function send($email = '', $name = '')
	{
		if ($email == '' && count($this->_cc) == 0 && count($this->_bcc) == 0)
		{
			throw new CException('No receipients! Not sending any emails!', -1);
			return;
		}

		if (is_array($email))
		{
			$success = true;
			foreach($email as $receipient)
			{
				$success = $this->send($receipient[0],$receipient[1]) && $success;
			}
			return $success;
		}

		$fromEmail = $fromName = '???';
		if (is_array($this->from) && count($this->from) == 2)
		{
			$fromEmail = $this->from[0];
			$fromName = $this->from[1];
			$from = $this->_emailAndName($fromEmail, $fromName);
		}
		elseif (is_string($this->from))
		{
			$fromName = '';
			$fromEmail = $from = $this->from;
		}

		$subject = $this->subject;

		$debugIPs = array(
			'82.170.45.117', // R.P. telfort atlas
			'62.163.39.56', // M.N. UPC cron cruiser
		);

		if (isset($_SERVER, $_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], $debugIPs))
		{
			$subject .= ' ['.$fromEmail.' ~ '.$email.' ~ '.uniqid().']';

			$fromEmail = substr($fromEmail, 0, strpos($fromEmail, '@')) . '@lynth.nl';
			$from = $this->_emailAndName($fromEmail, $fromName);
			
			$email = 'arie@lynth.nl';
			$name = 'Arie Arieson';
		}

		Yii::log(
			'Sending e-mail'."\n".
			print_r(
				array_merge(
					array('to'=>$this->_emailAndName($email, $name), 'subject'=>$subject, 'layout'=>$this->layout, 'attachments'=>array_keys($this->_attachments)),
					$this->_tplData
				),
				true
			)
		);
		
		if(ini_get('safe_mode'))
		{
			return mail($this->_emailAndName($email, $name), $subject, $this->_createBody(), $this->_createHeaders($from));
		}
		else
		{
			return mail($this->_emailAndName($email, $name), $subject, $this->_createBody(), $this->_createHeaders($fromEmail));
		}
	}

	/**
	* Attach a file to this e-mail
	* @param string $file The filename to attach
	* @param string $sendFilename The filename to send the file as
	*/
	public function addAttachment($file, $sendFilename, $mime = 'image/jpeg', $cDisposition = 'attachment')
	{
		$contentId = Fn::getRandomString(8);
		$this->_attachments[$sendFilename] = array(
			'contentId' => $contentId,
			'file' => $file,
			'mime' => $mime,
			'cDisposition' => $cDisposition
		);
		return $contentId;
	}

	/**
	* Remove an attachment from the e-mail
	* @var $sendFilename The filename to be removed from the attachments
	*/
	public function deleteAttachment($sendFilename)
	{
		unset($this->_attachments[$sendFilename]);
	}

	/**
	* Used internally only
	* @ignore
	*/
	private function _emailAndName($email, $name)
	{
		if (stristr(PHP_OS, 'win'))
			return $email; // PHP windows bugs on name + email. Only supply e-mail.
		
		if (trim($name) != '')
		{
			return '"'.$name.'" <'.$email.'>';
		}
		return $email;
	}

	private function _emailArrayToString($array)
	{
		$emails = array();
		foreach($array as $recipient)
		{
			$emails[] = $this->_emailAndName((array)$recipient[0], (array)$recipient[1]);
		}
		return implode(',', $emails);
	}

	/**
	* Used internally only
	* @ignore
	*/
	private function _createHeaders($from = '')
	{
		return
			'Mime-Version: '.$this->mimeVersion . $this->_eol.
			($from != ''
				? 'From: '.$from.$this->_eol . 'Reply-To: '.$from.$this->_eol
				: '').
			(count($this->_cc) > 0
				? 'Cc: '.$this->_emailArrayToString($this->_cc).$this->_eol
				: '').
			(count($this->_bcc) > 0
				? 'Bcc: '.$this->_emailArrayToString($this->_bcc).$this->_eol
				: '').
			(count($this->_attachments) > 0
				? 'Content-Type: multipart/mixed; boundary=mixed-'.$this->_mixedHash
				: 'Content-Type: multipart/alternative; boundary=alternative-'.$this->_alternativeHash
			);
	}

	/**
	* Used internally only
	* @igone
	*/
	private function _createBody()
	{
		$bodyHtml = $this->_qpEncode($this->_body);
		$bodyPlainText = $this->_qpEncode($this->_bodyPlainText, false);
		
		$body =
			'--alternative-'.$this->_alternativeHash.$this->_eol.
			'Content-Type: text/plain; charset='.$this->charset.$this->_eol.
			'Content-Transfer-Encoding: quoted-printable'.$this->_eol.$this->_eol.
			$bodyPlainText.$this->_eol.$this->_eol.

			'--alternative-'.$this->_alternativeHash.$this->_eol.
			'Content-Type: text/html; charset='.$this->charset.$this->_eol.
			'Content-Transfer-Encoding: quoted-printable'.$this->_eol.$this->_eol.
			$bodyHtml.$this->_eol.$this->_eol.
			
			'--alternative-'.$this->_alternativeHash.'--'.$this->_eol;

		if (count($this->_attachments) == 0)
			return $body;

		$body =
			'--mixed-'.$this->_mixedHash.$this->_eol.
			'Content-Type: multipart/alternative; boundary=alternative-'.$this->_alternativeHash.$this->_eol.$this->_eol.
			$body;

		foreach($this->_attachments as $filename => $attInfo)
		{
			if (!file_exists($attInfo['file']))
				throw new CException('File '.$attInfo['file'].' does not exist, cannot be attached.');
			
			$body .=
				'--mixed-'.$this->_mixedHash.$this->_eol.
				'Content-Type: '.$attInfo['mime'].'; name="'.$filename.'"'.$this->_eol.
				'Content-Disposition: '.$attInfo['cDisposition'].'; filename="'.$filename.'"'.$this->_eol.
				'Content-Transfer-Encoding: base64'.$this->_eol.
				'Content-ID: '.$attInfo['contentId'].$this->_eol.
				$this->_eol.
				chunk_split( base64_encode( file_get_contents( $attInfo['file'] ) ), $this->lineLength, $this->_eol ).
				$this->_eol.$this->_eol;
		}

		$body .= '--mixed-'.$this->_mixedHash.'--';
		return $body;
	}

	/**
	 * @ignore
	 */
	private function _qpEncode($text, $stripEmptyLines = true, $recursed = false)
	{
		if (!$recursed)
		{
			$text = str_replace('=', '=3D', $text);
			$text = preg_replace('/\t/', '', $text);
		}
		$lines = explode($this->_eol, $text);
		$newText = '';
		foreach($lines as $line)
		{
			if ($stripEmptyLines && trim($line) == '') continue;
			if (strlen($line) > $this->lineLength + 1)
			{
				$newText .= substr($line, 0, $this->lineLength).'='.$this->_eol.$this->_qpEncode(substr($line, $this->lineLength), $stripEmptyLines, true);
			}
			else
			{
				$newText .= $line.$this->_eol;
			}
		}
		return $newText;
	}
}