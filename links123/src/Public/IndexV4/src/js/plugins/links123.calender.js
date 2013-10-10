/*
 * @name: calender
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
   * Calender Class Definition
   * 1. 根据月份显示日历
   * 2. 根据日加载任务列表
   */
  var Calender = function( elem ){
    this.$table = elem;
    this.init();
  }
  
  Calender.prototype = {
    init : function(){
      var date = new Date();
      
      this.current = {
        year : date.getFullYear(),
        month : date.getMonth(),
        day : date.getDate(),
        week : date.getDay()
      }
      
      this.date = date;
      this.year = date.getFullYear();
      this.month = date.getMonth();
      this.day = date.getDate();
      this.week = date.getDay();
      
      this.renderHeader();
      this.renderChooser();
      this.renderCalender();
      //this.changeMonth();
      //this.bindEventHandler();
    },
    renderHeader : function(){
      var back = "<tbody><tr>";
      for(var i in weeks){
        back += '<th>' + weeks[i] + '</th>';
      }
      back += "</tr></tbody>";
      this.$table.html(back);
      this.$tbody = this.$table.children('tbody');
    },
    renderChooser : function(){
      $('.J_chooser').children('span').html( months[this.month] + '月 ' + this.year + '年 ' + this.day + '日' );
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
        back += '<td><a href="#">'+i+'</a></td>';
        i++;
      }
      back += '</tr>';
      // 第一行结束
      // 第二行及以后开始
      this.date.setDate(i);
      while( true ){
        back += '<tr>';
        for( var j = 0; j < 7; j++ ){
          back += '<td><a href="#">'+i+'</a></td>';
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
            break;
          }
        };
        back += '</tr>';
      }
      // 第二行及以后结束
      
      this.$tbody.append(back);
    },
    changeMonth : function( month ){
      if( month ){
        this.month = month;
        this.date.setMonth( this.month - 1 );
      }
      
      var self = this;
      
      this.fetch( function(){
        self.renderChooser();
        self.renderBody();
      } );
    },
    bindEventHandler : function(){
      var self = this;
      this.$chooser.on('click', '.prevm', function(){
        if( self.month == 1 ){
          return;
        }
        self.changeMonth( self.month - 1 );
      });
      this.$chooser.on('click', '.nextm', function(){
        if( self.month == self.current.month || self.month == 12 ){
          return;
        }
        self.changeMonth( self.month + 1 );
      });
    },
    fetch : function( success ){
      var self = this;
      $.ajax({
        url : self.reqUrl,
        dataType : 'json',
        type : 'get',
        cache : false,
        error : function(XMLHttpRequest, textStatus, errorThrown){
          console.error(XMLHttpRequest);
          console.error(textStatus);
          console.error(errorThrown);
        },
        success : function( resp ){
          self.data = resp.result;
          success && success();
        }
      });
    }
  }

  /*
   * Clock Class Definition
   * 燃尽图 +　计时
   */
   var Clock = function(){

   }
   Clock.prototype = {

   }


  /*
   * linkscalender plugin definition
   * 必须使用table调用
   */
  $.fn.linkscalender = function(){
    var c = this.data('calender');
    if(!c) this.data('calender', c = new Calender(this));
    return this;
  }

  $('#J_dayViewCalender').linkscalender();
  $('#J_weekViewCalender').linkscalender();
}(jQuery));