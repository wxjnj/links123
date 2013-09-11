<?php
/**
 * @desc 公告管理模型
 * @name AdvertModel.class.php
 * @package Admin
 * @author Frank UPDATE 2013-09-5
 * @version 1.0
 */
class AnnouncementModel extends CommonModel {
	public $_validate =	array(
		array('title', 'require', '标题必须'),
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
}
?>
