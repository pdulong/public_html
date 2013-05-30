<?php

/**
 * The FirePHP LogRoute extension for Yii Framework is free software. It is released under the terms of
 * the following BSD License.
 *
 * Copyright (c) 2010, BJ Basañes (shikishiji@gmail.com).
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice, this list of
 *       conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright notice, this list
 *       of conditions and the following disclaimer in the documentation and/or other materials
 *       provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY <COPYRIGHT HOLDER> ``AS IS'' AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those of the
 * authors and should not be interpreted as representing official policies, either expressed
 * or implied, of <copyright holder>.
 *
 * @copyright Copyright (c) BJ Basañes
 * @author BJ Basañes <shikishiji@gmail.com>
 * @package Shiki
 * @subpackage FirePHPLogRoute
 */

/**
 * Sends Yii log messages to FirePHP. Since this class inherits from CLogRoute,
 * it uses the same method and configuration as the other built-in
 * logging components: CFileLogRoute, CWebLogRoute, etc.
 *
 * @todo additional properties as wrappers for FirePHP's options
 * @todo maybe an access to it's instance? And allowing immediate logging as opposed to delayed logging by Yii
 *
 * @author BJ Basañes <shikishiji@gmail.com>
 * @package Shiki
 * @subpackage FirePHPLogRoute
 * @version 0.1
 */
class ShikiFirePHPLogRoute extends CLogRoute
{
    /**
     * The path alias to FirePHP core lib's fb.php
     * @var string
     */
    public $fbPath;
		public $groupByToken=true;
		
    /**
     * FirePHP options. Available keys are:
     *  - maxObjectDepth: The maximum depth to traverse objects (default: 10)
     *  - maxArrayDepth: The maximum depth to traverse arrays (default: 20)
     *  - useNativeJsonEncode: If true will use json_encode() (default: true)
     *  - includeLineNumbers: If true will include line numbers and filenames (default: false)
     * @var array
     */
    public $options = array(
        'maxObjectDepth' => 2,
        'maxArrayDepth' => 5,
        'includeLineNumbers' => false,
    );

		public function init()
		{
		}
		
    /**
     * Load fb.php. This is called only when processLogs() is called
     *
     */
    protected function includeLib()
    {
        if (!isset($this->fbPath)) {
            throw new Exception('Please set a path alias to the FirePHP lib path.');
        } else {
            Yii::import($this->fbPath, true);
            FB::setOptions($this->options);
        }
    }

    /**
	 * Processes log messages and sends them to specific destination.	 *
	 * @param array list of messages.  Each array elements represents one message
	 * with the following structure:
	 * array(
	 *   [0] => message (string)
	 *   [1] => level (string)
	 *   [2] => category (string)
	 *   [3] => timestamp (float, obtained by microtime(true));
	 */
    protected function processLogs($logs)
    {
			// http://github.com/shiki/yii-firephplogroute/issues#issue/1
			// This gets thrown "Fatal error: Exception thrown without a stack frame in Unknown on line 0" if
			// FirePHP tries to throw an exception when we are already under an exception handler and headers were already sent.
			if (Yii::app()->getErrorHandler()->getError() && headers_sent())
				return;

			$this->includeLib();

			FB::info('Yii version '.Yii::getVersion().', PHP version '.PHP_VERSION.(defined('LDEV') && LDEV?', LDEV':'').(defined('YII_DEBUG') && YII_DEBUG?', YII_DEBUG':''));
			$results = array();
			$stack = array();
			$n=0;
			$tableLogs = array();
			foreach ($logs as $log)
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
							if(!$this->groupByToken)
								$token=$log[2];
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
			}

			if (count($tableLogs) > 0)
			{
				$table = array(array('Time', 'Category', 'Level', 'Message'));
				foreach($tableLogs as $log)
				{
					$table[] = array(sprintf('%0.5f', $log[3] - YII_BEGIN_TIME), $log[2], $log[1], $log[0]);
				}
				FB::table('Log', $table);
			}
			
			$now=microtime(true);
			while(($last=array_pop($stack))!==null)
			{
				$delta=$now-$last[3];
				$token=$this->groupByToken ? $last[0] : $last[2];
				if(isset($results[$token]))
					$results[$token]=$this->aggregateResult($results[$token],$delta);
				else
					$results[$token]=array($token,1,$delta,$delta,$delta);
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
			array_unshift($entries, array('Command', 'n', 'total (s)', 'avg (s)', 'min (s)', 'max (s)'));
			$stats = Yii::app()->getDb()->getStats();
			FB::table('Profiling ('.$stats[0].' queries, '.(memory_get_usage(true) / 1024).' kB now, '.(memory_get_peak_usage(true) / 1024).' kB max)', $entries);
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