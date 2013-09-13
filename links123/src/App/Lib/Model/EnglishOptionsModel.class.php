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
        $i = array(1, 2, 3, 4);
        foreach ($ret as $key => $value) {
            //不能随机打乱的判断
            $d_1 = preg_match("/all(\s)+of(\s)+the(\s+)above.?/i", $value['content']);
            $d_2 = preg_match("/none(\s)+of(\s)+the(\s)+above.?/i", $value['content']);
            $d_3 = preg_match("/either(\s)+B(\s)+or(\s)+C.?/i", $value['content']);
            $d_4 = preg_match("/(both(\s)+)?B(\s)+and(\s)+C.?/i", $value['content']);
            $c_1 = preg_match("/(both(\s)+A)?(\s)+and(\s)+B.?/i", $value['content']);
            $c_2 = preg_match("/either(\s)+A(\s)+or(\s)+B.?/i", $value['content']);
            if (preg_match("/True/i", $value['content'])) {
                $is_double_true = true;
            }
            if (preg_match("/False/i", $value['content'])) {
                $is_double_false = true;
            }
            $ret[$key]['sort'] = current($i);
            if ($c_1 || $c_2) {
                $ret[$key]['sort'] = 3;
            } else if ($d_1 || $d_2 || $d_3 || $d_4) {
                $ret[$key]['sort'] = 4;
            }
            unset($i[array_search($ret[$key]['sort'], $i)]);

            if ($d_1 || $d_2 || $d_3 || $d_4 || $c_1 || $c_2 || ($is_double_false && $is_double_true)) {
                $is_rand = false;
            }
            $ret[$key]['content'] = ftrim($value['content']);
        }
        if ($is_rand) {
            shuffle($ret);
        } else {
            $ret = array_sort($ret, "sort","asc");
        }
        
        return $ret;
    }

}

?>
