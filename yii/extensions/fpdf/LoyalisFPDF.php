<?php
class LoyalisFPDF extends MyFPDF
{
	private $_memos=array();
	private $_memoscounter=0;
	private $regions;

	public function __construct($orientation='P', $unit='mm', $format='A4')
	{
		parent::__construct($orientation, $unit, $format);
	}

	public function init()
	{
		defined('FPDF_FONTPATH') or define('FPDF_FONTPATH', Yii::getPathOfAlias('ext.fpdf.fonts').DIRECTORY_SEPARATOR);

		$this->AddFont('arial');
		$this->SetMargins(10, 10, 10);
	}

	public function newPage($page)
	{
		$orientation=$page->isLandscape ? 'L' : 'P';
		$this->AddPage($orientation, 'A4');
		$this->SetAlpha(0.5);
		$this->Image(Yii::app()->runtimePath.'/pages/'.$page->id.'/'.$page->filename, 10, 10, $page->width, $page->height);
		$this->regions=$page->regions;
		
		$this->SetFont('arial', '', 16);
		$this->setDrawColor(255, 0, 0);
		$this->setTextColor(255, 0, 0);
		foreach($this->regions as $n=>$region)
		{
			$this->Rect($region->left+10, $region->top+10, $region->width, $region->height, 20, 'B');
			$this->SetXY($region->left+20, $region->top+20);
			$this->Write(0, ((string)$n+1));
		}
	}
	
	public function AddMemo($memo)
	{
		$this->_memos[++$this->_memoscounter] = $memo;
		$num=(string)$this->_memoscounter;

		$this->SetFont('arial', '', 16);
		
		$this->SetAlpha(0.2);
		$r=hexdec(substr($memo->color, 1, 2));
		$g=hexdec(substr($memo->color, 3, 2));
		$b=hexdec(substr($memo->color, 5, 2));
		$this->SetFillColor($r,$g,$b);

		// Draw rectangle. The +10 is to adjust for the margins
		$this->Rect($memo->left+75, $memo->top+20, max(20, $this->GetStringWidth($num)+10), 20, 'F');

		$this->SetAlpha(1);
		$this->SetTextColor(255-$r,255-$g,255-$b);
		$this->setXY($memo->left+77, $memo->top+30);
		
		// put a number in the rectangle
		$this->Cell(100, 0, $num);
	}

	private function RenderMemo($i, $memo)
	{
		$this->SetFont('arial', '', 10);
		
		$memoText=preg_replace('~\s{2,}~', ' ', str_replace(array("\r\n", "\n\r", "\r"), ' ', $memo->memo));
		$this->Cell(0, 13, $i.'.   '.$memo->author.': '.$memoText, 'B', 2);

		$this->SetFont('arial', '', 12);
		if ($memo->comment != '')
			$this->MultiCell(0, 13, $memo->comment);
		else
			$this->MultiCell(0, 13, '-- geen toelichting --');

		$this->Ln();
	}

	public function FlushMetaData()
	{
		$this->SetMargins(30, 30, 30);
		$this->AddPage('P', 'A4');
		$this->SetTextColor(0,0,0);
		$this->SetDrawColor(150, 150, 150);
		
		$this->SetFont('arial', '', 10);
		foreach($this->regions as $n=>$region)
		{
			$this->Cell(0, 13, 'Regio '.((string)$n+1), 'B', 2);
			foreach($region->memoDistribution as $text=>$num)
			{
				$this->Cell(0, 13, $text.' : '.$num, '', 2);
			}
			$this->Ln();
		}
		
		foreach($this->_memos as $i=>$memo)
		{
			$this->StartTransaction();
			$page=$this->PageNo();
			$this->RenderMemo($i, $memo);
			if ($this->PageNo() > $page)
			{
				$this->RollbackTransaction();
				$this->AddPage();
				$this->RenderMemo($i, $memo);
			}
			$this->EndTransaction();
		}
		$this->_memos=array();
	}
}