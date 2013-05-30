<?
class URLInput extends CInputWidget
{
	public $model;
	public $attributes;
	public $htmlOptions;

	public function run()
	{
		echo CHtml::activeTextField($this->model, $this->attribute, is_array($this->htmlOptions) ? $this->htmlOptions : array() );
		Yii::app()->clientScript->registerScript('URLInput_'.uniqid(),
			'$("#'.CHtml::activeId($this->model, $this->attribute).'").blur( function() {
				var check = ["http://", "https://", "ftp://", "mailto:"];
				var val = $(this).val();
				if (val=="") {
					return;
				}
				var found = false;
				$(check).each( function() {
					if (val.substring(0,this.length) == this) found = true;
				});
				if (found) return;
				var containsSlashes = ($(this).val().indexOf("/") != -1);
				$(this).val("http://" + $(this).val() + (!containsSlashes?"/":""));
			});',
			CClientScript::POS_READY
		);
	}
}