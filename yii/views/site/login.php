<?php
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/scripts/md5.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/scripts/login.js');
Yii::app()->clientScript->registerCSSFile(Yii::app()->baseUrl.'/styles/login.css');
?>

<h1>Inloggen</h1>

<p>Vult u aub hier onder uw gebruikersnaam en wachtwoord in om in te loggen:</p>

<div class="form wide">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Velden met <span class="required">*</span> zijn verplicht.</p>

	<div class="row">
		<?php echo $form->labelEx($model,'username'); ?>
		<?php echo $form->textField($model,'username'); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password'); ?> <small><?php echo CHtml::link('Wachtwoord vergeten?', array('/user/forgotpass')); ?></small>
		<?php echo $form->error($model,'password'); ?>
		<div class="flash-notice" id="caps">
			LET OP: Caps Lock lijkt aan te staan!
		</div>
	</div>

	<div class="row buttons">
		<?php echo $form->checkBox($model,'rememberMe'); ?>
		<?php echo $form->label($model,'rememberMe', array('class'=>'inl')); ?>
		<?php echo $form->error($model,'rememberMe'); ?>
		<p class="hint inl">Gebruik deze optie niet op een gedeelde computer.</p>
	</div>

	<div class="row buttons">
		<?php echo $form->hiddenField($model, 'password_enc'); ?>
		<?php echo $form->hiddenField($model, 'key', array('value'=>Yii::app()->user->getState('key', ''))); ?>
		<?php echo CHtml::submitButton('Inloggen'); ?>
	</div>

<?php $this->endWidget(); ?>
</div>