<?php
class ForgotPassForm extends CFormModel
{
	public $email;

	public function rules()
	{
		return array(
			array('email','required'),
			array('email','checkEmail'),
		);
	}

	public function checkEmail($attribute,$params)
	{
		if (trim($this->$attribute) == '') return;
		if (!User::model()->exists('LOWER(email)=:email', array('email' => strtolower($this->$attribute))))
			$this->addError($attribute, 'Er is geen gebruiker bekend met het door u opgegeven e-mail adres.');
	}

	public function attributeLabels()
	{
		return array(
			'email' => 'E-mail adres',
		);
	}
}