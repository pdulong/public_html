<?php
class LangSwitcher extends CWidget
{
	public function init()
	{
		Yii::app()->clientScript->registerCssFile(CHtml::asset(dirname(__FILE__).'/assets/langswitcher.css'));
	}
	
	public function run()
	{
		if (!Yii::app()->langHandler->multilangEnabled || Yii::app()->langHandler->getAdditionalLanguageCodes() === array())
			return;
		$languages = Yii::app()->langHandler->languages;
		$primaryLanguageCode = Yii::app()->langHandler->getPrimaryLanguageCode();
		echo CHtml::openTag('div', array('id'=>'langswitcher'));
		
		foreach($languages as $code => $label)
		{
			if (method_exists(Yii::app()->controller, 'createLanguageUrl'))
				$newRoute=Yii::app()->controller->createLanguageUrl($code);
			else
				$newRoute = Yii::app()->urlManager->mergeGet(array('lang'=>$code));

			$image = CHtml::asset(dirname(__FILE__).'/assets/'.$code.'.gif');
			if (Yii::app()->getLanguage() == $code)
			{
				echo CHtml::image($image, $label, array('title' => $label, 'class'=>'current'));
			}
			else
			{
				echo CHtml::link(CHtml::image($image, $label, array('title' => $label, 'class'=>'faded')), $newRoute);
			}
		}
		echo CHtml::closeTag('div');
		Yii::app()->clientScript->registerScript(
			'LangSwitcher.init',
			'$("#langswitcher img:not(.current)").each( function() { $(this).hover(function() { $(this).toggleClass("hl faded"); }); });',
			CClientScript::POS_READY
		);
	}
}