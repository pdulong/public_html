<?php
defined('LDEV') or define('LDEV',true);
defined('YII_DEBUG') or define('YII_DEBUG',LDEV);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',0);

require_once(dirname(__FILE__).'/yii/extensions/Fn.php');
require_once(dirname(__FILE__).'/yii/framework/yii.php');
Yii::createWebApplication(dirname(__FILE__).'/yii/config/webapp.php')->run();