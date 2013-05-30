$(document).ready( function() {

/******************
 INIT
******************/
var respondent=$('#Respondent').val(),pageId=$('#PageID').val(),init_pos=$('#letter').offset();
$('#Respondent,#PageID').remove();

$(window).resize( function() {
	init_pos=$('#letter').offset()
});

/******************
 SAVE MEMO
******************/
function saveMemo(memo) {
	var ta=$('textarea', memo);
	var data={
		respondent: respondent,
		pageId: pageId,
		memoId: memo.data('memoId'),
		top: Math.round(memo.offset().top - init_pos.top - 4),
		left: Math.round(memo.offset().left - init_pos.left - 4),
		comment: ta.size() ? ta.val() : ''
	};
	$.post('/pageMemo/create', data, function(response) {
		if (response.result==1) {
			if (ta.size()) {
				$('.comment-text', memo).html(ta.val().replace(/(\r\n|\n\r|\n)/g, '<br />')).show();
				$('[name="save-comment"]', memo).remove();
				$(ta).remove();
			}
			memo.attr('id', 'live_memo_' + response.id).removeClass('memo').addClass('live-memo');
			var mInner=memo.children('.memo-inner');
			setTimeout(function() { mInner.fadeOut('slow'); }, 1000);
		} else {
			alert('Fout bij opslaan. Probeert u het aub later nog eens. ('+response.result+')');
		}
	}, 'json');
}

/******************
 DEFINE DRAGGABLES
******************/
$('.memo').draggable({
	cursorAt: {left:72,top:20},
	revert: 'invalid',
	helper: 'clone'
});
$('.memo-disabled').draggable('disable');

/******************
 DEFINE DROPPABLES
******************/
$('.letter-region').droppable({
	accept: '.memo',
	over: function(event, ui) {
		$(event.target).addClass('drop-hover');
	},
	out: function(event, ui) {
		$(event.target).removeClass('drop-hover');
	},
	drop: function(event, ui) {
		var target=$(event.target);
		target.fadeOut(500, function() { $(this).removeClass('drop-hover').css('opacity', 0.4).show()});
		var memo=ui.draggable.clone();
		ui.draggable.not('.comment').fadeTo(500, 0.2).draggable('disable');
		var id=memo.attr('id');
		id=id.substr(id.lastIndexOf('_') + 1);
		memo.data('memoId', id).attr('id','').css({
			position: 'absolute',
			left:ui.offset.left - init_pos.left,
			top:ui.offset.top-init_pos.top
		}).appendTo($('#letter'));

		if ($('.comment', memo).size() > 0) {
			$('.comment', memo).width(200).show().find('[name="comment-input"]').focus();
		} else {
			saveMemo(memo);
		}
	},
	tolerance: 'pointer'
});

/******************
 SAVE COMMENT
******************/
$('#letter').delegate('[name="save-comment"]', 'click', function() {
	saveMemo($(this).parent().parent().parent());
});

/******************
 DELETE NOTE
******************/
$('#letter').delegate('.delete-comment', 'click', function() {
	$this=$(this);
	var id=$this.parent().parent().parent().parent().attr('id');
	id=id.substr(id.lastIndexOf('_') + 1);
	if (id)	{
		$.post('/pageMemo/delete/id/'+id, {respondent: respondent}, function(response) {
			if (response.result==1) {
				$this.parent().parent().parent().parent().remove();
				var m=$('#memo_' + response.memoId);
				m.css({opacity: 1, width: 'auto'}).removeClass('memo-disabled').draggable('enable');
			} else {
				alert('Fout bij verwijderen. Probeert u het aub later nog eens.');
			}
		}, 'json');
	}	else {
		var memoId=$this.parent().parent().parent().parent().data('memoId');
		$('#memo_' + memoId).css('opacity', 1).css('opacity', 'auto').removeClass('memo-disabled').draggable('enable');
		$this.parent().parent().parent().parent().remove();
	}
})

/******************
 .PUNAISE CLICK
******************/
$('#letter').delegate('.punaise', 'click', function(e) {
	if ($(this).parent().find('.memo-inner').is(':visible')) {
		$(this).parent().css('z-index', 10);
		$(this).parent().find('.memo-inner').fadeOut();
	} else {
		$(this).parent().css('z-index', 1000);
		$this=$(this).parent().find('.memo-inner');
		$('#letter .memo-inner').not($this).fadeOut();
		$this.fadeIn();
	}
});

});