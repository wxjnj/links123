/*
*   Calendar
*/


(function(){

	/*
		year => 等同于自然年份
		month => 0-11
		date => 1-31
		day => 0-6
	*/
	var config = {};
	config.CHS_WEEKS = ['一','二','三','四','五','六','日'];
	config.CHS_MONTHS = ['一','二','三','四','五','六','七','八','九','十','十一','十二'];
	config.ENG_WEEKS = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
	config.ENG_MONTHS = ['January','February','March','April','May','June','July','Auguest','September','October','November','December'];

	//oo
	var Class = function(parent){
		var _class = function(){
				this.init.apply(this,arguments);
			},
			key,
			subclass;
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
		_class.prototype.get = function(k){
			return this[k];
		};
		_class.prototype.set = function(k, v){
			this[k] = v;
			return this;
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
			self.marksStore = {};

			self.timer = null;

			var today = self.today = Date.today();
			self.setCurrentDate(today);
			//self.loadMarks(self.currentYear, self.currentMonth);
			$('.cal-header-date-prev').on('click', function(){
				self.prevView();
			});
			$('.cal-header-date-next').on('click', function(){
				self.nextView();
			});

		},

		showDate: function(){
			var self = this;
			var type = self.type;
			var currentDate = self.currentDate;
			var currentMonth = self.currentMonth;
			var currentYear = self.currentYear;

			if(type == 'Date'){
				self.date_text = currentYear + '年' + (currentMonth+1) + '月' + currentDate + '日';
			}

			if(type == 'Week') {

			}

			if(type == 'Month') {
				self.date_text = currentYear + '年' + (currentMonth+1) + '月';
			}

			$('.cal-header-date').html(self.date_text);

		},

		changeType: function(targetType){
			this.type = targetType;
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
		WeekPrevView: function(){},
		WeekNextView: function(){},
		MonthPrevView: function(){},
		MonthNextView: function(){},
		//ajax封装
		request: function(params){
			var defaultConfig = {
				'type':'GET',
				dataType:'json'
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
			if (m == 1 && (y % 40 == 0 && y % 100 != 0) || y % 400 == 0){
				len[1] = 29;
			}
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
			self.currentMarkId = self.currentYear + '-' + self.currentMonth;
			self.showDate();
		},
		loadMarks: function(callBack){
			var self = this;
			var year = self.currentYear;
			var month = self.currentMonth;
			self.request({
				url: PUBLIC + '/IndexV4/src/json/' + year + '/' + month + '.json'
			}).fail(function(c, e){
				alert(c + ':' + e);
			}).done(function(d){
				self.marksStore[self.currentMarkId] = d;
				self[self.type + 'View'].renderMarks();
			});
		}
	};

	Calendar.DateView = {
		Init: function(){
			var self = Calendar.DateView;

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
					time: time,
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
				var pass = now - Calendar.today;
				var sec_px = 24 * 60 * 60 / self.burnDownElement.find('.chart-body').height();
				var height = pass / 1000 / sec_px + 39;
				self.burnDownChartElement.height(height).show();
				self.burnDownChartTimeElement.html(now.toString('HH:mm'));

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
			var marks = self.marks = Calendar.marksStore[Calendar.currentYear + '-' + Calendar.currentMonth][Calendar.currentDate];

			var lis = '';
			var count = 0;

			var time;
			var hour;
			var minute;
			var m;

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

	//月级视图 - 基础
	var MonthView = new Class;
	MonthView.include({
		init: function(){
			this.weekStart = 1;
			this.year = null;
			this.month = null;
		},
		initBaseArray: function(y, m){
			var self = this;
			self.year = y;
			self.month = y;

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
			var len = arr.length % 7;
			if(!len == 0){
				len = 7 - len;
				for(i = 1; i <= len; i++){
					arr.push(0);
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
				if(o == 0){
					o = '<td></td>';
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

	//周级视图
	var WeekView = new Class;

	//事件
	var MarkClass = new Class;
	MarkClass.include({
		init: function(m){
			var self = this;
			var time = m.time;
			var desc = m.desc;
			time = new Date(time);
			var year = time.getFullYear();
			var month = time.getMonth();
			var date = time.getDate();
			var hour = time.getHours();
			var minute = time.getMinutes();

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
			});

			self.html.find('.del').click(function(){
				self.html.remove();
			});
		},
		destroy: function(){
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
				start: function(event, ui) {
					//alert(this);
				},
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
					//alert(this);
				}
			});
		}
	});

	window.Calendar = Calendar;

})();


// 调样式用
$(function(){

	Calendar.Init()

	var DateView = Calendar.DateView;
	DateView.Init();
	DateView.miniMonthView.render();

	Calendar.loadMarks();

});


