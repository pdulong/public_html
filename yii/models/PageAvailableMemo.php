<?php
class PageAvailableMemo extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return PageAvailableMemo the static model class
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
		return 'PageAvailableMemo';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('pageId, memoId', 'required'),
			array('pageId, memoId', 'numerical', 'integerOnly'=>true),
		);
	}

	public function relations()
	{
		return array(
			'memo'=>array(self::BELONGS_TO, 'Memo', 'memoId'),
			'page'=>array(self::BELONGS_TO, 'Page', 'pageId'),
		);
	}
}