<?php
$this->pageTitle = 'Wachtwoord wijzigen';
?>

<h1>Wachtwoord wijzigen</h1>
<?php if (Yii::app()->user->getFlash('password_changed')): ?>
	<div class="flash-success">
		Uw wachtwoord is gewijzigd. Gebruikt u aub uw nieuwe wachtwoord als u de volgende keer inlogt.
	</div>
<?php else: ?>
	<div class="form wide">

	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'user-form',
		'enableAjaxValidation'=>false,
	)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password',array('size'=>40,'value' => '')) ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'new_password'); ?>
		<?php echo $form->passwordField($model,'new_password',array('size'=>40,'maxlength'=>14)) ?>
		<p class="hint">Minimum <?php echo User::MIN_PASSWORD_LENGTH; ?>, maximum <?php echo User::MAX_PASSWORD_LENGTH; ?> karakters.</p>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'new_password_repeat'); ?>
		<?php echo $form->passwordField($model,'new_password_repeat',array('size'=>40,'maxlength'=>14)) ?>
	</div>
	<div class="row buttons">
		<?php echo CHtml::submitButton('Wachtwoord wijzigen'); ?>
	</div>

	<?php $this->endWidget(); ?>
	</div>
<?php endif; ?>