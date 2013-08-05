<?php
// 链接模型
class LinksModel extends CommonModel {
	public $_validate =	array(
		array('title', 'require', '标题必须'),
		array('link', 'require', '链接必须'),
		array('language', 'require', '语言必须'),
		array('intro', 'require', '简介必须'),
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
}
?>
