<?php
// 直达网址模型
class DirectLinksModel extends CommonModel {
	public $_validate =	array(
		array('tag', 'require', '标签必须'),
		array('url', 'require', '链接地址必须'),
	);
	
	public $_auto =	array(
		array('update_time', 'time', self::MODEL_BOTH, 'function'),
	);
}
?>
