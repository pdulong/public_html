<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php
echo "<?php\n";
$nameColumn=$this->guessNameColumn($this->tableSchema->columns);
$label=$this->pluralize($this->class2name($this->modelClass));
echo "\$this->pageTitle='$label Bewerken';\n";
echo "\$this->breadcrumbs=array(
	'$label'=>array('index'),
	'Bewerken',
);\n";
?>
?>

<h1><?php echo $label; ?> Bewerken</h1>

<?php echo "<?php echo \$this->renderPartial('_form', array('model'=>\$model)); ?>"; ?>