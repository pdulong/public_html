<?php
class PageMemoController extends Controller
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
				'actions'=>array('create','delete'),
				'users'=>array('*'),
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
		if(Yii::app()->request->isPostRequest)
		{
			if (
				!isset($_POST['top'],$_POST['left'],$_POST['memoId'],$_POST['pageId'])
				|| !is_numeric($_POST['left'])
				|| !is_numeric($_POST['top'])
				|| !is_numeric($_POST['memoId'])
				|| !is_numeric($_POST['pageId'])
			)
			{
				echo CJSON::encode(array('result'=>-1));
				Yii::app()->end();
			}

			if (!isset($_POST['comment']))
				$_POST['comment']='';

			/*if (isset($_POST['respondent']) && $_POST['respondent'] != 'anoniem')
			{
				// prevent cheating
				if (PageMemo::model()->countByAttributes(array('respondent'=>$_POST['respondent'], 'memoId'=>$_POST['memoId'], 'pageId'=>$_POST['pageId'])) > 0)
				{
					echo CJSON::encode(array('result'=>-2));
					Yii::app()->end();
				}
			}*/

			$page=Page::model()->findByPk($_POST['pageId'], array('select'=>'id','with'=>'availableMemos','together'=>true));
			if ($page===null)
			{
				echo CJSON::encode(array('result'=>-3));
				Yii::app()->end();
			}
			$found=false;
			foreach($page->availableMemos as $m)
			{
				if ($m->id==$_POST['memoId'])
				{
					$found=true;
					break;
				}
			}
			if (!$found)
			{
				echo CJSON::encode(array('result'=>-4));
				Yii::app()->end();
			}

			$model=new PageMemo;

			$model->top=$_POST['top'];
			$model->left=$_POST['left'];
			$model->comment=strip_tags($_POST['comment']);
			$model->memoId=$_POST['memoId'];
			$model->respondent=$_POST['respondent'];
			$model->pageId=$page->id;
			if($model->save())
			{
				echo CJSON::encode(array('result'=>1,'id'=>$model->id));
				Yii::app()->end();
			}
			else
			{
				echo CJSON::encode(array('result'=>-5));
				Yii::app()->end();
			}
		}
		else
		{
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
		}
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
			if (!Yii::app()->user->checkAccess('admin'))
			{
				if (!isset($_POST['respondent']))
				{
					echo CJSON::encode(array('result'=>-1));
					Yii::app()->end();
				}
				$model=PageMemo::model()->findByAttributes(array('id'=>$_GET['id'], 'respondent'=>$_POST['respondent']), array('select'=>array('id','memoId')));
				if ($model===null)
				{
					echo CJSON::encode(array('result'=>-2));
					Yii::app()->end();
				}
			}
			else
			{
				$model=$this->loadModel();
			}

			$model->delete();

			echo CJSON::encode(array('result'=>1,'memoId'=>$model->memoId));
			Yii::app()->end();
		}
		elseif(Yii::app()->request->isPostRequest)
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
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
				$this->_model=PageMemo::model()->findbyPk($_GET['id']);
			if($this->_model===null)
				throw new CHttpException(404,'The requested page does not exist.');
		}
		return $this->_model;
	}
}
