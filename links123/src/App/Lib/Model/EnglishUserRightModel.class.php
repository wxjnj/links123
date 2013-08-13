<?php

/**
 * 用户正确题目记录表
 * @author Adam 2013.6.5 
 */
class EnglishUserRightModel extends CommonModel {

    /**
     * 增加用户做题正确记录数
     * @param array $question_info [所做题目信息数组]
     * @param int $select_option [用户做题选项]
     * @return array [数组中，next_level为下一级别数组，false不升级；status为操作结果状态，false失败true成功;right_num增加后的对题数] 
     * @throws
     * @author Adam $date:2013.6.5$
     */
    public function updateRightRecord($question_info, $select_option) {
        $return = array();
        $return['next_level'] = false;
        $return['status'] = true;
        $return['right_num'] = 0;
        //更改用户做对题目的数量
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $user_right_list = unserialize(cookie('english_user_right_list')); //所有答题正确数组
            $key = $question_info['voice'] . "-" . $question_info['target'] . "-" . $question_info['pattern'] . "-" . $question_info['object'] . "-" . $question_info['level']; //根据条件为键值
            //获取当前存储的值，防止为空
            $user_right_list[$key]['right_num'] = intval($user_right_list[$key]['right_num']); //当前条件答题正确数
            $user_right_list[$key]['continue_error_num'] = intval($user_right_list[$key]['continue_error_num']); //当前条件连错数
            $user_right_list[$key]['rice'] = intval($user_right_list[$key]['rice']); //当前条件大米数
            //答题正确
            if ($question_info['answer'] == $select_option) {
                $user_right_list[$key]['right_num']++; //增加答题正确数
                $user_right_list[$key]['rice']+=100; //增加本次答题大米数
                $user_right_list[$key]['continue_error_num'] = 0; //正确答题，连错数归零
                //判断是否答对10题，是则升级
                if ($user_right_list[$key]['right_num'] >= 10) {
                    $return['next_level'] = true;
                    $user_right_list[$key]['right_num'] = 10;
                }
            } else {
                //答题错误
                $user_right_list[$key]['continue_error_num']++; //增加错误数
                //上一次错误，连错扣一级
                if ($user_right_list[$key]['continue_error_num'] == 2) {
                    $user_right_list[$key]['right_num']--;
                    $user_right_list[$key]['rice']-=100;
                    $user_right_list[$key]['continue_error_num'] = 0; //连错两次后自动清零
                }
            }
            //排除负数
            if ($user_right_list[$key]['right_num'] < 0) {
                $user_right_list[$key]['right_num'] = 0;
            }
            //排除负数
            if ($user_right_list[$key]['rice'] < 0) {
                $user_right_list[$key]['rice'] = 0;
            }
            cookie("english_user_right_list", serialize($user_right_list));
            $return['right_num'] = $user_right_list[$key]['right_num'];
        } else {
            //用户是否曾经做过此题
            $englishReocrdModel = D("EnglishRecord");
            $record = $englishReocrdModel->where("`user_id`=" . $_SESSION[C('MEMBER_AUTH_KEY')] . " AND `question_id`={$question_info['id']}")->find();
            $map = array();
            $map['user_id'] = $_SESSION[C('MEMBER_AUTH_KEY')];
            $map['voice'] = $question_info['voice'];
            $map['target'] = $question_info['target'];
            $map['pattern'] = $question_info['pattern'];
            $map['object'] = $question_info['object'];
            $map['level'] = $question_info['level'];
            $ret = $this->where($map)->find();
            if (!empty($ret)) {
                if ($question_info['answer'] == $select_option) {
                    //未做过此题
                    if (empty($record)) {
                        $ret['right_num']++; //增加正确题目数量
                        $ret['rice']+=100; //增加大米数
                        $ret['continue_error_num'] = 0; //连错归零
                        if ($ret['right_num'] >= 10) {
                            $return['next_level'] = true;
                            $ret['right_num'] = 10;
                        }
                    }
                } else {
                    //未做过此题
                    if (empty($record)) {
                        $ret['continue_error_num']++;
                        if ($ret['continue_error_num'] == 2) {
                            $ret['right_num']--;
                            $ret['rice']-=100;
                            $ret['continue_error_num'] = 0; //连错两次归零
                        }
                    }
                }
                if ($ret['right_num'] < 0) {
                    $ret['right_num'] = 0;
                }
                if ($ret['rice'] < 0) {
                    $ret['rice'] = 0;
                }
                if (false === $this->save($ret)) {
                    Log::write('增加用户正确题目数失败：' . $this->getLastSql(), Log::SQL);
                    $return['status'] = false;
                } else {
                    $return['right_num'] = $ret['right_num'];
                }
            } else {
                if ($question_info['answer'] == $select_option) {
                    $map['right_num'] = 1;
                    $map['rice'] = 100;
                    $return['right_num'] = 1;
                    $map['continue_error_num'] = 0; //连错为0
                } else {
                    $map['right_num'] = 0;
                    $return['right_num'] = 0;
                    $map['continue_error_num'] = 1; //连错为1
                }
                if (false === $this->add($map)) {
                    Log::write('更新用户正确题目数失败：' . $this->getLastSql(), Log::SQL);
                    $return['status'] = false;
                }
            }
        }
        if ($return['next_level']) {
            D("EnglishUserInfo")->updateEnglishUserBest(); //更新用户最佳等级
        }
        return $return;
    }

    /**
     * 获取当前用户做对题目的数量
     * @author Adam 2013.6.5
     * @param int $voice [口语]
     * @param int $target [训练目标]
     * @param int $pattern [试题形式]
     * @param int $object [科目]
     * @param int $level [等级]
     * @return int 
     */
    public function getNowUserRightNum($voice, $target, $pattern, $object, $level) {
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $user_righr_list = unserialize(cookie('english_user_right_list'));
            $key = $voice . "-" . $target . "-" . $pattern . "-" . $object . "-" . $level; //根据条件为键值
            return intval($user_righr_list[$key]["right_num"]);
        } else {
            $map = array();
            $map['voice'] = $voice;
            $map['target'] = $target;
            $map['pattern'] = $pattern;
            $map['object'] = $object;
            $map['level'] = $level;
            $map['user_id'] = $_SESSION[C('MEMBER_AUTH_KEY')];
            $ret = $this->where($map)->getField("right_num");
            return intval($ret);
        }
    }


}

?>
