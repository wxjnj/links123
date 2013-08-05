$(function(){
	//
	ZeroClipboard.setMoviePath( PUBLIC+"/skin/zeroclipboard/ZeroClipboard.swf" ); 
	var clip = new ZeroClipboard.Client();
	clip.setHandCursor( true );
	//
	clip.addEventListener('load', function (client) {
		//alert("Flash movie loaded and ready.");
	});
	clip.addEventListener('mouseOver', function (client) {
		// update the text on mouse over
		clip.setText( $("#biao1").val() );
	});
	clip.addEventListener('complete', function (client, text) {
		//alert("Copied text to clipboard: " + text );
		$("#biao1").select();
		alert("已复制好，可贴粘。");
	});
	//
	clip.glue( 'd_clip_button', 'd_clip_container' );

	/**/
	$("#biao1").change(function(){
		clip.setText($(this).val());
	})
});