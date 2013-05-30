<?php
$this->pageTitle='Gebruikersbeheer';
?>

<h1>Gebruikersbeheer</h1>

<ul class="actions">
	<li><?php echo CHtml::link('Gebruiker toevoegen', array('/user/create')); ?></li>
</ul>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'user-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'username',
		'email',
		array(
			'name'=>'active',
			'value'=>'$data->active?"Ja":"Nee"',
			'filter'=>array(0=>'Nee',1=>'Ja'),
		),
		array(
			'name'=>'role',
			'value'=>'!is_null($data->authAssignment) ? ucfirst($data->authAssignment->authItem->description) : "<em>geen</em>"',
			'type'=>'raw',
			'filter'=>UserController::getAvailableRolesListData(),
		),
		array(
			'class'=>'CButtonColumn',
			'buttons'=>array(
				'update'=>array(
					'visible'=>'$data->id != Yii::app()->user->id',
				),
				'delete'=>array(
					'visible'=>'$data->id != Yii::app()->user->id',
				),
				'view'=>array(
					'visible'=>'false'
				)
			),
			'deleteConfirmation'=>'Weet u zeker dat u deze gebruiker wilt verwijderen?',
		),
	),
)); ?>