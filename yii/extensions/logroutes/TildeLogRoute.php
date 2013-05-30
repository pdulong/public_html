<?php
/**
 * TildeLogRoute extends CLogRoute to output all log messages into hidden console, available by
 * hot key "Ctrl" + "~" (tilde).
 *
 * @author Stepan Kravchenko <stepan.krab@gmail.com>
 * @version 1.0.0
 */
class TildeLogRoute extends CLogRoute
{
    /**
     * Height of tilde console in pixels.
     * @var int
     */
    public $consoleHeight = '500';

    /**
     * Colors for each log level.
     * @var array
     */
    public $colors = array(
        'trace'   => '#cccccc',
        'warning' => '#FFC338',
        'error'   => '#DD1C1C',
        'info'    => '#7654FF',
        'profile' => '#cccccc',
        'default' => '#cccccc',
    );

	/**
	 * Displays the log messages.
	 * @param array list of log messages
	 */
	public function processLogs($logs)
	{
        $app = Yii::app();
        if(!($app instanceof CWebApplication) || $app->getRequest()->getIsAjaxRequest()) {
            return;
        }

        include(dirname(__FILE__).DIRECTORY_SEPARATOR.'Debug_HackerConsole_Main.php');
        new Debug_HackerConsole_Main($this->consoleHeight, true);
        
		$viewFile = dirname(__FILE__).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'tilde.php';
		include(Yii::app()->findLocalizedFile($viewFile, 'en'));
	}

	protected function aggregateResult($result,$delta)
	{
		list($token,$calls,$min,$max,$total)=$result;
		if($delta<$min)
			$min=$delta;
		else if($delta>$max)
			$max=$delta;
		$calls++;
		$total+=$delta;
		return array($token,$calls,$min,$max,$total);
	}
}
