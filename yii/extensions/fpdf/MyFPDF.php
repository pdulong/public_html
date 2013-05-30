<?php
/*
 * Class extends FPDF to provide rollback functionality
 */
class MyFPDF extends AlphaFPDF
{
	protected $____globalvars;
	
	public function __construct($orientation='P', $unit='mm', $format='A4')
	{
		parent::__construct($orientation, $unit, $format);
	}

	protected function StartTransaction()
	{
		$this->____globalvars = array();
		foreach($this as $k => $v)
		{
			if (!method_exists($this, $k) && $k != '____globalvars')
			{
				$this->____globalvars[$k] = $v;
			}
		}
	}

	protected function EndTransaction()
	{
		unset($this->____globalvars);
	}

	protected function RollbackTransaction()
	{
		foreach ($this->____globalvars as $k => $v)
		{
			$this->{$k} = $v;
		}
		$this->EndTransaction();
	}
}