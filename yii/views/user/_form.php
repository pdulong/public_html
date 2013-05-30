<div class="form wide">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'user-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Velden met <span class="required">*</span> zijn verplicht.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'username'); ?>
		<?php echo $form->textField($model,'username',array('size'=>40,'maxlength'=>30)); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'email'); ?>
		<?php echo $form->textField($model,'email',array('size'=>40,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'email'); ?>
	</div>

	<?php if ($model->isNewRecord): ?>
		<div class="row">
			<?php echo $form->labelEx($model,'password'); ?>
			<?php if ($model->scenario=='register'): ?>
				<?php echo $form->passwordField($model,'password',array('size'=>40,'maxlength'=>User::MAX_PASSWORD_LENGTH)); ?>
			<?php elseif ($model->scenario=='insert'): ?>
				<?php echo $form->textField($model,'password',array('size'=>40,'maxlength'=>User::MAX_PASSWORD_LENGTH)); ?>
			<?php endif; ?>

			<p class="hint">Minimum <?php echo User::MIN_PASSWORD_LENGTH; ?>, maximum <?php echo User::MAX_PASSWORD_LENGTH; ?> karakters.</p>
			<?php echo $form->error($model,'password'); ?>
		</div>

		<?php if ($model->scenario=='register'): ?>
			<div class="row">
				<?php echo $form->labelEx($model,'password_repeat'); ?>
				<?php echo $form->passwordField($model,'password_repeat',array('size'=>32,'maxlength'=>32)); ?>
				<?php echo $form->error($model,'password_repeat'); ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ($model->scenario === 'update' || $model->scenario === 'insert'): ?>
		<div class="row">
			<?php echo $form->labelEx($model, 'role'); ?>
			<?php echo $form->dropDownList($model, 'role', UserController::getAvailableRolesListData()); ?>
			<?php echo $form->error($model, 'role'); ?>
		</div>
	
		<div class="row buttons">
			<?php echo $form->checkbox($model,'active'); ?>
			<?php echo $form->labelEx($model,'active',array('class'=>'inl')); ?>
			<?php echo $form->error($model,'active'); ?>
		</div>
	<?php endif; ?>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->scenario === 'register' ? 'Registreer nu' : ($model->isNewRecord ? 'Toevoegen' : 'Opslaan')); ?>
	</div>

<?php $this->endWidget(); ?>

</div>