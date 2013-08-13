<?php
// 收藏视图模型
class CollectionViewModel extends ViewModel {

	public $viewFields = array(
	
		'collection' => array('mid', 'lnk_id', 'create_time', '_type'=>'LEFT'),

		'links' => array('title', 'category', 'link', 'collect_num', '_on'=>'collection.lnk_id=links.id'),

	);
}

?>

