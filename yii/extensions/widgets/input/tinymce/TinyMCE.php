<?php
class TinyMCE extends CInputWidget
{
	public $model;
	public $attribute;
	public $options;
	public $width=400;
	public $height=300;
	public $rows=10;
	public $cols=20;
	public $allowImages=false;

	private $defaultOptions=array(
		'theme'=>'advanced',
		'plugins'=>'paste,fullscreen,inlinepopups,embed',
		'convert_urls'=>false,
		'theme_advanced_buttons1'=>'pastetext,pasteword,|,bold,italic,|,undo,redo,|,link,unlink,|,fullscreen,help',
		'theme_advanced_buttons2'=>'',
		'theme_advanced_buttons3'=>'',
	);

	public function init()
	{
		if (Yii::app()->user->checkAccess('admin'))
		{
			$this->options['theme_advanced_buttons1']='pastetext,pasteword,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull';
			$this->options['theme_advanced_buttons2']='bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,anchor,embed,'.($this->allowImages?'image,':'').'|,cleanup,code';
			$this->options['theme_advanced_buttons3']='removeformat,|,sup,sub,|,charmap,|,fullscreen,help';
			if ($this->allowImages)
			{
				$this->options['file_browser_callback']='openImgChooser';
				Yii::app()->clientScript->registerScriptFile( CHtml::asset(Yii::getPathOfAlias('application.modules.ImageChooser.assets')).'/scripts/icinput.js');
			}
		}

		$assetDir=Yii::app()->assetManager->publish(dirname(__FILE__).'/assets');

		$cs=Yii::app()->clientScript;
		$cs->registerCoreScript('jquery');
		$cs->registerScriptFile($assetDir.'/jquery.tinymce.js');
		$this->defaultOptions['script_url']=$assetDir.'/tiny_mce.js';
	}

	public function run()
	{
		if (ctype_digit($this->width)) $this->width .= 'px';
		if (ctype_digit($this->height)) $this->height .= 'px';

		echo CHtml::activeTextArea($this->model, $this->attribute, array(
			'style'=>'width:'.$this->width.';height:'.$this->height.';',
			'rows'=>$this->rows,'cols'=>$this->cols
		));
		$fieldId = CHtml::activeId($this->model, $this->attribute);
		$options = CJavaScript::encode(array_merge($this->defaultOptions, (array)$this->options));
		Yii::app()->clientScript->registerScript('TinyMce.init.'.$fieldId, "\$('#{$fieldId}').tinymce($options);", CClientScript::POS_READY);
	}
}