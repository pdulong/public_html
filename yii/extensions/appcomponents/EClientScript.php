<?php
class EClientScript extends CClientScript
{
	public $combineJs = true;
	public $combineCss = true;
	protected $coreCssFiles = array();

	/**
	 * Render the head of the HTML
	 */
	public function renderHead(&$output)
	{
		if ($this->combineJs && isset($this->scriptFiles[parent::POS_HEAD]) && count($this->scriptFiles[parent::POS_HEAD]) !==  0)
		{
			$combine = array();
			$leaveAlone = array();
			foreach($this->scriptFiles[parent::POS_HEAD] as $jsFile)
			{
				if (substr($jsFile, 0, 4) != 'http')
					$combine[] = ltrim($jsFile, '/');
				else
					$leaveAlone[] = $jsFile;
			}
			$this->scriptFiles[parent::POS_HEAD] = array_merge($leaveAlone, array(Yii::app()->baseUrl.'/min/?f='.implode(',', $combine)));
		}

		$this->cssFiles = $this->coreCssFiles + $this->cssFiles;
		
		unset($this->cssFiles['** DUMMY **']);

		if ($this->combineCss && count($this->cssFiles) !==  0)
		{
			$cssFiles = array();
			foreach ($this->cssFiles as $url => $media)
			{
				$cssFiles[$media][] = ltrim($url, '/');
				unset($this->cssFiles[ $url ]);
			}
			foreach($cssFiles as $media => $files)
			{
				$this->cssFiles[ Yii::app()->baseUrl.'/min/?f=' . implode(',', $files) ] = $media;
			}
		}
		parent::renderHead($output);
	}

	/**
	 * Register a core CSS file. Core CSS files are *always* loaded before normal CSS files
	 */
	public function registerCoreCssFile($url,$media='')
	{
		$this->coreCssFiles[ $url ] = $media;
		// now force CClientScript to set it's private property _hasScripts to true
		parent::registerCssFile('** DUMMY **');
		return $this;
	}

	/**
	 * Override of CClientScript::isCssFileRegistered to take core CSS files into account
	 */
	public function isCssFileRegistered($url)
	{
		return in_array(array_keys($this->cssFiles, $url)) || in_array(array_keys($this->coreCssFiles, $url));
	}
}