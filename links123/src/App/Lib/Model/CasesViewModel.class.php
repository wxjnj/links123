<?php
// 案例视图模型
class CasesViewModel extends ViewModel {

	public $viewFields = array(
	
		'cases' => array('id', 'case_name', 'category', 'pic', 'home_show', 'create_time', 'sort', '_type'=>'inner'),

		'category' => array('cat_name', 'prt_id', '_on'=>'cases.category=category.id'),

	);
}

?>
