<?php
// 目录图片模型
class CatPicModel extends CommonModel {
	public $_validate =	array(
		array('name', 'require', '名称必须'),
		array('pic', 'require', '缩略图必须'),
		array('pic_big', 'require', '大图必须'),
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
}
?>
