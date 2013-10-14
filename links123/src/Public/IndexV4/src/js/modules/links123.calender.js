/*
 * @name: links123 日程组件
 * @author: lpgray
 * @datetime: 2013-10-08 12:54
 * */
$(document).ready(function(){
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
  //datetime是否已过当前时间
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
  // 根据时间对象 date , 与 百分比 p 获取，date的百分比时间，时间精确到每10分钟
  UTILS.getPercentDatetime = function(d1, p){
    var datetime = new Date( d1.getTime() );
    var day = datetime.getDate();
    datetime.setHours(0);
    datetime.setMinutes(0);
    datetime.setSeconds(0);
    datetime.setMilliseconds(0);
    p = p == 0 ? 0.01 : p;
    var back = new Date( datetime.getTime() + ( 24 * 60 * 60 * 1000 * p) );
    // back.setMinutes( Math.floor( back.getMinutes()/10 ) * 10 );
    return back;
  }
  // string -> datetime
  UTILS.str2datetime = function(dateStr){
    //var dateStr="2011-08-03 09:15:11"; //returned from mysql timestamp/datetime field
    var a=dateStr.split(" ");
    var d=a[0].split("-");
    var t=a[1].split(":");
    var date = new Date(d[0],(d[1]-1),d[2],t[0],t[1]);
    return date;
  }
  // string -> date
  UTILS.str2date = function(dateStr){
    //var dateStr="2011-08-03 09:15:11"; //returned from mysql timestamp/datetime field
    var a=dateStr.split("-");
    var date = new Date(a[0],(a[1]-1),a[2], 0, 0);
    return date;
  }
  // 比较第二个时间在第一个时间的周中、周前、周后、同时获取第二个时间周一 - 周日时间对象
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
  // 比较date2是date1的当天、之前、之后
  UTILS.isOneDay = function( date1, date2 ){
    var date1 = new Date( date1.getTime() );
    var date2 = new Date( date2.getTime() );
    date1.setHours(0);
    date1.setMinutes(0);
    date1.setMilliseconds(0);
    date1.setSeconds(0);

    date2.setHours(0);
    date2.setMinutes(0);
    date2.setMilliseconds(0);
    date2.setSeconds(0);
    var back = {};
    if( date2.getTime() == date1.getTime() ){
      back.klass = 'same';
    }else if( date2.getTime() > date1.getTime() ){
      back.klass = 'after';
    }else{
      back.klass = 'before';
    }
    return back;
  }
  // 比较两个时间是否同一个月
  UTILS.isOneMonth = function( date1, date2 ){
    var d1 = new Date( date1.getTime() );
    var d2 = new Date( date2.getTime() );
    d1.setDate(1);
    d1.setHours(0);
    d1.setMinutes(0);
    d1.setMilliseconds(0);
    d1.setSeconds(0);

    d2.setDate(1);
    d2.setHours(0);
    d2.setMinutes(0);
    d2.setMilliseconds(0);
    d2.setSeconds(0);
    return d1 - d2 === 0;
  }
  // 输出date 的 yyyy-MM-dd形式
  UTILS.date2str = function(date){
    var m = date.getMonth() + 1;
    var d = date.getDate();
    m = m < 10 ? '0' + m : m;
    d = d < 10 ? '0' + d : d;
    var y = date.getFullYear();
    return y +'-'+ m +'-'+d;
  }
  // 输出datetime 的 yyyy-MM-dd HH:mm形式
  UTILS.datetime2str = function(datetime){
    var m = datetime.getMonth() + 1;
    var d = datetime.getDate();
    m = m < 10 ? '0' + m : m;
    d = d < 10 ? '0' + d : d;
    var y = datetime.getFullYear();

    var h = datetime.getHours();
    h = h < 10 ? '0' + h : h;
    var min = datetime.getMinutes();
    min = min < 10 ? '0' + min : min;

    return y +'-'+ m +'-' + d + ' ' + h + ':' + min;
  }
  // pop延迟函数
  UTILS.popShowing = null;
  // 展示pop提示框
  UTILS.showPop = function( $trigger, strings ){
    var $self = $trigger;
    UTILS.popShowing = setTimeout(function(){
      $('#J_popCtn').html(strings);
      
      var popW = $('#J_pop').outerWidth();
      var popH = $('#J_pop').outerHeight();

      var btnL = $self.offset().left;
      var btnT = $self.offset().top;
      var btnW = $self.outerWidth();

      var popT = parseInt( btnT ) - parseInt( popH ) + 14;
      var popL = parseInt( btnL ) + parseInt( btnW )/2 - parseInt( popW )/2

      $('#J_pop').css({'top' : popT, 'left' : popL});
      $('#J_pop').fadeIn();
    }, 600);
  }
  // 展示pop提示框
  UTILS.hidePop = function(){
    clearTimeout(UTILS.popShowing);
    $('#J_pop').fadeOut();
  }
  // 字符串前端校验
  UTILS.checkString = function( string ){
    // 非空、无注入
    var notNull = /^\S+$/.test( string );
    var notInject = !/^<script>*<\/script>+$/.test( string );
    // d('非空' + notNull);
    // d('无注入' + notInject);
    return notNull && notInject;
  }
  // 改变时间对象
  UTILS.changeDatetime = function( code, datetime, number ){
    switch (code) {
      case 'Y':
      return new Date( datetime.getTime() + number * 365 * 24 * 60 * 60 * 1000 );
      break;
      case 'M':
      return new Date( datetime.getTime() + number * 30 * 24 * 60 * 60 * 1000);
      break;
      case 'W':
      return new Date( datetime.getTime() + number * 7 * 24 * 60 * 60 * 1000);
      break;
    }
  }

  //日级视图
  var DayView = function( date ){
    var self = this;
    this.$taskMgr = $('#J_taskMgr');
    this.$smallTaskList = $('#J_dvSmallTaskList');
    this.$mainTaskList = $('#J_dvMainTaskList');
    this.$calender = $('#J_dayViewCalender');
    this.changeDate( date );
    this.bindEditTaskItemHandler();
    this.bindDragTaskItemHandler();
    this.bindAddTaskHandler();
    this.bindRmvTaskHandler();
    this.bindDateSelectedHandler();
  }
  DayView.prototype = {
    // 改变日期
    changeDate : function( date ){
      var self = this;
      this.date = date ? new Date(date.getTime()) : new Date();
      this.renderCalender();
      this.renderChooser();
      this.renderBurnChart();
      this.fetchTasks();
      clearInterval(this.autoRun);
      this.autoRun = setInterval(function(){
        self.showClock();
      }, 100);
    },
    // 渲染小日历
    renderCalender : function(){
      this.$calender.linkscalender(MonthCalender, this.date);
    },
    // 渲染燃尽图显示当前时间
    renderBurnChart : function(){
      var klass = UTILS.isOneDay( new Date(), new Date( this.date.getTime() ) )['klass'];
      this.$taskMgr.removeClass('same after before').addClass(klass);
      if( klass === 'same' ){
        this.$clock = $('#J_dvClock');
        this.$burnChart = $('#J_dvBurnChart');
        this.showClock();
      }else{
        this.$clock = null;
        this.$burnChart = null;
        clearInterval( this.autoRun );
      }
    },
    showClock : function(){
      var self = this;
      if( this.$clock && this.$burnChart ){
        this.$burnChart.height(UTILS.getTimePercent(new Date()) + '%');
        this.$clock.css('top', parseInt(this.$burnChart.height()) - 34);
        this.$clock.html( UTILS.printTime(new Date()) );
        this.$mainTaskList.find('.task_item').each(function(){
          if( parseInt($(this).css('top')) + $(this).outerHeight() / 2 <= self.$burnChart.outerHeight() ){
            $(this).removeClass('todo');
          }else{
            $(this).addClass('todo');
          }
        });
      } else {
        clearInterval( self.autoRun );
      }
    },
    // 根据具体的日加载任务并按时间高度显示
    fetchTasks : function(){
      var self = this;
      $.ajax({
        url : '/Public/IndexV4/src/json/day_task.json',
        dataType : 'json',
        data : {
          'datetime' : UTILS.datetime2str( self.date )
        },
        type : 'get',
        cache : false,
        error : function(XMLHttpRequest, textStatus, errorThrown){
          console.error(textStatus);
        },
        success : function( response ){
          self.renderAjaxResponse( response );
        }
      });
    },
    renderChooser : function(){
      var self = this;
      $('#J_chooser').children('span').html( 
        this.date.getFullYear() + '年 ' +
        (this.date.getMonth() + 1) + '月 ' +
        this.date.getDate() + '日 ');
      $('#J_prev_date').unbind().bind('click', function(){
        self.changeDate( UTILS.changeDatetime( 'D', self.date, -1 ) );
      });
      $('#J_next_date').unbind().bind('click', function(){
        self.changeDate( UTILS.changeDatetime( 'D', self.date, 1 ) );
      });
    },
    renderAjaxResponse : function( response ){
      var self = this;
      var resp = response.task;
      var smallListStr = '<h4>全天事件 —— '+ resp.length +'件</h4><ul>';
      var mainListStr = "";
      for( var i in resp ){
        smallListStr += '<li>'+ resp[i].name +'</li>';

        var planTime = UTILS.str2datetime( resp[i].planTime );
        var klass = UTILS.isPassed( planTime ) ? 'todo' : '';
        var timeFmtd = UTILS.printTime( planTime );
        var percent = UTILS.getTimePercent( planTime );
        var percented = parseInt( parseInt(self.$mainTaskList.height()) * percent/100 ) - 20;
        mainListStr += '<a href="#" data-datetime="'+ resp[i].planTime +'" data-id="'+ resp[i].id +'" data-name="'+ resp[i].name +'" class="task_item '+klass+'" style="top:'+ percented +'px"><strong>'+ timeFmtd +'</strong>&nbsp;<span class="task_name">'+ resp[i].name +'</span> <span class="remove_task">x</span></a>';
      }
      smallListStr += "</ul>";
      self.$smallTaskList.html(smallListStr);

      self.$mainTaskList.html(mainListStr);

      // 解决密度过近的问题，先按高度降序排列
      var taskDomArray = self.$mainTaskList.find('.task_item').get();
      var length = taskDomArray.length;
      var temp = null;
      for( var tIdx = 0;  tIdx <= length-2 ; tIdx++ ){
        for(var j=length-1; j >= 1; j--){
          var $item = $(taskDomArray[j]);
          var $item2 = $(taskDomArray[j - 1]);
          if( parseInt( $item.css('top') ) > parseInt( $item2.css('top') ) ){
            temp = taskDomArray[j];
            taskDomArray[j] = taskDomArray[j - 1];
            taskDomArray[j - 1] = temp;
          }
        }
      }
      for( var stdIdx = 1; stdIdx < length ; stdIdx++ ){
        var prevT = parseInt($(taskDomArray[stdIdx - 1]).css('top'));
        var $self = $(taskDomArray[stdIdx]);
        if( prevT - parseInt($self.css('top')) < 20 ){
          $self.css('top', prevT - 20);
          continue;
        }
        if( parseInt( $self.css('top') ) < 0 ){
          $self.css('top', 0);
          continue;
        }
      }
    },
    bindEditTaskItemHandler : function(){
      var self = this;
      this.$taskMgr.on('click', '.task_name', function(){
        var $this = $(this);
        if( !$this.children('input').length ){
          $this.html('<input type="text" class="task_editor" value="'+$this.parent().data('name')+'"/>');
          $this.children('input').focus().bind('keyup', function(e){
            if (e.keyCode === 13){
              $(this).blur();
            }
          });
        }
      });
      this.$taskMgr.on('blur', 'input.task_editor', function(){
        var datetime = $(this).parent().parent().data('datetime');
        var dname = $(this).parent().parent().data('name');
        var origin = dname ? dname.replace(/\s/g, '') : '';
        var changed = $(this).val().replace(/\s/g, '');
        var id = $(this).parent().parent().data('id') || '';
        if( origin !== changed ){
          if( !UTILS.checkString( changed ) ){
            alert('请输入你的内容');
          }else{
            self.saveTask( id, changed, datetime );
          }
        }else if( origin !== '' ){
          $(this).parent().html(origin);
        }
      });
    },
    bindDragTaskItemHandler : function(){
      var self = this;
      this.$taskMgr.on('mousedown', '.task_item', function(e){
        if( !$(e.target).is('.task_item') ) return;

        var $taskItem = $(this);
        var tH = parseInt($taskItem.height());
        var left = $taskItem.css('left');
        var top = $taskItem.css('top');
        var ctnH = parseInt( self.$mainTaskList.outerHeight() );
        var lastT = e.clientY;

        $(document).bind('mousemove', function(e){
          top = parseInt(top) + parseInt(e.clientY) - parseInt(lastT);
          if( top > 0 && top < ctnH){
            $taskItem.css('top', top);
            var changedTime = UTILS.getPercentDatetime( self.date, top/ctnH );
            $taskItem.data('datetime', UTILS.datetime2str( changedTime ));
            $taskItem.children('strong').html( UTILS.printTime( changedTime ) );
          }
          lastT = e.clientY;
        });
        $(document).bind('mouseup', function(){
          self.saveTask($taskItem.data('id'), $taskItem.data('name'), $taskItem.data('datetime'));
          $(document).unbind('mouseup mousemove');
        });
      });
    },
    bindAddTaskHandler : function(){
      var self = this;
      this.showTipInfo = function(e){
        var $monthTip = $('#J_monthTip');
        if( $(e.target).is('#J_dvMainTaskList') ){
          var left = e.clientX;
          var top = e.clientY + $(window).scrollTop();
          var ctnT = self.$mainTaskList.offset().top;
          var hoverTime = UTILS.getPercentDatetime( self.date, (top-ctnT) / self.$mainTaskList.height() );
          $monthTip.css({'display':'block', 'left' : left + 16, 'top' : top })
                   .data({ 'datetime':hoverTime ,'top':top-ctnT } )
                   .children('.c_mt_ctn')
                   .html( UTILS.printTime( hoverTime ) + ' 点击添加日程' );
        }else{
          $monthTip.css({'display':'none'});
        }
      }
      this.$mainTaskList.bind('mousemove', this.showTipInfo);
      this.newTask = function(e){
        if( $(e.target).is('#J_dvMainTaskList') ){
          self.$mainTaskList.unbind('mousemove', this.showTipInfo);
          self.$mainTaskList.unbind('mousedown', this.newTask);
          var $monthTip = $('#J_monthTip');
          $monthTip.css({'display':'none'});
          var time = UTILS.printTime($monthTip.data('datetime'));
          var top = $monthTip.data('top');
          var dom = '<div class="task_item" data-datetime="'+ UTILS.datetime2str($monthTip.data('datetime')) +'" style="top:'+top+'px"><strong>'+time+'</strong> <span><input type="text" class="task_editor"/></span>&nbsp;&nbsp;<a href="#" class="cancel_add">取消</a></div>';
          self.$mainTaskList.append(dom);
        }
      }
      this.$mainTaskList.bind('mousedown', this.newTask);
      this.$mainTaskList.bind('mouseout', function(e){
        var $monthTip = $('#J_monthTip');
        $monthTip.css({'display':'none'});
      });
      this.$mainTaskList.on('click', '.cancel_add', function(){
        $(this).parent().remove();
        self.$mainTaskList.bind('mousemove', self.showTipInfo);
        self.$mainTaskList.bind('mousedown', self.newTask);
      });
    },
    bindRmvTaskHandler : function(){
      var self = this;
      this.$taskMgr.on('click', '.remove_task', function(){
        self.rmvTsk($(this).parent());
      });
    },
    saveTask : function(id, name, planTime){
      var self = this;
      d('save task');
      d(id + ' ' + name + ' ' + planTime);
      $.post('/Public/IndexV4/src/json/day_task.json', {
          'datetime' : UTILS.datetime2str( self.date ),
          'id' : id,
          'planTime' : planTime,
          'name' : name
      }, function( response ){
          self.renderAjaxResponse( response );
          if( !id ){
            self.$mainTaskList.bind('mousemove', self.showTipInfo);
            self.$mainTaskList.bind('mousedown', self.newTask);
          }
          $('#J_tabMonthViewTrigger').data('changed', true);// 记录已更新状态，当进入月或周时强制更新
          $('#J_tabWeekViewTrigger').data('changed', true);
          alert('提交成功，与后端整合后将会正确更新任务列表');
      }, 'json');
    },
    rmvTsk : function($tskDom){
      $.post('/Public/IndexV4/src/json/day_task.json', {
        'id' : $tskDom.data('id')
      }, function( response ){
        $tskDom.remove();
      }, 'json');
    },
    bindDateSelectedHandler : function(){
      var self = this;
      this.$calender.on('click', 'a', function(){
        if( !$(this).parents('td').hasClass('active') ){
          self.changeDate( UTILS.str2date($(this).data('datetime')) );
        }
      });
    }
  }

  //周级视图
  var WeekView = function( date ){
    this.$table = $('#J_weekViewCalender');
    this.$table.linkscalender(WeekCalender);
    this.changeDate(date);
  }
  WeekView.prototype = {
    changeDate : function( date, forceFetch ){
      var self = this;
      if( this.date && UTILS.isOneWeek(date, this.date)['klass'] === 'same' ){
        // d('没变周 强制更新：' + forceFetch );
        this.renderChooser();
        forceFetch && this.fetchTasks();
        return;
      }
      this.date = date ? new Date(date.getTime()) : new Date();
      this.oneWeekObj = UTILS.isOneWeek( new Date(), new Date( this.date.getTime() ) );
      this.dates = this.oneWeekObj['dates'];
      this.renderBurnChart();
      this.renderChooser();
      this.fetchTasks();
      clearInterval(this.autoRun);
      this.autoRun = setInterval(function(){
        self.showClock();
      }, 100);
    },
    // 渲染燃尽图显示当前时间
    renderBurnChart : function(){
      var self = this;

      this.$table.find('td').html('<div class="relative_div"><div class="burn_down_chart"></div></div>');

      // this.renderChooser(oneWeekObj['dates']);
      this.$table.removeClass('same before after').addClass( this.oneWeekObj['klass'] );

      if( this.oneWeekObj['klass'] === 'same' ){
        var current = new Date();
        var day = current.getDay() === 0 ? 7 : current.getDay();
        this.$table.find('tr:eq(1)').children('td').each(function(i){
          if( i+1 < day ){
            $(this).find('.burn_down_chart').height('100%');
            return;
          }else if( i+1 === day ){
            self.$burnChart = $(this).find('.burn_down_chart');
            self.$burnChart.html('<div class="relative_div"><h5 class="clock"></h5></div>');
          }
        });
      }else{
        self.$burnChart = null;
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
      }else{
        clearInterval(this.autoRun);
      }
    },
    // 加载任务
    fetchTasks : function(){
      var self = this;
      $.ajax({
        url : '/Public/IndexV4/src/json/week_task.json',
        dataType : 'json',
        data : {
          'datetime' : UTILS.datetime2str( self.date )
        },
        type : 'get',
        cache : false,
        error : function(XMLHttpRequest, textStatus, errorThrown){
          console.error(textStatus);
        },
        success : function( resp ){
          self.tasks = resp.tasks;
          self.renderTasks();
        }
      });
    },
    renderChooser : function(){
      var self = this;
      $('#J_chooser').children('span').html( this.dates['sunday'].getFullYear() + '年 ' +
        (this.dates['monday'].getMonth() + 1) + '.' + this.dates['monday'].getDate() + '-' +
        (this.dates['sunday'].getMonth() + 1) + '.' + this.dates['sunday'].getDate());
      $('#J_prev_date').unbind().bind('click', function(){
        self.changeDate( UTILS.changeDatetime('W', self.date, -1) );
      });
      $('#J_next_date').unbind().bind('click', function(){
        self.changeDate( UTILS.changeDatetime('W', self.date, 1) );
      });
    },
    renderTasks : function(){
      var self = this;
      var z = 0;
      for( var j in this.oneWeekObj['dates'] ){
        var tsks = self.tasks[ UTILS.date2str( this.oneWeekObj['dates'][j] ) ];
        
        var $td = this.$table.find('tr:eq(1)').children('td:eq('+z+')');

        $td.attr('data-datetime', UTILS.date2str( this.oneWeekObj['dates'][j] ) );
        
        if( tsks ){
          var back = "";
          for( var o in tsks ){
            var planTime = UTILS.str2datetime( tsks[o].planTime );
            var klass = UTILS.isPassed( planTime ) ? 'todo' : '';
            var timeFmtd = UTILS.printTime( planTime );
            var percent = UTILS.getTimePercent( planTime );
            var percented = percent > 94.5 ? 94.5 : percent;
            back += '<div class="task_item '+klass+'" style="top:'+percented+'%"><span>x</span>..................</div>';
          }
          $td.append(back);

          $td.bind('mouseenter', function(){
            var tsks = self.tasks[ $(this).data('datetime') ];
            if( tsks ){
              var popStr = "<ul>";
              for( var i in tsks ){
                popStr += '<li>'+ tsks[i].name +' ('+ tsks[i].planTime.substring(11) +')</li>'; 
              }
              popStr += '</ul>';
              UTILS.showPop( $(this) , popStr );
            }
          });
          $td.bind('mouseleave', function(){
            UTILS.hidePop();
          });
        }else{
          $td.unbind('mouseleave mouseenter');
        }
        z++;
      }
    }
  }

  //月级视图
  var MonthView = function( date ){
    this.$table = $('#J_monthViewCalender');
    this.changeDate( date );
  }
  MonthView.prototype = {
    // 改变日期
    changeDate : function(date, forceFetch){
      if( this.date && UTILS.isOneMonth( date, this.date ) ){
        // d('没变月 强制更新：' + forceFetch );
        this.renderChooser();
        forceFetch && this.fetchTasks();
        return;
      }
      this.date = date ? new Date(date.getTime()) : new Date();
      this.fetchTasks();
      this.renderChooser();
    },
    // 渲染月日历
    renderCalender : function(option){
      this.$table.linkscalender(MonthCalender, this.date, option);
    },
    // 加载月度任务
    fetchTasks : function(){
      var self = this;
      $.ajax({
        url : '/Public/IndexV4/src/json/month_task.json',
        dataType : 'json',
        data : {
          'datetime' : UTILS.datetime2str(self.date)
        },
        type : 'get',
        cache : false,
        error : function(XMLHttpRequest, textStatus, errorThrown){
          //console.error(textStatus);
        },
        success : function( resp ){
          self.tasks = resp.tasks;
          self.$table.linkscalender(MonthCalender, self.date, {cellCreatedCallback : function(celldate){
            var tasks = self.tasks[ UTILS.date2str( celldate ) ];
            if( tasks ){
              var back = '<div class="task_items">';
              for( var i in tasks ){
                back += '<span class="task_item">x</span> ';
              }
              back += "</div>";
              return back;
            }
            return '';
          }});
          // 绑定悬浮事件
          self.$table.find('td').find('a').each(function(){
            var $this = $(this);
            if( $this.data('datetime') && self.tasks[ $this.data('datetime') ] ){
              $this.mouseenter(function(){
                var tsks = self.tasks[$(this).data('datetime')];
                var showString = "<ul>";
                $.each(tsks, function(i){
                  showString += '<li>'+ tsks[i].name.substring(0, 10) +' ('+ tsks[i].planTime.substring(11) +')</li>';
                });
                showString += '</ul>';
                UTILS.showPop( $(this), showString );
              });
              $this.mouseleave(function(){
                UTILS.hidePop();
              });
            }
          });
        }
      });
    },
    renderChooser : function(){
      $('#J_chooser').children('span').html(this.date.getFullYear() + '年 ' + (this.date.getMonth()+1) + '月 ');
      var self = this;
      $('#J_prev_date').unbind().bind('click', function(){
        self.changeDate( UTILS.changeDatetime('M', self.date, -1) );
      });
      $('#J_next_date').unbind().bind('click', function(){
        self.changeDate( UTILS.changeDatetime('M', self.date, 1) );
      });
    }
  }
  
  // MonthCalender Class Definition
  var MonthCalender = function( elem, date, option ){
    this.$table = elem;
    this.option = option;
    this.changeDate( date );
  }
  MonthCalender.prototype = {
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
      var date = new Date( this.date.getTime() );
      date.setDate(i);
      // 第一行开始
      // 1号的星期数
      var weekInFirstDay = date.getDay() == 0 ? 7 : date.getDay();
      back += '<tr>';
      if( weekInFirstDay != 1 ){
        back += '<td class="' + this.dayStatus(date) + '" colspan="'+ (weekInFirstDay-1) +'"></td>';
      }
      for ( ; weekInFirstDay <=7 ; weekInFirstDay++ ){
        back += '<td class="'+ this.dayStatus(date) +'"><div class="relative_div"><a data-datetime="'+ UTILS.date2str(date) +'" href="#">'+i+'</a>'+ this.option.cellCreatedCallback( date ) +'</div></td>';
        i++;
        date.setDate(i);
      }
      back += '</tr>';
      // 第一行结束
      // 第二行及以后开始
      date.setDate(i);
      while( true ){
        back += '<tr>';
        for( var j = 0; j < 7; j++ ){
          back += '<td class="'+ this.dayStatus(date) +'"><div class="relative_div"><a data-datetime="'+ UTILS.date2str(date) +'" href="#">'+i+'</a>'+ this.option.cellCreatedCallback( date ) +'</td></div>';
          i++;
          date.setDate(i);
          if( date.getDate() == 1 ) break;
        }
        if( date.getDate() == 1 ){
          date.setMonth( date.getMonth() - 1);
          date.setDate(--i);
          var lastDay = 7 - date.getDay();
          if( lastDay > 0 && lastDay != 7 ){
            back += '<td class="'+ this.dayStatus(date) +'" colspan="'+ lastDay +'"></td>';
          }
          break;
        };
        back += '</tr>';
      }
      // 第二行及以后结束
      this.$table.append(back);
    },
    changeDate : function( date ){
      if( !this.date || !UTILS.isOneMonth( date, this.date ) ){
        this.date = new Date( date.getTime() );
        this.renderHeader();
        this.renderCalender();
      }else{
        this.$table.find('td').removeClass('active');
        this.$table.find('a[data-datetime='+ UTILS.date2str( date ) +']').parents('td').addClass('active');
      }
    },
    dayStatus : function(date){ // active, passed , not
      var current = new Date();
      var back = '';
      if( UTILS.isOneDay( date, current )['klass'] == 'same' ){
        back = 'active ';
      }
      if( date.getFullYear() === current.getFullYear() && date.getMonth() === current.getMonth() && date.getDate() === current.getDate() ){
        return back += 'current';
      } else if(
        ( date.getFullYear() < current.getFullYear()) || 
        ( date.getFullYear() === current.getFullYear() && date.getMonth() < current.getMonth() ) || 
        date.getFullYear() === current.getFullYear() && date.getMonth() === current.getMonth() && date.getDate() < current.getDate() ){
        return back += 'passed';
      }
      return back += 'not';
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

  // 视图管理对象
  var ViewMgr = {
    constructors : {
      'dayView' : DayView,
      'weekView' : WeekView,
      'monthView' : MonthView
    },
    weekView : null,
    monthView : null,
    dayView : null,
    active : null,
    openView : function( viewName, date, forceFetch ){
      if( this.active && this[this.active] ){
        date = date || this[this.active].date;
      }
      if( this.active === viewName ){
        return;
      } else if( this[viewName] ) {
        this[viewName].changeDate( date, forceFetch );
      } else {
        this[viewName] = new this.constructors[viewName]( date );
      }
      this.active = viewName;
    }
  }

  /*
   * 视图切换
   */
  $('#J_switches').children().bind('click', function(){
    $(this).addClass('active').siblings().removeClass('active');
    var viewId = $(this).data('href');
    $(viewId).css('display','block').siblings().css('display','none');
    var changed = false;
    if( viewId !== '#dayView' && $(this).data('changed') ){
      changed = true;
      $(this).data('changed', false);
    }
    ViewMgr.openView( $(this).data('viewname'), null, changed );
  });
  $('#J_switches').children('.active').click();
  /*
   * 从月级、周级视图进入日级视图
   */
  var enterDayView = function(){
    var view = 'dayView';
    var date = UTILS.str2date($(this).data('datetime'));
    d(view + ' ' + date);
    $('#J_tabDayViewTrigger').addClass('active').siblings().removeClass('active');
    $('#'+view).css('display','block').siblings().css('display','none');
    ViewMgr.openView(view, date);
  }
  $('#J_weekViewCalender').on('click', 'td', enterDayView);
  $('#J_monthViewCalender').on('click', 'a', enterDayView);

}(jQuery));
});