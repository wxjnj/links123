/*
*   Calendar
*/
(function(){
	var config = {};
	config.CHS_WEEKS = ['一','二','三','四','五','六','日'];
	config.CHS_MONTHS = ['一','二','三','四','五','六','七','八','九','十','十一','十二'];
	config.ENG_WEEKS = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
	config.ENG_MONTHS = ['January','February','March','April','May','June','July','Auguest','September','October','November','December'];
	function countObjLength(o){
		var count = 0, i;
		for(i in o) count ++;
		return count;
	}
	Date.prototype.add = function(days){
		return new Date(this * 1 + days * 86400000);
	};
	//oo
	var Class = function(parent){
		var _class = function(){
				this.init.apply(this,arguments);
			}, key, subclass;
		_class.prototype.init = function(){};
		if(parent){
			subclass = function(){};
			subclass.prototype = parent.prototype;
			_class.uber = parent.prototype;
			_class.prototype = new subclass;
		}
		_class.extend = function(obj){
			for(key in obj){
				_class[key] = obj[key];
			}
		};
		_class.include = function(obj){
			for(key in obj){
				_class.prototype[key] = obj[key];
				if(_class.uber && typeof(_class.uber[key]) == 'function'){
					obj[key].spfunc = _class.uber[key];
				}
			}
		};
		_class.prototype._super = function(){
			arguments.callee.caller.spfunc.apply(this, arguments);
		};
		return _class;
	};

	//top控制器
	var Calendar = {
		Init: function(type){
			var self = this;
			self.type = type || 'Date';
			self.weekStart = 1;
			self.marksStore = {};
			self.timer = null;
			var today = self.today = Date.today();
			self.tooltip = $('<div class="calendar-tip"><div class="content"></div><span class="ang"></span></div>');
			self.ajaxOverlayer = $('.cal-ajax-overlayer');
			self.setCurrentDate(today);
			$('.cal-header-date-prev').on('click', function(){
				self.prevView();
			});
			$('.cal-header-date-next').on('click', function(){
				self.nextView();
			});
			$('.cal-view-select-btn').on('click', function(){
				$('.cal-view-select-btn').removeClass('active');
				var type = $(this).attr('data-type');
				self.changeType(type);
			});
			$('.cal-body').on('mouseover', '.cal-month td', function(){
				var td = $(this);
				var date = td.find('a').attr('data-date');
				if(!self.marksStore[self.currentMarkId] || !self.marksStore[self.currentMarkId][date]) return;
				var marks = self.marksStore[self.currentMarkId][date];
				var tem = '';
				$.each(marks, function(k, v){
					tem += '<p>' + v.desc + '</p>';
				});	
				self.tooltip.find('.content').html(tem).end().appendTo(td).show();
			}).on('mouseout', '.cal-month td', function(){
				self.tooltip.hide();
			});
			self.DateView.Init();
			self.MonthView.Init();
			self.WeekView.Init();
			self.DateView.miniMonthView.render();
			self.DateView.mainPanel.show();
			self.loadMarks();
		},
		showDate: function(){
			var self = this;
			var type = self.type;
			var currentDate = self.currentDate;
			var currentMonth = self.currentMonth;
			var currentYear = self.currentYear;
			if(type == 'Date'){
				self.date_text = currentYear + '年' + (currentMonth + 1) + '月' + currentDate + '日';
			}
			if(type == 'Week') {
				var start = self.currentWeekStartObject;
				var end = self.currentWeekEndObject;
				var startYear = start.getFullYear();
				var startMonth = start.getMonth() + 1;
				var startDate = start.getDate();
				var endYear = end.getFullYear();
				var endMonth = end.getMonth() + 1;
				var endDate = end.getDate();
				var endShowYear = endYear == startYear ? '' : endYear + '年';
				if(endShowYear != '' || endMonth != startMonth){
					var endShowMonth = endMonth + '月';
				}else{
					var endShowMonth = '';
				}
				self.date_text = startYear + '年' + startMonth + '月' + startDate + '日 - ' + endShowYear + endShowMonth + endDate + '日';
			}
			if(type == 'Month') {
				self.date_text = currentYear + '年' + (currentMonth+1) + '月';
			}
			$('.cal-header-date').html(self.date_text);
		},
		changeType: function(targetType){
			this.type = targetType;
			$('.cal-view-select-btn').removeClass('active');
			$('.cal-view-select-btn-' + targetType.toLowerCase()).addClass('active');
			this.showDate();
			this.DateView.mainPanel.hide();
			this.WeekView.mainPanel.hide();
			this.MonthView.mainPanel.hide();
			var cur = this[targetType + 'View'];
			cur.mainPanel.show();
			if(targetType == 'Date'){
				Calendar.DateView.miniMonthView.render();
			}
			if(targetType == 'Week'){
			}
			if(targetType == 'Month'){
				Calendar.MonthView.tableView.render();
			}
			cur.renderMarks();
		},
		prevView: function(){
			this[this.type + 'PrevView']();
		},
		nextView: function(){
			this[this.type + 'NextView']();
		},
		DateChangeFunc: function(t){
			var self = this;
			var targetDate = new Date(self.currentYear,
				self.currentMonth,
				t);
			self.setCurrentDate(targetDate);
			self.DateView.miniMonthView.render();
			if(self.marksStore[self.currentMarkId]){
				self.DateView.renderMarks();
			}else{
				self.loadMarks();
			}
		},
		DatePrevView: function(){
			this.DateChangeFunc(this.currentDate - 1);
		},
		DateNextView: function(){
			this.DateChangeFunc(this.currentDate + 1);
		},
		WeekChangeFunc: function(t){
			var self = this;
			var targetDate = new Date(self.currentYear,
				self.currentMonth, t);
			self.setCurrentDate(targetDate);
			var id_start = self.currentWeekStartObject.getFullYear() + '-' + self.currentWeekStartObject.getMonth();
			var id_end = self.currentWeekEndObject.getFullYear() + '-' + self.currentWeekEndObject.getMonth();
			if(self.marksStore[id_start] && self.marksStore[id_end]){
				self.WeekView.renderMarks();
			}else{
				self.loadMarks();
			}
		},
		WeekPrevView: function(){
			this.WeekChangeFunc(this.currentWeekStart - 7);
		},
		WeekNextView: function(){
			this.WeekChangeFunc(this.currentWeekStart + 7);
		},
		MonthChangeFunc: function(t){
			var self = this;
			var targetDate = new Date(self.currentYear,
				t, 1);
			self.setCurrentDate(targetDate);
			self.MonthView.tableView.render();
			if(self.marksStore[self.currentMarkId]){
				self.MonthView.renderMarks();
			}else{
				self.loadMarks();
			}
		},
		MonthPrevView: function(){
			this.MonthChangeFunc(this.currentMonth - 1);
		},
		MonthNextView: function(){
			this.MonthChangeFunc(this.currentMonth + 1);
		},
		//ajax封装
		request: function(params){
			var self = this;
			//self.ajaxOverlayer.find('.content').html('').end().show();
			var defaultConfig = {
				type: 'GET',
				dataType: 'json'
			};
			params = $.extend(defaultConfig, params);
			var defer = $.Deferred();
			var ajax = $.ajax(params).done(function(json) {
				if (json['status'] === 1) {
					defer.resolveWith(ajax, [json['data']]);
				} else {
					defer.rejectWith(ajax, [json['status'], json['info']]);
				}
			}).fail(function(xhr, textStatus) {
				if (xhr.status == 200) {
					defer.rejectWith(ajax, [xhr.status, 'JSON解析失败']);
				} else {
					defer.rejectWith(ajax, [xhr.status, xhr.statusText]);
				}
			});
			return defer.promise();
		},
		//计算月份天数
		howManyDaysInMonth: function(y, m){
			var len = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
			if (m == 1 && (y % 40 == 0 && y % 100 != 0) || y % 400 == 0) len[1] = 29;
			return len[m];
		},
		//计算日期是全年的第几天
		posInYear: function(y, m, d){
			for (var i = 1; i < m; ++i) {
				d += howManyDaysInMonth(y, i);
			}
			return d;
		},
		compare: function(a, b){
			if(a.getFullYear() < b.getFullYear){
				return -1;
			}else if(a.getFullYear() > b.getFullYear()){
				return 1;
			}else if(a.getMonth() < b.getMonth()){
				return -1;
			}else if(a.getMonth() > b.getMonth()){
				return 1;
			}else if(a.getDate() < b.getDate()){
				return -1;
			}else if(a.getDate() > b.getDate()){
				return 1;
			}else{
				return 0;
			}
		},
		//获取日期是星期几
		posInWeek: function(y, m, d){
			return (new Date(y, m ,d)).getDay();
		},
		setCurrentDate: function(day){
			var self = this;
			self.currentDateObject = day;
			self.currentDate = day.getDate();
			self.currentMonth = day.getMonth();
			self.currentYear = day.getFullYear();
			self.currentWeekStart = day.getDate() - (day.getDay() - Calendar.weekStart);
			self.currentWeekEnd = self.currentWeekStart + 6;
			self.currentWeekStartObject = new Date(self.currentYear, self.currentMonth, self.currentWeekStart);
			self.currentWeekEndObject = new Date(self.currentYear, self.currentMonth, self.currentWeekEnd);
			self.currentMarkId = self.currentYear + '-' + (self.currentMonth);
			self.showDate();
		},
		loadMarks: function(callBack){
			var self = this;
			var year = self.currentYear;
			var month = self.currentMonth;
			if(self.type == 'Week'){
				var year1 = self.currentWeekStartObject.getFullYear();
				var month1 = self.currentWeekStartObject.getMonth();
				var year2 = self.currentWeekEndObject.getFullYear();
				var month2 = self.currentWeekEndObject.getMonth();
			}
			//周有跨年和跨月
			if(self.type == 'Week' && year1 != year2 || month1 != month2 ){
				var o1 = self.request({
					url: PUBLIC + '/IndexV4/src/json/' + year1 + '/' + (month1+1) + '.json'
				});
				var o2 = self.request({
					url: PUBLIC + '/IndexV4/src/json/' + year2 + '/' + (month2+1) + '.json'
				});

				$.when(o1, o2).fail(function(c, e){
					self[self.type + 'View'].renderMarks();
				}).done(function(d1, d2){
					self.ajaxOverlayer.hide();
					d1 = d1 === null ? [] : d1;
					d2 = d2 === null ? [] : d2;
					self.marksStore[year1 + '-' + month1] = d1;
					self.marksStore[year2 + '-' + month2] = d2;
					self[self.type + 'View'].renderMarks();
				});
			}else{
				self.request({
					url: PUBLIC + '/IndexV4/src/json/' + year + '/' + (month+1) + '.json'
				}).fail(function(c, e){
					self[self.type + 'View'].renderMarks();
				}).done(function(d){
					self.ajaxOverlayer.hide();
					d = d === null ? [] : d;
					self.marksStore[self.currentMarkId] = d;
					self[self.type + 'View'].renderMarks();
				});
			}
		}
	};
	Calendar.DateView = {
		Init: function(){
			var self = Calendar.DateView;
			self.mainPanel = $('.cal-day');
			self.miniMonthElement = $('.cal-mini-month-panel').find('tbody');
			self.marksListElement = $('.cal-day-note-list');
			self.burnDownElement = $('.cal-day-burn-down-chart');
			self.burnDownChartElement = self.burnDownElement.find('.chart');
			self.burnDownChartTimeElement = self.burnDownChartElement.find('.time-show');
			self.miniMonthView = new MiniMonthView;
			self.miniMonthElement.on('click', 'a', function(){
				var d = $(this).attr('data-date');
				Calendar.DateChangeFunc(d);
			});
			self.burnDownElement.find('.chart-body').dblclick(function(e){
				var pos = e.pageY - $(this).offset().top;
				pos = pos - pos % 3;
				var time = +Date.today() + pos / 3 * 15 * 60 * 1000
				var mark = new MarkClass({
					time: time / 1000,
					desc: ''
				});
				mark.appendTo($(this));
				var baseLine = self.burnDownChartElement.offset().top + self.burnDownChartElement.height();
				if(mark.html.offset().top + mark.html.height() <= baseLine){
					mark.html.addClass('cal-day-event-item-pass');
				}
				mark.html.find('.desc').trigger('dblclick');
			});
		},
		renderBurnDownChart: function(){
			clearTimeout(Calendar.timer);
			var self = this;
			var op = Calendar.compare(Calendar.currentDateObject, Date.today());
			if(op != 0){
				self.burnDownChartTimeElement.hide();
				if(op < 0){
					self.burnDownChartElement.height(self.burnDownElement.height()).show();
					self.burnDownElement.find('.cal-day-event-item').addClass('cal-day-event-item-pass');
				}else{
					self.burnDownChartElement.hide();
				}
			}else{
				var now = new Date();
				var pass = now - Date.today();
				var sec_px = 24 * 60 * 60 / self.burnDownElement.find('.chart-body').height();
				var height = pass / 1000 / sec_px + 39;
				self.burnDownChartElement.height(height).show();
				self.burnDownChartTimeElement.html(now.toString('HH:mm')).show();

				var baseLine = self.burnDownChartElement.offset().top + self.burnDownChartElement.height();
				self.burnDownElement.find('.cal-day-event-item').each(function(){
					if($(this).offset().top + $(this).height() <= baseLine){
						$(this).addClass('cal-day-event-item-pass');
					}
				});
				Calendar.timer = setTimeout(function(){
					Calendar.DateView.renderBurnDownChart();
				}, 30000);
			}
		},
		renderMarks: function(){
			var self = this;
			var tem = Calendar.marksStore[Calendar.currentYear + '-' + Calendar.currentMonth];
			if(tem && tem[Calendar.currentDate]){
				var marks = self.marks = tem[Calendar.currentDate];
			}else{
				var marks = self.marks = [];
			}
			var lis = '';
			var count = 0;
			var chartBody = self.burnDownElement.find('.chart-body');
			chartBody.empty();
			$.each(marks, function(k, v){
				lis += '<li>' + k + ' : ' + v.desc + '</li>';
				count++;
				var mark = new MarkClass(v);
				mark.appendTo(chartBody);
			});
			self.marksListElement.find('.cal-day-ul').html(lis);
			self.marksListElement.find('.marks-count').html(count);
			self.renderBurnDownChart();
		}
	};
	Calendar.MonthView = {
		Init: function(){
			var self = this;
			self.mainPanel = $('.cal-month');
			self.tableElement = self.mainPanel.find('table');
			self.tableView = new MonthTable;
			self.tableElement.on('click', 'a', function(){
				var y = Calendar.currentYear;
				var m = Calendar.currentMonth;
				var d = $(this).attr('data-date');
				d = new Date(y,m,d);
				Calendar.setCurrentDate(d);
				Calendar.changeType('Date');
			});
		},
		renderBurnDownChart: function(){
			var self = this;
			var year = Calendar.currentYear;
			var month = Calendar.currentMonth;
			var todayDate = Calendar.today.getDate();

			if(year <= Calendar.today.getFullYear() && month < Calendar.today.getMonth()){
				self.tableElement.find('td').addClass('month_pass_date');
			}else if(year == Calendar.today.getFullYear() && month == Calendar.today.getMonth()){
				self.tableElement.find('td').each(function(k, v){
					var cur = $(v).find('a').attr('data-date');
					if(cur != -1 && cur < todayDate) {
						$(v).addClass('month_pass_date');
					}else if(cur == todayDate){
						self.todayElement = $(v);
					}
				});
			}
		},
		renderMarks: function(){
			var self = this;
			var marks = Calendar.marksStore[Calendar.currentYear + '-' + Calendar.currentMonth];
			if(marks){
				self.marks = marks;
			}else{
				self.marks = marks = {};
			}
			$.each(marks, function(k, v){
				var cur = self.tableElement.find('.td_' + k).parent('td');
				$.each(v, function(i, n){
					var top = parseInt(i / 7) * 7 + 5;
					var left = i % 7 * 7 + 5;
					top = 'top:' + top + 'px;';
					left = 'left:' + left + 'px;';
					var style = top + left;
					cur.append('<b class="month_task_dot" style="' + style + '"></b>');
				});
			});
			self.renderBurnDownChart();
		}
	};
	Calendar.WeekView = {
		Init: function(){
			var self = this;
			self.mainPanel = $('.cal-week');
			self.tableElement = self.mainPanel.find('table');
			self.tableElement.on('click', 'td', function(){
				var y = $(this).attr('data-year');
				var m = $(this).attr('data-month');
				var d = $(this).attr('data-date');
				Calendar.setCurrentDate(new Date(y,m,d));
				Calendar.changeType('Date');
			});
		},
		renderBurnDownChart: function(){
			clearTimeout(Calendar.timer);
			var self = this;
			var start = Calendar.currentWeekStartObject;
			var today = Calendar.today;
			self.burnDownChartElement = $('<div class="chart"><h5 class="time-show">00:00</h5></div>');
			var todayIndex = today.getDay() - Calendar.weekStart;
			var today_td = self.tableElement.find('td:eq(' + todayIndex + ')');
			self.burnDownChartElement.appendTo(today_td);
			var now = new Date();
			var pass = now - Calendar.today;
			var sec_px = 24 * 60 * 60 / today_td.height() - 6;
			var height = pass / 1000 / sec_px - 6;
			self.burnDownChartElement.height(height).show();
			self.burnDownChartElement.find('.time-show').html(now.toString('HH:mm'));
			var baseLine = self.burnDownChartElement.offset().top + self.burnDownChartElement.height();
			today_td.find('.week-mark-line').each(function(){
				if($(this).offset().top + $(this).height() <= baseLine){
					$(this).addClass('week-mark-line-pass');
				}
			});
			Calendar.timer = setTimeout(function(){
				Calendar.DateView.renderBurnDownChart();
			}, 30000);
		},
		renderMarks: function(){
			var self = this;
			var start = Calendar.currentWeekStartObject;
			var end = Calendar.currentWeekEndObject;
			var today = Calendar.today;
			var year, month, date, marks, tem, td, op, i, line, time, desc, hour, minute;
			self.tableElement.find('td').empty();
			var burndown = false;
			for(i = 0; i <=6; i++){
				tem = start.add(i);
				year = tem.getFullYear();
				month = tem.getMonth();
				date = tem.getDate();

				if(Calendar.marksStore[year + '-' + month] && Calendar.marksStore[year + '-' + month][date]){
					marks = Calendar.marksStore[year + '-' + month][date];
				}else{
					marks = [];
				}
				td = self.tableElement.find('td:eq(' + i + ')');
				op = Calendar.compare(tem, today);
				td.removeClass('month_pass_date');
				if(op == -1) td.addClass('month_pass_date');
				if(op == 0) burndown = true;
				$.each(marks, function(k, v){
					line = $('<div class="week-mark-line"><b></b><span></span></div>');
					time = new Date(v.time * 1000);
					desc = v.desc;
					hour = time.getHours();
					minute = time.getMinutes();
					line.css('top', hour * 4 * 3 + minute / 15 * 3);
					td.attr({
						'data-date': date,
						'data-month': month,
						'data-year': year
					}).append(line);
				});
			}
			if(burndown){
				self.renderBurnDownChart();
			}
		}
	};
	//月级视图 - 基础
	var MonthView = new Class;
	MonthView.include({
		init: function(){
			this.weekStart = Calendar.weekStart;
			this.year = null;
			this.month = null;
		},
		initBaseArray: function(y, m){
			var self = this;
			self.year = y;
			self.month = m;
			var weekStart = self.weekStart;
			var len = Calendar.howManyDaysInMonth(y, m);
			var startWeekDay = Calendar.posInWeek(y, m, 1);
			var arr = [];
			var i;
			for(i = 1; i <= len; i++){
				arr.push(i);
			}
			for(i = 1, len = startWeekDay - weekStart; i <= len; i++){
				arr.unshift(0);
			}
			len = arr.length % 7;
			if(!len == 0){
				len = 7 - len;
				for(i = 1; i <= len; i++){
					arr.push(-1);
				}
			}
			self.baseArray = arr;
		},
		buildTable: function(){
			var self = this;
			if(self.year !== Calendar.currentYear || self.month !== Calendar.currentMonth) {
				self.initBaseArray(Calendar.currentYear, Calendar.currentMonth);
			}
			var baseArray = self.baseArray;
			var o;
			var table = '';
			for(var i = 0, len = baseArray.length; i < len; i++){
				o = baseArray[i];
				if(o == 0 || o == -1){
					o = '<td><a data-date="'+ o +'" style="display:none"></a></td>';
				}else{
					o = '<td><a class="td_' + o + '" href="javascript:;" data-date="' + o + '">' + o + '</a></td>';
				}
				if((i + 1) % 7 == 0){
					o += '</tr>'
				}
				if(i % 7 == 0){
					o = '<tr>' + o;
				}
				table += o;
			}
			return table;
		}
	});
	//日级视图的月历
	var MiniMonthView = new Class(MonthView);
	MiniMonthView.include({
		render: function(wrap){
			var self = this;
			wrap = wrap || Calendar.DateView.miniMonthElement;
			var table = self.buildTable();
			if(wrap instanceof $){
				wrap.html(table);
			}else{
				$(wrap).html(table);
			}
			wrap.find('.td_' + Calendar.currentDate).addClass('active');
		}
	});
	//月级视图的月历
	var MonthTable = new Class(MonthView);
	MonthTable.include({
		render: function(wrap){
			var self = this;
			//console.log(Calendar.MonthView.tableElement);
			wrap = wrap || Calendar.MonthView.tableElement.find('tbody');
			var table = self.buildTable();
			if(wrap instanceof $){
				wrap.html(table);
			}else{
				$(wrap).html(table);
			}
			wrap.find('.td_' + Calendar.currentDate).addClass('active');
		}
	});
	//事件
	var MarkClass = new Class;
	MarkClass.include({
		init: function(m){
			var self = this;
			var time = new Date(m.time * 1000);
			var desc = m.desc;
			var year = time.getFullYear();
			var month = time.getMonth();
			var date = time.getDate();
			var hour = time.getHours();
			var minute = time.getMinutes();
			self.id = m.id;
			self.date = date;
			self.baseTime = +new Date(year, month, date, 0);
			var showtime = time.toString('HH:mm');
			var html = '<div class="cal-day-event-item">' +
				'<b class="dot"></b>' +
				'<span class="time">' + showtime + '</span>' +
				'<span class="desc">' + desc + '</span>' +
				'<a class="enter" href="javascript:;">确定</a>' +
				'<a class="del" href="javascript:;">×</a>' +
				'</div>';
			self.html = $(html).css('top', hour * 4 * 3 + minute / 15 * 3);
			self.html.find('.desc').dblclick(function(){
				var desc = $(this).html();
				$(this).html('<input class="input_desc" type="text" maxlength="30" value="' + desc + '" >');
				self.html.find('.input_desc').select();
				self.html.find('.enter').show();
				return false;
			});
			self.html.find('.enter').click(function(){
				var d = $(this).siblings('.desc')
				var v = d.find('.input_desc').val();
				d.html(v);
				$(this).hide();
				self.update();
			});
			self.html.find('.del').click(function(){
				self.html.remove();
			});
		},
		update: function(){
			var self = this;
			if(self.id !== undefined){
				//修改一条记录
				/*
				post数据
				{
					id: xxx,
					desc: xxx,
					time: xxx
				}
				*/
			}else{
				//创建一条记录
				/*
				post数据
				{
					desc: xxx,
					time: xxx
				}
				*/
			}
		},
		removeElement: function(){
		  this.html.remove();
		},
		appendTo: function(el){
			var self = this;
			self.html.appendTo(el);
			self.html.draggable({
				axis: 'y',
				grid: [100, 3],
				containment: 'parent',
				start: function(event, ui) {},
				drag: function(event, ui) {
					var top = ui.position.top;
					var time = top / 3 * 15 * 60 * 1000 + self.baseTime;
					time =  new Date(time);
					if(time.getDate() != self.date){
						time = '24:00';
					}else{
						time = time.toString('HH:mm');
					}
					$(this).find('.time').html(time);
					baseLine = Calendar.DateView.burnDownChartElement.offset().top +
						Calendar.DateView.burnDownChartElement.height();
					if($(this).offset().top + $(this).height() <= baseLine){
						$(this).addClass('cal-day-event-item-pass');
					}else{
						$(this).removeClass('cal-day-event-item-pass');
					}
				},
				stop: function(event, ui) {
					self.update();
				}
			});
		}
	});
	window.Calendar = Calendar;
})();
// 调用
$(function(){
	Calendar.Init()
});

