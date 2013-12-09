/*
var screenStyle = '';
if(screen.width >= 1366){ screenStyle = 'widescreen'; }
if($.cookies.get('screenStyle') == 'wide'){
	screenStyle = 'widescreen';
}else if($.cookies.get('screenStyle') == 'nml'){
	screenStyle = '';
}
*/
//搜索结果页保持宽屏宽度，避免横向滚动条出现
//如果窗体宽度小于1170，让thl向左移动，视觉上尽量保持居中
var screenStyle = 'widescreen';
var windowWidth=window.innerWidth
|| document.documentElement.clientWidth
|| document.body.clientWidth;
if(windowWidth < 1170){ screenStyle = 'widescreen normalscreen'; }
document.getElementsByTagName('body')[0].className = screenStyle;
