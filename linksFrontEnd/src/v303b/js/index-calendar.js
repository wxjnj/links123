$(function(){
    /*
    $('.title-tabs').on('click', 'li', function(){
        $('.title-tabs').find('li').removeClass('active');
        $(this).addClass('active');
        var t = $(this).attr('data-tab');
        $('.social-box').hide();
        $('.social-' + t).show()
    });
    */

    window.newsTimer = null;
    changeNews();
    autoChangeNews();
    $('.pic-news-tabs').on('click', 'a', function(){
        clearTimeout(window.newsTimer);
        window.newsTimer = null;
        $('.pic-news-tabs').find('a').removeClass('active');
        $(this).addClass('active');
        changeNews();
        autoChangeNews();
    }).on('mouseenter', 'a', function(){
        var $this = $(this)
        clearTimeout(window.newsTimer);
        window.newsTimer = null;
        $('.pic-news-tabs').find('a').removeClass('active');
        $this.addClass('active');
        changeNews();
        //autoChangeNews();
    }).on('mouseout', 'a', function(){
        autoChangeNews();
    });

    function autoChangeNews(){
        clearTimeout(window.newsTimer);
        window.newsTimer = null;
        window.newsTimer = setTimeout(function(){
            var o = $('.pic-news-tabs').find('.active').attr('data-tab') * 1;
            if(o == 3){
                o = 0;
            }else{
                o += 1;
            }
            $('.pic-news-tabs').find('a').removeClass('active');
            $('.pic-news-tabs').find('a:eq(' + o + ')').addClass('active');

            changeNews();

            autoChangeNews()
        }, 5000);
    }

    function changeNews(){
        var idx = $('.pic-news-tabs').find('.active').attr('data-tab');
        var o = news[idx];
        $('.pic-news').find('img').attr('src', o.img).end()
            .find('.pic-news-title').html('<a target="_blank" href="'+ o.url +'">' + o.title + '</a>').end()
            .find('.pic-news-desc a').attr('href', o.url).html(o.desc);
    }

    $('.text-news').find('p:gt(9)').each(function(){
        if($(this).hasClass('more-news')) return;
        $(this).addClass('wide-news');
    });
    
});


/*
 *   Calendar
 */
(function() {
    var config = {};
    config.CHS_WEEKS = ['一', '二', '三', '四', '五', '六', '日'];
    config.CHS_MONTHS = ['一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '十一', '十二'];
    config.ENG_WEEKS = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
    config.ENG_MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'Auguest', 'September', 'October', 'November', 'December'];
    function countObjLength(o) {
        var count = 0,
            i;
        for (i in o) count++;
        return count;
    }
    Date.prototype.add_day = function(days) {
        return new Date(this * 1 + days * 86400000);
    };
    //oo
    var Class = function(parent) {
        var _class = function() {
                this.init.apply(this, arguments);
            },
            key,
            subclass;
        _class.prototype.init = function() {};
        if (parent) {
            subclass = function() {};
            subclass.prototype = parent.prototype;
            _class.uber = parent.prototype;
            _class.prototype = new subclass;
        }
        _class.extend = function(obj) {
            for (key in obj) {
                _class[key] = obj[key];
            }
        };
        _class.include = function(obj) {
            for (key in obj) {
                _class.prototype[key] = obj[key];
                if (_class.uber && typeof(_class.uber[key]) == 'function') {
                    obj[key].spfunc = _class.uber[key];
                }
            }
        };
        _class.prototype._super = function() {
            arguments.callee.caller.spfunc.apply(this, arguments);
        };
        return _class;
    };

    //top控制器
    var Calendar = {
        Init: function(type) {
            var self = this;

            if($('body').hasClass('widescreen')){
                self.G = 3;
            }else{
                self.G = 2;
            }

            self.defaultMarkTitle = '新建日程';
            self.type = type || 'Date';
            $('.cal-view-select-btn').removeClass('active');
            $('.cal-view-select-btn-' + self.type.toLowerCase()).addClass('active');
            self.weekStart = 1;
            self.marksStore = {};
            self.timer = null;
            var today = self.today = Date.today();
            self.tooltip = $('<div class="calendar-tip"><div class="content"></div><span class="ang"></span></div>');
            self.ajaxOverlayer = $('.cal-ajax-overlayer');
            self.setCurrentDate(today);
            $('.cal-header-date-prev').on('click', function() {
                self.prevView();
            });
            $('.cal-header-date-next').on('click', function() {
                self.nextView();
            });
            $('.cal-view-select-btn').on('click', function() {
                $('.cal-view-select-btn').removeClass('active');
                var type = $(this).attr('data-type');
                self.changeType(type);
            });

            /**/
            self.DateView.Init();
            self.MonthView.Init();
            self.WeekView.Init();
            self.DateView.miniMonthView.render();
            self.DateView.mainPanel.show();
            self.loadMarks();
            /**/
        },
        showDate: function() {
            var self = this;
            var type = self.type;
            var currentDate = self.currentDate;
            var currentMonth = self.currentMonth;
            var currentYear = self.currentYear;
            if (type == 'Date') {
                self.date_text = currentYear + '年' + (currentMonth + 1) + '月' + currentDate + '日';
            }
            if (type == 'Week') {
                var start = self.currentWeekStartObject;
                var end = self.currentWeekEndObject;
                var startYear = start.getFullYear();
                var startMonth = start.getMonth() + 1;
                var startDate = start.getDate();
                var endYear = end.getFullYear();
                var endMonth = end.getMonth() + 1;
                var endDate = end.getDate();
                var endShowYear = endYear == startYear ? '': endYear + '年';
                if (endShowYear != '' || endMonth != startMonth) {
                    var endShowMonth = endMonth + '月';
                } else {
                    var endShowMonth = '';
                }
                self.date_text = startYear + '年' + startMonth + '月' + startDate + '日 - ' + endShowYear + endShowMonth + endDate + '日';
            }
            if (type == 'Month') {
                self.date_text = currentYear + '年' + (currentMonth + 1) + '月';
            }
            self.cal_date_text = currentYear + '年' + (currentMonth + 1) + '月' + currentDate + '日';
            $('.cal-header-date').html(self.date_text);
            $('.cal_v303_show_date').html(self.cal_date_text);
        },
        changeType: function(targetType) {
            this.type = targetType;
            $('.cal-view-select-btn').removeClass('active');
            $('.cal-view-select-btn-' + targetType.toLowerCase()).addClass('active');
            this.showDate();
            this.DateView.mainPanel.hide();
            this.WeekView.mainPanel.hide();
            this.MonthView.mainPanel.hide();
            var cur = this[targetType + 'View'];
            cur.mainPanel.show();
            //this.MainView.renderMonthView();
            if (targetType == 'Date') {
                Calendar.DateView.miniMonthView.render();
            }
            if (targetType == 'Week') {}
            if (targetType == 'Month') {
                Calendar.MonthView.tableView.render();
            }
            cur.renderMarks();
        },
        /*
        MainViewChangeFunc: function(t) {
            var self = this;
            var targetDate = new Date(self.currentYear, self.currentMonth, t);
            self.setCurrentDate(targetDate);
            self.MainView.renderMonthView();
            if (self.marksStore[self.currentMarkId]) {
                self.DateView.renderMarks();
            } else {
                self.loadMarks();
            }
        },*/
        prevView: function() {
            this[this.type + 'PrevView']();
        },
        nextView: function() {
            this[this.type + 'NextView']();
        },
        DateChangeFunc: function(t) {
            var self = this;
            var targetDate = new Date(self.currentYear, self.currentMonth, t);
            self.setCurrentDate(targetDate);
            self.DateView.miniMonthView.render();
            //self.MainView.renderMonthView();
            if (self.marksStore[self.currentMarkId]) {
                self.DateView.renderMarks();
            } else {
                self.loadMarks();
            }
        },
        DatePrevView: function() {
            this.DateChangeFunc(this.currentDate - 1);
        },
        DateNextView: function() {
            this.DateChangeFunc(this.currentDate + 1);
        },
        WeekChangeFunc: function(t) {
            var self = this;
            var targetDate = new Date(self.currentYear, self.currentMonth, t);
            self.setCurrentDate(targetDate);
            var id_start = self.currentWeekStartObject.getFullYear() + '-' + self.currentWeekStartObject.getMonth();
            var id_end = self.currentWeekEndObject.getFullYear() + '-' + self.currentWeekEndObject.getMonth();
            //self.MainView.renderMonthView();
            if (self.marksStore[id_start] && self.marksStore[id_end]) {
                self.WeekView.renderMarks();
            } else {
                self.loadMarks();
            }
        },
        WeekPrevView: function() {
            this.WeekChangeFunc(this.currentWeekStart - 7);
        },
        WeekNextView: function() {
            this.WeekChangeFunc(this.currentWeekStart + 7);
        },
        MonthChangeFunc: function(t) {
            var self = this;
            var targetDate = new Date(self.currentYear, t, 1);
            self.setCurrentDate(targetDate);
            self.MonthView.tableView.render();
            //self.MainView.renderMonthView();
            if (self.marksStore[self.currentMarkId]) {
                self.MonthView.renderMarks();
            } else {
                self.loadMarks();
            }
        },
        MonthPrevView: function() {
            this.MonthChangeFunc(this.currentMonth - 1);
        },
        MonthNextView: function() {
            this.MonthChangeFunc(this.currentMonth + 1);
        },
        //ajax封装
        request: function(params) {
            var self = this;
            //self.ajaxOverlayer.find('.content').html('').end().show();
            var defaultConfig = {
                type: 'POST',
                dataType: 'json'
            };
            params = $.extend(defaultConfig, params);
            var defer = $.Deferred();
            var ajax = $.ajax(params).done(function(json) {
                //defer.resolveWith(ajax, [json]);
                if(typeof json.status == 'undefined') {
                    defer.resolveWith(ajax, [json]);
                }
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
        howManyDaysInMonth: function(y, m) {
            var len = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            if (m == 1 && (y % 40 == 0 && y % 100 != 0) || y % 400 == 0) len[1] = 29;
            return len[m];
        },
        //计算日期是全年的第几天
        posInYear: function(y, m, d) {
            for (var i = 1; i < m; ++i) {
                d += howManyDaysInMonth(y, i);
            }
            return d;
        },
        compare: function(a, b) {
            if (a.getFullYear() != b.getFullYear()) {
                return a.getFullYear() - b.getFullYear();
            }
            if (a.getMonth() != b.getMonth()) {
                return a.getMonth() - b.getMonth();
            }
            if (a.getDate() != b.getDate()) {
                return a.getDate() - b.getDate();
            }
            return 0;
        },
        //获取日期是星期几
        posInWeek: function(y, m, d) {
            return (new Date(y, m, d)).getDay();
        },
        setCurrentDate: function(day) {
            var self = this;
            self.currentDateObject = day;
            self.currentDate = day.getDate();
            self.currentMonth = day.getMonth();
            self.currentYear = day.getFullYear();
            self.currentWeekStart = day.getDate() - (day.getDay() == 0 ? 7 : day.getDay() ) + Calendar.weekStart;
            self.currentWeekEnd = self.currentWeekStart + 6;
            self.currentWeekStartObject = new Date(self.currentYear, self.currentMonth, self.currentWeekStart);
            self.currentWeekEndObject = new Date(self.currentYear, self.currentMonth, self.currentWeekEnd);
            self.currentMarkId = self.currentYear + '-' + (self.currentMonth);
            self.showDate();
        },
        //获取数据接口
        loadMarks: function(callBack) {
            var self = this;
            var year = self.currentYear;
            var month = self.currentMonth;
            if (self.type == 'Week') {
                var year1 = self.currentWeekStartObject.getFullYear();
                var month1 = self.currentWeekStartObject.getMonth();
                var year2 = self.currentWeekEndObject.getFullYear();
                var month2 = self.currentWeekEndObject.getMonth();
            }
            //周有跨年和跨月
            if (self.type == 'Week' && year1 != year2 || month1 != month2) {
                var o1 = self.request({
                    url: URL + '/getSchedule?year=' + year1 + '&month=' + (month1 + 1),
                    type: 'GET'
                });
                var o2 = self.request({
                    url: URL + '/getSchedule?year=' + year2 + '&month=' + (month2 + 1),
                    type: 'GET'
                });

                $.when(o1, o2).fail(function(c, e) {
                    self[self.type + 'View'].renderMarks();
                }).done(function(d1, d2) {
                        self.ajaxOverlayer.hide();
                        d1 = d1 === null ? [] : d1;
                        d2 = d2 === null ? [] : d2;

                        if(d1.length != 0) {
                            $.each(d1, function(k, v){
                                $.each(v, function(i, c){
                                    c.desc = c.content;
                                    c.time = c.datetime;
                                });
                                d1[parseInt(k)] = v;
                                if(parseInt(k) + '' != k) delete d1[k];
                            });
                        }
                        if(d2.length != 0) {
                            $.each(d2, function(k, v){
                                $.each(v, function(i, c){
                                    c.desc = c.content;
                                    c.time = c.datetime;
                                });
                                d2[parseInt(k)] = v;
                                if(parseInt(k) + '' != k) delete d2[k];
                            });
                        }

                        self.marksStore[year1 + '-' + month1] = d1;
                        self.marksStore[year2 + '-' + month2] = d2;
                        self[self.type + 'View'].renderMarks();
                        //self.MainView.renderMarks();
                    });
            } else {
                self.request({
                    url: URL + '/getSchedule?year=' + year + '&month=' + (month + 1),
                    type: 'GET'
                }).fail(function(c, e) {
                        self[self.type + 'View'].renderMarks();
                    }).done(function(d) {
                        self.ajaxOverlayer.hide();
                        d = d === null ? [] : d;
                        if(d.length != 0) {
                            $.each(d, function(k, v){
                                $.each(v, function(i, c){
                                    c.desc = c.content;
                                    c.time = c.datetime;
                                });
                                d[parseInt(k)] = v;
                                if(parseInt(k) + '' != k) delete d[k];
                            });
                        }
                        self.marksStore[self.currentMarkId] = d;
                        self[self.type + 'View'].renderMarks();
                        //self.MainView.renderMarks();
                    });
            }
        },

        //新建和更新日程: id==null为新建，有id值为更新
        update: function(id, time, desc, element){
            User.CheckLogin();
            var self = this;
            var url, data;
            if(!id || id == 0 ) {
                url = URL + '/addSchedule';
                data = {
                    time: time/1000,
                    desc: desc
                };
            }else{
                url = URL + '/updateSchedule';
                data = {
                    time: time/1000,
                    desc: desc,
                    id: id
                };
            }
            self.request({
                url: url,
                data: data
            }).fail(function(c, e){
                }).done(function(d){
                    if(id != 'null'){
                    //    element.attr('data-id', d);
                    }
                    self.marksStore = {};
                    self.loadMarks();
                });
        },
        //删除任务
        deleteMark: function(id){
            var self = this;
            self.request({
                url: URL + '/delSchedule',
                data: {
                    id: id
                }
            }).fail(function(c, e){
                }).done(function(d){
                    self.marksStore = {};
                    self.loadMarks();
                });
        }
    };

    Calendar.DateView = {
        Init: function() {
            var self = Calendar.DateView;
            self.mainPanel = $('.cal-day');
            self.miniMonthElement = $('.cal-mini-month-panel').find('tbody');

            self.miniMonthView = new MiniMonthView;
            self.miniMonthElement.on('click', 'a', function() {
                var d = $(this).attr('data-date');
                Calendar.DateChangeFunc(d);
            });

            self.colorCode = '#!4587';
            self.colors = {
                'b' : 'blue',
                'g' : 'green',
                'r' : 'red'
            };

            $('.cal-date-marks-table').on('click', '.add-btn', function(){
                var d = $(this).parent('.desc');
                d.html('<div class="desc-input-div"><input type="text" value="" /><b class="blue active" data-code="b"></b><b class="green" data-code="g"></b><b class="red" data-code="r"></b></div>');
                d.find('input').select();

                return false;
            });


            $('.cal-date-marks-table').on('click', '.delete-btn', function(){
                var li = $(this).parent('li');
                var id = li.attr('data-id');
                if(id && id != 0){
                    Calendar.deleteMark(id);
                }
            });

            $('.cal-date-marks-table').on('click', '.desc-content', function(){
                if($(this).find('input').size()) return;
                var x = $(this).attr('title');
                var d = $(this).parent('.desc');
                var c = $(this).attr('data-color');
                d.html('<div class="desc-input-div"><input type="text" data-old="' + x + '" data-color="' + c + '" value="' + x + '" /><b class="blue" data-code="b"></b><b class="green" data-code="g"></b><b class="red" data-code="r"></b></div>');
                d.find('input').select();
                d.find('.' + c).addClass('active');
            });

            $('.cal-date-marks-table').on('click', '.desc-input-div b', function(){
                $(this).siblings('b').removeClass('active').end().addClass('active');
            });


            $('.cal-date-marks-table').on('keydown', 'input', function(e){
                if(e.keyCode == 13){
                    var input = $(this);
                    var li = input.parents('li');
                    var desc = input.val() + self.colorCode + li.find('.active').attr('data-code');
                    var h = li.attr('data-time');
                    var time = Date.today();
                    var id = li.attr('data-id');
                    time.setHours(h);
                    Calendar.update(id, time, desc);
                }else if(e.keyCode == 27){
                    var input = $(this);
                    var li = input.parents('li');
                    var desc = input.attr('data-old') + self.colorCode + input.attr('data-color').charAt(0);
                    var h = li.attr('data-time');
                    var time = Date.today();
                    var id = li.attr('data-id');
                    time.setHours(h);
                    Calendar.update(id, time, desc);
                }
            });

        },
        renderMarks: function() {
            var self = this;
            var tem = Calendar.marksStore[Calendar.currentYear + '-' + Calendar.currentMonth];
            if (tem && tem[Calendar.currentDate]) {
                var marks = self.marks = tem[Calendar.currentDate];
            } else {
                var marks = self.marks = [];
            }
            var lis = '';
            var count = 0;

            var all_lis = '';
            var tem = '';
            for(var i = 0; i < 24; i+=2) {
                if(i < 10) {
                    tem = '0' + i;
                }else{
                    tem = i;
                }
                all_lis += '<li class="line_'+i+'" data-time="'+i+'"><b class="dot"></b><span class="time">'+tem+':00</span><span class="desc"><a class="add-btn" href="javascript:;">增加日程 +</a></span></li>';
            }


            $('.cal-date-marks-table').html(all_lis);
            var v;
            var len = marks.length;
            var h;
            var line;
            var desc;
            var color;
            var tem;
            var short_desc;

            for(var i = 0; i < len; i++){
                v = marks[i];
                h = (new Date(v.time * 1000)).getHours();
                desc = v.desc;
                if(desc.search(self.colorCode) >= 0){
                    tem = desc.split(self.colorCode);
                    color = self.colors[tem[1]];
                    desc = tem[0];
                }else{
                    color = 'blue';
                }

                if(h % 2 != 0) h -= 1;
                line = $('.cal-date-marks-table').find('.line_' + h);
                if(h < 10) h = '0' + h;
                line.find('.time').html(h + ':00');

                if(desc.length > 15) {
                    short_desc = desc.substring(0, 13) + '...';
                }else{
                    short_desc = desc;
                }

                if(!line.find('.add-btn').size()){
                    continue;
                }

                line.find('.desc').html('<a class="desc-content ' + color + '" data-color="' + color + '" title="' + desc + '" href="javascript:;">' + short_desc + '</a>');
                line.append('<a class="delete-btn" href="javascript:;">×</a>');
                line.attr('data-id', v.id);
            }
            $('.cal-date-marks-table').find('li:last').css({
                'border': 0,
                'height': '35px'
            });

            if(Calendar.compare(Date.today(), Calendar.currentDateObject) == 0){
                var h = (new Date()).getHours();
                if(h % 2 != 0) h -= 1;
                $('.cal-date-marks-table').find('.line_' + h).addClass('current-time');
            }

        }
    };
    Calendar.MonthView = {
        Init: function() {
            var self = this;
            self.mainPanel = $('.cal-month');
            self.tableElement = self.mainPanel.find('table');
            self.tableView = new MonthTable;
            self.tableElement.on('click', 'td', function() {
                var y = Calendar.currentYear;
                var m = Calendar.currentMonth;
                var d = $(this).find('a').attr('data-date');
                d = new Date(y, m, d);
                Calendar.setCurrentDate(d);
                Calendar.changeType('Date');
            });
        },
        renderBurnDownChart: function() {
            var self = this;
            var year = Calendar.currentYear;
            var month = Calendar.currentMonth;
            var todayDate = Calendar.today.getDate();

            if (year <= Calendar.today.getFullYear() && month < Calendar.today.getMonth()) {
                self.tableElement.find('td').addClass('month_pass_date');
            } else if (year == Calendar.today.getFullYear() && month == Calendar.today.getMonth()) {
                self.tableElement.find('td').each(function(k, v) {
                    var cur = $(v).find('a').attr('data-date');
                    if (cur != - 1 && cur < todayDate) {
                        $(v).addClass('month_pass_date');
                    } else if (cur == todayDate) {
                        self.todayElement = $(v);
                    }
                });
            }
        },
        renderMarks: function() {
            var self = this;
            var marks = Calendar.marksStore[Calendar.currentYear + '-' + Calendar.currentMonth];
            if (marks) {
                self.marks = marks;
            } else {
                self.marks = marks = {};
            }
            $.each(marks, function(k, v) {
                var cur = self.tableElement.find('.td_' + k).parent('td');
                $.each(v, function(i, n) {
                    var top = parseInt(i / 7) * 7 + 5;
                    var left = i % 7 * 7 + 5;
                    top = 'top:' + top + 'px;';
                    left = 'left:' + left + 'px;';
                    var style = top + left;
                    cur.append('<b class="month_task_dot" style="' + style + '"></b>');
                });
            });
        }
    };
    Calendar.WeekView = {
        Init: function() {
            var self = this;
            self.mainPanel = $('.cal-week');
            self.tableElement = self.mainPanel.find('table');
            self.tableElement.on('click', 'td', function() {
                var y = $(this).attr('data-year');
                var m = $(this).attr('data-month');
                var d = $(this).attr('data-date');
                Calendar.setCurrentDate(new Date(y, m, d));
                Calendar.changeType('Date');
            });
        },
        renderMarks: function() {
            var self = this;
            var start = Calendar.currentWeekStartObject;
            var end = Calendar.currentWeekEndObject;
            var today = Calendar.today;
            var year, month, date, marks, tem, td, op, i, line, time, desc, hour, minute;
            self.tableElement.find('td').empty();
            var burndown = false;

            for (i = 0; i <= 6; i++) {
                tem = start.add_day(i);
                year = tem.getFullYear();
                month = tem.getMonth();
                date = tem.getDate();
                if (Calendar.marksStore[year + '-' + month] && Calendar.marksStore[year + '-' + month][date]) {
                    marks = Calendar.marksStore[year + '-' + month][date];
                } else {
                    marks = [];
                }
                td = self.tableElement.find('td:eq(' + i + ')');
                td.attr({
                    'data-date': date,
                    'data-month': month,
                    'data-year': year
                });
                op = Calendar.compare(tem, today);
                td.removeClass('month_pass_date');
                $.each(marks, function(k, v) {
                    line = $('<div class="week-mark-line"><b></b><span></span></div>');
                    time = new Date(v.time * 1000);
                    desc = v.desc;
                    hour = time.getHours();
                    minute = time.getMinutes();
                    line.css('top', hour * 4 * Calendar.G + minute / 15 * Calendar.G);
                    td.append(line);
                });
            }

        }
    };
    //月级视图 - 基础
    var MonthView = new Class;
    window.MonthView = MonthView;
    MonthView.include({
        init: function() {
            this.weekStart = Calendar.weekStart;
            this.year = null;
            this.month = null;
        },
        initBaseArray: function(y, m) {
            var self = this;
            self.year = y;
            self.month = m;
            var weekStart = self.weekStart;
            var len = Calendar.howManyDaysInMonth(y, m);
            var startWeekDay = Calendar.posInWeek(y, m, 1);
            var arr = [];
            var i;
            for (i = 1; i <= len; i++) {
                arr.push(i);
            }
            for (i = 1, len = startWeekDay == 0 ? 7 - weekStart : startWeekDay - weekStart; i <= len; i++) {
                arr.unshift(0);
            }
            len = arr.length % 7;
            if (!len == 0) {
                len = 7 - len;
                for (i = 1; i <= len; i++) {
                    arr.push( - 1);
                }
            }
            self.baseArray = arr;
        },
        buildTable: function(date) {
            var self = this;
            if (self.year !== Calendar.currentYear || self.month !== Calendar.currentMonth) {
                self.initBaseArray(Calendar.currentYear, Calendar.currentMonth);
            }
            var baseArray = self.baseArray;
            var o;
            var table = '';
            for (var i = 0, len = baseArray.length; i < len; i++) {
                o = baseArray[i];
                if (o == 0 || o == - 1) {
                    o = '<td class="op-td-'+i%7+'"><a data-date="' + o + '" style="display:none"></a></td>';
                } else {
                    o = '<td class="op-td-'+i%7+'"><a class="td_' + o + '" href="javascript:;" data-date="' + o + '">' + o + '</a></td>';
                }
                if ((i + 1) % 7 == 0) {
                    o += '</tr>'
                }
                if (i % 7 == 0) {
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
        render: function(wrap) {
            var self = this;
            wrap = wrap || Calendar.DateView.miniMonthElement;
            var table = self.buildTable();
            if (wrap instanceof $) {
                wrap.html(table);
            } else {
                $(wrap).html(table);
            }
            wrap.find('.td_' + Calendar.currentDate).addClass('active');
        }
    });
    //月级视图的月历
    var MonthTable = new Class(MonthView);
    MonthTable.include({
        render: function(wrap) {
            var self = this;
            wrap = wrap || Calendar.MonthView.tableElement.find('tbody');
            var table = self.buildTable();
            if (wrap instanceof $) {
                wrap.html(table);
            } else {
                $(wrap).html(table);
            }
            wrap.find('.td_' + Calendar.currentDate).addClass('active');
            if(self.baseArray.length / 7 == 5){
                wrap.find('td, th').removeClass('more-line');
            }else{
                wrap.find('td, th').addClass('more-line');
            }
        }
    });
 
    window.Calendar = Calendar;
})();