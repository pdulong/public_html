<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php
echo "<?php\n";
$label=$this->pluralize($this->class2name($this->modelClass));
echo "\$this->pageTitle='$label Overzicht';\n";
echo "\$this->breadcrumbs=array(
	'$label',
);\n";
?>
?>

<h1><?php echo $label; ?> Overzicht</h1>

<?php echo "<?php"; ?> $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
