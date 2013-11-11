// $(function(){

// 	if(!$('#K_banner_01_map').size()) return;
// 	var coodrs = $('#K_banner_01_map').find('area:first').attr('coords').split(',');
// 	$('#K_banner_01_arrow').css({
// 		left: +coodrs[0] + (coodrs[2]-coodrs[0])/2 - 27 + 'px',
// 		top: coodrs[1] - 50 + 'px'
// 	}).show();

// 	$('#K_banner_01_map').find('.doll').on('mouseover', function(){
// 		var coodrs = $(this).attr('coords').split(',');
// 		$('#K_banner_01_arrow').css({
// 			left: +coodrs[0] + (coodrs[2]-coodrs[0])/2 - 27 + 'px',
// 			top: coodrs[1] - 50 + 'px'
// 		}).show();
// 	});

// 	$('#K_banner_01').on('click', function(){
// 		$.cookies.set('big_pic_01_clicked', '1', { expiresAt: (new Date).add_day(365) });
// 	});

// 	// banner
// 	setTimeout(function(){
// 		var o = $('.big-pic');
// 		var o1 = null;
// 		$('#J_Apps>li').each(function(){
// 			if($(this).attr('id') == 1){
// 				o1 = $(this).find('img');
// 				return;
// 			}
// 		});
// 		if(!o1){ return; }
// 		var pos = o1.offset();
// 		o.css({'border': '1px dotted #ccc', 'position': 'absolute', 'z-index': 999 });
// 		o.animate({
// 			width: 0,
// 			height: 0,
// 			top: pos.top-o.height()+36,
// 			left: pos.left+36
// 		}, 1000, function(){
// 			o.remove();
// 		});

// 	}, 6000);

// });