<?php
class Letter extends CActiveRecord
{
	public $editorUsers;
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return Letter the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'Letter';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('description', 'length', 'max'=>255),
			array('description', 'required'),
			array('intro', 'filter', 'filter'=>array(new CHtmlPurifier, 'purify')),
			array('endDate', 'ext.validators.DateValidator'),
			array('editorUsers', 'safe'),
			array('description', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'pages'=>array(self::HAS_MANY, 'Page', 'letterId', 'order'=>'position ASC'),
			'numPages'=>array(self::STAT, 'Page', 'letterId'),
			'viewerUsers'=>array(self::MANY_MANY, 'User', 'UserLetterPermission(letterId,userId)'),
		);
	}

	/**
	 * Generate the PDF
	 */
	public function generatePdf()
	{
		Yii::import('ext.fpdf.*');
		$pdf=new LoyalisFPDF('P', 'pt', 'A4');
		$pdf->init();

		foreach($this->pages as $n=>$page)
		{
			$pdf->NewPage($page);
			foreach($page->memos as $m)
			{
				$memo=new stdClass;
				$memo->author=$m->respondent;
				$memo->left=$m->left;
				$memo->top=$m->top;
				$memo->comment=$m->comment;
				$memo->color=$m->memo->color;
				$memo->memo=$m->memo->description;
				$pdf->AddMemo($memo);
			}
		}
		$pdf->FlushMetaData();
		return $pdf;
	}
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'=>'ID',
			'src'=>'Src',
			'description'=>'Omschrijving',
			'use_memos'=>'Notities',
			'intro'=>'Introductie',
			'endDate'=>'Vervaldatum',
			'editorUsers'=>'Gebruikers'
		);
	}

	/**
	 * Before save: set the timestamp
	 */
	protected function beforeSave()
	{
		if (!parent::beforeSave()) return false;

		$this->endDate=Fn::beforeSaveTimestamp($this->endDate);
		return true;
	}

	/**
	 * After delete: remove all pages
	 */
	protected function afterDelete()
	{
		foreach($this->pages as $page)
			$page->delete();
		$permissions=UserLetterPermission::model()->findAll('letterId=:letterId', array(':letterId'=>$this->id));
		foreach($permissions as $permission)
			$permission->delete();
		
		parent::afterDelete();
	}
	
	/**
	 * Check if a user can view a letter
	 */
	public function isViewable($userId)
	{
		return Yii::app()->user->checkAccess('admin') || UserLetterPermission::model()->count('userId=:userId AND letterId=:letterId', array(':userId'=>$userId, ':letterId'=>$this->id)) > 0;
	}
	
	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		if (!Yii::app()->user->checkAccess('admin'))
		{
			$criteria=new CDbCriteria;
			$criteria->with=array(
				'viewableLetters'=>array(
					'condition'=>'description LIKE :description',
					'params'=>array(
						':description'=>'%'.$this->description.'%'
					)
				)
			);
			
			$criteria->compare('userId', Yii::app()->user->id);
			$user=User::model()->find($criteria);
			
			$models=array();
			if (null!==$user)
				$models=$user->viewableLetters;
			
			return new CArrayDataProvider($models, array(
				'sort'=>array(
					'attributes'=>array(
						'description',
						'endDate',
					),
					'defaultOrder'=>array(
						'endDate'=>false,
					),
					'separators'=>array(',','-'),
				),
			));
		}
		else
		{
			$criteria=new CDbCriteria;
			$criteria->compare('description',$this->description,true);
		
			return new CActiveDataProvider(get_class($this), array(
				'criteria'=>$criteria,
				'sort'=>array(
					'attributes'=>array(
						'description',
						'endDate',
					),
					'defaultOrder'=>array(
						'endDate'=>false,
					),
					'separators'=>array(',','-'),
				),
			));
		}
	}
}