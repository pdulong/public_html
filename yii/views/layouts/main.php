<?php
Yii::app()->clientScript
	->registerCoreScript('jquery')
	->registerCoreCssFile(Yii::app()->baseUrl.'/styles/main.css')
	->registerCoreCssFile(Yii::app()->baseUrl.'/styles/form.css');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->getLanguage(); ?>" lang="<?php echo Yii::app()->getLanguage(); ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="<?php echo Yii::app()->getLanguage(); ?>" />
	<meta name="Robots" content="noindex,nofollow" />
	<meta http-equiv="imagetoolbar" content="no" />
	<title><?php echo CHtml::encode($this->pageTitle).' | '.Yii::app()->name; ?></title>
</head>

<body>
	<div id="page">
		<?php if (Yii::app()->user->checkAccess('admin') || Yii::app()->user->checkAccess('viewer')): ?>
		<div id="mainmenu">
			<div id="mainmenu-left"></div>
			<div id="mainmenu-right"></div>
			<?php $this->widget('zii.widgets.CMenu',array(
				'items'=>array(
					array(
						'label'=>'Brieven',
						'url'=>array('/letter/admin'),
						'active'=>Fn::cr(array('letter/*')) || Fn::cr(array('page/*')),
					),
					array(
						'label'=>'Notities',
						'url'=>array('/memo/admin'),
						'active'=>Fn::cr(array('memo/*')),
						'visible'=>Yii::app()->user->checkAccess('admin')
					),
					array(
						'label'=>'Gebruikersbeheer',
						'url'=>array('/user/admin'),
						'active'=>Fn::cr(array('user/*')) && !Fn::cr(array('user/changepass')),
						'visible'=>Yii::app()->user->checkAccess('userAdmin')
					),
					array(
						'label'=>'Wachtwoord wijzigen',
						'url'=>array('/user/changepass'),
					),
					array(
						'label'=>'Uitloggen ('.Yii::app()->user->name.')',
						'url'=>array('/site/logout'),
					),
				),
			)); ?>
		</div>
		<?php endif; ?>
		<div id="nojs" class="flash-error">
			U heeft javascript uit staan in uw browser. Voor een correcte werking van deze website is javascript echter vereist.<br />
			Schakelt u aub javascript in alvorens verder te gaan.
		</div>
		<script type="text/javascript">// <![CDATA[
			$('#nojs').hide();
		// ]]></script>
		<?php echo $content; ?>
		<div id="spacer"></div>
</div>
</body>
</html>