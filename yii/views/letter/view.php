<?php
$this->pageTitle=$letter->description;
Yii::app()->clientScript
	->registerCoreScript('jquery')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/memo-admin.js');
?>

<div id="<?php echo $model->isLandscape ? 'topbar' : 'sidebar'; ?>">
	<div id="pager">
		<div id="prev">
			<?php if ($_GET['page'] > 1): ?>
				<?php echo CHtml::link(CHtml::image(Yii::app()->baseUrl.'/img/prev.gif', 'Vorige pagina'), array('/letter/view', 'id'=>$letter->id, 'description'=>Fn::fUrl($letter->description), 'page'=>$_GET['page']-1, 'respondent'=>isset($_GET['respondent']) ? $_GET['respondent'] : null)); ?>
			<?php endif; ?>
		</div>
		<div id="next">
			<?php if ($_GET['page'] < $letter->numPages): ?>
				<?php echo CHtml::link(CHtml::image(Yii::app()->baseUrl.'/img/next.gif', 'Volgende pagina'), array('/letter/view', 'id'=>$letter->id, 'description'=>Fn::fUrl($letter->description), 'page'=>$_GET['page']+1, 'respondent'=>isset($_GET['respondent']) ? $_GET['respondent'] : null)); ?>
			<?php endif; ?>
		</div>
		<p>Pagina <?php echo $_GET['page']; ?> van <?php echo $letter->numPages; ?></p>
	</div>

	<div class="actions-col">
		<?php if (count($usedMemos) == 0): ?>
			<p>Er zijn nog geen notities op deze pagina geplakt.</p>
		<?php else: ?>
		<h2>Legenda</h2>
			<div id="memo-wrapper">
				<?php foreach($model->availableMemos as $memo): ?>
					<?php if (in_array($memo->id, $usedMemos)): ?>
						<p style="color: <?php echo $memo->color; ?>"><?php echo CHtml::checkBox('memo_'.$memo->id, true); ?> <label for="memo_<?php echo $memo->id; ?>"><?php echo $memo->description; ?></label></p>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<h2>Filter op respondenten:</h2>
			<p><textarea name="filter" id="filter_box"></textarea></p>
			<small>Scheid respondenten d.m.v. een enter</small>

			<h2>Acties</h2>
			<p><?php echo CHtml::link('&#187; Download PDF', array('/letter/downloadPdf', 'id'=>$letter->id, 'description'=>Fn::fUrl($letter->description),'page'=>$_GET['page']), array('id'=>'DownloadPdfLink')); ?></p>


		<?php endif; ?>
	</div>
	<div class="url">
		<h2>URL</h2>
		<p>Respondenten kunt u de volgende URL doorgeven:</p>
		<p><?php echo Yii::app()->urlManager->createUrl('/letter/fill', array('id'=>$letter->id, 'description'=>Fn::fUrl($letter->description), 'page'=>$_GET['page'])); ?></p>
		<p>
			Optioneel kunt u ?respondent=&lt;<em>referentie</em>&gt; aan de URL toevoegen, waarbij de referentie maximaal 255 karakters mag bevatten.
			Spaties en speciale tekens (zoals &eacute;) zijn toegestaan, maar door browser-compatibiliteit hiervan niet aan te bevelen. Het beste is om enkel letters en cijfers te gebruiken.
		</p>
	</div>
</div>

<div id="letter" class="letter-semitrans">
	<img src="<?php echo CHtml::asset(Yii::app()->runtimePath.'/pages/'.$model->id.'/'.$model->filename); ?>" alt="" />
	<?php foreach($model->memos as $m):?>
	<div class="memo-dot memo_<?php echo $m->memo->id; ?> respondent_<?= $m->respondent; ?>" respondent="<?= $m->respondent; ?>" style="top: <?php echo $m->top+10; ?>px; left: <?php echo $m->left+65; ?>px; background-color: <?php echo $m->memo->color; ?>">
		<div class="admin-comment">
			<?php echo CHtml::encode($m->respondent); ?>: <?php echo $m->memo->description; ?>
			<div class="comment-text" style="display: block;">
				<?php echo nl2br($m->comment); ?>
			</div>
		</div>
	</div>
<?php endforeach; ?>
</div>
