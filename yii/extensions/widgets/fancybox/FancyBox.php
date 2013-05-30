<?php
class FancyBox extends CWidget
{
	public $options=array();
	private $defaultOptions=array(
		'imageScale'=>true,
		'transitionIn'=>'elastic',
		'transitionOut'=>'elastic',
		'speedIn'=>400,
		'speedOut'=>400,
		'hideOnContentClick'=>true
	);
	public $target;
	public $cssFile=null;

	public function init()
	{
		$assetDir=Yii::app()->assetManager->publish(dirname(__FILE__).'/assets');
		$cs=Yii::app()->clientScript;
		$cs->registerCoreScript('jquery');
		$cs->registerScriptFile($assetDir.'/jquery.fancybox-1.3.1.js');
		if (!is_null($this->cssFile))
			$cs->registerCSSFile($this->cssFile);
		else
			$cs->registerCSSFile($assetDir.'/jquery.fancybox-1.3.1.css');
	}
	
	public function run()
	{
		$options=CJavaScript::encode(array_merge($this->defaultOptions, $this->options));
		Yii::app()->clientScript->registerScript(
			'FancyBox.init.'.md5($this->target),
			'$("'.$this->target.'").fancybox('.$options.');',
			CClientScript::POS_READY
		);
	}
}