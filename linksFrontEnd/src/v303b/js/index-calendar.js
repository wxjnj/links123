//接口地址是URL + '/getNews'，参数是type，type默认为0，如果cookie中有news_type值，那么type取cookie中的news_type
var News = {};
window.newsTimer = null;

function cutstr(str,len) {
    if(!str) return '';
    var str_length = 0;
    var str_len = 0;
    str_cut = new String();
    str_len = str.length;
    for(var i = 0;i<str_len;i++) {
        a = str.charAt(i);
        str_length++;
        if(escape(a).length > 4){
            str_length++;
        }
        str_cut = str_cut.concat(a);
        if(str_length>=len){
            str_cut = str_cut.concat("...");
            return str_cut;
        }
    }
    if(str_length<len){
        return str;
    }
}
$('#social-title-tabs').find('li').each(function(k, v){
    if($(v).attr('data-status') == 1){
        $(v).append('<div class="mini-tip news-mini-tip"><div class="mini-tip-l"></div><div class="mini-tip-c">coming soon...</div><div class="mini-tip-r"></div></div>');
    }
});
$('.pic-news-tabs').find('a:gt(3)').hide();

$('.text-news').find('p:gt(10)').each(function(){
    if($(this).hasClass('more-news')) return;
    $(this).addClass('wide-news');
}); 

$('.social-box').find('.pic-news-title').each(function(k, v){
    var s = $(v).html();
    $(v).attr('title', s).html(cutstr(s, 38));
});
$('.social-box').find('.pic-news-desc a').each(function(k, v){
    var s = $(v).html();
    $(v).attr('title', s).html(cutstr($.trim(s), 120));
});

showNews();

function showNews(type) {
    if(!type){
        type = $.cookies.get('news_type');
        type = type != null ? type : 0;
    }
    $('.social-box').hide();
    $('.social-box:eq(' + type + ')').show();

    var stringLength = 38;
    if($('body').hasClass('widescreen')) {
        stringLength = 42;
    }
    $('.text-news-div').find('a').each(function(k, v){
        var t = $(v).attr('title');
        $(v).html(cutstr(t, stringLength));
    });

    changeNews();
    autoChangeNews();     
}

$('#social-title-tabs').on('click', 'li', function(){
    var st = $(this).attr('data-status');
    if(st == 1) return;
    clearTimeout(window.newsTimer);
    window.newsTimer = null;

    var tab = $(this).attr('data-tab');
    $('#social-title-tabs').find('li').removeClass('active');
    $(this).addClass('active');
    $('.pic-news-tabs').find('a').removeClass('active');
    $('.pic-news-tabs').find('a:first').addClass('active');

    $.cookies.set('news_type', tab);
    showNews(tab);

});

$('.pic-news-tabs').on('click', 'a', function(){
    clearTimeout(window.newsTimer);
    window.newsTimer = null;
    $('.pic-news-tabs').find('a').removeClass('active');
    $(this).addClass('active');
    changeNews();
    //autoChangeNews();
}).on('mouseenter', 'a', function(){
    var $this = $(this);
    clearTimeout(window.newsTimer);
    window.newsTimer = null;
    $('.pic-news-tabs').find('a').removeClass('active');
    $this.addClass('active');
    changeNews();
    //autoChangeNews();
}).on('mouseout', 'a', function(){
    //autoChangeNews();
});

function autoChangeNews(){
    clearTimeout(window.newsTimer);
    window.newsTimer = null;
    window.newsTimer = setTimeout(function(){
        var o = $('.pic-news-tabs:visible').find('.active').attr('data-tab') * 1;
        if(o == 4){
            o = 1;
        }else{
            o += 1;
        }
        $('.pic-news-tabs').find('a').removeClass('active');
        $('.pic-news-tabs').find('a:eq(' + (o-1) + ')').addClass('active');

        changeNews();
        if(o != 1){
            autoChangeNews()
        }
    }, 1000);
}

function changeNews(){
    var idx = $('.pic-news-tabs').find('.active').attr('data-tab');
    if(typeof idx == 'undefined') {
        idx = 1;
        $('.pic-news-tabs').find('a:first').addClass('active');
    }
    $('.extra-box').find('.pic-news').find('.pic-news-title').hide();
    $('.extra-box').find('.pic-news').find('.pic-news-title:eq('+(idx-1)+')').show();
    $('.extra-box').find('.pic-news').find('.pic-news-desc').hide();
    $('.extra-box').find('.pic-news').find('.pic-news-desc:eq('+(idx-1)+')').show();
    $('.extra-box').find('.pics-box').find('a').hide();
    $('.extra-box').find('.pics-box').find('a:eq('+ (idx-1) +')').show();

}



/*
 *   Calendar
 */
(function() {
    //config参数目前没有使用到，保留备用
    var config = {};
    config.CHS_WEEKS = ['一', '二', '三', '四', '五', '六', '日'];
    config.CHS_MONTHS = ['一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '十一', '十二'];
    config.ENG_WEEKS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
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
        dateTitleConfig: config,
        Init: function(type) {
            var self = this;

            /*
            if($('body').hasClass('widescreen')){
                self.G = 3;
            }else{
                self.G = 2;
            }
            */

            //self.idController = 0;

            //self.defaultMarkTitle = '新建日程';

            self.type = type || 'Date';
            $('.cal-view-select-btn').removeClass('active');
            $('.cal-view-select-btn-' + self.type.toLowerCase()).addClass('active');
            self.weekStart = 1;

            //缓存日程安排的变量
            self.marksStore = {};

            //self.timer = null;

            var today = self.today = Date.today();
            self.tooltip = $('<div class="calendar-tip"><div class="content"></div><span class="ang1"></span><span class="ang"></span></div>');
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

            $('#container').on('click', function(e){
                self.tooltip.hide();
                e = $.event.fix(e);
                if(e.target == this) {
                    Calendar.setCurrentDate(Date.today());
                    Calendar.changeType('Date');
                }
            }).on('mouseleave', 'td', function(e){
                e = $.event.fix(e);
                if(e.target == this && !self.tooltip.is(':hidden')){
                    self.tooltip.hide();
                }
            });

            $('.cal-body').on('mouseover', '.cal-month td a, .cal-week td a, .cal-month td b, .cal-week td b', function(e){
                e.preventDefault();
            });

            $('.cal-body').on('mouseover', '.cal-month td, .cal-week td', function(e){
                var td = $(this);
                var year, 
                    month,
                    date,
                    marks,
                    time,
                    top,
                    left,
                    tem;

                if(td.find('a').size()){
                    year = td.find('a').attr('data-year');
                    month = td.find('a').attr('data-month');
                    date = td.find('a').attr('data-date');
                }else{
                    year = td.attr('data-year');
                    month = td.attr('data-month');
                    date = td.attr('data-date');
                }

                marks = self.marksStore[year + '-' + month][date] || null;
                tem = '';
                if(!marks){
                    return;
                    tem = '<p>暂无日程</p>';
                }else{
                    $.each(marks, function(k, v){
                        time = new Date(v.time * 1000);
                        time = time.getHours();
                        if(time % 2 != 0) time -= 1;
                        if(time < 10) time = '0' + time;
                        tem += '<p>' + time + ':00-' + v.desc + '</p>';
                    });
                }

                top = td.offset().top;
                left = td.offset().left + td.width() / 2;
                self.tooltip.find('.content').html(tem).end().appendTo('#container');

                top -= self.tooltip.height() + 20;
                self.tooltip.css({
                    top: top + 'px',
                    left: left + 'px'
                }).show();
             });

            self.ReInit();
            /*
            self.DateView.Init();
            self.MonthView.Init();
            self.WeekView.Init();
            self.DateView.miniMonthView.render();
            self.DateView.mainPanel.show();
            self.loadMarks();
            */
        },
        ReInit: function(){
            var self = this;
            self.DateView.Init();
            self.MonthView.Init();
            self.WeekView.Init();
            self.changeType(self.type);
            //self.DateView.miniMonthView.render();
            //self.DateView.mainPanel.show();
            //self.loadMarks();
        },
        /*
            格式化日程表顶部显示的日期（周视图有跨月和跨年的情况）
        */
        showDate: function() {
            var self = this,
                type = self.type,
                currentDate = self.currentDate,
                currentMonth = self.currentMonth,
                currentYear = self.currentYear;

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
            //if (targetType == 'Week') {}
            if (targetType == 'Month') {
                Calendar.MonthView.tableView.render();
            }
            Calendar.loadMarks();
            //cur.renderMarks();
        },
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
            var nextDay = Calendar.currentDateObject.add_day(1);
            if (self.marksStore[self.currentMarkId] && self.marksStore[nextDay.getFullYear() + '-' + nextDay.getMonth()]) {
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
            //self.MainView.renderMonthView();
            if (self.marksStore[self.currentWeekStartObject.getFullYear() + '-' + self.currentWeekStartObject.getMonth()] && 
                self.marksStore[self.currentWeekEndObject.getFullYear() + '-' + self.currentWeekEndObject.getMonth()]) {
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

            var dateArray = [];
            if (self.type == 'Week') {
                var year1 = self.currentWeekStartObject.getFullYear();
                var month1 = self.currentWeekStartObject.getMonth();
                var year2 = self.currentWeekEndObject.getFullYear();
                var month2 = self.currentWeekEndObject.getMonth();
                if(Calendar.marksStore[year1 + '-' + month1] === undefined){
                    dateArray.push({y:year1, m:month1});
                }
                if(month2 != month1) dateArray.push({y:year2, m:month2});
            }
            if(self.type == 'Date'){
                var nextDay = self.currentDateObject.add_day(1);
                var year1 = self.currentDateObject.getFullYear();
                var month1 = self.currentDateObject.getMonth();
                var year2 = nextDay.getFullYear();
                var month2 = nextDay.getMonth();
                if(Calendar.marksStore[year1 + '-' + month1] === undefined){
                    dateArray.push({y:year1, m:month1});
                }
                if(month2 != month1) dateArray.push({y:year2, m:month2});
            }

            if(self.type == 'Month'){
                var year1 = self.currentDateObject.getFullYear();
                var month1 = self.currentDateObject.getMonth();
                var prevMonthObject = new Date(year1, month1 - 1);
                var nextMonthObject = new Date(year1, month1 + 1);
                var year2 = prevMonthObject.getFullYear();
                var month2 = prevMonthObject.getMonth();
                var year3 = nextMonthObject.getFullYear();
                var month3 = nextMonthObject.getMonth();
                if(Calendar.marksStore[year1 + '-' + month1] === undefined){
                    dateArray.push({y:year1, m:month1});
                }
                if(Calendar.marksStore[year2 + '-' + month2] === undefined){
                    dateArray.push({y:year2, m:month2});
                }
                if(Calendar.marksStore[year3 + '-' + month3] === undefined){
                    dateArray.push({y:year3, m:month3});
                }
            }

            if(dateArray.length == 0){
                self[self.type + 'View'].renderMarks();
                return;
            }
            //周有跨年和跨月 || 日视图单日有两天的数据 如果跨月就要双取 || 月视图会跨三个月
            self.ajaxArray = [];
            var o;
            $.each(dateArray, function(k, v){
                o = self.request({
                    url: URL + '/getSchedule?year=' + v.y + '&month=' + (v.m + 1),
                    type: 'GET'
                });
                self.ajaxArray.push(o);
            });

            var datas;
            $.when.apply(self, self.ajaxArray).done(function(){
                datas = Array.prototype.splice.call(arguments, 0);
                $.each(datas, function(k, data){

                    data = data === null ? [] : data;
                    if(data.length != 0) {
                        $.each(data, function(k, v){
                            $.each(v, function(i, c){
                                c.desc = c.content;
                                c.time = c.datetime;
                            });
                            data[parseInt(k)] = v;
                            if(parseInt(k) + '' != k) delete data[k];
                        });
                    }
                    self.marksStore[dateArray[k].y + '-' + dateArray[k].m] = data;
                });

                self[self.type + 'View'].renderMarks();

            });
        }
    };

    /*
        日视图的单条日程类

        封装增删改（查由Calendar控制）
    */
    var MarkLine = new Class;
    MarkLine.include({
        init: function(index, to){
            var self = this;
            self.format(index, to);
        },
        format: function(index, to){
            var self = this;
            var tem;
            if(index < 10) {
                tem = '0' + index;
            }else{
                tem = index;
            }
            self.element = $('<li class="line_'+index+'" data-timestamp="' + (to.setHours(index)*1) + '" data-time="'+index+'"><b class="dot"></b><span class="time">'+tem+':00</span><span class="desc"><a class="add-btn" href="javascript:;">增加日程</a></span></li>');

        },
        refresh: function(){
            var self = this;
            self.element.find('.desc').html('<a class="add-btn" href="javascript:;">增加日程</a>');
            self.element.find('.delete-btn').remove();
            self.element.removeAttr('data-id');
        },
        edit: function(){
            var self = this;
            var el = self.element;
            if(el.find('.desc-content').size()){
                var x = el.find('.desc-content').attr('title');
                var d = el.find('.desc');
                var c = el.find('.desc-content').attr('data-color');
                d.html('<div class="desc-input-div"><input type="text" data-old="' + x + '" data-color="' + c + '" value="" /><!--b class="blue" data-code="b"></b><b class="green" data-code="g"></b><b class="red" data-code="r"></b--></div>');
                d.find('input').val(unescape(x)).select();
                d.find('.' + c).addClass('active');    
            }else{
                var d = el.find('.desc')//.parent('.desc');
                d.html('<div class="desc-input-div"><input type="text" value="" /><!--b class="blue active" data-code="b"></b><b class="green" data-code="g"></b><b class="red" data-code="r"></b--></div>');
                d.find('input').select();
                el.append('<a class="delete-btn" href="javascript:;">×</a>')
            }

        },
        setMark: function(id, desc){
            var self = this;
            var tem, color;
            desc = $.trim(desc);
            if(!desc) {
                self.refresh();
                return;
            }

            if(desc.search(Calendar.DateView.colorCode) >= 0){
                tem = desc.split(Calendar.DateView.colorCode);
                color = Calendar.DateView.colors[tem[1]];
                desc = tem[0];
            }else{
                color = 'blue';
            }
            desc = $('<div/>').html(desc).text(); //后台传输过来的数据会编码html，利用这个解码
            if(desc.length > 15) {
                short_desc = desc.substring(0, 13) + '...';
            }else{
                short_desc = desc;
            }
            self.element.find('.desc').html('<a class="desc-content ' + color + '" data-color="' + color + '" title="' + desc + '" href="javascript:;">' + short_desc + '</a>');
            if(!self.element.find('.delete-btn').size()){
                self.element.append('<a class="delete-btn" href="javascript:;">×</a>');
            }
            self.element.attr('data-id', id);
        },
        deleteMark: function(){
            var self = this;
            var id = self.element.attr('data-id');
            self.element.find('.desc').html('<a class="add-btn" href="javascript:;">增加日程</a>');
            self.element.find('.delete-btn').remove();
            self.element.removeAttr('data-id');
            if(Calendar.marksStore[Calendar.currentMarkId] && Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate]){
                $.each(Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate], function(k, v){
                    if(!v) return;
                    if(v.id == id) {
                        Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate].splice(k, 1);
                    }
                });
            }
            if(id == 0 || !id) {
                if(!Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate] || 
                    Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate].length == 0){
                    if(Calendar.compare(Calendar.currentDateObject, Date.today()) == 0){
                        Calendar.marksStore = {};
                        Calendar.loadMarks();
                    }
                
                }
                return;
            }
            Calendar.request({
                url: URL + '/delSchedule',
                data: {
                    id: id
                }
            }).fail(function(c, e){
            }).done(function(d){
                //检查是否是清空当天日程，true -> 重新加载
                if(!Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate] || 
                    Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate].length == 0){
                    if(Calendar.compare(Calendar.currentDateObject, Date.today()) == 0){
                        Calendar.marksStore = {};
                        Calendar.loadMarks();
                    }
                
                }
            });

        },
        updateMark: function(id, time, desc){
            var self = this;
            var url, data, type;

            desc = $.trim(desc);

            if((id == 0) && (!desc || desc == '来创建今天新的日程吧！')) {
                return;
            }

            if(!desc){
                self.deleteMark();
                return;
            }

            //User.CheckLogin();

            if(!id || id == 0 ) {
                type = 'add';
                url = URL + '/addSchedule';
                data = {
                    time: time/1000,
                    desc: desc
                };
            }else{
                type = 'update';
                url = URL + '/updateSchedule';
                data = {
                    time: time/1000,
                    desc: desc,
                    id: id
                };
            }

            Calendar.request({
                url: url,
                data: data
            }).fail(function(c, e){
            }).done(function(d){
                if(type == 'add') {
                    if(!Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate]){
                        Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate] = [];
                    }
                    Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate].push({
                        id: d,
                        time: time / 1000,
                        desc: desc
                    });
                    self.element.attr('data-id', d);
                }
            });

            if(type == 'update') {
                $.each(Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate], function(k, v){
                    if(v.id == id) {
                        v.desc = desc;
                    }
                });
            }
            if(!Calendar.marksStore[Calendar.currentMarkId]){
                Calendar.marksStore[Calendar.currentMarkId] = {};
            }
            if(!Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate]){
                Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate] = [];
            }

            $.each(Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate], function(k, v){
                if(!v) return;
                if(v.id == 0){
                    Calendar.marksStore[Calendar.currentMarkId][Calendar.currentDate].splice(k, 1);
                    var o = $('.cal-date-marks-table').find('[data-id=0]'); // 跨时间会发生当前找不到id=0
                    if(o.size()){
                        var tt = o.attr('data-time');
                        Calendar.DateView.all_lis[tt].refresh();
                    }
                }
            });

        }
    });

    Calendar.DateView = {
        Init: function() {
            var self = Calendar.DateView;
            self.mainPanel = $('.cal-day');
            self.miniMonthElement = $('.cal-mini-month-panel').find('tbody');

            self.miniMonthView = new MiniMonthView;

            self.colorCode = '#!4587';
            self.colors = {
                'b' : 'blue',
                'g' : 'green',
                'r' : 'red'
            };

            self.miniMonthElement.on('click', 'a', function() {
                var y = $(this).attr('data-year');
                var m = $(this).attr('data-month');
                var d = $(this).attr('data-date');
                //Calendar.DateChangeFunc(d);
                Calendar.setCurrentDate(new Date(y, m, d));
                Calendar.changeType('Date');
            });

            $('.cal-date-marks-table').on('click', 'li', function(){
                if($(this).find('input').size()) return;
                var time = $(this).attr('data-time');
                var mark = self.all_lis[time];
                $.each(self.all_lis, function(k, v){
                    if(v.element.find('.desc-input-div').size()){
                        var id = v.element.attr('data-id');
                        var desc = v.element.find('input').val();
                        var time = v.element.attr('data-timestamp');
                        if(escape(desc) != v.element.find('input').attr('data-old')) {
                            v.updateMark(id, time, desc);
                        }
                        v.setMark(id, desc);
                    }
                });
                mark.edit();
            });

            $('.cal-date-marks-table')
                .off('click', '.delete-btn')
                .on('click', '.delete-btn', function(){
                    var li = $(this).parent('li');
                    var time = li.attr('data-time');
                    self.all_lis[time].deleteMark();
                    return false;
                });

            $('.cal-date-marks-table')
                .off('blur keyup', 'input')
                .on('blur keyup', 'input', function(e){
                    if(e.keyCode == 13){
                        //触发blur，且只触发一次
                        $(this)[0].blur();
                    }
                })
                .off('blur', 'input')
                .on('blur', 'input', function(e){
                    var time = $(this).parents('li').attr('data-time');
                    var mark = self.all_lis[time];
                    var id = mark.element.attr('data-id');
                    var desc = mark.element.find('input').val();
                    time = mark.element.attr('data-timestamp');
                    //time = Date.today().setHours(time);
                    if(escape(desc) != mark.element.find('input').attr('data-old')) {
                        mark.updateMark(id, time, desc);
                    }
                    mark.setMark(id, desc);
                    return false;
                });

        },
        renderMarks: function() {
            var self = this,
                tem = Calendar.marksStore[Calendar.currentYear + '-' + Calendar.currentMonth],
                arr = [],
                marks_currentDay,
                marks_nextDay, 
                ct,
                nextDay,
                marks,
                all_lis,
                timeArray,
                timeObject,
                i,
                len;

            if (tem && tem[Calendar.currentDate]) {
                marks_currentDay = tem[Calendar.currentDate];
                $.each(marks_currentDay, function(k, v){
                    ct = (new Date(v.time * 1000)).getHours();
                    if(ct >= 6) arr.push(v);
                });
                marks_currentDay = arr;
            } else {
                marks_currentDay = [];
            }

            nextDay = Calendar.currentDateObject.add_day(1);
            tem = Calendar.marksStore[nextDay.getFullYear() + '-' + nextDay.getMonth()];
            if(tem && tem[nextDay.getDate()]){
                marks_nextDay = tem[nextDay.getDate()];
                arr = [];
                $.each(marks_nextDay, function(k, v){
                    ct = (new Date(v.time * 1000)).getHours();
                    if(ct < 6) arr.push(v);
                });
                marks_nextDay = arr;
            }else{
                marks_nextDay = [];
            }

            marks = marks_currentDay.concat(marks_nextDay);

            all_lis = self.all_lis = {};

            $('.cal-date-marks-table').empty();

            timeArray = [6, 8, 10, 12, 14, 16, 18, 20, 22, 0, 2, 4];

            timeObject;

            $.each(timeArray, function(k, t){
                if(t == 0 || t == 2 || t == 4){
                    timeObject = Calendar.currentDateObject.add_day(1);
                }else{
                    timeObject = Calendar.currentDateObject;
                }
                all_lis[t] = new MarkLine(t, timeObject);
                $('.cal-date-marks-table').append(all_lis[t].element);
            });

            for(i = 0, len = marks.length; i < len; i++){
                v = marks[i];
                h = (new Date(v.time * 1000)).getHours();
                if(h % 2 != 0) h -= 1;
                all_lis[h].setMark(v.id, v.desc);
            }

            $('.cal-date-marks-table').find('li:last').css({
                'border': 0,
                'height': '35px'
            });
        }
    };
    Calendar.MonthView = {
        Init: function() {
            var self = this;
            self.mainPanel = $('.cal-month');
            self.tableElement = self.mainPanel.find('table');
            self.tableView = new MonthTable;
            self.tableElement.on('click', 'td', function() {
                var y = $(this).find('a').attr('data-year');//Calendar.currentYear;
                var m = $(this).find('a').attr('data-month');//Calendar.currentMonth;
                var d = $(this).find('a').attr('data-date');
                d = new Date(y, m, d);
                Calendar.setCurrentDate(d);
                Calendar.changeType('Date');
            });
        },
        renderMarks: function() {
            var self = this;
            var year1 = Calendar.currentDateObject.getFullYear();
            var month1 = Calendar.currentDateObject.getMonth();
            var prevMonthObject = new Date(year1, month1 - 1);
            var nextMonthObject = new Date(year1, month1 + 1);
            var year2 = prevMonthObject.getFullYear();
            var month2 = prevMonthObject.getMonth();
            var year3 = nextMonthObject.getFullYear();
            var month3 = nextMonthObject.getMonth();

            var marks1 = Calendar.marksStore[year1 + '-' + month1] || {};
            var marks2 = Calendar.marksStore[year2 + '-' + month2] || {};
            var marks3 = Calendar.marksStore[year3 + '-' + month3] || {};

            var marks = {};
            marks[year1 + '-' + month1] = marks1; 
            marks[year2 + '-' + month2] = marks2; 
            marks[year3 + '-' + month3] = marks3;

            var cur, top, left, style;
            $.each(marks, function(k, v) {
                $.each(v, function(d, m){
                    cur = self.tableElement.find('.td_' + k + '-' + d).parent('td');
                    $.each(m, function(i, n) {
                        top = parseInt(i / 7) * 7 + 5;
                        left = i % 7 * 7 + 5;
                        top = 'top:' + top + 'px;';
                        left = 'left:' + left + 'px;';
                        style = top + left;
                        cur.append('<b class="month_task_dot" style="' + style + '"></b>');
                    });
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
                arr.push(new Date(Calendar.currentYear, Calendar.currentMonth, i));
            }
            var currentMonthFirstDay = arr[0];
            for (i = 1, len = startWeekDay == 0 ? 7 - weekStart : startWeekDay - weekStart; i <= len; i++) {
                arr.unshift(currentMonthFirstDay.add_day(-i));
            }
            var currentMonthLastDay = arr[arr.length - 1];
            len = arr.length % 7;
            if (!len == 0) {
                len = 7 - len;
                for (i = 1; i <= len; i++) {
                    arr.push(currentMonthLastDay.add_day(i));
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
            var o, y, m, d, c;
            var table = '';
            for (var i = 0, len = baseArray.length; i < len; i++) {
                o = baseArray[i];

                y = o.getFullYear();
                m = o.getMonth();
                d = o.getDate();

                c = 'current-month';
                if(m != Calendar.currentMonth){
                    c = 'another-month';
                }

                o = '<td class="op-td-'+ (i%7) + ' ' + c + '"><a class="td_' + y+ '-' + m + '-' + d + 
                    '" href="javascript:;" data-year="' + y + '" data-month="' + m + '" data-date="' + d + 
                    '">' + d + '</a></td>';

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
            wrap.find('.td_' + Calendar.currentYear + '-' + Calendar.currentMonth + 
                '-' + Calendar.currentDate).addClass('active');
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
            wrap.find('.td_' + Calendar.currentYear + '-' + Calendar.currentMonth + 
                '-' + Calendar.currentDate).addClass('active');
            if(self.baseArray.length / 7 == 5){
                wrap.find('td, th').removeClass('more-line');
            }else{
                wrap.find('td, th').addClass('more-line');
            }
        }
    });
 
    window.Calendar = Calendar;
})();
