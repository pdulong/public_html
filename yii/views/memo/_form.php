<div class="form wide">

<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'memo-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Velden met <span class="required">*</span> zijn verplicht.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textArea($model,'description',array('cols'=>50,'rows'=>3)); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'color'); ?>
		<div style="float: left;">
			<?php $this->widget('ext.widgets.input.color.Farbtastic', array('model'=>$model, 'attribute'=>'color')); ?>
			<?php echo $form->error($model,'color'); ?>
		</div>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Preview', array('onclick'=>'return false;', 'id'=>'Preview', 'style'=>'display: none;')); ?>
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Toevoegen' : 'Opslaan'); ?>
	</div>

	<div id="PreviewWrapper" class="row buttons">
		<div class="memo">
			<div class="punaise"></div>
			<?php if (!$model->isNewRecord): ?>
				<?php echo CHtml::image(Yii::app()->baseUrl.'/memo/preview/?description=' . urlencode(str_replace(array("\r\n","\n\r","\n"), '|', $model->description)), '', array('id'=>'PreviewImage')); ?>
			<?php endif; ?>
		</div>
	</div>
<?php $this->endWidget(); ?>

</div>
<?php
$descriptionId=CHtml::activeId($model, 'description');
$baseUrl=Yii::app()->baseUrl;
$script=<<<EOSCRIPT
	$("#Preview").show().click( function() {
		if ($('#PreviewImage').size() == 0) {
			$('<img/>', {
				'id': 'PreviewImage'
			}).appendTo('#PreviewWrapper');
		}
		$('#PreviewImage').attr('src', '$baseUrl/memo/preview?description=' + $('#$descriptionId').val().replace(/(\\r\\n|\\n\\r|\\n)/g, '|') );
	});
EOSCRIPT;
Yii::app()->clientScript->registerScript('memoPreview', $script, CClientScript::POS_READY);