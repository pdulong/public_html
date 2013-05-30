<?php
class Farbtastic extends CInputWidget
{
	public $options;

	public function init()
	{
	}

	public function run()
	{
		list($name,$id)=$this->resolveNameID();
		if(!isset($this->htmlOptions['id']))
			$this->htmlOptions['id']=$id;
		if(!isset($this->htmlOptions['name']))
			$this->htmlOptions['name']=$name;

		if (!isset($this->htmlOptions['value']) && empty($this->model->{$this->attribute}))
			$this->htmlOptions['value']='#ff0000';
		
		if ($this->hasModel())
		{
			echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
			echo CHtml::tag('div', array('id'=>'Farbtastic_'.$id));
		}

		$this->registerScripts($id);
	}

	public function registerScripts($id)
	{
		$cs = Yii::app()->clientScript;
		$cs->registerCoreScript('jquery');
		$publishPath=Yii::app()->assetManager->publish(dirname(__FILE__).'/assets/farbtastic/');
		$cs->registerScriptFile($publishPath.'/farbtastic.js');
		$cs->registerCssFile($publishPath.'/farbtastic.css');

		Yii::app()->clientScript->registerScript(
			'Farbtastic_'.$id,
			"jQuery.farbtastic('#Farbtastic_".$id."').linkTo('#$id');",
			CClientScript::POS_READY
		);
	}
}