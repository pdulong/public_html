<?php
/**
 * Functions bootstrap file
 */
require(dirname(__FILE__).'/FnBase.php');

/**
 * Extra functionality for the current project
 * Extends FnBase which contains Yii functionality
 */
class Fn extends FnBase
{
	public static function friendlyFilename($filename)
	{
		$ext=substr($filename, strrpos($filename, '.') + 1);
		$name=substr($filename, 0, strrpos($filename, '.'));
		return self::fUrl($name).'.'.strtolower($ext);
	}
}