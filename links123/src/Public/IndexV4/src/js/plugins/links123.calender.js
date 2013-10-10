/*
 * @name: links123 日程组件
 * @author: lpgray
 * @datetime: 2013-10-08 12:54
 * */
(function($){
  var config = {};
  config.CHS_WEEKS = ['日','一','二','三','四','五','六'];
  config.CHS_MONTHS = ['一','二','三','四','五','六','七','八','九','十','十一','十二'];
  config.ENG_WEEKS = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];

  var weeks = config.CHS_WEEKS;
  var months = config.CHS_MONTHS;

  /*
   * 视图切换
   */
   $('#J_switches').children().bind('click', function(){
    $(this).addClass('active').siblings().removeClass('active');
    $($(this).data('href')).css('display','block').siblings().css('display','none');
   });
   $('#J_switches').children('.active').click();

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
    this.fetchTasks();
  }
  DayView.prototype = {
    // 改变日期
    changeDate : function( date ){
      this.date = date;
      this.renderCalender();
    },
    // 渲染小日历
    renderCalender : function(){
      $('#J_dayViewCalender').linkscalender(MonthCalender, this.date);
    },
    // 渲染燃尽图显示当前时间
    renderBurnChart : function(){
      var current = new Date();
      this.$burnChart.css('height', (current.getHours()*60+current.getMinutes()) / (24*60) * 100 + '%');
    },
    showClock : function(){
      var current = new Date();
      this.$clock.html( current.getHours() + ':' + current.getMinutes() );
    },
    // 根据具体的日加载任务
    fetchTasks : function(){
      var self = this;
      $.ajax({
        url : 'src/json/one_day_task.json',
        dataType : 'json',
        type : 'get',
        cache : false,
        error : function(XMLHttpRequest, textStatus, errorThrown){
          console.error(textStatus);
        },
        success : function( resp ){
          var smallListStr = '<h4>全天事件 —— '+ resp.length +'件</h4><ul>';
          var mainListStr = "";
          for( var i in resp ){
          	smallListStr += '<li>'+ resp[i].name +'</li>';
          	mainListStr += '<div class="task_item"><strong>'+resp[i].planTime+'</strong> '+ resp[i].name +'</div>';
          }
          smallListStr += "</ul>";
          self.$smallTaskList.html(smallListStr);
          self.$mainTaskList.html(mainListStr);
        }
      });
    }
  }
  //周级视图
  var WeekView = function(){

  }
  WeekView.prototype = {
    // 渲染周日历
    renderCalender : function(){

    },
    // 渲染燃尽图显示当前时间
    renderBurnChart : function(){

    },
    showClock : function(){

    },
    // 加载任务
    fetchTasks : function(){
      
    }
  }
  //月级视图
  var MonthView = function(){
    this.date = new Date();
    $('#J_weekViewCalender').linkscalender(MonthCalender, this.date);
  }
  MonthView.prototype = {
    // 改变日期
    changeDate : function(date){
      this.date = date;
      $('#J_weekViewCalender').linkscalender(MonthCalender, this.date);
    },
    // 渲染月日历
    renderCalender : function(){
      $('#J_weekViewCalender').linkscalender(MonthCalender, this.date);
    },
    // 加载月度任务
    fetchTasks : function(){
      
    }
  }
  // MonthCalender Class Definition
  var MonthCalender = function( elem, date ){
    this.$table = elem;
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
    renderChooser : function(){
      $('.J_chooser').children('span').html( months[this.date.getMonth()] + '月 ' + this.date.getFullYear() + '年 ' + this.date.getDay() + '日' );
    },
    renderCalender : function(){
      var back = '';
      var i = 1;
      this.date.setDate(i);
      // 第一行开始
      // 1号的星期数
      var weekInFirstDay = this.date.getDay();
      back += '<tr>';
      if( weekInFirstDay != 0 ){
        back += '<td colspan="'+ weekInFirstDay +'"></td>';
      }
      for ( ; weekInFirstDay <=6 ; weekInFirstDay++ ){
        back += '<td><a href="#" class="'+ this.dayStatus(this.date) +'">'+i+'</a></td>';
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
          back += '<td><a href="#" class="'+ this.dayStatus(this.date) +'">'+i+'</a></td>';
          i++;
          this.date.setDate(i);
          if( this.date.getDate() == 1 ) break;
        }
        if( this.date.getDate() == 1 ){
          this.date.setMonth( this.date.getMonth() - 1);
          this.date.setDate(--i);
          var lastDay = 6 - this.date.getDay();
          if( lastDay != 0 ){
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
      //this.renderChooser();
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

  /*
   * Clock
   * 分钟表
   */
   var Clock = function(){

   }
   Clock.prototype = {
    
   }


  /*
   * linkscalender plugin definition
   * 必须使用table调用
   */

  $.fn.linkscalender = function(type, date){
    var c = this.data('calender');
    if(!c){
      this.data('calender', c = new type(this, date));
    }else{
      c.changeDate( date );
    }
    return this;
  }

  var dayView = new DayView();
  var monthView = new MonthView();
  var testDate = new Date();
  testDate.setMonth(4);
  monthView.changeDate( testDate );
  
}(jQuery));