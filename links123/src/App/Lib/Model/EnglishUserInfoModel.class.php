<?php

/**
 * 英语角用户相关信息模型类
 * @author Adam 2013.6.7 
 */
class EnglishUserInfoModel extends CommonModel {

    /**
     * 做题后更新用户信息
     * @author Adam 2013.6.7
     * @param array $question_info [题目信息数组]
     * @param int $select_option [用户选择]
     * @return max [更新后的用户信息]
     */
    public function updateEnglishUserInfo($question_info, $select_option) {
        $english_user_info = array();
        $data = array();
        //初始化返回数组
        $return = array();
        $level_up = false; //是否升级
        $is_tested = D("EnglishRecord")->isUserTestQuestion($question_info['id']);
        $english_user_info = $this->getEnglishUserInfo($question_info['voice'], $question_info['target'], $question_info['object'], $question_info['level']); //获取当前英语角用户信息
//        dump($english_user_info);
//        exit;
        /* 初始化获取到的数据，防止数据为空 */
        //未登录游客的数据获取
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $this_key = $question_info['voice'] . "-" . $question_info['target'] . "-" . $question_info['object'] . "-" . $question_info['level']; //根据条件为键值
            $data["continue_error_num"] = intval($english_user_info['right_list'][$this_key]['continue_error_num']);
            $data["continue_right_num"] = intval($english_user_info['right_list'][$this_key]['continue_right_num']);
            $data["right_num"] = intval($english_user_info['right_list'][$this_key]['right_num']);
            $data["rice"] = intval($english_user_info['right_list'][$this_key]['rice']);
        } else {
            $data["continue_error_num"] = intval($english_user_info['continue_error_num']);
            $data["continue_right_num"] = intval($english_user_info['continue_right_num']);
            $data["right_num"] = intval($english_user_info['right_num']);
            $data["rice"] = intval($english_user_info['rice']);
        }
        $data['total_rice'] = intval($english_user_info['total_rice']); //获取当前总大米数
        $data['test_num'] = intval($english_user_info['test_num']) + 1; //做题数量增加
        $data['correct_num'] = intval($english_user_info['correct_num']); //答题正确数
        $data['error_num'] = intval($english_user_info['error_num']); //答题错误数
        //答题正确
        if ($question_info['answer'] == $select_option) {
            $data['correct_num']++; //增加答题正确数量
            $data['continue_error_num'] = 0; //连续错误数量置零
            $data['continue_right_num']++; //连续正确数增加
            $data['total_rice'] = $data['total_rice'] + 100; //更新总大米数
            if (!$is_tested) {
                $data['right_num']++; //当前条件下答题题目数增加
                $data['rice'] = $data['rice'] + 100; //更新当前条件下的大米数
                //判断是否答对10题，是则升级
                if ($data['right_num'] >= 10) {
                    $level_up = true;
                    $data['right_num'] = 10;
                }
            }
        } else {
            $data['error_num']++; //增加答题错误数
            $data['continue_error_num']++; //连续错误数增加
            $data['continue_right_num'] = 0; //连续正确数置零
            //连错两题
            if ($data['continue_error_num'] == 2) {
                $data['total_rice'] = $data['total_rice'] - 100; //扣除100大米
                $data['rice'] = $data['rice'] - 100;
                $data['right_num']--; //等级做对题目扣1
                $data['continue_error_num'] = 0; //连错两题，连错归零
            }
        }
        $data['best_object'] = intval($english_user_info['best_object']); //最佳科目
        $data['best_level'] = intval($english_user_info['best_level']); //最佳等级
        if ($data['total_rice'] < 0) {
            $data['total_rice'] = 0;
        }
        if ($data['rice'] < 0) {
            $data['rice'] = 0;
        }
        if ($data['right_num'] < 0) {
            $data['right_num'] = 0;
        }
        $return = $data;
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $data['right_list'][$this_key]['continue_error_num'] = $data["continue_error_num"];
            $data['right_list'][$this_key]['continue_right_num'] = $data["continue_right_num"];
            $data['right_list'][$this_key]['continue_error_num'] = $data["continue_error_num"];
            $data['right_list'][$this_key]['right_num'] = $data["right_num"];
            $data['right_list'][$this_key]['rice'] = $data["rice"];
            unset($data["continue_error_num"]);
            unset($data["continue_right_num"]);
            unset($data["continue_error_num"]);
            unset($data["right_num"]);
            unset($data["rice"]);
            cookie("english_user_info", serialize($data));
        } else {
            $data['update'] = time();
            $map = array();
            $map['user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
            $map['voice'] = $question_info['voice'];
            $map['target'] = $question_info['target'];
            $map['object'] = $question_info['object'];
            $map['level'] = $question_info['level'];
            if (false === $this->where($map)->save($data)) {
                Log::write("更新英语角用户大米数失败，SQL:" . $this->getLastSql(), Log::SQL);
            }
        }
        $return['level_up'] = $level_up;
        //升级了，更新用户最佳科目以及等级
        if ($level_up) {
            $best = $this->updateEnglishUserBest();
            $return['best_object'] = $best['object_id'];
            $return['best_level'] = $best['level_id'];
            $return['best_object_name'] = $best['object_name'];
            $return['best_level_name'] = $best['level_name'];
        }
        return $return;
    }

    /**
     * 获取用户英语角信息
     * @param type $voice
     * @param type $target
     * @param type $object
     * @param type $level
     * @return type 
     */
    public function getEnglishUserInfo() {
        //游客从cookie中获取
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || intval($_SESSION[C('MEMBER_AUTH_KEY')]) <= 0) {
            $english_user_info = unserialize(cookie("english_user_info"));
        } else {
            $map['user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
            $english_user_info = $this->where($map)->find(); //获取最新的用户信息
        }
        //初始化信息，防止记录不存在
        $english_user_info['total_rice'] = intval($english_user_info['total_rice']);
        $english_user_info['test_num'] = intval($english_user_info['test_num']);
        $english_user_info['error_num'] = intval($english_user_info['error_num']);
        $english_user_info['correct_num'] = intval($english_user_info['correct_num']);

        //最佳科目的信息
        $best_object_info = D("EnglishObject")->where("id=" . intval($english_user_info['best_object']))->find();
        if (!empty($best_object_info)) {
            $english_user_info['best_object_name'] = $best_object_info['name'];
        } else {
            //用户无最佳科目，则获取最低科目
            $low_object = D("EnglishObject")->where("status=1")->order("sort asc")->find();
            $english_user_info['best_object_name'] = $low_object['name'];
            $english_user_info['best_object'] = $low_object['id'];
        }
        //最佳等级信息
        $best_level_info = D("EnglishLevel")->where("id=" . intval($english_user_info['best_level']))->find();
        if (!empty($best_level_info)) {
            $english_user_info['best_level_name'] = $best_level_info['name'];
        } else {
            //用户无最佳等级，则获取最低等级
            $low_level = D("EnglishLevel")->where("status=1")->order("sort asc")->find();
            $english_user_info['best_level_name'] = $low_level['name'];
            $english_user_info['best_level'] = $low_level['id'];
        }

        return $english_user_info;
    }

    /**
     * 保存用户英语角信息
     * @param array $english_user_info [用户信息数组]
     * @return void
     * @throws
     * @author Adam $date2013.6.27$
     */
    public function saveEnglishUserInfo($english_user_info) {
        //游客保存到cookie中
        if (intval($_SESSION[C('MEMBER_AUTH_KEY')]) <= 0) {
            cookie("english_user_info", serialize($english_user_info));
        } else {
            $english_user_info['user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
            $english_user_info['updated'] = time();
            $ret = $this->where("user_id={$english_user_info['user_id']}")->save($english_user_info);
            if (false !== $ret && $ret < 1) {
                $this->add($english_user_info);
            }
        }
    }

    /**
     * 获取英语角用户最佳记录，最佳科目以及级别
     * @author Adam 2013.6.8 
     */
    public function getEnglishUserBest() {
        $englishObjectModel = D("EnglishObject");
        $englishLevelModel = D("EnglishLevel");
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
//            $english_user_info = unserialize(cookie("english_user_info"));
            $english_user_right_list = unserialize(cookie("user_count_info_list")); //获取用户做题正确数记录
            //科目列表，获取科目的排序，为优先获取靠前的科目准备
            $object_list = D("EnglishObject")->where("status=1")->select();
            foreach ($object_list as $value) {
                $object_sort[$value['id']] = $value['sort'];
            }
            //等级列表，获取等级的排序，为优先获取靠后的等级准备
            $level_list = D("EnglishLevel")->where("status=1")->select();
            foreach ($level_list as $value) {
                $level_sort[$value['id']] = $value['sort'];
            }
            //最高等级排序数，最高科目排序数
            $max_level_sort = 0;
            $max_object_sort = 0;
            $best_level = array(); //最佳数组
            foreach ($english_user_right_list as $key => $value) {
                //循环获取包含right_num的正确数量，以及正确数量等于10
                if ($value['right_num'] == 10) {
                    $condition = explode("-", $key);
                    $ret[$key]['level_id'] = $condition[3]; //从键值中获取等级和科目id
                    $ret[$key]['object_id'] = $condition[2];
                    //等级更高，设置最高等级和科目为当前
                    if ($level_sort[$ret[$key]['level_id']] > $max_level_sort) {
                        $max_level_sort = $level_sort[$ret[$key]['level_id']]; //更新最高等级的排序号，为下次比较做准备
                        $best_level = $ret[$key];
                    } else if ($level_sort[$ret[$key]['level_id']] == $max_level_sort) {//等于时，比较科目
                        //科目更靠前则获取当前科目为最佳
                        if ($object_sort[$ret[$key]['object_id']] < $max_object_sort) {
                            $max_object_sort = $object_sort[$ret[$key]['object_id']];
                            $best_level = $ret[$key];
                        }
                    }
                }
            }
            //未取到最佳，获取最低等级和科目
            if (empty($best_level)) {
                $low_object = $englishObjectModel->where("status=1")->order("sort asc")->find();
                $best_level['object_id'] = $low_object['id'];
                $low_level = $englishLevelModel->where("status=1")->order("sort asc")->find();
                $best_level['level_id'] = $low_level['id'];
            }
//            $english_user_info['best_object'] = $best_level['object_id'];
//            $english_user_info['best_level'] = $best_level['level_id'];
//            cookie("english_user_info", serialize($english_user_info));
        } else {
            $best_level = D("EnglishUserCount")->alias("a")
                    ->field("c.id as object_id,b.id as level_id")
                    ->where("a.right_num=10")
                    ->join(C("DB_PREFIX") . "english_level b on b.id=a.level")
                    ->join(C("DB_PREFIX") . "english_object c on c.id=a.object")
                    ->order("b.sort desc,c.sort asc")
                    ->find();
            //未取到最佳，获取最低等级和科目
            if (false === $best_level || empty($best_level)) {
                $low_object = $englishObjectModel->where("status=1")->order("sort asc")->find();
                $best_level['object_id'] = $low_object['id'];
                $low_level = $englishLevelModel->where("status=1")->order("sort asc")->find();
                $best_level['level_id'] = $low_level['id'];
            }
//            $user_id = $_SESSION[C('MEMBER_AUTH_KEY')];
//            $ret = $this->where("`user_id`={$user_id}")->save(array('best_object' => $best_level['object_id'], 'best_level' => $best_level['level_id']));
//            if (false === $ret) {
//                Log::write("更新用户英语角最佳等级失败，SEL:" . $this->getLastSql(), Log::SQL);
//            }
        }
        $best_level['object_name'] = $englishObjectModel->where("id={$best_level['object_id']}")->getField("name");
        $best_level['level_name'] = $englishLevelModel->where("id={$best_level['level_id']}")->getField("name");
        return $best_level;
    }

    /**
     * 根据排行榜请求的类型获取排行榜用户列表
     * @param type $type [排行类型，object_1代表科目ID为1，total_rice为全部大米数排行，continue_right_num为连对数排行]
     * @param type $limit [排行的个数，默认为3]
     * @return max [返回获取到的用户列表，如果有分类0代表第一个分类，1代表第二个分类。] 
     * @author Adam $date2013.6.20$
     */
    public function getTopUserListByTypeName($type, $limit = 3) {
        $ret = array();
        //全部大米数
        if ($type == "total_rice") {
            $ret[0] = $this->alias("english_user_info")
                    ->field("(select b.total_rice from " . C("DB_PREFIX") . "english_user_info b where b.user_id=english_user_info.user_id order by b.updated desc limit 1) as rice_sum,user.nickname as nickname,level.name as best_level_name")
                    ->join(C("DB_PREFIX") . "english_level level on english_user_info.best_level=level.id")
                    ->join(C("DB_PREFIX") . "member user on english_user_info.user_id=user.id")
                    ->where("user.status=1")
                    ->group("english_user_info.user_id")
                    ->order("total_rice DESC")
                    ->limit($limit)
                    ->select();
        } else if ($type == "continue_right_num") {
            
        } else if (preg_match("/object/", $type)) {
            $object_name = substr($type, 7);
            $object_id = D("EnglishObject")->where("`name`='{$object_name}'")->getField("id");
            $englishUserCountModel = D("EnglishUserCount");
            $ret[0] = $englishUserCountModel->getTopRiceUserList(3, 0, 1, 0, $object_id, 0);
            $ret[1] = $englishUserCountModel->getTopRiceUserList(3, 0, 2, 0, $object_id, 0);
        } else {
            $englishUserCountModel = D("EnglishUserCount");
            $ret[0] = $englishUserCountModel->getTopRiceUserList(3, 1);
            $ret[1] = $englishUserCountModel->getTopRiceUserList(3, 2);
        }
        $low_level_name = D("EnglishLevel")->where("status=1")->order("sort asc")->getField("name");
        if (!empty($ret[0])) {
            foreach ($ret[0] as $key => $value) {
                if (empty($value['best_level_name'])) {
                    //用户无最佳等级，则获取最低等级
                    $ret[0][$key]['best_level_name'] = $low_level_name;
                }
                if ($value['rice_sum'] <= 0) {
                    unset($ret[0][$key]);
//                    $ret[0][$key]['rice_sum'] = 0;
                }
            }
        }
        if (!empty($ret[1])) {
            foreach ($ret[1] as $key => $value) {
                if (empty($value['best_level_name'])) {
                    //用户无最佳等级，则获取最低等级
                    $ret[1][$key]['best_level_name'] = $low_level_name;
                }
                if ($value['rice_sum'] < 0) {
                    unset($ret[1][$key]);
//                    $ret[1][$key]['rice_sum'] = 0;
                }
            }
        }
        return $ret;
    }

}

?>
