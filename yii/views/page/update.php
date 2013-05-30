<?php
$this->pageTitle='Brief Bewerken';
Yii::app()->clientScript
	->registerCoreScript('jquery')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/page.update.js')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.ui.core.js')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.ui.widget.js')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.ui.mouse.js')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.ui.draggable.js')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.ui.droppable.js')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.effects.core.js')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.effects.explode.js');
?>

<h1>Brief bewerken</h1>

<div id="<?php echo $model->isLandscape ? 'topbar' : 'sidebar'; ?>">
	<div class="tools">
		<img id="ModeDraw" class="mode-img mode-img-current" src="/img/crosshair.gif" alt="Rechthoeken maken" />
		<img id="ModeResize" class="mode-img" src="/img/mouse.gif" alt="Rechthoeken verslepen en aanpassen" />
		<img id="ImgTrash" src="/img/trash.gif" alt="Sleep een blok hierheen om deze te verwijderen" />
	</div>

	<div class="form">

		<?php
		$form=$this->beginWidget('CActiveForm', array(
			'id'=>'page-form',
			'enableAjaxValidation'=>false,
		)); ?>

			<?php echo $form->errorSummary($model); ?>

			<div id="regions">
				<?php foreach($model->regions as $region): ?>
					<input type="hidden" id="region_input_<?php echo $region->id; ?>" name="Region[]" value="<?php echo $region->left,',',$region->top,',',$region->width,',',$region->height; ?>"/>
				<?php endforeach; ?>
			</div>

			<div class="row">
				<?php echo $form->labelEx($model,'use_memos',array('for'=>false)); ?>
				<div style="float: left;">
					<?php
					$available=array();
					$askComment=array();
					foreach ($model->availableMemosRaw as $a)
					{
						$available[]=$a->memoId;
						$askComment[$a->memoId]=$a->askComment;
					}
					
					$baseId1=CHtml::getIdByName('Page[use_memos][]');
					$baseId2=CHtml::getIdByName('Page[ask_comments][]');
					foreach(Memo::listData() as $id=>$title)
					{
						$htmlOptions['id']=$baseId1.'_'.$id;
						$htmlOptions['value']=$id;
						$checked=in_array($id,$available);
						echo CHtml::checkBox('Page[use_memos][]', $checked, $htmlOptions);
						echo ' <span class="inl">', CHtml::label($title, $htmlOptions['id']), '</span><br />';
						
						$htmlOptions['id']=$baseId2.'_'.$id;
						echo '<div id="cb_extra_'.$id.'">';
							echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
							echo CHtml::checkBox('Page[ask_comments][]', isset($askComment[$id]) && $askComment[$id], $htmlOptions);
							echo ' <span class="inl">', CHtml::label('+toeliching', $htmlOptions['id']), '</span><br />';
						echo '</div>';
					}
					?>
				</div>
				<div style="clear:left;"></div>
				<?php echo $form->error($model,'use_memos'); ?>
			</div>

			<div class="row buttons">
				<?php echo CHtml::submitButton($model->isNewRecord ? 'Toevoegen' : 'Opslaan'); ?>
			</div>

		<?php $this->endWidget(); ?>

		</div>
</div>

<div id="letter" style="background:url(<?php echo CHtml::asset(Yii::app()->runtimePath.'/pages/'.$model->id.'/'.$model->filename); ?>) no-repeat; width: <?php echo $model->width; ?>px; height: <?php echo $model->height; ?>px;">
	<div id="letter-overlay" style="width: <?php echo $model->width; ?>px; height: <?php echo $model->height; ?>px;">
		<?php foreach($model->regions as $region): ?>
		<div class="box" id="region_<?php echo $region->id; ?>" style="left: <?php echo $region->left; ?>px; top: <?php echo $region->top; ?>px; width: <?php echo $region->width; ?>px; height: <?php echo $region->height; ?>px;">
			<div class="resize"></div>
		</div>
	<?php endforeach; ?>
	</div>
</div>

<div id="NewBox" class="box" style="display: none">
	<div class="resize"></div>
</div>

<?php
$script=<<<EOSCRIPT
function resetShowHide() {
	$('[id^="Page_use_memos_"]').each(function() {
		var id=String($(this).attr('id'));
		id=id.substr(id.lastIndexOf('_')+1);
		var div=$('#cb_extra_'+id);
		if ($(this).is(':checked')) {
			div.show();
		} else {
			div.hide();
			$(':input', div).attr('checked', false);
		}
	});
}
$('[id^="Page_use_memos_"]').click(resetShowHide);
resetShowHide();
EOSCRIPT;

Yii::app()->clientScript->registerScript('cbs', $script, CClientScript::POS_READY);