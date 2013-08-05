<?php

/**
 * 英语角说力新版测试Action类
 *
 * @author Adam $date2013-07-21$
 */
class NewIndexAction extends EnglishAction {

    public function index() {
        $question = D("EnglishQuestion")->getQuestionToIndex(1, 1, 1, 1, 1);
        $this->assign("question", $question);
        $this->display();
    }

}

?>
