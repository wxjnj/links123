<?php

/**
 * 英语角说力跟读句子表模型类
 *
 * @author Adam $date2013-08-09$
 */
class EnglishQuestionSpeakSentenceModel extends CommonModel {

    /**
     * 获取说力试题跟读句子列表
     * @param int $question_id
     * @return array
     */
    public function getSpeakQuestionSentenceList($question_id) {
        $map['question_id'] = $question_id;
        $ret = $this->where($map)->order("`start_time` ASC")->select();
        if (false === $ret) {
            $ret = array();
        }
        return $ret;
    }

}

?>
