var listLevel;
var listObj;

function renderLevelTable(value){
	if(!value){
		value = 0;
	}
	var list = new Array();
	$.each(listLevel,function(key,item){
		var temp;
		if(item.count == value){
			temp = new Array('<tr><td>',item['id'],
					'</td>','<td>',item['name'],'</td>','<td>',item['count'],'</td></tr>');
			list.push(temp.join(''));
		}
	});
	var html = list.join(' ');
	$('#level').empty().html(list.join(' '));
}

function renderObjTable(value){
	if(!value){
		value = 0;
	}
	
	var list = new Array();
	$.each(listObj,function(key,item){
		var temp;
		if(item.count == value){
			temp = new Array('<tr><td>',item['id'],
					'</td>','<td>',item['name'],'</td>','<td>',item['count'],'</td></tr>');
			list.push(temp.join(''));
		}
	});
	$('#object').empty().html(list.join(' '));
}

$('document').ready(function(){
	listLevel = $.parseJSON($('#levelData').val());
	listObj = $.parseJSON($('#objData').val());
	$('#level_btn').click(function(){
		renderLevelTable($('#level_input').val());
	})
	
	$('#obj_btn').click(function(){
		renderObjTable($('#obj_input').val());
	})
});
