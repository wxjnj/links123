<?php

/**
 * 英语角媒体表视图模型类
 *
 * @author Adam $date2013.08.27$
 */
class EnglishMediaViewModel extends ViewModel {

    public $viewFields = array(
        'englishMedia' => array('id', 'name', 'voice', 'pattern','recommend','object','subject', 'difficulty', 'play_type', 'storage_type', 'path', 'media_source_url', 'media_thumb_img', 'special_recommend', 'created', 'updated', 'status', '_type' => 'LEFT'),
        'englishObject' => array('name' => "object_name", '_on' => 'englishMedia.object=englishObject.id', '_type' => 'LEFT'),
        'englishLevel' => array('name' => "level_name", '_on' => 'englishMedia.level=englishLevel.id', '_type' => 'LEFT'),
        'englishMediaSubject' => array('name' => "subject_name", '_on' => 'englishMedia.subject=englishMediaSubject.id', '_type' => 'LEFT'),
    );

}

?>
