<?php

// 链接视图模型(前台)
class LinksFntViewModel extends ViewModel {

    public $viewFields = array(
        'links' => array('id', 'title', 'logo', 'category', 'language', 'link', 'intro', 'grade', 'create_time', 'status', 'say_num', 'collect_num', 'sort', 'uid', 'ding', 'cai', 'mid', 'recommended', '_type' => 'LEFT'),
        'category' => array('cat_name', 'prt_id', 'sort' => 'csort', '_on' => 'links.category=category.id')
    );

}
?>

