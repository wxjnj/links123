<?php

// 链接视图模型
class EnglishQuestionViewModel extends ViewModel {
    /*
      public $viewFields = array(
      'englishQuestion' => array('id', 'voice', 'pattern', 'target', 'media_url', 'media_local_url', 'media_text_url', 'name', 'answer', 'created', 'updated', 'answer_num', 'status', 'content', '_type' => 'LEFT'),
      'englishObject' => array('name' => "object_name", '_on' => 'englishQuestion.object=englishObject.id', '_type' => 'LEFT'),
      'englishLevel' => array('name' => "level_name", '_on' => 'englishQuestion.level=englishLevel.id', '_type' => 'LEFT')
      ); */

    public $viewFields = array(
        'englishQuestion' => array('id', 'voice', 'target', 'media_url', 'media_text_url', 'name', 'answer', 'created', 'updated', 'answer_num', 'status', 'content', '_type' => 'LEFT'),
        'englishMedia' => array('englishMedia.pattern'=>'pattern','_on' => 'englishQuestion.media_id=englishMedia.id', '_type' => 'LEFT'),
        'englishObject' => array('name' => "object_name", '_on' => 'englishMedia.object=englishObject.id', '_type' => 'LEFT'),
        'englishLevel' => array('name' => "level_name", '_on' => 'englishMedia.level=englishLevel.id', '_type' => 'LEFT')
    );

}

?>
