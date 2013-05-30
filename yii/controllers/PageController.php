<?php
class PageController extends Controller
{
	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_model;
	private $_letter;

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
				'actions'=>array('create','update','admin','delete'),
				'roles'=>array('admin'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$letter=$this->loadLetter();
		$model=new Page;
		$model->letterId=$letter->id;

		if(isset($_POST['Page']))
		{
			$model->attributes=$_POST['Page'];
			$model->attachBehavior('sorting', array(
				'class'=>'ext.behaviors.ar.Sorting',
				'dependentOn'=>'letterId',
			));

			if($model->save())
			{
				$file=CUploadedFile::getInstance($model, 'filename');
				@mkdir(Yii::app()->runtimePath.'/pages/'.$model->id);
				$filename=Fn::friendlyFilename($file->getName());

				$gd=new GDImage($file->getTempName());
				if ($gd->getWidth() == $gd->getHeight())
				{
					// Square
					$gd->process(array(array('action'=>'resize','params'=>array(575,575,'fill'))));
					$model->width=575;
					$model->height=575;
				}
				else if ($gd->getWidth() < $gd->getHeight())
				{
					// Portrait
					$gd->process(array(array('action'=>'resize','params'=>array(575,822,'fill'))));
					$model->width=575;
					$model->height=822;
				}
				else if ($gd->getWidth() > $gd->getHeight())
				{
					// Landscape
					$gd->process(array(array('action'=>'resize','params'=>array(822,575,'fill'))));
					$model->width=822;
					$model->height=575;
				}

				$gd->save(Yii::app()->runtimePath.'/pages/'.$model->id.'/'.$filename);
				$model->filename=$filename;

				$model->save(false, array('filename','width','height'));
				$this->redirect(array('/page/update', 'id'=>$model->id));
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

		if(isset($_POST['Page']))
		{
			$model->attributes=$_POST['Page'];
			if (!is_array($model->use_memos) || count($model->use_memos) == 0)
				$model->addError('num_memos', 'U dient ten minste 1 notitie type te selecteren');
			
			if (!isset($_POST['Region']))
				$model->addError('num_memos', 'U heeft geen enkele paragraaf definieerd.');

			if(!$model->hasErrors() && $model->save())
			{
				/**
				 * Handle adding and removing available memo types
				 */
				$model->use_memos=(array)$model->use_memos;
				$model->ask_comments=(array)$model->ask_comments;
				$currentMemos=$model->availableMemosRaw;
				foreach($currentMemos as $memo)
				{
					$memoId=$memo->memoId;
					if (($pos=array_search($memoId, $model->use_memos)) !== false)
					{
						// memo was selected and is again selected. do nothing.
						// But we do need to make sure not to add it either (since it's already there ...)
						unset($model->use_memos[$pos]);

						// However, check if askComment is still the same
						if (in_array($memoId, $model->ask_comments) != $memo->askComment)
						{
							$memo->askComment=in_array($memoId, $model->ask_comments);
							$memo->save(false,array('askComment'));
						}
					}
					else
					{
						// memo was selected but not anymore. Remove all references to it
						PageAvailableMemo::model()->deleteAll('pageId=:pageId AND memoId=:memoId', array(':pageId'=>$model->id, ':memoId'=>$memoId));
						PageMemo::model()->deleteAll('pageId=:pageId AND memoId=:memoId', array(':pageId'=>$model->id, ':memoId'=>$memoId));
					}
				}
				
				foreach($model->use_memos as $memoId)
				{
					// memo wasn't selected but is selected now. add it.
					$lm=new PageAvailableMemo;
					$lm->memoId=$memoId;
					$lm->pageId=$model->id;
					$lm->askComment=in_array($memoId, $model->ask_comments);
					$lm->save();
				}
				
				/**
				 * Handle the regions
				 */
				PageRegion::model()->deleteAll('pageId=:pageId', array(':pageId'=>$model->id));
				if (isset($_POST['Region']))
				{
					foreach((array)$_POST['Region'] as $region)
					{
						$region=array_map('round', array_map('trim', explode(',', $region)));
						if (count($region)==4)
						{
							$regionModel=new PageRegion;
							$regionModel->pageId=$model->id;
							list($regionModel->left, $regionModel->top, $regionModel->width, $regionModel->height) = $region;
							$regionModel->save();
						}
					}
				}

				$this->redirect(array('/page/admin', 'letterId'=>$model->letter->id));
			}
		}

		if (count($model->availableMemos) == 0)
		{
			$memos=Memo::model()->findAll();
			foreach($memos as $m)
			{
				$model->use_memos[]=$m->id;
			}
		}
		else
		{
			$memos=$model->availableMemos;
			foreach($memos as $m)
			{
				$model->use_memos[]=$m->id;
			}
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
			$model=$this->loadModel();
			$model->attachBehavior('sorting', array(
				'class'=>'ext.behaviors.ar.Sorting',
				'dependentOn'=>'letterId',
			));
			$model->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(array('/page/admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$letter=$this->loadLetter();
		$model=new Page('search');
		$model->unsetAttributes();
		$model->letterId=$letter->id;
		if(isset($_GET['Page']))
			$model->attributes=$_GET['Page'];

		$this->render('admin',array(
			'model'=>$model,
			'letter'=>$letter,
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
				$this->_model=Page::model()->findbyPk($_GET['id']);
			if($this->_model===null)
				throw new CHttpException(404,'The requested page does not exist.');
		}
		return $this->_model;
	}

	/**
	 * Returns the letter model based on the primary key given in the GET variable.
	 * If the letter model is not found, an HTTP exception will be raised.
	 */
	public function loadLetter()
	{
		if($this->_letter===null)
		{
			if(isset($_GET['letterId']))
				$this->_letter=Letter::model()->findbyPk($_GET['letterId']);
			if($this->_letter===null)
				throw new CHttpException(404,'The requested page does not exist.');
		}
		return $this->_letter;
	}
}