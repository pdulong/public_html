<?php
class DateValidator extends CTypeValidator
{
	public function validateAttribute($object,$attribute)
	{
		$this->type = 'date';
		$this->dateFormat = CLocale::getInstance( Yii::app()->language )->getDateFormat('short');
		parent::validateAttribute($object, $attribute);
	}
}