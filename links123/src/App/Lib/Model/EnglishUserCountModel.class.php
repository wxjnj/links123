<?php

/**
 * 英语角用户信息统计模型
 * @author Adam 2013.6.5 
 */
class EnglishUserCountModel extends CommonModel {

    /**
     * 获取用户英语角统计信息
     * @param int $view_type [查看方式，1科目等级，2专题难度，3推荐难度,4特别推荐]
     * @param int $voice [美音/英音：1/2]
     * @param int $target [听力/说力：1/2]
     * @param int $object [科目id]
     * @param int $level [等级id]
     * @return max [用户统计信息]
     * @throws
     * @author Adam $date2013.8.18$ 
     */
    public function getEnglishUserCountInfo($view_type = 1, $object, $level, $subject, $recommend, $difficulty, $voice = 1, $target = 1) {
        $user_count_info = array();
        $map['view_type'] = $view_type;
        if (intval($voice) > 0) {
            $map['voice'] = $voice;
        }
        if (intval($target) > 0) {
            $map['target'] = $target;
        }
        if ($view_type == 1) {
            if (intval($level) > 0) {
                $map['level'] = $level;
            }
            if (intval($object) > 0) {
                $map['object'] = $object;
            }
        } else if ($view_type == 2) {
            if (intval($subject) > 0) {
                $map['subject'] = $subject;
            }
            if (intval($difficulty) > 0) {
                $map['difficulty'] = $difficulty;
            }
        } else if ($view_type == 3) {
            if (intval($recommend) > 0) {
                $map['recommend'] = $recommend;
            }
            if (intval($difficulty) > 0) {
                $map['difficulty'] = $difficulty;
            }
        } else if ($view_type == 4) {
            $map['recommend'] = 0;
            $map['difficulty'] = 0;
        }
        //
        //游客对应游客信息统计表english_tourist_count
        if (!$this->userService->isLogin()) {
            $map['user_id'] = $this->userService->getGuestId();//intval(cookie('english_tourist_id')); //从cookie获取游客id
            if ($map['user_id'] > 0) {
                $user_count_info = D("EnglishTouristCount")->where($map)->find();
            }
        } else {
            $map['user_id'] = $this->userService->getUserId();
            if ($map['user_id'] > 0) {
                $user_count_info = $this->where($map)->find();
            }
        }
        //
        //初始化信息，防止记录不存在
        $user_count_info['right_num'] = intval($user_count_info['right_num']);
        $user_count_info['continue_right_num'] = intval($user_count_info['continue_right_num']);
        $user_count_info['continue_error_num'] = intval($user_count_info['continue_error_num']);
        $user_count_info['rice'] = intval($user_count_info['rice']);
        $user_count_info['view_type'] = in_array(intval($user_count_info['view_type']), array(1, 2, 3, 4)) ? intval($user_count_info['view_type']) : $view_type;
        $user_count_info['voice'] = intval($user_count_info['voice']) > 0 ? intval($user_count_info['voice']) : $voice;
        $user_count_info['target'] = intval($user_count_info['target']) > 0 ? intval($user_count_info['target']) : $target;
        $user_count_info['object'] = intval($user_count_info['object']) > 0 ? intval($user_count_info['object']) : $object;
        $user_count_info['level'] = intval($user_count_info['level']) > 0 ? intval($user_count_info['level']) : $level;
        $user_count_info['subject'] = intval($user_count_info['subject']) > 0 ? intval($user_count_info['subject']) : $subject;
        $user_count_info['recommend'] = intval($user_count_info['recommend']) > 0 ? intval($user_count_info['recommend']) : $recommend;
        $user_count_info['difficulty'] = intval($user_count_info['difficulty']) > 0 ? intval($user_count_info['difficulty']) : $difficulty;

        return $user_count_info;
    }

    /**
     * 获取用户英语角统计信息
     * @param int $view_type [查看方式，1科目等级，2专题难度，3推荐难度,4特别推荐]
     * @param array $params
     * @throws
     * @author Adam $date2013.09.17$ 
     */
    public function getEnglishUserCount($view_type = 1, $params) {
        $user_count_info = array();
        $map['view_type'] = $view_type;
        if (intval($params['voice']) > 0) {
            $map['voice'] = $params['voice'];
        }
        if (intval($params['target']) > 0) {
            $map['target'] = $params['target'];
        }
        if ($view_type == 1) {
            if (intval($params['object']) > 0) {
                $map['object'] = $params['object'];
            }
            if (intval($params['level']) > 0) {
                $map['level'] = $params['level'];
            }
        } else if ($view_type == 2) {
            if (intval($params['subject']) > 0) {
                $map['subject'] = $params['subject'];
            }
            if (intval($params['difficulty']) > 0) {
                $map['difficulty'] = $params['difficulty'];
            }
        } else if ($view_type == 3) {
            if (intval($params['recommend']) > 0) {
                $map['recommend'] = $params['recommend'];
            }
            if (intval($params['difficulty']) > 0) {
                $map['difficulty'] = $params['difficulty'];
            }
        } else if ($view_type == 4) {
            $map['recommend'] = 0;
            $map['difficulty'] = 0;
        } else if ($view_type == 5) {
            if (intval($params['ted']) > 0) {
                $map['ted'] = $params['ted'];
            }
            if (intval($params['difficulty']) > 0) {
                $map['difficulty'] = $params['difficulty'];
            }
        }
        //
        //游客对应游客信息统计表english_tourist_count
        if (!$this->userService->isLogin()) {
            $map['user_id'] = $this->userService->getGuestId();//intval(cookie('english_tourist_id')); //从cookie获取游客id
            if ($map['user_id'] > 0) {
                $user_count_info = D("EnglishTouristCount")->where($map)->find();
            }
        } else {
            $map['user_id'] = $this->userService->getUserId();
            if ($map['user_id'] > 0) {
                $user_count_info = $this->where($map)->find();
            }
        }
        //
        //初始化信息，防止记录不存在
        $user_count_info['right_num'] = intval($user_count_info['right_num']);
        $user_count_info['continue_right_num'] = intval($user_count_info['continue_right_num']);
        $user_count_info['continue_error_num'] = intval($user_count_info['continue_error_num']);
        $user_count_info['rice'] = intval($user_count_info['rice']);
        $user_count_info['view_type'] = intval($user_count_info['view_type']);
        if (!in_array($user_count_info['view_type'], array(1, 2, 3, 4, 5))) {
            $user_count_info['view_type'] = $view_type;
        }
        if ($user_count_info['voice'] == 0) {
            $user_count_info['voice'] = $params['voice'];
        }
        if ($user_count_info['target'] == 0) {
            $user_count_info['target'] = $params['target'];
        }
        if ($view_type == 1) {
            if ($user_count_info['object'] == 0) {
                $user_count_info['object'] = $params['object'];
            }
            if ($user_count_info['level'] == 0) {
                $user_count_info['level'] = $params['level'];
            }
        } else if ($view_type == 2) {
            if ($user_count_info['subject'] == 0) {
                $user_count_info['subject'] = $params['subject'];
            }
            if ($user_count_info['difficulty'] == 0) {
                $user_count_info['difficulty'] = $params['difficulty'];
            }
        } else if ($view_type == 3) {
            if ($user_count_info['recommend'] == 0) {
                $user_count_info['recommend'] = $params['recommend'];
            }
            if ($user_count_info['difficulty'] == 0) {
                $user_count_info['difficulty'] = $params['difficulty'];
            }
        } else if ($view_type == 5) {
            if ($user_count_info['ted'] == 0) {
                $user_count_info['ted'] = $params['ted'];
            }
            if ($user_count_info['difficulty'] == 0) {
                $user_count_info['difficulty'] = $params['difficulty'];
            }
        }

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
        $map = array();
        $user_count_info['updated'] = time();
        $map['view_type'] = intval($user_count_info['view_type']);
        $map['voice'] = intval($user_count_info['voice']);
        $map['target'] = intval($user_count_info['target']);
        $map['object'] = intval($user_count_info['object']);
        $map['level'] = intval($user_count_info['level']);
        $map['subject'] = intval($user_count_info['subject']);
        $map['recommend'] = intval($user_count_info['recommend']);
        $map['ted'] = intval($user_count_info['ted']);
        $map['difficulty'] = intval($user_count_info['difficulty']);
        //
        //游客对应游客信息统计表english_tourist_count
        if (!$this->userService->isLogin()) {
            $map['user_id'] = $this->userService->getGuestId();//intval(cookie('english_tourist_id')); //从cookie获取游客id
            if ($map['user_id'] > 0) {
                $englishTouristCountModel = D("EnglishTouristCount");
                $ret = $englishTouristCountModel->where($map)->save($user_count_info); //如果存在修改记录
                //
                //不存在记录则增加记录
                if (false !== $ret && $ret < 1) {
                    $user_count_info['user_id'] = $map['user_id'];
                    $englishTouristCountModel->add($user_count_info);
                }
            }
        } else {
            $map['user_id'] = $this->userService->getUserId();
            if ($map['user_id'] > 0) {
                $ret = $this->where($map)->save($user_count_info); //如果存在修改记录
                //
                //不存在记录则增加记录
                if (false !== $ret && $ret < 1) {
                    $user_count_info['user_id'] = $map['user_id'];
                    $this->add($user_count_info);
                }
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
        $condition = array();
        $params = array();
        if (intval($voice) > 0) {
            $condition['user_count_info.voice'] = $voice;
            $params['voice'] = $voice;
        }
        if (intval($target) > 0) {
            $condition['user_count_info.target'] = $target;
            $params['target'] = $target;
        }
        if (intval($pattern) > 0) {
            $condition['user_count_info.pattern'] = $pattern;
            $params['pattern'] = $pattern;
        }
        if (intval($object) > 0) {
            $object_map['id'] = $object;
            $objecr_name = D("EnglishObject")->where($object_map)->getField("name");
            if ($objecr_name != "综合") {
                $condition['user_count_info.object'] = $object;
                $params['object'] = $object;
            }
        }
        if (intval($level) > 0) {
            $condition['user_count_info.level'] = $level;
            $params['level'] = $level;
        }
        $list = $this->alias("user_count_info")
                ->field("SUM(user_count_info.rice) as rice_sum,user.face,user.id as user_id,user.nickname as nickname,level.name as best_level_name")
                ->join(C("DB_PREFIX") . "english_user_info english_user_info on user_count_info.user_id=english_user_info.user_id")
                ->join(C("DB_PREFIX") . "english_level level on english_user_info.best_level=level.id")
                ->join("RIGHT JOIN " . C("DB_PREFIX") . "member user on user_count_info.user_id=user.id")
                ->where($condition)
                ->group("user_count_info.user_id")
                ->order("rice_sum DESC")
                ->limit($limit)
                ->select();
        if (!$this->userService->isLogin()) {
            $list[] = $this->getNowUserCountToTop($params);
        } else {
            $user_id = $this->userService->getUserId();
            $user_is_in_top = false;
            foreach ($list as $value) {
                if ($value['user_id'] == $user_id) {
                    $user_is_in_top = true;
                }
            }
            if (false == $user_is_in_top) {
                $list[] = $this->getNowUserCountToTop($params);
            }
        }
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
        $map = array();
        $map['voice'] = $voice;
        $map['target'] = $target;
        $map['object'] = $object;
        $map['level'] = $level;
        $data['right_num'] = 0;
        $data['rice'] = 0;
        $data['continue_right_num'] = 0;
        $data['continue_error_num'] = 0;
        $data['updated'] = time();
        //
        //游客对应游客信息统计表english_tourist_count
        if (!$this->userService->isLogin()) {
            $map['user_id'] = $this->userService->getGuestId();//intval(cookie('english_tourist_id')); //从cookie获取游客id
            if ($map['user_id'] > 0) {
                $englishTouristCountModel = D("EnglishTouristCount");
                if (false === $englishTouristCountModel->where($map)->save($data)) {
                    Log::write('重置英语角等级失败：' . $englishTouristCountModel->getLastSql(), Log::SQL);
                }
            }
        } else {
            $map['user_id'] = $this->userService->getUserId();
            if ($map['user_id'] > 0) {
                if (false === $this->where($map)->save($data)) {
                    Log::write('重置英语角等级失败：' . $this->getLastSql(), Log::SQL);
                }
            }
        }
        D("EnglishRecord")->clearUserRecord($object, $level, $voice, $target); //重置用户记录
    }

    /**
     * 
     */
    public function getNowUserCountToTop($params) {
        if (!$this->userService->isLogin()) {
            $model = D("EnglishTouristCount");
            $params['user_id'] = $this->userService->getGuestId();//intval(cookie('english_tourist_id'));
            $ret = $model->field("SUM(rice) as rice_sum")->where($params)->find();
            $user_info = D("EnglishUserInfo")->getEnglishUserInfo();
            $ret['nickname'] = "我";
            $ret['face'] = "face.jpg";
            $ret['best_level_name'] = $user_info['best_level_name'];
        } else {
            $condition = array();
            $condition['user_count_info.user_id'] = $this->userService->getUserId();
            if (intval($params['voice']) > 0) {
                $condition['user_count_info.voice'] = $params['voice'];
            }
            if (intval($params['target']) > 0) {
                $condition['user_count_info.target'] = $params['target'];
            }
            if (intval($params['pattern']) > 0) {
                $condition['user_count_info.pattern'] = $params['pattern'];
            }
            if (intval($params['object']) > 0) {
                $object_map['id'] = $params['object'];
                $objecr_name = D("EnglishObject")->where($object_map)->getField("name");
                if ($objecr_name != "综合") {
                    $condition['user_count_info.object'] = $params['object'];
                }
            }
            if (intval($params['level']) > 0) {
                $condition['user_count_info.level'] = $params['level'];
            }
            $ret = $this->alias("user_count_info")
                    ->field("SUM(user_count_info.rice) as rice_sum,user.face,user.id as user_id,user.nickname as nickname,level.name as best_level_name")
                    ->join(C("DB_PREFIX") . "english_user_info english_user_info on user_count_info.user_id=english_user_info.user_id")
                    ->join(C("DB_PREFIX") . "english_level level on english_user_info.best_level=level.id")
                    ->where($condition)
                    ->find();
            if ($ret == false) {
                $ret = array();
            }
        }
        return $ret;
    }

}

?>
