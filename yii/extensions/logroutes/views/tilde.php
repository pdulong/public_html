<?php
Debug_HackerConsole_Main::out('Yii version: '.Yii::getVersion(), 'Trace', $this->colors['trace']);
Debug_HackerConsole_Main::out('PHP version: '.PHP_VERSION, 'Trace', $this->colors['trace']);
Debug_HackerConsole_Main::out('LDEV: '.(defined('LDEV') && LDEV ? 'Yes':'No'), 'Trace', $this->colors['trace']);
Debug_HackerConsole_Main::out('YII_DEBUG: '.(defined('YII_DEBUG') && YII_DEBUG ? 'Yes':'No')."\n", 'Trace', $this->colors['trace']);

$results=array();
$stack=array();
$tableLogs=array();
$n=0;
foreach($logs as $index=>$log)
{
	if ($log[1] === CLogger::LEVEL_PROFILE)
	{
		$message=$log[0];
		if(!strncasecmp($message,'begin:',6))
		{
			$log[0]=substr($message,6);
			$stack[]=$log;
		}
		else if(!strncasecmp($message,'end:',4))
		{
			$token=substr($message,4);
			if(($last=array_pop($stack))!==null && $last[0]===$token)
			{
				$delta=$log[3]-$last[3];
				if(isset($results[$token]))
					$results[$token]=$this->aggregateResult($results[$token],$delta);
				else
					$results[$token]=array($token,1,$delta,$delta,$delta);
			}
			else
				throw new CException(Yii::t('yii','CProfileLogRoute found a mismatching code block "{token}". Make sure the calls to Yii::beginProfile() and Yii::endProfile() be properly nested.',
					array('{token}'=>$token)));
		}
		continue;
	}
	else
	{
		$tableLogs[] = $log;
	}
	$time = date('H:i:s.', $log[3]).(int)(($log[3] - (int)$log[3]) * 1000);
  $msg = '['.$time.'] '.$log[2].":".$log[0];
  $group = isset($this->colors[$log[1]]) ? $log[1] : 'default';

  Debug_HackerConsole_Main::out($msg, ucfirst($group), $this->colors[$group]);
}

$entries = array();
foreach($results as $idx => $result)
{
	$entries[] = array(
		$result[0], // command
		(string)$result[1], // n
		sprintf('%0.5f',$result[4]), //total
		sprintf('%0.5f',$result[4]/$result[1]), //avg
		sprintf('%0.5f',$result[2]), //min
		sprintf('%0.5f',$result[3]), //max
	);
}

$func=create_function('$a,$b','return $a[2]<$b[2]?1:0;');
usort($entries,$func);
$stats = Yii::app()->getDb()->getStats();
foreach($entries as $entry)
	Debug_HackerConsole_Main::out($entry[1].'x '.$entry[0]."\n".'avg='.$entry[2].', min='.$entry[3].', max='.$entry[4]."\n", 'Profile', $this->colors['profile']);

$stats = Yii::app()->getDb()->getStats();

Debug_HackerConsole_Main::out("\n".$stats[0].' queries, '.(memory_get_usage(true) / 1024).' kB now, '.(memory_get_peak_usage(true) / 1024).' kB max', 'Profile', $this->colors['profile']);