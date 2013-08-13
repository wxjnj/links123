// JavaScript Document
var blankreg = new RegExp("^[ ]*$");
$(document).ready(function() {
	
	//position()ing of tips menu
	//	$("#tips").css('width', $("#search_text").width());
	//$("#tips").css('left', $("#search_text").position().left);
	
	//是IE7，则添加left
	if((navigator.appName == "Microsoft Internet Explorer") && (navigator.appVersion.match(/7./i)=='7.')){ 
		$("#tips").css('left', $("#search_text").position().left);
	
	}
	$("#tips").css('top', $("#search_text").position().top + 27);

	//////////////////////////// Start odf Search Tips and Autocomplete ///////////////////////////////	
	var lis, count = 0;

	//search field toool tips
	$("#search_text").keyup(function(event) {

		if ($("#search_text").val().match(blankreg) == null) {
			//store origial entry
			if (event.keyCode != 40 && event.keyCode != 38) {
				$("#search_text_o").val($("#search_text").val());
			}
			
			//some entry that makes sense
			$.ajax({
				type: "POST",
				url: APP + "Index/searchTips",
				cache: false,
				data: "search_text=" + $("#search_text").val(),
				success: function(response) {
					//
					if (response == -1) {
						//no any match found	
						$("#tips").css('display', 'none');
					} else {
						//some match found	
						$("#tips").css('display', 'block');
						$("#tips").html(response);

						lis = $("#tips li input.valid.valid").size();

						$("#tips li input.valid").mouseover(function() {
							$(this).css('background', '#eee');
							$("#search_text").val($(this).val());
						});

						$("#tips li input.valid").mouseout(function() {
							$(this).css('background', 'none');
						});

						$("#tips li input.valid").click(function() {
							$("#search_text").val($(this).val());
							$("#tips").css('display', 'none');
						});

						//key down from search text field
						$("#search_text").keydown(function(event) {
							if (event.keyCode == 40) {
								event.preventDefault();

								$("#search_text").val($("#tips li input#li1").val());
								$("#tips li input#li0").focus();
								$("#tips li input#li1").css('background', '#eee');
								count = 1;

							} else if (event.keyCode == 38) {
								$("#search_text").val($("#tips li input.valid:last").val());
								$("#tips li input.valid:last").focus();
								$("#tips li input.valid:last").css('background', '#eee');
								count = lis;

							}
						});

						//hide tips when mouse hit search box or anywhere esle on the document
						$("#search_text").mousedown(function() {
							$("#tips").fadeOut('slow');
						});

						$(document).mousedown(function(e) {
							if (e.pageX > ($("#tips").position().left + $("#tips").width()) || e.pageX < ($("#tips").position().left) || e.pageY > $("#tips").position().top + $("#tips").height() || e.pageX < ($("#tips").position().top)) {
								$("#tips").fadeOut('slow');
							}
						});

						//scrolling up and down tip list or submit form
						$("#tips li input").keydown(function(event) {
							if (event.keyCode == 38) {
								//scrolling up and down tips list
								count--;
								if (count < 0) {
									count = lis - 1;
								} else if (count == 0) {
									//		
									$("#search_text").focus();
									$("#search_text").val("");
									setTimeout(function() {
										$("#search_text").val($("#search_text_o").val())
									},1);
								}

								//scroll up tips list
								$("#tips li input.valid").each(function() {
									if (this.id == 'li' + count) {
										$(this).css('background', '#eee');
										$("#search_text").val($(this).val());
									} else {
										$(this).css('background', 'none');
									}
								});

							} else if (event.keyCode == 40) {
								//scroll down tips list
								event.preventDefault();
								count++;
								if (count > lis) {
									count = 0;
									$("#search_text").focus();
									$("#search_text").val("");
									setTimeout(function() {
										$("#search_text").val($("#search_text_o").val())
									},1);
								}

								//iterate through list 
								$("#tips li input.valid").each(function() {
									if (this.id == 'li' + count) {
										$(this).css('background', '#eee');
										$("#search_text").val($(this).val());
									} else {

										$(this).css('background', 'none');

									}
								});
							} else if (event.keyCode == 13) {
								//submit form
								$("#tips").fadeOut('slow');
								$("#btn_search").trigger("click");
								//////// trigger ajax
								
								$.ajax({
			type: "POST",
			url: APP + "Index/searchTips",
			cache: false,
			data: "sbmt=true&search_text=" + $("#search_text").val(),
			success: function(response) { }

		});
		
								
							}
							//
						});

					}
					////////////end of successful response ////////////
				},
				error: function() {
					//on ajax error	  
					$("#tips").fadeOut('slow');
				}

				///////////////// end of Ajax /////////////////  
			});

			//no entry
		} else {
			$("#tips").fadeOut('slow');
		}

		//////////////////////////// End odf Search Tips and Autocomplete ///////////////////////////////	
	});

/////	//submit search form ///////////
	$("#btn_search").click(function() {

		$.ajax({
			type: "POST",
			url: APP + "Index/searchTips",
			cache: false,
			data: "sbmt=true&search_text=" + $("#search_text").val(),
			success: function(response) { }

		});

	});

	//////////////////// End of Document Ready ////////////////////////////////////////	
})