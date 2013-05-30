<?php
$this->pageTitle='Brieven';
?>

<h1>Brieven</h1>

<?php if (Yii::app()->user->checkAccess('admin')): ?>
	<ul class="actions">
		<li><?php echo CHtml::link('Brief toevoegen', array('/letter/create', '_ref')); ?></li>
	</ul>
<?php endif; ?>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'letter-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'description',
		array(
			'name'=>'numPages',
			'header'=>'#Pagina\'s',
			'filter'=>false,
		),
		array(
			'name'=>'endDate',
			'value'=>'$data->endDate!="" ? Fn::datef()->formatDateTime($data->endDate, "full", null) : "<em>geen</em>"',
			'type'=>'raw',
			'filter'=>false,
		),
		array(
			'class'=>'CButtonColumn',
			'template'=>'{view} {update} {pages} {delete}',
			'buttons'=>array(
				'view'=>array(
					'visible'=>'$data->numPages > 0',
				),
				'update'=>array(
					'visible'=>'Yii::app()->user->checkAccess("admin")',
				),
				'pages'=>array(
					'label'=>'Bewerk de pagina\'s van deze brief',
					'imageUrl'=>Yii::app()->baseUrl.'/styles/gridview/pages.png',
					'url'=>'Yii::app()->urlManager->createUrl("/page/admin", array("letterId"=>$data->id));',
					'visible'=>'Yii::app()->user->checkAccess("admin")',
				),
				'delete'=>array(
					'visible'=>'Yii::app()->user->checkAccess("admin")',
				),
			),
			'updateButtonUrl'=>'Yii::app()->urlManager->createUrl("/letter/update", array("id"=>$data->id, "description"=>Fn::fUrl($data->description)));',
			'viewButtonUrl'=>'Yii::app()->urlManager->createUrl("/letter/view", array("id"=>$data->id, "description"=>Fn::fUrl($data->description), "page"=>1));',
			'deleteConfirmation'=>"Weet u zeker dat u deze brief wilt verwijderen?\n\nLET OP: Alle data die aan deze brief gekoppeld is zal ook verwijderd worden.\n\nDeze actie is niet ongedaan te maken!"
		),
	),
)); ?>