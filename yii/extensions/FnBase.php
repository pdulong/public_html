<?php
/**
 * Extra functionality for the Yii framework
 */
class FnBase
{
	/**
	* Summarize a given text
	* @param string $text The text to be summarized
	* @param int $length The maximum number of characters to keep from the text
	* @param string $append A string to append to the summarized text
	* @param boolean $splitOnWholeWords Split only on whole words (true) or also split in the middle of words (false)
	* @return string A summarized text, appended by the append parameter
	*/
	public static function summarize($text, $length, $append = '...', $splitOnWholeWords = true)
	{
		if (strlen($text) <= $length) return $text;
		$split = 0;
		if ($splitOnWholeWords)
		{
			$i = 0; $lplus1 = $length + 1;
			while (($i = strpos($text, ' ', $i + 1)) < $lplus1)
			{
				if ($i === false) break;
				$split = $i;
			}
		}
		else
			$split = $length;

		return substr($text, 0, $split).$append;
	}

	/**
	* Gets the first non-empty parameter
	* @return string|int|array|float The first non-empty parameter (either a number, false, or a valued string)
	*/
	public static function first()
	{
		foreach(func_get_args() as $arg)
			if (is_numeric($arg) || $arg === false || $arg != '') return $arg;
		return '';
	}

	/**
	 * Create a friendly URL
	 * @param string|array $input String or array of strings to create a friendly URL for
	 * @return string Friendly URL
	 */
	public static function fUrl($input)
	{
		if (is_array($input)) return array_map(array('FnBase', 'fUrl'), $input);

		if (Yii::app()->getLanguage()=='de')
		{
			$input = preg_replace('/&(.)uml;/', "$1e", htmlentities(utf8_decode($input)));
		}
		$fURL	= preg_replace('/&(.)(acute|cedil|circ|ring|tilde|uml);/', "$1", htmlentities(utf8_decode($input)));
		$fURL	= preg_replace('/&#(\d+);/', '', $fURL);
		$fURL	= preg_replace('/([^a-z0-9]+)/', '-', strtolower(html_entity_decode($fURL)));
		$fURL	= trim($fURL, '-');
		return $fURL;
	}

	/**
	* Generate a random string
	* @param int $length The length of the random string to be generated
	* @param string $pattern A pattern indicating how to build the random string, 'c' -> character, 'C' -> capital, 'd' -> digit
	* @return string A random string
	*/
	public static function getRandomString($length, $pattern = null)
	{
		$str = '';
		if (!is_null($pattern))
		{
			$patternLength = strlen($pattern);
			if ($patternLength < $length)
				$pattern = str_repeat($pattern, floor($length / $patternLength)).substr($pattern, 0, $length % $patternLength);
			else if ($patternLength > $length)
				$pattern = substr($pattern, 0, $length);
		}
		else
		{
			$chars = array('d', 'c', 'C');
			$pattern = '';
			for ($i = 0; $i < $length; $i++)
			$pattern .= $chars[rand(0,2)];
		}
		for ($ch = 0; $ch < strlen($pattern); $ch++)
		{
			if ($pattern[$ch] == 'd') $char = rand(48, 57);
			if ($pattern[$ch] == 'c') $char = rand(97, 122);
			if ($pattern[$ch] == 'C') $char = rand(65, 90);
			$str .= chr($char);
		}
		return $str;
	}

	/**
	* Get the contents of a directory (non - recursive)
	* @param string $directory Which directory to get the contents of
	* @param string $dirsort Callback function to sort directories
	* @param string $filesort Callback function to sort files
	* @return array Associative array with the contents of the directory, dirs are in key 'dirs' and files are in key 'files'
	*/
	public static function dirArray($directory, $dirsortCallback = 'strnatcasecmp', $filesortCallback = 'strnatcasecmp')
	{
		if (!function_exists($dirsortCallback))  $dirsortCallback = 'strnatcasecmp';
		if (!function_exists($filesortCallback)) $filesortCallback = 'strnatcasecmp';

		$dirlist = array();
		$filelist = array();
		if ($dir = @opendir($directory))
		{
			while ($file = readdir($dir))
			{
				if ($file == '..' || $file == '.') continue;
				if (is_dir($directory.'/'.$file.'/')) $dirlist[] = $file;
				else $filelist[] = $file;
			}
			closedir($dir);
			usort($dirlist, $dirsortCallback);
			usort($filelist, $filesortCallback);
		}
		else
		{
			throw new CException('Directory '.$directory.' does not exist (or I cannot open it)!');
		}

		return array('dirs' => $dirlist, 'files' => $filelist);
	}

	/**
	* Removes a directory and all of its contents
	* @param $path The path to be deleted
	*/
	public static function rmdir_recursive($path)
	{
		$path = rtrim($path, '/').'/';
		$dir = opendir($path);
		while ( ($file = readdir($dir)) !== false )
		{
			if ($file == '.' || $file == '..') continue;
			$fullpath= $path.$file;
			if( is_dir($fullpath) )
			{
				self::rmdir_recursive($fullpath);
			}
			else
				unlink($fullpath);
		}
		closedir($dir);
		rmdir($path);
	}

	/**
	* Get human readable filesize
	* @param string|int $fileOrNumber File to get the size of, or an integer to convert to human readable filesize
	* @param int $decimals Number of decimals (defaults to 0 in case of bytes)
	* @param string $decimalSep Decimal seperator
	* @param string $thousandsSep Thousands seperator
	* @param array $units Units to use
	* @return string Human readable filesize with prefix
	*/
	public static function fsize($fileOrNumber, $decimals = 2, $decimalSep = ',', $thousandsSep = '.', $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'))
	{
		if (is_numeric($fileOrNumber))
			$fsize = ceil($fileOrNumber);
		else if (file_exists($fileOrNumber))
			$fsize = filesize($fileOrNumber);
		else
			return false;

		$x = 0;
		while ($fsize > 1024)
		{
			$fsize /= 1024;
			$x++;
		}
		return number_format($fsize, $x == 0 ? 0 : $decimals, $decimalSep, $thousandsSep).' '.$units[$x];
	}

	/**
	 * Returns an associative range, eg assoc_range(1,2) => array(1=>1, 2=>2)
	 * @param int $begin The first value in the range
	 * @param int $end The last value in the range
	 * @return array Range array
	 */
	public static function assoc_range($begin, $end)
	{
		$range = range($begin, $end);
		return array_combine($range, $range);
	}

	/**
	 * @return CDateFormatter
	 */
	public static function datef()
	{
		return new CDateFormatter( Yii::app()->getLanguage() );
	}

	/**
	 * @return CNumberFormatter
	 */
	public static function numberf()
	{
		return new CNumberFormatter( Yii::app()->getLanguage() );
	}

	/**
	 * Convert a date to a timestamp
	 * @param string $value A date
	 * @return string|integer A timestamp
	 */
	public static function beforeSaveTimestamp($value)
	{
		if (ctype_digit($value) && $value > 0)
			return $value;
		if (empty($value))
			return null;
		$dateFormat = CLocale::getInstance( Yii::app()->language )->getDateFormat('short');
		return CDateTimeParser::parse($value, $dateFormat, true);
	}

	/**
	 * Make a price safe to insert in the database
	 * @param string|int|float $value The price
	 * @return float The price, formatted in such a way that it can be inserted into the database
	 */
	public static function beforeSavePrice($value)
	{
		return trim(str_replace(array(',', '.--', '.-'), array('.', '.00'), $value));
	}

	/**
	 * Checks whether the current route is the given route
	 * @param array $route A route
	 * @return boolean Whether the given route is the current route
	 */
	public static function cr($route)
	{
		if (!isset($route))
			return false;

		$route=(array)$route;
		$route[0]=trim($route[0], '/');
		$currentRoute=trim(Yii::app()->controller->getRoute(),'/');

		if(!strcasecmp($route[0], $currentRoute) || (($pos=strpos($route[0], '*')) && !strcasecmp(substr($route[0], 0, $pos-1), substr($currentRoute, 0, $pos-1))))
		{
			if(count($route)>1)
			{
				foreach(array_splice($route,1) as $name=>$value)
				{
					if(!isset($_GET[$name]) || $_GET[$name]!=$value)
						return false;
				}
			}
			return true;
		}
		return false;
	}
}