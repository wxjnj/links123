<?php

// 链接视图模型
class EnglishQuestionViewModel extends ViewModel {

    public $viewFields = array(
        'englishQuestion' => array('id', 'target','media_id', 'media_url', 'media_text_url', 'name', 'answer', 'created', 'updated', 'answer_num', 'status', 'content', '_type' => 'LEFT'),
        'englishMedia' => array('englishMedia.media_source_url' => 'media_source_url','englishMedia.special_recommend' => 'special_recommend', '_on' => 'englishQuestion.media_id=englishMedia.id', '_type' => 'LEFT'),
        'englishCatquestion' => array("cat_id", '_on' => 'englishQuestion.id=englishCatquestion.question_id AND englishCatquestion.type=1', '_type' => 'LEFT'),
        'englishCategory' => array("cat_attr_id", '_on' => 'englishCatquestion.cat_id=englishCategory.cat_id', '_type' => 'LEFT'),
        /*'englishObject' => array('name' => "object_name", '_on' => 'englishMedia.object=englishObject.id', '_type' => 'LEFT'),
        //'englishLevel' => array('name' => "level_name", '_on' => 'englishMedia.level=englishLevel.id', '_type' => 'LEFT'),
        //'englishMediaSubject' => array('name' => "subject_name", '_on' => 'englishMedia.subject=englishMediaSubject.id', '_type' => 'LEFT')
        'englishCatquestion' => array('cat_id' => "question_cat_id", '_on' => 'englishQuestion.id=englishCatquestion.question_id AND englishCatquestion.type=0', '_type' => 'LEFT'),
        'englishCategory' => array('_on' => 'englishCategory.cat_id=englishCatquestion.cat_id', '_type' => 'LEFT'),
        'englishLevelOne' => array('_table'=>'lnk_english_levelname','_as'=>'englishLevelOne','name'=>'level_one_name', '_on' => 'englishLevelOne.id=englishCategory.level_one', '_type' => 'LEFT'),
        'englishLevelTwo' => array('_table'=>'lnk_english_levelname','_as'=>'englishLevelTwo','name'=>'level_two_name', '_on' => 'englishLevelTwo.id=englishCategory.level_two', '_type' => 'LEFT'),
        'englishLevelThr' => array('_table'=>'lnk_english_levelname','_as'=>'englishLevelThr','name'=>'level_thr_name', '_on' => 'englishLevelThr.id=englishCategory.level_thr', '_type' => 'LEFT')*/
    );

}

?>
