var Cal = function(){};
Cal.howManyDaysInMonth = function(y, m){
	var len = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	if ((y % 40 == 0 && y % 100 != 0) || y % 400 == 0){
		len[1] = 29;
	}
	return len[m - 1];
};
Cal.posInYear = function(y, m, d){
	for (var i = 1; i < m; ++i) {
		d += howManyDaysInMonth(y, i);
	}
	return d;
}
Cal.posInWeek = function(y, m, d){
	return (new Date(y, m ,d)).getDay();
};
//(y - 1 + (y - 1) / 4 - (y - 1) / 100 + (y - 1) / 400 + d) % 7;



// 调样式用
$(function(){

	var config = {};
	config.CHS_WEEKS = ['一','二','三','四','五','六','日'];
	config.CHS_MONTHS = ['一','二','三','四','五','六','七','八','九','十','十一','十二'];
	config.ENG_WEEKS = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
	config.ENG_MONTHS = ['January','February','March','April','May','June','July','Auguest','September','October','November','December'];

	var date = new Date();
	var year = date.getFullYear();
	var month = date.getMonth();
	var day = date.getDate();
	var daysLen = Cal.howManyDaysInMonth(year, month);

	var posInWeek = new Date(year, month, 31);
});


