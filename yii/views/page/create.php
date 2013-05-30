<?php
$this->pageTitle='Pagina toevoegen';
?>

<h1>Pagina toevoegen</h1>
<p>Aan de brief  &ldquo;<?php echo CHtml::encode($model->letter->description); ?>&rdquo;</p>

<div class="form wide">

<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'page-form',
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array('enctype'=>'multipart/form-data'),
)); ?>

	<p class="note">Velden met <span class="required">*</span> zijn verplicht.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'filename'); ?>
		<?php echo $form->fileField($model,'filename',array('size'=>40)); ?>
		<?php echo $form->error($model,'filename'); ?>
		<p class="hint">
			Optimale afmetingen zijn:<br />
			&bull; 575 pixels breed en 822 pixels hoog voor staande uitingen<br />
			&bull; 822 pixels breed en 575 pixels hoog voor liggende uitingen<br />
			&bull; 575 pixels breed en 575 pixels hoog voor vierkante uitingen<br />
		</p>
		<br />
		<p class="hint">Afbeeldingen die groter of kleiner zijn worden automatisch<br />bijgeschaald naar één van deze afmetingen (rekening<br />houdend met de oriëntatie van de uiting) waardoor vertekening kan ontstaan.</p>
		<br />
		<p class="hint">Toegestane bestandstypen zijn jpeg, gif en png</p>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Verder'); ?>
	</div>

<?php $this->endWidget(); ?>

</div>