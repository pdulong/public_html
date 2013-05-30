<?php
class SiteController extends Controller
{
	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		Yii::app()->clientScript->reset();
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
			{
				echo $error['message'];
			}
			else
			{
				if (defined('YII_DEBUG') && YII_DEBUG)
				{
					$this->layout=false;
					$this->render('/system/exception', array(
						'data'=>array_merge(
							$error,
							array(
								'time'=>time(),
								'version'=>'Yii '.Yii::getVersion()
							)
						)
					));
				}
				else if (in_array($error['code'], array(400,403,404,500,503)))
				{
					$this->render('/system/error'.$error['code'], array('data'=>$error));
				}
				else
				{
					$this->render('/system/error', array('data'=>$error));
				}
			}
		}
		else
		{
			$this->render('/system/error');
		}
	}

	/**
	 * The login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			if($model->validate() && $model->login())
			{
				Yii::app()->user->setState('key', null, null);
				$this->redirect(Yii::app()->user->returnUrl);
			}
		}

		Yii::app()->user->setState('key', Fn::getRandomString(32));
		$this->render('login',array(
			'model'=>$model
		));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}