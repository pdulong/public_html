<?php
class UserIdentity extends CUserIdentity
{
	private $_id;
	private $_password_enc;

	public function authenticate()
	{
		$key=Yii::app()->user->getState('key', '');
		$username=strtolower($this->username);
		$user=User::model()->find('active=1 AND LOWER(username)=?',array($username));
		if($user===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if ($this->_password_enc !== md5($user->password.$key) && md5($this->password)!==$user->password)
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->_id=$user->id;
			$this->username=$user->username;
			$this->errorCode=self::ERROR_NONE;
		}
		return !$this->errorCode;
	}

	public function getId()
	{
		return $this->_id;
	}

	public function setPasswordEnc($pass)
	{
		$this->_password_enc = $pass;
	}
}