<?php
// 资料下载模型
class DownloadModel extends CommonModel {
	public $_validate =	array(
		array('title', 'require', '标题必须'),
		array('fanme', 'require', '资料必须'),
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
}
?>
