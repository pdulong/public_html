<?php
class Controller extends CController
{
	public $layout='//layouts/main';
	public $breadcrumbs=array();
	public $robots;
	public $metaDesc;
	public $postfixPageTitle=true;

	/**
	 * Force a Friendly URL - ensure the slugs in the URL are correct
	 */
	protected function forceFUrl($model, $attribute=null, $getParamname=null, $return = false)
	{
		$rewrite = array();
		if (is_array($model))
		{
			foreach($model as $info)
			{
				list($_m, $_a) = $info;
				if (isset($info[2]))
					$_g=$info[2];
				else
					$_g=$_a;
				$rewrite = array_merge($rewrite, $this->forceFUrl($_m,$_a,$_g,true));
			}
		}
		else if (is_object($model))
		{
			$fUrl = Fn::fUrl($model->$attribute);
			$getParamname = Fn::first($getParamname,$attribute);
			if (!isset($_GET[$getParamname]) || $fUrl != $_GET[$getParamname])
				$rewrite[$getParamname] = $fUrl;
		}
		if ($return)
			return $rewrite;

		if ($rewrite !== array())
			$this->redirect(Yii::app()->urlManager->mergeGet($rewrite), true, 301);
	}

	/**
	 * Override of CController::redirect to go back to the last visited page
	 * when $url is null
	 */
	public function redirect($url,$terminate=true,$statusCode=302)
	{
		if (is_array($url) && ($pos=array_search('_ref', $url)))
		{
			unset($url[$pos]);
			if (isset($_GET['ref']) && ($refs=Yii::app()->user->getState('__refs')) && isset($refs[$_GET['ref']]))
				$this->redirect($refs[$_GET['ref']], $terminate, $statusCode);
		}
		parent::redirect($url, $terminate, $statusCode);
	}
}