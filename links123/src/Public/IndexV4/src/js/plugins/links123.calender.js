/*
 * @name: links123 日程组件
 * @author: lpgray
 * @datetime: 2013-10-08 12:54
 * */
(function($){
  var config = {};
  config.CHS_WEEKS = ['一','二','三','四','五','六','日'];
  config.CHS_MONTHS = ['一','二','三','四','五','六','七','八','九','十','十一','十二'];
  config.ENG_MONTHS = ['January','February','March','April','May','June','July','Auguest','September','October','November','December'];
  config.ENG_WEEKS = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];

  var weeks = config.CHS_WEEKS;
  
  /* TEST HELPERS */
  var d = function( str ){
    console.debug('TEST----');
    console.debug(str);
  }

  /*
   * Utils
   */
  var UTILS = {}
  //datetime是否在当前时间之前
  UTILS.isPassed = function(datetime){
    var current = new Date();
    return current.getTime() - datetime.getTime() < 0;
  }
  //获取时间字符串
  UTILS.printTime = function(datetime){
    var h = datetime.getHours();
    var m = datetime.getMinutes();
    h = h < 10 ? '0' + h : h;
    m = m < 10 ? '0' + m : m;
    return h + ':' + m;
  }
  // 获取时间占全天时间的百分比
  UTILS.getTimePercent = function(datetime){
    return (datetime.getHours()*60 + datetime.getMinutes()) / (24*60) * 100;
  }
  // string -> date
  UTILS.str2date = function(dateStr){
    //var dateStr="2011-08-03 09:15:11"; //returned from mysql timestamp/datetime field
    var a=dateStr.split(" ");
    var d=a[0].split("-");
    var t=a[1].split(":");
    var date = new Date(d[0],(d[1]-1),d[2],t[0],t[1]);
    return date;
  }
  // 比较第二个时间在第一个时间的周中、周前、周后、同时获取第二个时间周一 - 周日的两个时间对象
  UTILS.isOneWeek = function( date1, date2 ){
  	date1.setHours(0);
    date1.setMinutes(0);
  	date1.setMilliseconds(0);
    date1.setSeconds(0);
    // 把date全部转换成上周日然后相减
    var cha1;
    if(date1.getDay() == 0){
      cha1 = 7 * 24 * 60 * 60 * 1000;
    }else{
      cha1 = (date1.getDay() - 0) * 24 * 60 * 60 * 1000;
    }
    var cha1 = (date1.getDay() - 0) * 24 * 60 * 60 * 1000;
    var date1Sun = new Date( date1.getTime() - cha1 );

    date2.setHours(0);
    date2.setMinutes(0);
    date2.setMilliseconds(0);
    date2.setSeconds(0);
    var cha2;
    if(date2.getDay() == 0){
      cha2 = 7 * 24 * 60 * 60 * 1000;
    }else{
      cha2 = (date2.getDay() - 0) * 24 * 60 * 60 * 1000;
    }
    var date2Sun = new Date( date2.getTime() - cha2 );

    // console.debug(date1);
    // console.debug(date2);

    var back = { 
      'dates' : {
        'monday'  : new Date( date2Sun.getTime() + 24 * 60 * 60 * 1000 ),
        'tue' : new Date( date2Sun.getTime() + 2 * 24 * 60 * 60 * 1000 ),
        'wed' : new Date( date2Sun.getTime() + 3 * 24 * 60 * 60 * 1000 ),
        'thu' : new Date( date2Sun.getTime() + 4 * 24 * 60 * 60 * 1000 ),
        'fri' : new Date( date2Sun.getTime() + 5 * 24 * 60 * 60 * 1000 ),
        'sat' : new Date( date2Sun.getTime() + 6 * 24 * 60 * 60 * 1000 ), 
        'sunday' : new Date( date2Sun.getTime() + 7 * 24 * 60 * 60 * 1000 )
      }
    }
    if( date1Sun - date2Sun == 0 ){
      back.klass = 'same';
      // console.debug('周中');
    }else if( date1Sun - date2Sun < 0 ){
      back.klass =  'after';
      // console.debug( '周后' );
    }else{
      back.klass =  'before';
      // console.debug('周前');
    }
    return back;
  }
  UTILS.date2str = function(date){
    var m = date.getMonth() + 1;
    var d = date.getDate();
    h = m < 10 ? '0' + m : m;
    d = d < 10 ? '0' + d : d;
    var y = date.getFullYear();
    return y +'-'+m+'-'+d;
  }
  // var t1 = new Date();
  // var t2 = new Date();
  // t1.setMonth(6);
  // t2.setMonth(6);
  // t1.setDate(11);
  // t2.setDate(12);
  // UTILS.isOneWeek( t1, t2 );
  // t1.setDate(1);
  // t2.setDate(8);
  // UTILS.isOneWeek( t1, t2 );
  // t1.setDate(30);
  // t2.setDate(23);
  // UTILS.isOneWeek( t1, t2 );


  //日级视图
  var DayView = function(){
    this.$clock = $('#J_dvClock');
    this.$burnChart = $('#J_dvBurnChart');
    this.$smallTaskList = $('#J_dvSmallTaskList');
    this.$mainTaskList = $('#J_dvMainTaskList');
    this.date = new Date();
    this.renderCalender();
    this.showClock();
    this.renderBurnChart();
    this.renderChooser();
    this.fetchTasks();
  }
  DayView.prototype = {
    // 改变日期
    changeDate : function( date ){
      this.date = date;
      this.renderCalender();
      this.renderBurnChart( date );
    },
    // 渲染小日历
    renderCalender : function(){
      $('#J_dayViewCalender').linkscalender(MonthCalender, this.date);
    },
    // 渲染燃尽图显示当前时间
    renderBurnChart : function(){
      var current =  new Date();
      this.$burnChart.css('height', UTILS.getTimePercent(current) + '%');
    },
    showClock : function(){
      var current = new Date();
      this.$clock.html( UTILS.printTime(current) );
    },
    // 根据具体的日加载任务
    fetchTasks : function(){
      var self = this;
      $.ajax({
        url : 'src/json/day_task.json',
        dataType : 'json',
        type : 'get',
        cache : false,
        error : function(XMLHttpRequest, textStatus, errorThrown){
          console.error(textStatus);
        },
        success : function( response ){
          var resp = response.task;
          var smallListStr = '<h4>全天事件 —— '+ resp.length +'件</h4><ul>';
          var mainListStr = "";
          for( var i in resp ){
          	smallListStr += '<li>'+ resp[i].name +'</li>';
            var planTime = UTILS.str2date( resp[i].planTime );
            var klass = UTILS.isPassed( planTime ) ? 'todo' : '';
            var timeFmtd = UTILS.printTime( planTime );
            var percent = UTILS.getTimePercent( planTime );
            var percented = percent > 94.5 ? 94.5 : percent;

          	mainListStr += '<a class="task_item '+klass+'" style="top:'+ percented +'%"><strong>'+ timeFmtd +'</strong> '+ resp[i].name +'</div>';
          }
          smallListStr += "</ul>";
          self.$smallTaskList.html(smallListStr);
          self.$mainTaskList.html(mainListStr);
        }
      });
    },
    renderChooser : function(){
      $('#J_chooser').children('span').html( config.CHS_MONTHS[this.date.getMonth()] + '月 ' + config.ENG_MONTHS[this.date.getMonth()] + ' ' + this.date.getFullYear() + ' ' + this.date.getDate() + '日');
    }
  }

  //周级视图
  var WeekView = function( date ){
    this.date = date || new Date();
    this.$table = $('#J_weekViewCalender');
    this.$table.linkscalender(WeekCalender);
    this.fetchTasks();
    var self = this;
    this.autoRun = setInterval(function(){
      self.showClock();
    }, 1000);
  }
  WeekView.prototype = {
    // 渲染燃尽图显示当前时间
    renderBurnChart : function(){
      var current = new Date();
      var self = this;
      this.$table.find('td').html('<div class="burn_down_chart"></div>');
      var oneWeekObj = UTILS.isOneWeek( new Date(), this.date );
      this.renderChooser(oneWeekObj['dates']);
      this.$table.addClass( oneWeekObj['klass'] );
      if( oneWeekObj['klass'] === 'same' ){
        var day = current.getDay() === 0 ? 7 : current.getDay();
        this.$table.find('tr:eq(1)').children('td').each(function(i){
          if( i+1 < day ){
            $(this).children('.burn_down_chart').height('100%');
            return;
          }else if( i+1 === day ){
            self.$burnChart = $(this).children('.burn_down_chart');
            self.$burnChart.html('<div class="relative_div"><h5 class="clock"></h5></div>');
            
            self.showClock();
          }
        });
      }else{
        self.$burnChart = null;
      }

      var z = 0;
      for( var j in oneWeekObj['dates'] ){
        var tsks = self.tasks[ UTILS.date2str( oneWeekObj['dates'][j] ) ];
        // console.debug(UTILS.date2str( oneWeekObj['dates'][j] ) + tsk);
        var $td = this.$table.find('tr:eq(1)').children('td:eq('+z+')');
        if( tsks ){
          var back = "";
          for( var o in tsks ){
            var planTime = UTILS.str2date( tsks[o].planTime );
            var klass = UTILS.isPassed( planTime ) ? 'todo' : '';
            var timeFmtd = UTILS.printTime( planTime );
            var percent = UTILS.getTimePercent( planTime );
            var percented = percent > 94.5 ? 94.5 : percent;

            back += '<div class="task_item '+klass+'" style="top:'+percented+'%"><span>x</span>..................</div>';
          }
          $td.append(back);
        }
        z++;
      }
    },
    showClock : function(){
      if( this.$burnChart ){
        var current = new Date();
        var self = this;
        this.$burnChart.height( UTILS.getTimePercent( current ) + '%');
        this.$burnChart.find('.clock').html( UTILS.printTime( current ) );
        this.$burnChart.parent().find('.task_item').each(function(){
          if( parseInt($(this).css('top')) + $(this).outerHeight() <= self.$burnChart.outerHeight() ){
            $(this).removeClass('todo');
          }else{
            $(this).addClass('todo');
          }
        });
      }
    },
    // 加载任务
    fetchTasks : function(){
      var self = this;
      $.ajax({
        url : 'src/json/week_task.json',
        dataType : 'json',
        type : 'get',
        cache : false,
        error : function(XMLHttpRequest, textStatus, errorThrown){
          console.error(textStatus);
        },
        success : function( resp ){
          self.tasks = resp.tasks;
          self.renderBurnChart();
        }
      });
    },
    renderChooser : function( weekObj ){
      $('#J_chooser').children('span').html( config.ENG_MONTHS[weekObj['sunday'].getMonth()] + ' ' + weekObj['sunday'].getFullYear() + '年 ' +
        (weekObj['monday'].getMonth() + 1) + '.' + weekObj['monday'].getDate() + '-' +
        (weekObj['sunday'].getMonth() + 1) + '.' + weekObj['sunday'].getDate());
    }
  }

  //月级视图
  var MonthView = function(){
    this.date = new Date();
    this.fetchTasks();
    this.renderChooser();
  }
  MonthView.prototype = {
    // 改变日期
    changeDate : function(date){
      this.date = date;
      this.fetchTasks();
    },
    // 渲染月日历
    renderCalender : function(option){
      $('#J_monthViewCalender').linkscalender(MonthCalender, this.date, option);
    },
    // 加载月度任务
    fetchTasks : function(){
      var self = this;
      $.ajax({
        url : 'src/json/month_task.json',
        dataType : 'json',
        type : 'get',
        cache : false,
        error : function(XMLHttpRequest, textStatus, errorThrown){
          console.error(textStatus);
        },
        success : function( resp ){
          self.tasks = resp.tasks;
          self.renderCalender( {cellCreatedCallback : function(date){
            var tasks = self.tasks[date.getDate()];
            if( tasks ){
              var back = '<div class="task_items">';
              for( var i in tasks ){
                back += '<span class="task_item">x</span> ';
              }
              back += "</div>";
              return back;
            }
            return '';
          }} );
        }
      });
    },
    renderChooser : function(){
      $('#J_chooser').children('span').html( config.CHS_MONTHS[this.date.getMonth()] + '月 ' + config.ENG_MONTHS[this.date.getMonth()] + ' ' + this.date.getFullYear() );
    }
  }
  
  // MonthCalender Class Definition
  var MonthCalender = function( elem, date, option ){
    this.$table = elem;
    this.option = option;
    this.init();
  }
  MonthCalender.prototype = {
    init : function(){
      this.current = new Date();
      this.changeDate(new Date());
    },
    renderHeader : function(){
      var back = "<tr>";
      for(var i in weeks){
        back += '<th>' + weeks[i] + '</th>';
      }
      back += "</tr>";
      this.$table.html(back);
    },
    renderCalender : function(){
      var back = '';
      var i = 1;
      this.date.setDate(i);
      // 第一行开始
      // 1号的星期数
      var weekInFirstDay = this.date.getDay() == 0 ? 7 : this.date.getDay();
      back += '<tr>';
      if( weekInFirstDay != 1 ){
        back += '<td class="active" colspan="'+ (weekInFirstDay-1) +'"></td>';
      }
      for ( ; weekInFirstDay <=7 ; weekInFirstDay++ ){
        back += '<td><a href="#" class="'+ this.dayStatus(this.date) +'">'+i+'</a>'+ this.option.cellCreatedCallback( this.date ) +'</td>';
        i++;
        this.date.setDate(i);
      }
      back += '</tr>';
      // 第一行结束
      // 第二行及以后开始
      this.date.setDate(i);
      while( true ){
        back += '<tr>';
        for( var j = 0; j < 7; j++ ){
          back += '<td><a href="#" class="'+ this.dayStatus(this.date) +'">'+i+'</a>'+ this.option.cellCreatedCallback( this.date ) +'</td>';
          i++;
          this.date.setDate(i);
          if( this.date.getDate() == 1 ) break;
        }
        if( this.date.getDate() == 1 ){
          this.date.setMonth( this.date.getMonth() - 1);
          this.date.setDate(--i);
          var lastDay = 7 - this.date.getDay();
          if( lastDay > 0 && lastDay != 7 ){
            back += '<td colspan="'+ lastDay +'"></td>';
          }
          break;
        };
        back += '</tr>';
      }
      // 第二行及以后结束
      this.$table.append(back);
    },
    changeDate : function( date ){
      this.date = date;
      this.renderHeader();
      this.renderCalender();
    },
    dayStatus : function(date){ // active, passed , not
      if( date.getFullYear() === this.current.getFullYear() && date.getMonth() === this.current.getMonth() && date.getDate() === this.current.getDate() ){
        return 'active';
      } else if( 
        ( date.getFullYear() < this.current.getFullYear()) || 
        ( date.getFullYear() === this.current.getFullYear() && date.getMonth() < this.current.getMonth() ) || 
        date.getFullYear() === this.current.getFullYear() && date.getMonth() === this.current.getMonth() && date.getDate() < this.current.getDate() ){
        return 'passed';
      }
      return 'not';
    }
  }

  // WeekCalender Class Definition
  var WeekCalender = function( elem ){
    this.$table = elem;
    this.renderHeader();
    this.renderBody();
  }
  WeekCalender.prototype = {
    renderBody : function(){
      var back = "<tr>";
      for( i = 0 ; i < 7 ; i ++ ){
        back += '<td></td>';
      }
      back += "</tr>";
      this.$table.append(back);
    },
    renderHeader : function(){
      var back = "<tr>";
      for(var i in weeks){
        back += '<th>' + weeks[i] + '</th>';
      }
      back += "</tr>";
      this.$table.html(back);
    }
  }

  /*
   * linkscalender plugin definition
   * 必须使用table调用
   */
  $.fn.defaults = {
    cellCreatedCallback : function(date){
      return '';
    } // 每增加一个单元格触发一次, 通过编写这个回调来给单元格加东西，想加什么return什么。
  }
  $.fn.linkscalender = function(type, date, options){
    var c = this.data('calender');
    var option = $.extend(true, {}, $.fn.defaults, options);
    if(!c){
      this.data('calender', c = new type(this, date, option));
    }else{
      c.changeDate( date );
    }
    return this;
  }

  /*
   * 视图切换
   */
  $('#J_switches').children().bind('click', function(){
    $(this).addClass('active').siblings().removeClass('active');
    $($(this).data('href')).css('display','block').siblings().css('display','none');
  });
  $('#J_switches').children('.active').click();




  var testDate = new Date();
  testDate.setMonth(9);
  testDate.setDate(6);
  /* MAIN */
  var dayView = new DayView();
  var monthView = new MonthView();
  var weekView = new WeekView();
  
  dayView.changeDate( testDate );

  
  /* TEST */
  // var testDate2 = new Date('2013-10-11 14:56');
  // var testDate3 = new Date('2013-10-10 15:05');
  // TEST.d( testDate2 + ' 是否已经超过当前时间 ' + UTILS.isPassed( testDate2 ) );
  // TEST.d( testDate3 + ' 是否已经超过当前时间 ' + UTILS.isPassed( testDate3 ) );
}(jQuery));