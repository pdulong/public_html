<?php
class Date extends CInputWidget
{
	public $useJqUi=true;
	public $options;
	private $defaultOptions = array(
		'altField' => false,
		'altFormat' => false,
		'appendText' => false,
		'buttonImage' => false,
		'buttonImageOnly' => false,
		'buttonText' => false,
		'changeMonth' => false,
		'changeYear' => false,
		'closeText' => 'close',
		'constrainInput' => false,
		'currentText' => false,
		'dayNamesShort' => false,
		'defaultDate' => false,
		'duration' => false,
		'firstDay' => false,
		'gotoCurrent' => false,
		'hideIfNoPrevNext' => false,
		'isRTL' => false,
		'maxDate' => false,
		'minDate' => false,
		'navigationAsDateFormat' => false,
		'nextText' => 'next',
		'numberOfMonths' => false,
		'prevText' => 'prev',
		'shortYearCutoff' => false,
		'showAnim' => 'fadeIn',
		'showButtonPanel' => false,
		'showCurrentAtPos' => false,
		'showMonthAfterYear' => false,
		'showOn' => false,
		'showOptions' => false,
		'showOtherMonths' => false,
		'stepMonths' => false,
		'yearRange' => false
	);

	public function init()
	{
		$locale = CLocale::getInstance( Yii::app()->language );

		if (!isset($this->defaultOptions['dateFormat']))
			$this->defaultOptions['dateFormat'] = strtr($locale->getDateFormat('short'), array(
				'MM' => 'mm',
				'M' => 'm',
				'yy' => 'y',
				'y' => 'yy'
			));

		if (!$this->useJqUi)
			return;
		
		if (!isset($this->defaultOptions['dayNames']))
			$this->defaultOptions['dayNames'] = array_map('ucfirst', $locale->getWeekDayNames('wide'));

		if (!isset($this->defaultOptions['dayNamesMin']))
			$this->defaultOptions['dayNamesMin'] = $locale->getWeekDayNames('abbreviated');

		if (!isset($this->defaultOptions['monthNames']))
			$this->defaultOptions['monthNames'] = array_map('ucfirst', array_values($locale->getMonthNames('wide')));

		if (!isset($this->defaultOptions['montNamesShort']))
			$this->defaultOptions['montNamesShort'] = array_values($locale->getMonthNames('abbreviated'));
	}

	public function run()
	{
		list($name,$id)=$this->resolveNameID();
		if(!isset($this->htmlOptions['id']))
			$this->htmlOptions['id']=$id;
		if(!isset($this->htmlOptions['name']))
			$this->htmlOptions['name']=$name;

		if (!isset($this->htmlOptions['value']) && !empty($this->model->{$this->attribute}))
		{
			if (ctype_digit($this->model->{$this->attribute}))
			{
				$this->htmlOptions['value'] = Fn::datef()->formatDateTime($this->model->{$this->attribute}, 'short', null);
			}
			else if (is_string($this->model->{$this->attribute}))
			{
				$this->htmlOptions['value'] = $this->model->{$this->attribute};
			}
		}
		if ($this->model->{$this->attribute} == 0)
		{
			$this->htmlOptions['value'] = '';
		}

		if ($this->hasModel())
			echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);

		if ($this->useJqUi)
			$this->registerScripts($id);
	}

	public function registerScripts($id)
	{
		$cs = Yii::app()->clientScript;
		$cs->registerCoreScript('jquery');
		$cs->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.ui.core.js');
		$cs->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.ui.datepicker.js');
		$cs->registerCssFile(Yii::app()->baseUrl.'/styles/jquery.ui/jquery.ui.css');

		$options = array_filter(array_merge($this->defaultOptions, (array)$this->options));

		$newOptions = array();
		if (isset($this->options['minDate']))
		{
			$newOptions[] = CJavaScript::encode('minDate').':new Date('.date('Y', $this->options['minDate']).','.( date('n', $this->options['minDate']) -1 ).','.date('j', $this->options['minDate']).')';
			unset($options['minDate']);
		}
		if (isset($this->options['maxDate']))
		{
			$newOptions[] = CJavaScript::encode('maxDate').':new Date('.date('Y', $this->options['maxDate']).','.( date('n', $this->options['maxDate']) -1 ).','.date('j', $this->options['maxDate']).')';
			unset($options['maxDate']);
		}
		foreach($options as $k => $v)
			$newOptions[] = CJavaScript::encode($k).':'.CJavaScript::encode($v);

		Yii::app()->clientScript->registerScript(
			'DatePicker_'.$id,
			"$('#".$id."').datepicker({".implode(',', $newOptions)."});",
			CClientScript::POS_READY
		);
	}
}