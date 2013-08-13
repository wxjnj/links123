<?php
// 目录图片视图模型
class CatPicViewModel extends ViewModel {

	public $viewFields = array(
	
		'catPic' => array('id', 'name', 'rid', 'sort', 'create_time', '_type'=>'LEFT'),

		'category' => array('cat_name', '_on'=>'catPic.rid=category.id'),

	);
}

?>

