<?php
/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php
echo "<?php\n";
$label=$this->pluralize($this->class2name($this->modelClass));
echo "\$this->pageTitle='$label Toevoegen';\n";
echo "\$this->breadcrumbs=array(
	'$label'=>array('index'),
	'Toevoegen',
);\n";
?>
?>

<h1><?php echo $label; ?> Toevoegen</h1>

<?php echo "<?php echo \$this->renderPartial('_form', array('model'=>\$model)); ?>"; ?>
