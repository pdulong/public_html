jQuery.fn.extend({
	equalHeights: function() {
		if (this.size() <= 1) return;
		var newHeight = -1;
		this.each( function() {
			newHeight = Math.max($(this).height(), newHeight);
		});
		if (newHeight == -1) return;
		this.each( function() {
			$(this).height(newHeight);
		});
	}
});