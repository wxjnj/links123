<?php
// 公告模型
class AnnouncementModel extends CommonModel {
	public $_validate =	array(
		array('title', 'require', '标题必须'),
		//array('content', 'require', '内容必须'),
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
}
?>
