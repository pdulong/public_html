<?php
/**
 * This is the configuration for generating message translations
 * for the Yii framework. It is used by the 'yiic message' command.
 */
$webappConfig=require(dirname(__FILE__).'/../config/webapp.php');
return array(
	'sourcePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'../..',
	'messagePath'=>dirname(__FILE__),
	'languages'=>array_keys($webappConfig['components']['langHandler']['languages']),
	'fileTypes'=>array('php'),
	'exclude'=>array(
		'.svn',
		'/yii/framework/yiilite.php',
		'/yii/framework/yiit.php',
		'/yii/framework/i18n/data',
		'/yii/data/i18n',
		'/yii/messages',
		'/yii/framework/vendors',
		'/yii/framwork/web/js',
	),
);