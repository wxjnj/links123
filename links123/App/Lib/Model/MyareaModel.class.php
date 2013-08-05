<?php
// 我的地盘模型
class MyareaModel extends CommonModel {
	public $_validate =	array(
		array('web_name', 'require', '网站名称必须'),
		array('url', 'require', '链接必须'),
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
}
?>
