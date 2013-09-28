<?php


/**
 * Description of EnglishQuestionSpeakViewModel
 *
 * @author adam 2013.09.26
 */
class EnglishQuestionSpeakViewModel extends ViewModel {
    public $viewFields = array(
        'englishQuestionSpeak' => array('id', 'media_id', 'name',  'created', 'updated', 'answer_num', 'status','_type' => 'LEFT'),
        'englishMedia' => array('englishMedia.pattern' => 'pattern', 'englishMedia.media_source_url' => 'media_source_url', 'englishMedia.created' => 'media_created', 'englishMedia.difficulty' => 'difficulty', 'englishMedia.recommend' => 'recommend', 'englishMedia.special_recommend' => 'special_recommend','englishMedia.ted' => 'ted', 'englishMedia.voice' => 'voice','englishMedia.caption' => 'caption', '_on' => 'englishQuestionSpeak.media_id=englishMedia.id', '_type' => 'LEFT'),
        'englishObject' => array('name' => "object_name", '_on' => 'englishMedia.object=englishObject.id', '_type' => 'LEFT'),
        'englishLevel' => array('name' => "level_name", '_on' => 'englishMedia.level=englishLevel.id', '_type' => 'LEFT'),
        'englishMediaSubject' => array('name' => "subject_name", '_on' => 'englishMedia.subject=englishMediaSubject.id', '_type' => 'LEFT')
    );
}

?>
