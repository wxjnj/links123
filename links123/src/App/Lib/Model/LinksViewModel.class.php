<?php
// 链接视图模型
class LinksViewModel extends ViewModel {

	public $viewFields = array(
	
		'links' => array('id', 'title', 'category', 'language', 'link', 'grade', 'create_time', 'status', 'sort', 'uid','click_num','recommended', '_type'=>'LEFT'),

		'category' => array('cat_name', 'prt_id', '_on'=>'links.category=category.id', '_type'=>'LEFT'),
			
		//'member' => array('nickname'=>'mbr_name', '_on'=>'links.mid=member.id', '_type'=>'LEFT'),
			
		'user' => array('nickname'=>'usr_name', '_on'=>'links.uid=user.id'),

	);
}

?>

