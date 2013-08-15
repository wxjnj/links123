 $(function(){
	 
    $('#frm_reg').ajaxForm({
        beforeSubmit:  checkForm,  // pre-submit callback
        success:       complete,  // post-submit callback
        dataType: 'json'
    });
    function checkForm(){
       alert('checkid')
    }
    function complete(data){
        alert('cdd')
    }
 });
function checkTitle(){
    $.post('__URL__/checkTitle',{'title':$('#title').val()},function(data){
        $('#result').html(data.info).show();
    },'json');
}
