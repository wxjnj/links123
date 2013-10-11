var ZhiDaLan = { // 直达框
	Init: function(){
		$(document).on('click', function(){
			$('#direct_text').val($('#direct_text').attr('txt')).addClass('ipton');
		});
		/*
		 $("#header").on('mouseenter', function(){
		 var tag = $.trim($('#direct_text').val());
		 if(tag == $('#direct_text').attr('txt')){
		 $("#direct_text").select().removeClass('ipton');
		 }else{
		 $("#direct_text").removeClass('ipton');
		 }
		 }).on('mouseleave', function(){
		 var tag = $.trim($('#direct_text').val());
		 if(tag == '' || tag == $('#direct_text').attr('txt')){
		 $('#search_text').select();
		 $('#direct_text').addClass('ipton');
		 }
		 });*/

		$("#direct_text").on('mouseout', function(){
			var tag = $.trim($('#direct_text').val());
			if(tag == '' || tag == $('#direct_text').attr('txt')){
				$('#search_text').select();
				$('#direct_text').addClass('ipton');
			}
		});

		$("#direct_text").on('click', function(){
			var tag = $.trim($('#direct_text').val());
			if (tag == $('#direct_text').attr('txt')){
				$('#direct_text').val('').removeClass('ipton');
			}
			return false;
		}).on('blur', function(){
				$('#direct_text').addClass('ipton');
			});

		$('.J_direct_submit').on('click', function(){
			$("#frm_drct").trigger('submit');
			$("#direct_text")[0].focus();
			return false;
		});

		$("#frm_drct").on('submit', function(){
			var tag = $.trim($('#direct_text').val());
			if (tag == '' || tag == $('#direct_text').attr('txt')){
				return false;
			}
			$('#direct_text').select();
		});
	}
};
