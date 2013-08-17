<?php

class EnglishOptionsModel extends CommonModel {

    protected $_auto = array(
        array("created", "time", 3, "function")
    );

    public function getQuestionOptionList($question_id) {
        $map['question_id'] = $question_id;
        $map['status'] = 1;
        $ret = $this->where($map)->order("`sort`")->select();
        if (false === $ret) {
            return array();
        }
        //判断题目是否需要随机打乱，存在一些规则无法随机
        $is_rand = true;
        $is_double_true = false; //是否为True文字选项
        $is_double_false = false; //是否为False文字选项
        foreach ($ret as $value) {
            //不能随机打乱的判断
            $d_1 = preg_match("/all\sof\sthe\sabove.?/i", $value['content']);
            $d_2 = preg_match("/none\sof\sthe\sabove.?/i", $value['content']);
            $d_3 = preg_match("/either\sB\sor\sC.?/i", $value['content']);
            $d_4 = preg_match("/both\sB\sand\sC.?/i", $value['content']);
            $c_1 = preg_match("/both\sA\sand\sB.?/i", $value['content']);
            $c_2 = preg_match("/either\sA\sor\sB.?/i", $value['content']);
            if (preg_match("/True/i", $value['content'])) {
                $is_double_true = true;
            }
            if (preg_match("/False/i", $value['content'])) {
                $is_double_false = true;
            }
            if ($d_1 || $d_2 || $d_3 || $d_4 || $c_1 || $c_2 || ($is_double_false && $is_double_true)) {
                $is_rand = false;
                break;
            }
        }
        if ($is_rand) {
            shuffle($ret);
        }
        return $ret;
    }

}

?>
