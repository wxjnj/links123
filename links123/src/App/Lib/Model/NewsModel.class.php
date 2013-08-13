<?php
// 新闻模型
class NewsModel extends CommonModel {
	public $_validate =	array(
		array('title', 'require', '新闻标题必须'),
		array('author', 'require', '作者必须'),
		array('come_from', 'require', '来源必须'),
		array('content', 'require', '内容必须'),
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
}
?>
