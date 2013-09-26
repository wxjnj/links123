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