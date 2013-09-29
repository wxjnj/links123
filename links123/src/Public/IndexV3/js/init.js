var screenStyle = '';
if($.cookies.get('screenStyle') == 'wide'){
	screenStyle = 'widescreen';
}
if($.cookies.get('screenStyle') != 'wide' && screen.width >= 1440){
	screenStyle = 'widescreen';
}
document.getElementsByTagName('body')[0].className = screenStyle;