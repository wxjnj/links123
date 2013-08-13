<?php
// 友情链接模型
class OutsideLinksModel extends CommonModel {
	public $_validate =	array(
		array('title', 'require', '标题必须'),
		array('url', 'require', '链接必须'),
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
}
?>
