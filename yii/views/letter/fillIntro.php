<?php
$this->pageTitle=$model->description.' - Intro';
?>

<?php echo $model->intro; ?>

<?php echo CHtml::link('Verder &#187;', array('/letter/fill', 'id'=>$model->id, 'description'=>Fn::fUrl($model->description), 'page'=>1, 'respondent'=>isset($_GET['respondent']) ? $_GET['respondent'] : null));