$(document).ready( function() {

var pdfUrl=$('#DownloadPdfLink').attr('href');

$('.memo-dot').hover(
	function(e) {
		$(this).css({'z-index':2000}).stop().fadeTo(500, 1).children().show();
	},
	function(e) {
		$(this).css({'z-index':1}).stop().fadeTo(500, 0.2).children().hide();
	}
);

function getPdfUrl() {
	var data=new Array();
	$('#memo-wrapper input').each( function() {
		if ($(this).attr('checked')) {
			var id=$(this).attr('id');
			id=id.substring(id.lastIndexOf('_')+1);
			data.push(id);
		}
	});
	var url=pdfUrl;
	if (data.length > 0) {
		url+= '?m[]='+(data.join('&m[]='));
	}
	return url;
}

$('#memo-wrapper').delegate('input', 'click', function() {
	var $this=$(this),c=$this.attr('checked'),id=$this.attr('id');
	id=id.substring(id.lastIndexOf('_')+1);
	if (c) {
		$('.memo_'+id).stop().fadeIn();
	} else {
		$('.memo_'+id).stop().fadeOut();
	}
	$('#DownloadPdfLink').attr('href', getPdfUrl());
});

});