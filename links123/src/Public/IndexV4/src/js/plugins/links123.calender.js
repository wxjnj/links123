/*
 * @name: calender
 * @author: lpgray
 * @datetime: 2013-10-08 12:54
 * */
(function($){
  var weeks = ['日','一','二','三','四','五','六'];
  
  var Calender = function( tableId, chooserId, reqUrl ){
    this.$table = $('#' + tableId);
    this.$chooser = $('#' + chooserId);
    this.reqUrl = reqUrl;
    this.data = {}
    this.init();
  }
  
  Calender.prototype = {
    init : function(){
      var date = new Date();
      
      this.current = {
        year : date.getFullYear(),
        month : date.getMonth() + 1,
        day : date.getDate(),
        week : date.getDay()
      }
      
      this.date = date;
      this.year = date.getFullYear();
      this.month = date.getMonth() + 1;
      this.day = date.getDate();
      this.week = date.getDay();
      
      this.renderHeader();
      
      this.changeMonth();
      
      this.bindEventHandler();
    },
    renderHeader : function(){
      this.$table.html('');
      var $thead = $('<thead />');
      $thead.html('<tr></tr>');
      $thr = $thead.children('tr');
      for(var i in weeks){
        $thr.append('<th>' + weeks[i] + '</th>');
      }
      this.$thead = $thead;
      this.$table.html($thead);
      this.$table.append('<tbody></tbody>');
      this.$tbody = this.$table.children('tbody');
    },
    renderChooser : function(){
      var s = '<a href="#" class="prevm"><</a>' + 
              '<span class="year">' + this.year + '年</span>' +
              '<span class="month">' + this.month + '月</span>' +
              '<a href="#" class="nextm">></a>';
      this.$chooser.html(s);
    },
    renderBody : function(){
      var back = '';
      var date = this.date;
      
      var i = 1;
      date.setDate(i);
      
      back += "<tr>";
      for( var j = 0; j < 7; j++ ){
        date.setMonth(this.month - 1);
        date.setFullYear(this.year);
        if( date.getDay() == j ){
          
          var k = this.isPassedCurrent(date);
          
          if( k === 1 ){
            back += '<td class="ignore"><a href="#">' + date.getDate() + '</a></td>';
          }else if( k === 2){
            back += '<td class="' + this.data[date.getDate()] +'"><a href="#"><strong style="text-decoration: underline;">' + date.getDate() + '</strong></a></td>';
          }else{
            back += '<td class="' + this.data[date.getDate()] +'"><a href="#">' + date.getDate() + '</a></td>';
          }
          i++;
          date.setDate(i);
        }else{
          back += '<td><a href="#">&nbsp;</a></td>';
        }
      }
      back += "</tr>";
      
      var isNewMonth = false;
      for( var x = 0; x < 35; x++ ){
        date.setMonth(this.month - 1);
        date.setFullYear(this.year);
        date.setDate(i);
        if( date.getDay() == 0 ){
          back += "<tr>";
        }
        var k = this.isPassedCurrent(date);
        if( k === 1 ){
          back += '<td class="ignore"><a href="#">' + date.getDate() + '</a></td>';
        }else if( k === 2){
          back += '<td class="' + this.data[date.getDate()] +'"><a href="#"><strong style="text-decoration: underline;">' + date.getDate() + '</strong></a></td>';
        }else{
          back += '<td class="' + this.data[date.getDate()] +'"><a href="#">' + date.getDate() + '</a></td>';
        }
        
        if( date.getDay() == 6 ){
          back += "</tr>";
        }
        
        i++;
      }
      
      this.$tbody.html(back);
    },
    isPassedCurrent : function( date ){ // 0 1 2
      if( date.getFullYear() >= this.current.year ){
        if( (date.getMonth() + 1) > this.current.month || (date.getMonth() + 1) > this.month ){
          return 1;
        }else if( (date.getMonth() + 1) == this.current.month ){
          if( date.getDate() > this.current.day ){
            return 1;
          }else if( date.getDate() == this.current.day ){
            return 2;
          }
        }
      }
      return 0;
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
}(jQuery));