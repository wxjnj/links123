/*
*   Calendar
*/

var Cal;

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


	//oop
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

	Cal = new Class;

	//类方法
	Cal.extend({
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
		//获取日期是星期几
		posInWeek: function(y, m, d){
			return (new Date(y, m ,d)).getDay();
		}
	});

	Cal.include({
		init: function(type){
			var self = this;
			self.type = type || 'day';
			self.marksStore = {};
			self.dayView = new DayView;

			var today = self.today = Date.today();
			self.setCurrentDay(today);
			//self.loadMarks(self.currentYear, self.currentMonth);
		},
		setCurrentDay: function(day){
			var self = this;
			self.currentDay = day.getDate();
			self.currentMonth = day.getMonth();
			self.currentYear = day.getFullYear();
		},
		loadMarks: function(callBack){
			var self = this;
			var year = self.currentYear;
			var month = self.currentMonth;
			Cal.request({
				url: PUBLIC + '/IndexV4/src/json/' + year + '/' + month + '.json'
			}).fail(function(c, e){
				alert(c + ':' + e);
			}).done(function(d){
				self.marksStore[year + '-' + month] = d;
				callBack && callBack.call(self, d);
			});
		}
	});

	//月级视图 - 基础
	var MonthView = new Class;
	MonthView.include({
		init: function(){
			this.weekStart = 1;
		},
		initBaseArray: function(y, m){
			var self = this;
			var weekStart = self.weekStart;
			var len = Cal.howManyDaysInMonth(y, m);
			var startWeekDay = Cal.posInWeek(y, m, 1);
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
			var table = self.buildTable();
			if(wrap instanceof $){
				wrap.html(table);
			}else{
				$(wrap).html(table);
			}
		}
	});

	//top控制器
	var View = new Class;


	//周级视图
	var WeekView = new Class;
	//日级视图
	var DayView = new Class;

	DayView.include({
		init: function(){
			var self = this;
			self.miniMonthView = new MiniMonthView;
		}
	});

	var Mark = Cal.Mark = new Class;
	Mark.include({
		init: function(m){
			var self = this;
			var time = m.time;
			var desc = m.desc;
			var time = new Date(time);
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
			self.html = $(html).css('top', hour * 4 * 3 + minute / 5 * 3);

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
				},
				stop: function(event, ui) {
					//alert(this);
				}
			});
		}
	});

})();


// 调样式用
$(function(){

	window.calendar = new Cal;
	var miniMonthElement = $('.cal-mini-month-panel').find('tbody');
	var marksListElement = $('.cal-day-note-list');
	var burnDownElement = $('.cal-day-burn-down-chart');

	calendar.dayView.miniMonthView.initBaseArray(calendar.currentYear, calendar.currentMonth);
	calendar.dayView.miniMonthView.render(miniMonthElement);
	miniMonthElement.find('.td_' + calendar.currentDay).addClass('active');

	calendar.loadMarks(function(d){
		var self = this;
		var marks = d[self.currentDay];

		var lis = '';
		var count = 0;

		var time;
		var hour;
		var minute;
		var m;
		$.each(marks, function(k, v){
			lis += '<li>' + k + ' : ' + v.desc + '</li>';
			count++;

			var mark = new Cal.Mark(v);
			mark.appendTo(burnDownElement.find('.chart-body'));

		});

		marksListElement.find('.cal-day-ul').html(lis);
		marksListElement.find('.marks-count').html(count);
	})


});


