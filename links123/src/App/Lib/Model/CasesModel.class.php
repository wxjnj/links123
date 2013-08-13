<?php
// 案例模型
class CasesModel extends CommonModel {
	public $_validate =	array(
		array('case_name', 'require', '案例名称必须'),
		array('pic', 'require', '案例图片必须'),
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
}
?>
