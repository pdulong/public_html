<?php
class UserController extends Controller
{
	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_model;

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array('activate'),
				'users'=>array('*'),
			),
			array('allow',
				'actions'=>array('forgotpass'),
				'users'=>array('?')
			),
			array('allow',
				'actions'=>array('changepass'),
				'users'=>array('@'),
			),
			array('allow',
				'actions'=>array('create','update'),
				'roles'=>array('userAdmin'),
			),
			array('allow',
				'actions'=>array('view','admin','delete'),
				'roles'=>array('userAdmin'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Creates a new model.
	 */
	public function actionCreate()
	{
		$model=new User;
		$model->active=true;

		if(isset($_POST['User']))
		{
			$model->attributes=$_POST['User'];
			if($model->save())
				$this->redirect(array('/user/admin'));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Lets a user change their password
	 */
	public function actionChangePass()
	{
		$model = User::model()->findByPk( Yii::app()->user->id );
		$model->scenario = 'changepass';
		if (isset($_POST['User']))
		{
			$model->attributes = $_POST['User'];
			if ($model->validate())
			{
				$model->password = $model->new_password;
				$model->save(false,array('password'));
				Yii::app()->user->setFlash('password_changed', true);
				$this->redirect(array('/user/changepass'));
			}
		}

		$this->render('changepass', array(
			'model' => $model,
		));
	}

	/**
	 * Sends a use a new password in case they've forgotten what it was
	 */
	public function actionForgotPass()
	{
		$model = new ForgotPassForm;
		if (isset($_POST['ForgotPassForm']))
		{
			$model->attributes = $_POST['ForgotPassForm'];
			if ($model->validate())
			{
				$user = User::model()->find('LOWER(email)=?', array( strtolower($model->email) ));

				$newPass = $user->resetPassword();

				$Em = new Email;
				$Em->from = Yii::app()->params['email']['default::from'];
				$Em->subject = 'Uw nieuwe wachtwoord voor de website '.Yii::app()->name;
				$Em->setBody('forgotpass', array(
					'user' => $user,
					'newPass' => $newPass,
					'who' => 'op uw verzoek',
				));
				$Em->send($user->email);
				Yii::app()->user->setFlash('password_reset', true);
				$this->refresh();
			}
		}

		$this->render('forgotpass', array(
			'model' => $model,
		));
	}

	/**
	 * Updates a particular model.
	 */
	public function actionUpdate()
	{
		$model=$this->loadModel();

		if(isset($_POST['User']))
		{
			$model->attributes=$_POST['User'];
			if($model->save())
				$this->redirect(array('/user/admin'));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			$model=$this->loadModel();
			$model->delete();

			if(!isset($_GET['ajax']))
				$this->redirect(array('/user/admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Generates listData for the dropdowns on the create/update forms
	 */
	public static function getAvailableRolesListData()
	{
		$roles = array();
		if (Yii::app()->user->isGuest)
			return $roles;
		
		$allRoles = Yii::app()->authManager->getAllRoles();
		foreach($allRoles as $role)
		{
			$roles[$role->name] = ucfirst($role->description);
		}
		return $roles;
	}
	
	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new User('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['User']))
			$model->attributes=$_GET['User'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 */
	public function loadModel()
	{
		if($this->_model===null)
		{
			if(isset($_GET['id']))
				$this->_model=User::model()->findbyPk($_GET['id']);
			if($this->_model===null)
				throw new CHttpException(404,'The requested page does not exist.');
		}
		if (isset($this->_model) && isset($this->_model->authAssignment) && (!Yii::app()->user->checkAccess($this->_model->authAssignment->itemname) || $this->_model->id == Yii::app()->user->id))
			throw new CHttpException(404,'The requested page does not exist.');
		return $this->_model;
	}
}