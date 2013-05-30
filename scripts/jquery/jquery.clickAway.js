jQuery.fn.extend({
	clickAway: function(options) {
		return this.each( function() {
			var $this=$(this), text=$this.val();
			
			$(this).focus(function() {
				if ($this.val() == text) {
					$this.val('');
					if ($.isFunction(options.onEmpty)) {
						options.onempty(this);
					}
				}
			}).blur(function() {
				if ($this.val() == '') {
					$this.val(text);
					if ($.isFunction(options.onRefill)) {
						options.onrefill(this);
					}
				}
			})
		});
	}
});
