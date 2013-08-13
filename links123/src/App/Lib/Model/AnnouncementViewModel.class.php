<?php
// 公告视图模型
class AnnouncementViewModel extends ViewModel {

	public $viewFields = array(
	
		'announcement' => array('id', 'title', 'uid', 'create_time', 'status','click_num', 'sort', '_type'=>'LEFT'),

		'user' => array('nickname', '_on'=>'announcement.uid=user.id'),

	);
}

?>

