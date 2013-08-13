<?php
//新闻视图模型
class NewsViewModel extends ViewModel {

	public $viewFields = array(
	
		'news' => array('id', 'title', 'category', 'author', 'come_from', 'create_time', 'page_view', '_type'=>'LEFT'),

		'category' => array('cat_name', 'prt_id', '_on'=>'news.category=category.id'),

	);
}

?>

