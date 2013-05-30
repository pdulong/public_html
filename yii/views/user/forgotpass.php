<?php
$this->pageTitle = 'Wachtwoord vergeten';
$this->robots = 'noindex,nofollow';
?>
<h1>Wachtwoord vergeten</h1>
<br />
<?php if (Yii::app()->user->getFlash('password_reset')): ?>
	<div class="flash-success">
	Een nieuw wachtwoord is gegenereerd en naar uw e-mail adres gestuurd.<br />
	Als u in de komende paar minuten geen e-mail ontvangt, controleert u dan aub ook uw SPAM box.<br />
	Zodra u uw nieuwe wachtwoord ontvangen hebt kunt u hier mee <?php echo CHtml::link('inloggen', array('site/login')); ?>.
	</div>
<?php else: ?>
	Indien u uw wachtwoord vergeten bent, vult u dan hieronder het e-mail-adres in waarmee u aangemeld bent om een
	nieuwe toegestuurd te krijgen.<br />
	<br />
	<div class="form wide">
		<?php
		$form = $this->beginWidget('CActiveForm', array(
			'id'=>'user-form',
			'enableAjaxValidation'=>false,
		));
		?>
		
		<?php echo $form->errorSummary($model); ?>
		<div class="row">
			<?php echo $form->labelEx($model,'email'); ?>
			<?php echo $form->textField($model,'email',array('size'=>40,'maxlength'=>255)); ?>
		</div>
		<div class="row buttons">
			<?php echo CHtml::submitButton('Stuur mij een nieuw wachtwoord'); ?>
		</div>
		<?php $this->endWidget(); ?>
	</div>
<?php endif ; ?>