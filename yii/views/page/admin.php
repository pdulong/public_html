<?php
$this->pageTitle='Pagina\'s van de brief '.$letter->description;
?>

<h1>Pagina's van de brief &ldquo;<?php echo CHtml::encode($letter->description); ?>&rdquo; beheren</h1>

<ul class="actions">
	<li><?php echo CHtml::link('Pagina toevoegen', array('/page/create', 'letterId'=>$letter->id)); ?></li>
	<li><?php echo CHtml::link('Terug naar brieven overzicht', array('/letter/admin')); ?></li>
</ul>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'page-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'position',
		'filename',
		array(
			'name'=>'numAvailableMemos',
			'filter'=>false,
			'header'=>'# Notities beschikbaar',
		),
		array(
			'class'=>'CButtonColumn',
			'buttons'=>array(
				'view'=>array(
					'visible'=>'false',
				),
			),
			'deleteConfirmation'=>"Weet u zeker dat u deze pagina wilt verwijderen?\n\nLET OP: Deze actie is niet ongedaan te maken!",
		),
	),
)); ?>
