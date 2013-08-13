<?php

/**
 * 英语角用户信息统计模型
 * @author Adam 2013.6.5 
 */
class EnglishUserCountModel extends CommonModel {

    /**
     * 获取用户英语角统计信息
     * @param int $voice [美音/英音：1/2]
     * @param int $target [听力/说力：1/2]
     * @param int $object [科目id]
     * @param int $level [等级id]
     * @return max [用户统计信息]
     * @throws
     * @author Adam $date2013.6.27$ 
     */
    public function getEnglishUserCountInfo($voice, $target, $object, $level) {
        $user_count_info = array();
        //游客
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || intval($_SESSION[C('MEMBER_AUTH_KEY')]) <= 0) {
            $key = $voice . "-" . $target . "-" . $object . "-" . $level;
            $user_count_info_list = unserialize(cookie("user_count_info_list"));
            $user_count_info = $user_count_info_list[$key];
        } else {
            $map['user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
            if ($voice > 0) {
                $map['voice'] = $voice;
            }
            if ($target > 0) {
                $map['target'] = $target;
            }
            if ($object > 0) {
                $map['object'] = $object;
            }
            if ($level > 0) {
                $map['level'] = $level;
            }
            $user_count_info = $this->where($map)->find();
        }

        //初始化信息，防止记录不存在
        $user_count_info['right_num'] = intval($user_count_info['right_num']);
        $user_count_info['continue_right_num'] = intval($user_count_info['continue_right_num']);
        $user_count_info['continue_error_num'] = intval($user_count_info['continue_error_num']);
        $user_count_info['rice'] = intval($user_count_info['rice']);
        $user_count_info['voice'] = intval($user_count_info['voice']) > 0 ? intval($user_count_info['voice']) : $voice;
        $user_count_info['target'] = intval($user_count_info['target']) > 0 ? intval($user_count_info['target']) : $target;
        $user_count_info['object'] = intval($user_count_info['object']) > 0 ? intval($user_count_info['object']) : $object;
        $user_count_info['level'] = intval($user_count_info['level']) > 0 ? intval($user_count_info['level']) : $level;

        return $user_count_info;
    }

    /**
     * 保存用户英语角统计信息
     * @param array $user_count_info [用户统计信息数组]
     * @return void
     * @throws
     * @author Adam $date2013.6.27$
     */
    public function saveEnglishUserCountInfo($user_count_info) {
        if (intval($_SESSION[C('MEMBER_AUTH_KEY')]) <= 0) {
            $user_count_info_list = unserialize(cookie("user_count_info_list"));
            $key = $user_count_info['voice'] . "-" . $user_count_info['target'] . "-" . $user_count_info['object'] . "-" . $user_count_info['level'];
            $user_count_info_list[$key]['right_num'] = intval($user_count_info['right_num']);
            $user_count_info_list[$key]['continue_right_num'] = intval($user_count_info['continue_right_num']);
            $user_count_info_list[$key]['continue_error_num'] = intval($user_count_info['continue_error_num']);
            $user_count_info_list[$key]['rice'] = intval($user_count_info['rice']);
            cookie("user_count_info_list", serialize($user_count_info_list));
        } else {
            $user_count_info['updated'] = time();
            $map['user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
            $map['voice'] = $user_count_info['voice'];
            $map['target'] = $user_count_info['target'];
            $map['object'] = $user_count_info['object'];
            $map['level'] = $user_count_info['level'];
            $ret = $this->where($map)->save($user_count_info);
            if (false !== $ret && $ret < 1) {
                $user_count_info['user_id'] = $map['user_id'];
                $this->add($user_count_info);
            }
        }
    }

    /**
     * 根据分类获取用户大米数排行榜
     * @author Adam 2013.6.13
     * @param int $limit [显示排行数量]
     * @param int $voice [排行条件语音]
     * @param int $target [排行条件目标]
     * @param int $pattern [排行条件形式]
     * @param int $object [排行条件科目]
     * @param int $level [排行条件等级]
     * @return array 
     */
    public function getTopRiceUserList($limit = 3, $voice, $target, $pattern, $object, $level) {
        $condition = "1=1";
        if (intval($voice) > 0) {
            $condition.=" AND user_count_info.voice={$voice}";
        }
        if (intval($target) > 0) {
            $condition.=" AND user_count_info.target={$target}";
        }
        if (intval($pattern) > 0) {
            $condition.=" AND user_count_info.pattern={$pattern}";
        }
        if (intval($object) > 0) {
            if (D("EnglishObject")->where("id={$object}")->getField("name") != "综合") {
                $condition.=" AND user_count_info.object={$object}";
            }
        }
        if (intval($level) > 0) {
            $condition.=" AND user_count_info.level={$level}";
        }
        $list = $this->alias("user_count_info")
                ->field("SUM(user_count_info.rice) as rice_sum,user.nickname as nickname,level.name as best_level_name")
                ->join(C("DB_PREFIX") . "english_user_info english_user_info on user_count_info.user_id=english_user_info.user_id")
                ->join(C("DB_PREFIX") . "english_level level on english_user_info.best_level=level.id")
                ->join(C("DB_PREFIX") . "member user on user_count_info.user_id=user.id")
                ->where($condition)
                ->group("user_count_info.user_id")
                ->order("rice_sum DESC")
                ->limit($limit)
                ->select();
        return $list;
    }

    /**
     * 重置做题正确数
     * @param int $voice
     * @param int $pattern
     * @param int $object
     * @param int $level 
     * @return void
     * @author Adam $date2013.6.22$
     */
    public function resetRightNum($voice, $target, $object, $level) {
        if (!isset($_SESSION[C('MEMBER_AUTH_KEY')]) || empty($_SESSION[C('MEMBER_AUTH_KEY')])) {
            $user_count_info_list = unserialize(cookie('user_count_info_list'));
            $this_key = $voice . "-" . $target . "-" . $object . "-" . $level; //根据条件为键值
            $user_count_info_list[$this_key]['right_num'] = 0;
            $user_count_info_list[$this_key]['continue_right_num'] = 0;
            $user_count_info_list[$this_key]['continue_error_num'] = 0;
            cookie("user_count_info_list", serialize($user_count_info_list));
        } else {
            $map = array();
            $map['voice'] = $voice;
            $map['target'] = $target;
            $map['object'] = $object;
            $map['level'] = $level;
            $map['user_id'] = intval($_SESSION[C('MEMBER_AUTH_KEY')]);
            $data['right_num'] = 0;
            $data['rice'] = 0;
            $data['continue_right_num'] = 0;
            $data['continue_error_num'] = 0;
            $data['updated'] = time();
            if (false === $this->where($map)->save($data)) {
                Log::write('重置英语角等级失败：' . $this->getLastSql(), Log::SQL);
            }
        }
        D("EnglishRecord")->clearUserRecord($object, $level, $voice, $target); //重置用户记录
    }

}

?>
