<div class="form wide">

<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'letter-form',
	'enableAjaxValidation'=>false,
	'stateful'=>true,
)); ?>

	<p class="note">Velden met <span class="required">*</span> zijn verplicht.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model, 'intro'); ?>
		<?php	$this->widget('ext.widgets.input.tinymce.TinyMCE', array('model'=>$model, 'attribute'=>'intro', 'height'=>200)); ?>
		<p class="hint">Optioneel: een introductie voor de brief. Als u deze leeg laat begint een respondent direct op pagina 1.</p>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model, 'endDate'); ?>
		<?php	$this->widget('ext.widgets.input.date.Date', array('model'=>$model, 'attribute'=>'endDate')); ?>
		<p class="hint">Als u hier een datum opgeeft is de brief na deze datum niet meer in te vullen.</p>
	</div>
	
	<div class="row" style="overflow: hidden;">
		<?php echo $form->labelEx($model, 'editorUsers'); ?>
		<div class="inl" style="float: left;">
			<?php echo $form->checkBoxList($model, 'editorUsers', User::getViewersListData()); ?>
		</div>
		<p class="hint" style="clear:left;">Hierboven kunt u aangeven welke gebruikers deze brief mogen bekijken.</p>
		<p class="hint">Beheerders staan niet in deze lijst omdat zij per definitie alle brieven mogen bekijken.</p>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Verder' : 'Opslaan'); ?>
	</div>

<?php $this->endWidget(); ?>

</div>