<?php
$this->pageTitle=$letter->description;
Yii::app()->clientScript
	->registerCoreScript('jquery')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.ui.core.js')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.ui.widget.js')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.ui.mouse.js')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.ui.draggable.js')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/jquery/ui/jquery.ui.droppable.js')
	->registerScriptFile(Yii::app()->baseUrl.'/scripts/memo.js');
?>
<?php if (Yii::app()->user->isGuest): ?>
	<div id="spacer"></div>
<?php endif; ?>
<div id="<?php echo $model->isLandscape? 'topbar' : 'sidebar'; ?>">
	<div id="memos">
		<?php foreach($model->availableMemosRaw as $memo): ?>
		<?php $disabled=''; foreach($memos as $m) if ($m->memoId==$memo->memoId) $disabled=' memo-disabled'; ?>
		<div class="memo<?php echo $disabled; ?>" id="memo_<?php echo $memo->memoId; ?>">
			<div class="punaise"></div>
			<div class="memo-inner">
				<img src="<?php echo $memo->memo->imageUrl; ?>" alt="<?php echo str_replace(array("\r\n","\n\r","\n"), ' ', $memo->memo->description); ?>" />
				<?php if ($memo->askComment): ?>
					<div class="comment">
						Toelichting:
						<div class="comment-text"></div>
						<textarea name="comment-input" cols="20" rows="2"></textarea><br />
						<input type="submit" name="save-comment" value="Ok" />
						<div class="comment-options">
							<a class="delete-comment" href="javascript:void(0);">Notitie verwijderen</a>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>

<div id="letter">
	<img src="<?php echo CHtml::asset(Yii::app()->runtimePath.'/pages/'.$model->id.'/'.$model->filename); ?>" alt="" />
	<?php foreach($model->regions as $region): ?>
		<div class="letter-region" style="left: <?php echo $region->left; ?>px; top: <?php echo $region->top; ?>px; width: <?php echo $region->width; ?>px; height: <?php echo $region->height; ?>px;"></div>
	<?php endforeach; ?>
	<?php foreach($memos as $m): ?>
		<div class="live-memo" id="live_memo_<?php echo $m->id; ?>" style="position: absolute; left: <?php echo $m->left; ?>px; top: <?php echo $m->top; ?>px; ">
			<div class="punaise"></div>
			<div class="memo-inner" style="display: none;">
				<img src="<?php echo $m->memo->imageUrl; ?>" alt="<?php echo str_replace(array("\r\n","\n\r","\n"), ' ', $m->memo->description); ?>" />
				<div class="comment" style="display: block;">
					Toelichting:
					<div class="comment-text" style="display:block;"><?php echo CHtml::encode($m->comment); ?></div>
					<br />
					<div class="comment-options">
						<a class="delete-comment" href="javascript:void(0);">Notitie verwijderen</a>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<input type="hidden" id="Respondent" value="<?php echo isset($_GET['respondent'])?$_GET['respondent']:'anoniem'; ?>" />
<input type="hidden" id="PageID" value="<?php echo $model->id; ?>" />