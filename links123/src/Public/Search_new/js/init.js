var screenStyle = '';
if(screen.width >= 1366){ screenStyle = 'widescreen'; }
if($.cookies.get('screenStyle') == 'wide'){
	screenStyle = 'widescreen';
}else if($.cookies.get('screenStyle') == 'nml'){
	screenStyle = '';
}
document.getElementsByTagName('body')[0].className = screenStyle;
