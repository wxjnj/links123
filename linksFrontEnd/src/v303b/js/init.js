function createCookie(c, d, f, e) {
	var e = (e) ? e : "/";
	if (f) {
		var b = new Date();
		b.setTime(b.getTime() + (f * 24 * 60 * 60 * 1000));
		var a = "; expires=" + b.toGMTString() + ";path=/"
	} else {
		var a = ""
	}
	document.cookie = c + "=" + d + a; // + "; path=" + e
}

function readCookie(b) {
	var e = b + "=";
	var a = document.cookie.split(";");
	for (var d = 0; d < a.length; d++) {
		var f = a[d];
		while (f.charAt(0) == " ") {
			f = f.substring(1, f.length)
		}
		if (f.indexOf(e) == 0) {
			return f.substring(e.length, f.length)
		}
	}
	return null
}
var screenStyle = '';
if(screen.width >= 1366){	//13寸主流目前是1366
	screenStyle = 'widescreen';
}
if(readCookie('screenStyle') == 'wide'){
	screenStyle = 'widescreen';
}else if(readCookie('screenStyle') == 'nml'){
	screenStyle = '';
}

document.getElementsByTagName('body')[0].className = screenStyle;