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
  var WeekView = function(){
    this.date = new Date();
    this.renderCalender();
  }
  WeekView.prototype = {
    // 渲染周日历
    renderCalender : function(){
      $('#J_weekViewCalender').linkscalender(WeekCalender);
    },
    // 渲染燃尽图显示当前时间
    renderBurnChart : function(){
      // this date week
      var current = new Date();
    },
    showClock : function( td ){
      var current = new Date();
      $(td).find('.clock').html( UTILS.printTime(current) );
    },
    // 加载任务
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
          console.debug(resp);
        }
      });
    },
    renderChooser : function(){
      $('#J_chooser').children('span').html( config.CHS_MONTHS[this.date.getMonth()] + '月 ' + config.ENG_MONTHS[this.date.getMonth()] + ' ' + this.date.getFullYear() + ' ' + this.date.getDate() + '日');
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
        back += '<td><div class="burn_down_chart"></div></td>';
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

  /* MAIN */
  var dayView = new DayView();
  var monthView = new MonthView();
  var weekView = new WeekView();
  var testDate = new Date();
  testDate.setMonth(4);
  dayView.changeDate( testDate );

  
  /* TEST */
  // var testDate2 = new Date('2013-10-11 14:56');
  // var testDate3 = new Date('2013-10-10 15:05');
  // TEST.d( testDate2 + ' 是否已经超过当前时间 ' + UTILS.isPassed( testDate2 ) );
  // TEST.d( testDate3 + ' 是否已经超过当前时间 ' + UTILS.isPassed( testDate3 ) );
}(jQuery));