<?php
$this->pageTitle='Notities';
?>
<h1>Notities</h1>

<ul class="actions">
	<li><?php echo CHtml::link('Notitie toevoegen', array('/memo/create')); ?></li>
</ul>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'memo-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'description',
		array(
			'name'=>'color',
			'value'=>'"<span style=\"color: $data->color;\">$data->color</span>"',
			'type'=>'raw',
		),
		array(
			'name'=>'numUses',
			'header'=>'# Gebruikt',
			'filter'=>false,
		),
		array(
			'class'=>'CButtonColumn',
			'buttons'=>array(
				'view'=>array(
					'visible'=>'false'
				),
			),
			'deleteConfirmation'=>"Weet u zeker dat u deze notitie wilt verwijderen?\n\nLET OP: Als deze notitie al is gebruikt op een brief wordt deze daar vanaf gehaald."
		),
	),
)); ?>
