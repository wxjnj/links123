/*! Links123CN - v4.0.0 - 2013-10-27 */
var screenStyle = '';
if(screen.width >= 1280){ screenStyle = 'widescreen'; }
if($.cookies.get('screenStyle') == 'wide'){
	screenStyle = 'widescreen';
}else if($.cookies.get('screenStyle') == 'nml'){
	screenStyle = '';
}
document.getElementsByTagName('body')[0].className = screenStyle;