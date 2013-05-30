<?php
class LetterController extends Controller
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
				'actions'=>array('fill'),
				'users'=>array('*'),
			),
			array('allow',
				'actions'=>array('admin','view','downloadPdf'),
				'roles'=>array('viewer'),
			),
			array('allow',
				'actions'=>array('create','update','delete'),
				'roles'=>array('admin'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 */
	public function actionView()
	{
		$model=Letter::model()->findByPk($_GET['id'], array(
			'with'=>array(
				'pages'=>array('select'=>array('id','filename','position','width','height')),
				'pages.availableMemos',
				'pages.memos'=>array('select'=>array('respondent','top','left','comment')),
				'pages.memos.memo'=>array('select'=>array('color','description'))
			),
			'together'=>true,
		));

		if ($model===null)
			throw new CHttpException(404, 'Pagina niet gevonden');

		if (!$model->isViewable(Yii::app()->user->id))
			throw new CHttpException(403, 'U heeft onvoldoende rechten deze pagina te bekijken.');

		$this->forceFUrl($model, 'description');

		if (isset($_GET['page']) && isset($model->pages[$_GET['page']-1]))
			$page=$model->pages[$_GET['page']-1];
		else
			throw new CHttpException(404, 'Pagina niet gevonden.');

		$usedMemos=array();
		foreach($page->memos as $m)
		{
			$usedMemos[$m->memo->id]=true;
		}
		$usedMemos=array_keys($usedMemos);

		//echo '<pre>'; print_r($page->memos); echo '</pre>';

		$this->render('view',array(
			'model'=>$page,
			'letter'=>$model,
			'usedMemos'=>$usedMemos,
		));
	}

	/**
	 * Download a PDF report
	 */
	public function actionDownloadPdf()
	{
		if (!isset($_GET['page']) || !ctype_digit($_GET['page']))
			throw new CHttpException(404, 'De opgevraagde pagina is niet gevonden');

		$criteria=new CDbCriteria(array(
			'with'=>array(
				'pages'=>array('select'=>array('id','filename','width','height')),
				'pages.memos'=>array('select'=>array('id','respondent','comment','top','left')),
				'pages.memos.memo'=>array('select'=>array('color','description')),
			),
			'together'=>true,
			'order'=>'memos.top ASC, memos.left ASC',
			'condition'=>'position=:position',
			'params'=>array(':position'=>$_GET['page']),
		));

		$condition='';
		$params=array();
		if (isset($_GET['m']))
		{
			foreach($_GET['m'] as $i=>$id)
			{
				if (!ctype_digit($id))
					unset($_GET['m']);
			}
			if (count($_GET['m']) > 0)
			{
				$criteria->addInCondition('memos.memoId', $_GET['m']);
			}
		}

		$model=Letter::model()->findByPk($_GET['id'], $criteria);

		if (!$model->isViewable(Yii::app()->user->id))
			throw new CHttpException(403, 'U heeft onvoldoende rechten deze pagina te bekijken.');

		if ($model===null)
			throw new CHttpException(404, 'Pagina niet gevonden');

		$pdf=$model->generatePdf();
		$pdf->Output(Fn::friendlyFilename(date('Ymd').'-rapport '.$model->description.'-pagina-'.$_GET['page'].'.pdf'), 'D');
	}

	/**
	 * Displays a particular model.
	 */
	public function actionFill()
	{
		$model=$this->loadModel();
		$this->forceFUrl($model, 'description');

		if ($model->endDate !='' && $model->endDate < time())
			throw new CHttpException(404, 'De URL voor deze brief is verlopen.');

		if (isset($_GET['do']))
		{
			if ($_GET['do'] == 'outro')
			{
				$this->render('fillOutro', array(
					'model'=>$model,
				));
			}
			else
			{
				throw new CHttpException(404, 'Onbekende aanvraag: ?do='.$_GET['do']);
			}
		}
		else
		{
			if (!isset($_GET['page']))
			{
				if (isset($model->intro) && $model->intro != '')
				{
					$this->render('fillIntro', array(
						'model'=>$model,
					));
				}
				else
				{
					$this->redirect(array('/letter/fill', 'id'=>$model->id, 'description'=>Fn::fUrl($model->description), 'page'=>1));
				}
			}
			else if (!ctype_digit($_GET['page']))
			{
				throw new CHttpException(404, 'De opgevraagde pagina is niet gevonden');
			}
			else
			{
				if (isset($model->pages[$_GET['page']-1]))
					$page=$model->pages[$_GET['page']-1];
				else
					throw new CHttpException(404, 'Pagina niet gevonden');

				$memos=array();
				if (isset($_GET['respondent']))
					$memos=PageMemo::model()->scopeRespondent($_GET['respondent'], $page->id)->findAll();

				$this->render('fill',array(
					'model'=>$page,
					'letter'=>$model,
					'memos'=>$memos,
				));
			}
		}
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Letter;

		if(isset($_POST['Letter']))
		{
			$model->attributes=$_POST['Letter'];
			$model->editorUsers=$_POST['Letter']['editorUsers'];

			if($model->save())
			{
				if (isset($model->editorUsers) && is_array($model->editorUsers))
				{
					foreach($_POST['Letter']['editorUsers'] as $userId)
					{
						$permission=new UserLetterPermission;
						$permission->userId=$userId;
						$permission->letterId=$model->id;
						$permission->save();
					}
				}
				$this->redirect(array('/page/admin', '_ref', 'letterId'=>$model->id));
			}
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionUpdate()
	{
		$model=$this->loadModel();
		$this->forceFUrl($model, 'description');

		if(isset($_POST['Letter']))
		{
			$model->attributes=$_POST['Letter'];
			$model->editorUsers=$_POST['Letter']['editorUsers'];

			if($model->save())
			{
				$permissions=UserLetterPermission::model()->findAll('letterId=:letterId', array(':letterId'=>$model->id));
				foreach($permissions as $permission)
					$permission->delete();

				if (isset($model->editorUsers) && is_array($model->editorUsers))
				{
					foreach($_POST['Letter']['editorUsers'] as $userId)
					{
						$permission=new UserLetterPermission;
						$permission->userId=$userId;
						$permission->letterId=$model->id;
						$permission->save();
					}
				}
				$this->redirect(array('/letter/admin'));
			}
		}

		foreach($model->viewerUsers as $user)
		{
			$model->editorUsers[]=$user->id;
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel()->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(array('/letter/admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Letter('search');
		$model->unsetAttributes();
		if(isset($_GET['Letter']))
			$model->attributes=$_GET['Letter'];

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
				$this->_model=Letter::model()->findbyPk($_GET['id']);
			if($this->_model===null)
				throw new CHttpException(404,'The requested page does not exist.');
		}
		return $this->_model;
	}
}
