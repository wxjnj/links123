<?php
// 说说视图模型
class CommentViewModel extends ViewModel {

	public $viewFields = array(
	
		'comment' => array('id', 'lnk_id', 'mid', 'comment', 'create_time', '_type'=>'LEFT'),

		'member' => array('nickname', 'face', '_on'=>'comment.mid=member.id', '_type'=>'LEFT'),
			
		'links' => array('title', 'category', 'link', '_on'=>'comment.lnk_id=links.id'),

	);
}

?>

