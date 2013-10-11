var APP = $CONFIG['APP'];
var URL = $CONFIG['URL'];
var PUBLIC = $CONFIG['PUBLIC'];

$(function(){
  getHeight();
  $(window).on('resize', function(){
    getHeight();
  });
  function getHeight(){
    var h = $(window).height() - 127;
    $("#main-wrap").find('iframe').height(h);
  }
});