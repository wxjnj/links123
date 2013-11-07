(function($){
  function createPageNumbers(config){
    var total = config.total;  //一共有多少个页
    var current = config.current * 1;  //active的页码
    var show = config.show;  //最少显示多少个页码
    var ends = config.ends;  //两端显示多少个页码
    var all = [];
    var left = [];
    var leftSpan = config.span;
    var content = [];
    var rightSpan = config.span;
    var right = [];
    var offset, contentLeft, contentRight;

    if(total <= show){
      looper(1, total);
    }else{
      offset = ~~(show / 2);
      offset = offset == (show / 2) ? offset - 1 : offset;
      contentLeft = current - offset;
      contentRight = current + offset;

      if(contentLeft < 1){
        contentRight = contentRight - contentLeft + 1;
        contentLeft = 1;
      }

      if(contentRight > total){
        contentLeft = contentLeft - contentRight + total;
        contentRight = total;
      }

      if(contentLeft - ends <= 2){
        contentLeft = 1;
        leftSpan = null;
      }else{
        left = [1, ends];
      }

      if(contentRight + ends >= total - 1){
        contentRight = total;
        rightSpan = null;
      }else{
        right = [total - ends + 1, total];
      }

      if(left.length){
        looper(left[0], left[1]);
      }

      if(leftSpan){
        all.push(leftSpan);
      }

      looper(contentLeft, contentRight);

      if(rightSpan){
        all.push(rightSpan);
      }

      if(right.length){
        looper(right[0], right[1]);
      }

    }

    function looper(s, e){
      for(; s <= e; s++){
        all.push(s);
      }
    }

    var str = '';
    var active = '';
    var i = 0;
    var len = all.length;
    var v;
    for( ; i < len ; i++){
      v = all[i];
      active = '';
      if(v == current) active = 'active';
      if(v == config.span) active = 'disabled';

      str += '<li class="' + active + '"><a href="javascript:;" data-num="' + v + '">' + v + '</a></li>';
    }

    if(config.prev){
      active = current == 1 ? 'disabled' : '';
      str = '<li class="' + active + '"><a href="javascript:;" data-num="' + (current - 1) + '">' + config.prev + '</a></li>' + str;
      active = current == total ? 'disabled' : '';
      str += '<li class="' + active + '"><a href="javascript:;" data-num="' + (current + 1) + '">' + config.next + '</a></li>';
    }
    return str;
    //return '<ul>' + str + '</ul>';
  }

  var Sp = function(){
    this.defaultConfig = {
      container: '',
      next: '下一页', // or null
      prev: '上一页', // or null
      total: 100,
      current: 1,
      show: 8,
      ends: 2,
      span: '...' // or null
    };
  };

  Sp.prototype.render = function(userConfig){
    var self = this;
    var config = $.extend({}, self.config || self.defaultConfig, userConfig);
    var str = createPageNumbers(config);
    if(self.pages){
      self.pages.remove();
    }
    self.pages = $(str);
    self.pages.appendTo(config.container);
    self.config = config;
  }

  $.fn.extend({
    pagination: function(userConfig){
      var key = this.selector;
      var o;
      if(Sp[key]){
        o = Sp[key];
      }else{
        userConfig.container = this;
        o = new Sp();
        Sp[key] = o;
      }
      o.render(userConfig);
      return o;
    }
  });

})(jQuery);