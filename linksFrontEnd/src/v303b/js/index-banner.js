$(function(){

    var coodrs = $('#K_banner_01_map').find('area:first').attr('coords').split(',');
    $('#K_banner_01_arrow').css({
        left: +coodrs[0] + (coodrs[2]-coodrs[0])/2 - 27 + 'px',
        top: coodrs[1] - 50 + 'px'
    }).show();

    $('#K_banner_01_map').find('.doll').on('mouseover', function(){
        var coodrs = $(this).attr('coords').split(',');
        $('#K_banner_01_arrow').css({
            left: +coodrs[0] + (coodrs[2]-coodrs[0])/2 - 27 + 'px',
            top: coodrs[1] - 50 + 'px'
        }).show();
    });

});