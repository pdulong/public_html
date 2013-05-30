<?php
class PriceInput extends CInputWidget
{
	public $htmlOptions;

	public function run()
	{
		if ($this->model && $this->attribute)
			$val = $this->model->{$this->attribute};
		else if ($this->name && $this->value)
			$val = $this->value;
		else
			throw new CHttpException();

		$lc = CLocale::getInstance(Yii::app()->getLanguage());
		if (!$this->model->hasErrors($this->attribute))
			$val = number_format($val, 2, $lc->getNumberSymbol('decimal'), '');
		$this->htmlOptions = array_merge((array)$this->htmlOptions, array('value'=>$val));
		echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
	}
}