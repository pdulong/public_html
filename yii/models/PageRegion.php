<?php
class PageRegion extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return LetterRegion the static model class
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
		return 'PageRegion';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('pageId,top,left,width,height', 'numerical', 'integerOnly'=>true),
			array('pageId,top,left,width,height', 'required'),
		);
	}
	
	/**
	 * AR Relations
	 */
	public function relations()
	{
		return array(
			'page'=>array(self::BELONGS_TO, 'Page', 'pageId', 'with'=>array('memos'=>array('with'=>'memo'))),
		);
	}
	
	/**
	 * Return a list of all memos in this region
	 * counted by type
	 *  array(text on memo => count)
	 */
	public function getMemoDistribution()
	{
		$distribution=array();
		foreach($this->page->memos as $i=>$memo)
		{
			$mLeft=$memo->left+65;
			$mTop=$memo->top+15;
			
			$mDesc=preg_replace('~\s{2,}~', ' ', str_replace(array("\n", "\r"), ' ', $memo->memo->description));
			
			if ($mLeft < $this->left || $mLeft > $this->left + $this->width)
				continue;
			
			if ($mTop < $this->top || $mTop > $this->top + $this->height)
				continue;
			
			isset($distribution[$mDesc]) || $distribution[$mDesc]=0;
			$distribution[$mDesc]=$distribution[$mDesc]+1;
		}
		ksort($distribution);
		return $distribution;
	}
}