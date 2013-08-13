<?php
// 产品模型
class ProductModel extends CommonModel {
	public $_validate =	array(
		array('pdt_name', 'require', '产品名称必须'),
		array('pic', 'require', '产品图片必须'),
		array('content', 'require', '产品说明必须'),
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
}
?>
