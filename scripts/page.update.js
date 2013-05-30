$(document).ready(function() {

/**********************
 INIT
**********************/
var n=0,mode='draw',x,y,init_pos=$('#letter-overlay').offset();
$('#regions input[id^="region_input"]').each( function() {
	var id=$(this).attr('id');
	id=parseInt(id.substring(id.lastIndexOf('_') + 1));
	if (id>n) {
		n=id;
	}
});

$(window).resize( function() {
	init_pos=$('#letter-overlay').offset()
});

$('.resize').draggable({
	disabled: false,
	drag: function(e) {
		var target=$(e.target), parent=target.parent();
		parent.css({
			width: Math.max(target.offset().left + $(target).width() - parent.offset().left),
			height: Math.max(target.offset().top + $(target).height() - parent.offset().top)
		});
	},
	stop: function(e) {
		var p=$(e.target).parent(), id=p.attr('id');
		id=id.substring(id.lastIndexOf('_') + 1);
		$('#region_input_' + id).val( getBB(p) );
	}
}).hover(
	function() {
		$(this).parent().draggable('disable');
	},
	function() {
		if (mode!='draw') {
			$(this).parent().draggable('enable');
		}
	}
).draggable('disable');

$('.box').draggable({
	stop: function(e) {
			var elem=$(e.target), id=elem.attr('id');
			id=id.substring(id.lastIndexOf('_') + 1);
			$('#region_input_' + id).val( getBB(elem) );
		}
	}).draggable('disable');

/**********************
 GET BOUNDING BOX
**********************/
function getBB(elem) {
	var o=elem.offset();
	return Math.round(o.left-init_pos.left)+','+Math.round(o.top-init_pos.top)+','+Math.round(elem.width())+','+Math.round(elem.height())
}

/**********************
 START DRAW
**********************/
$('#letter-overlay').mousedown(function(e) {
	if (mode!='draw') {
		return;
	}
	$("#current").attr({ id: '' })
	x = Math.round(e.pageX - init_pos.left);
	y = Math.round(e.pageY - init_pos.top);
	var box=$('#NewBox').clone();
	box.attr('id','current').css({top: y, left: x}).show().appendTo($('#letter-overlay'));
	$('.resize', box).draggable({
		disabled: false,
		drag: function(e) {
			var target=$(e.target), parent=target.parent();
			parent.css({
				width: Math.max(target.offset().left + $(target).width() - parent.offset().left),
				height: Math.max(target.offset().top + $(target).height() - parent.offset().top)
			});
		},
		stop: function(e) {
			var p=$(e.target).parent(), id=p.attr('id');
			id=id.substring(id.lastIndexOf('_') + 1);
			$('#region_input_' + id).val( getBB(p) );
		}
	}).hover(
		function() {
			$(this).parent().draggable('disable');
		},
		function() {
			if (mode!='draw') {
				$(this).parent().draggable('enable');
			}
		}
	).draggable('disable');
});

/**********************
 DRAWING
**********************/
$('#letter-overlay').mousemove(function(e) {
	if (mode!='draw') {
		return;
	}
	$("#current").css({
		width:Math.ceil(e.pageX - x - init_pos.left),
		height:Math.ceil(e.pageY - y - init_pos.top)
	});
});

/**********************
 STOP DRAW
**********************/
$('#letter-overlay').mouseup(function() {
	if (mode!='draw') {
		return;
	}
	n++;
	var cur=$('#current'),o=$('#current').offset();
	$('<input>').attr({id: 'region_input_' + n, name: 'Region[]', type: 'hidden'}).val(
		getBB(cur)
	).appendTo('#regions');
	cur.attr({ id: 'region_' + n }).draggable({
		stop: function(e) {
			var elem=$(e.target), id=elem.attr('id');
			id=id.substring(id.lastIndexOf('_') + 1);
			$('#region_input_' + id).val( getBB(elem) );
		}
	}).draggable('disable');

});

/**********************
 SWITCH TO DRAW MODE
**********************/
$('#ModeDraw').click( function() {
	mode='draw';
	$('#ModeDraw').addClass('mode-img-current');
	$('#ModeResize').removeClass('mode-img-current');
	$('#letter-overlay').css('cursor','crosshair');
	$('.box').css('cursor','inherit').draggable('disable');
	$('.resize').css('cursor','inherit').draggable('disable');
});

/**********************
 SWITCH TO RESIZE MODE
**********************/
$('#ModeResize').click( function() {
	mode='resize';
	$('#ModeDraw').removeClass('mode-img-current');
	$('#ModeResize').addClass('mode-img-current');
	$('#letter-overlay').css('cursor','auto');
	$('.box').css('cursor','move').draggable('enable');
	$('.resize').css('cursor','nw-resize').draggable('enable');
});

/**********************
 DEFINE TRASHCAN
**********************/
$('#ImgTrash').droppable({
	accept: '.box',
	tolerance: 'pointer',
	drop: function(e,ui) {
		var elem=$(ui.draggable), id=elem.attr('id');
		id=id.substring(id.lastIndexOf('_') + 1);
		$('#region_input_' + id).remove();
		$(ui.draggable).effect('explode');
		$(e.target).css('background', '#fff');
	},
	over: function(e,ui) {
		$(e.target).css('background','#f00');
	},
	out: function(e,ui) {
		$(e.target).css('background','#fff');
	}
})

});