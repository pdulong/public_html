<?php
class Sorting extends CActiveRecordBehavior
{
	public $dependentOn=array();
	public $attribute='position';
	public $trigger;
	private $origTriggerValues;
	private $origDependentOnValues;

	/**
	 * Get the original value(s) of the trigger(s) after a record has been found
	 */
	public function afterFind($event)
	{
		$triggers = (array)$this->trigger;
		if (count($triggers) == 0) return;
		if (!$this->Owner->isNewRecord)
		{
			foreach($triggers as $trigger)
			{
				$this->origTriggerValues[$trigger] = $this->Owner->{$trigger};
			}
			foreach((array)$this->dependentOn as $depCol)
			{
				$this->origDependentOnValues[$depCol] = $this->Owner->{$depCol};
			}
		}
	}

	/**
	 * Determine position of new element before saving a new record
	 */
	public function beforeSave($event)
	{
		$triggers = (array)$this->trigger;
		if ($this->Owner->isNewRecord)
		{
			foreach($triggers as $trigger)
			{
				if (!$this->Owner->{$trigger})
				{
					$this->Owner->{$this->attribute} = null;
					return;
				}
			}
		}
		else
		{
			if (count($triggers) == 0) return;
			foreach($triggers as $trigger)
			{
				if ($this->Owner->{$trigger} == $this->origTriggerValues[$trigger]) return;
				if (!$this->Owner->{$trigger} && $this->origTriggerValues[$trigger])
				{
					$this->closeGaps(true);
					$this->Owner->{$this->attribute} = null;
					return;
				}
			}
		}
		$condition = array();
		$params = array();
		foreach((array)$this->dependentOn as $depCol)
		{
			if (is_null($this->Owner->{$depCol}))
			{
				$condition[] = $depCol.' IS NULL';
			}
			else
			{
				$condition[] = $depCol.'=:'.$depCol;
				$params[$depCol] = $this->Owner->{$depCol};
			}
		}
		$condition = implode(' AND ', $condition);
		$db = Yii::app()->db;
		$command = $db->createCommand(
			'SELECT '.$db->quoteColumnName($this->attribute).
			' FROM '.$db->quoteTableName($this->Owner->tableName()).
			($condition?' WHERE '.$condition:'').
			' ORDER BY '.$this->attribute.' DESC'.
			' LIMIT 1'
		);
		foreach($params as $col => $param)
			$command->bindParam($col, $param);
		$row = $command->query()->read();
		$this->Owner->{$this->attribute} = $row[$this->attribute] + 1;
	}

	/*
	 * Close gaps in position column after deletion of a record
	 */
	public function afterDelete($event)
	{
		$triggers = (array)$this->trigger;
		foreach($triggers as $trigger)
		{
			if (!$this->Owner->{$trigger}) return;
		}
		$this->closeGaps();
	}

	/**
	 * Close gaps in the position column
	 */
	private function closeGaps($useOriginalValues = false)
	{
		$condition = array();
		$params = array();
		foreach((array)$this->dependentOn as $depCol)
		{
			if ($useOriginalValues)
			{
				if (is_null($this->origDependentOnValues[$depCol]))
				{
					$condition[] = $depCol.' IS NULL';
				}
				else
				{
					$condition[] = $depCol.'=:'.$depCol;
					$params[$depCol] = $this->origDependentOnValues[$depCol];
				}
			}
			else
			{
				if (is_null($this->Owner->{$depCol}))
				{
					$condition[] = $depCol.' IS NULL';
				}
				else
				{
					$condition[] = $depCol.'=:'.$depCol;
					$params[$depCol] = $this->Owner->{$depCol};
				}
			}
		}
		$condition = implode(' AND ', $condition);
		$db = Yii::app()->db;
		$command = $db->createCommand(
			'UPDATE '.$db->quoteTableName($this->Owner->tableName()).
			' SET '.$db->quoteColumnName($this->attribute).'='.$db->quoteColumnName($this->attribute).'-1'.
			' WHERE '.$db->quoteColumnName($this->attribute).' > :'.$this->attribute.($condition?' AND '.$condition:'')
		);
		foreach($params as $col => $param)
			$command->bindParam($col, $param);
		$value=$this->Owner->{$this->attribute};
		$command->bindParam($this->attribute, $value);
		$command->execute();
	}
}