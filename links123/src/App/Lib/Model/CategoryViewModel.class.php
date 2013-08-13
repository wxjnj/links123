<?php
// 目录视图模型
class CategoryViewModel extends ViewModel {

	public $viewFields = array(
	
		'cat1' => array('id', 'cat_name', 'prt_id', 'level', 'path', 'sort', 'flag', 'status', 'uid', '_table'=>'lnk_category', '_as'=>'cat1', '_type'=>'left'),

		'cat2' => array('cat_name'=>'prt_name', '_table'=>'lnk_category', '_as'=>'cat2', '_on'=>'cat1.prt_id=cat2.id', '_type'=>'left'),
			
		'user' => array('nickname', '_on'=>'cat1.uid=user.id'),

	);
}

?>
