<?php
//产品视图模型
class ProductViewModel extends ViewModel {

	public $viewFields = array(
	
		'product' => array('id', 'pdt_name', 'category', 'pic', 'home_show', 'create_time', 'sort', '_type'=>'inner'),

		'category' => array('cat_name', 'prt_id', '_on'=>'product.category=category.id'),

	);
}

?>
