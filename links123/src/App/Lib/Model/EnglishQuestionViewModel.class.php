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
        'englishQuestion' => array('id', 'target', 'media_url', 'media_text_url', 'name', 'answer', 'created', 'updated', 'answer_num', 'status', 'content', '_type' => 'LEFT'),
        'englishMedia' => array('englishMedia.pattern' => 'pattern', 'englishMedia.media_source_url' => 'media_source_url', 'englishMedia.created' => 'media_created', 'englishMedia.difficulty' => 'difficulty', 'englishMedia.recommend' => 'recommend', 'englishMedia.special_recommend' => 'special_recommend', 'englishMedia.voice' => 'voice', '_on' => 'englishQuestion.media_id=englishMedia.id', '_type' => 'LEFT'),
        'englishObject' => array('name' => "object_name", '_on' => 'englishMedia.object=englishObject.id', '_type' => 'LEFT'),
        'englishLevel' => array('name' => "level_name", '_on' => 'englishMedia.level=englishLevel.id', '_type' => 'LEFT'),
        'englishMediaSubject' => array('name' => "subject_name", '_on' => 'englishMedia.subject=englishMediaSubject.id', '_type' => 'LEFT')
    );

}

?>
