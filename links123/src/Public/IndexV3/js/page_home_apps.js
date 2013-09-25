/*
 * @name: 首页app相关 js
 * @author: lpgray
 * @datetime: 2013-09-25 13:05
 */
$(function($){
  /*
   * app开关触发器
   */
  $.fn.links123_apptrigers = function( selector ){
    this.delegate(selector, 'click', function(){
      var appId = $(this).attr('href');
      $(this).data('links_app') || $(this).data('links_app', new App(appId));
      var app = $(this).data('links_app');
      app.show();
    });
  }
  $('#J_Apps').links123_apptrigers('a');
  
  /*
   * App类
   */
  var App = function( appId ){
    this.appId = appId;
    this.$elem = $( appId );
    //console.debug('init app-' + appId + '...');
  };
  App.prototype = {
    show : function(){
      this.$elem.show();
      console.debug( 'app-' + this.appId + ' showing...' );
    },
    close : function(){
      this.$elem.hide();
    }
  };
  
  /*
   * App管理器对象
   */
  var AppMgr = {
    currentApp : null
  }
  
  //使用FancyBox插件显示
  // $('#J_Apps').find('.fancybox-btn').fancybox({
      // padding: 10,
      // openSpeed  : 150,
      // closeSpeed  : 150,
      // closeClick : true,
      // helpers : {
        // overlay : null,
//         
      // },
      // onComplete : function(){
        // console.debug('...');
      // }
  // });
  
  //可拖动的panel用jquery.ui显示
  // $('#J_Apps').find('.jquery-ui-panel').fancybox({
//     
  // });
  
  
  
  /** $日历 * */
  // $('#J_calendar').click(function(){
    // $('#J_calendar_iframe').attr('src', 'http://baidu365.duapp.com/wnl.html');
    // $.fancybox({
      // href: '#J_box_calendar',
      // //closeBtn : false,
      // helpers:  {
        // title:  null,
        // overlay : null
      // },
      // width: 550,
      // height: 525,
      // autoSize: false
    // });
    // return false;
  // });
  // $('#J_box_calendar_list a').click(function() {
    // $('#J_calendar_iframe').attr('src', $(this).attr('data-url'));
    // return false;
  // });
  /** 日历$ **/
  
}(jQuery));