<?php
class PageMemo extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ParticipantMemo the static model class
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
		return 'PageMemo';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('memoId,top,left,pageId', 'numerical', 'integerOnly'=>true),
			array('memoId,top,left,pageId', 'required'),
			array('respondent','length','max'=>255),
			array('comment','filter','filter'=>'strip_tags'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'memo'=>array(self::BELONGS_TO, 'Memo', 'memoId'),
		);
	}

	/**
	 * Respondent scope
	 */
	public function scopeRespondent($respondent, $pageId)
	{
		if (isset($respondent, $pageId))
		{
			$this->dbCriteria->mergeWith(new CDbCriteria(array(
				'condition'=>'respondent=:respondent AND pageId=:pageId',
				'params'=>array(
					':respondent'=>$respondent,
					':pageId'=>$pageId,
				),
			)));
		}
		return $this;
	}
}