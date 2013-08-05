if (!window.XMLHttpRequest) {
	window.attachEvent("onload", enableAlphaImages);
}

function enableAlphaImages(){
	for (var i=0; i<document.all.length; i++){
			var obj = document.all[i];
			var bg = obj.currentStyle.backgroundImage;
			var img = document.images[i];
			if (bg && bg.match(/\.png/i) != null) {
				var img = bg.substring(5,bg.length-2);
				var offset = obj.style["background-position"];
				obj.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+img+"', sizingMethod='crop')";
				obj.style.background = "none";
		} else if (img && img.src.match(/\.png$/i) != null) {
			var src = img.src;
			img.style.width = img.width + "px";
			img.style.height = img.height + "px";
			img.style.filter ="progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+src+"', sizingMethod='crop')"
			img.src = "images/png.gif";//替换透明PNG的图片
		}
	}
}

