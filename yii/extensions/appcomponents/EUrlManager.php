<?php
class EUrlManager extends CUrlManager
{
	/**
	 * Create an URL
	 */
	public function createUrl($route,$params=array(),$ampersand='&')
	{
		foreach($params as $k=>$v)
		{
			if ($v==='_ref')
			{
				unset($params[$k]);
				$currentRoute=Yii::app()->controller->getRoute();
				$currentRouteFull=array_merge(array(0=>$currentRoute), $_GET);
				$ref='';
				$refs=(array)Yii::app()->user->getState('__refs');
				foreach($refs as $k=>$v)
				{
					if ($v===$currentRouteFull)
					{
						$ref=$k;
						break;
					}
				}
				if ($ref==='')
				{
					$i=0;
					while (true)
					{
						$ref=Fn::getRandomString(6);
						if (!isset($refs[$ref]))
						{
							$refs[$ref]=$currentRouteFull;
							Yii::app()->user->setState('__refs', $refs);
							break;
						}
						if ($i==50)
						{
							Yii::log('Could not create unique _ref code', CLogger::LEVEL_ERROR, 'application.EUrlManager');
							throw new CHttpException(500, 'Could not create unique _ref code');
						}
						$i++;
					}
				}
				$params['ref']=$ref;
			}
			else if ($v===null)
			{
				Yii::log('Dropping URL parameter '.$k.' because it is null.', CLogger::LEVEL_INFO, 'application.EUrlManager');
				unset($params[$k]);
			}
		}

		return 'https://'.$_SERVER['HTTP_HOST'].parent::createUrl($route,$params,$ampersand);
	}

	/**
	 * Get a route of the current route with one or more GET variables changed / added
	 */
	public function mergeGet($get)
	{
		return array_merge(array(Yii::app()->controller->getRoute()), $_GET, $get);
	}
}