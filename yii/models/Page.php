<?php
class Page extends CActiveRecord
{
	public $use_memos;
	public $ask_comments;
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return LetterPage the static model class
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
		return 'Page';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('use_memos', 'safe'),
			array('ask_comments', 'safe'),
			array('filename', 'ext.validators.EPhotoValidator',
				'mimeType'=>array('image/jpeg','image/gif','image/png'),
				'infoError'=>'Fout bij lezen {attribute}. Het bestand is waarschijnlijk geen -of een corrupte- afbeelding.',
				'mimeTypeError'=>'{attribute} is een ongeldig bestandstype. Alleen jpeg, gif en png bestanden zijn toegestaan.',
				'allowEmpty'=>false,
				'on'=>'insert'
			),
			array('filename', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'letter'=>array(self::BELONGS_TO, 'Letter', 'letterId'),
			'regions'=>array(self::HAS_MANY, 'PageRegion', 'pageId', 'order'=>'`regions`.`top` ASC, `regions`.`left` ASC'),
			'memos'=>array(self::HAS_MANY, 'PageMemo', 'pageId'),
			'availableMemos'=>array(self::MANY_MANY, 'Memo', 'PageAvailableMemo(pageId,memoId)'),
			'availableMemosRaw'=>array(self::HAS_MANY, 'PageAvailableMemo', 'pageId', 'with'=>'memo'),
			'numAvailableMemos'=>array(self::STAT, 'PageAvailableMemo', 'pageId'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'=>'ID',
			'letterId'=>'Brief',
			'filename'=>'Bestand',
			'use_memos'=>'Beschikbare notities',
			'position'=>'Pagina',
		);
	}

	/**
	 * After delete: delete availability and files
	 */
	protected function afterDelete()
	{
		foreach($this->regions as $region)
			$region->delete();

		foreach($this->memos as $memo)
			$memo->delete();

		PageAvailableMemo::model()->deleteAll('pageId=:pageId', array(':pageId'=>$this->id));

		$file=Yii::app()->runtimePath.'/pages/'.$this->id.'/'.$this->filename;
		if (file_exists($asset=Yii::app()->assetManager->getPublishedPath($file)))
		{
			@unlink($asset);
		}

		Fn::rmdir_recursive(Yii::app()->runtimePath.'/pages/'.$this->id);

		parent::afterDelete();
	}

	/**
	 * Is this page in landscape orientation?
	 */
	public function getIsLandscape()
	{
		return $this->width > $this->height;
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;
		$criteria->compare('filename',$this->filename,true);
		$criteria->compare('letterId',$this->letterId);
		
		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
			'sort'=>array(
				'attributes'=>array(
					'filename',
					'position',
				),
				'defaultOrder'=>array(
					'position'=>false,
				),
				'separators'=>array(',','-'),
			),
		));
	}
}