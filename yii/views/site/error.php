<?php
$this->pageTitle='Interne Server Fout';
?>

<h1>Interne Server Fout: <?php echo $code; ?></h1>

<div class="error">
<?php echo CHtml::encode($message); ?>
</div>