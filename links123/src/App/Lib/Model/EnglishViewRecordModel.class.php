<?php

/**
 * 用户已查看题目模型类
 *
 * @author Adam $date2013.7.12$
 */
class EnglishViewRecordModel extends CommonModel {

    /**
     * 添加用户查看记录
     * @param int $question_id [试题id]
     * @param int $level [等级]
     * @param int $object [科目]
     * @param int $diffculty [难度值]
     * @param int $subject [专题]
     * @param int $voice [口语]
     * @param int $target [目标]
     * @param int $pattern [类型]
     * @param int $view_type [查看方式，1科目等级，2专题难度，3推荐难度,4特别推荐]
     * @return
     */
    public function addRecord($question_id, $level, $object, $subject, $recommend, $difficulty = 1, $voice = 1, $target = 1, $pattern = 1, $view_type = 1) {
        $map = array();
        //游客
        if (!$this->userService->isLogin()) {
            $map['user_id'] = $this->userService->getGuestId();//intval(cookie('english_tourist_id')); //从cookie获取游客id
            //如果不存在游客id，获取最大游客id加1为新游客id
            if ($map['user_id'] == 0) {
                $map['user_id'] = $this->getNewTouristId();
            }
            cookie('english_tourist_id', $map['user_id'], 24 * 60 * 60 * 30); //更新游客id到cookie
            $map['user_id'] = -$map['user_id']; //游客id在数据库表中记录为负数
        } else {
            $map['user_id'] = $this->userService->getUserId(); //用户id为登录用户的对应用户id
        }
        $map['voice'] = intval($voice);
        $map['target'] = intval($target);
        $map['pattern'] = intval($pattern);
        $map['question_id'] = $question_id;
        $map['view_type'] = in_array($view_type, array(1, 2, 3, 4)) ? $view_type : 1;
        if ($view_type == 3) {
            $map['recommend'] = intval($recommend);
            $map['difficulty'] = intval($difficulty);
        } else if ($view_type == 2) {
            $map['subject'] = intval($subject);
            $map['difficulty'] = intval($difficulty);
        } else if ($view_type == 1) {
            $map['object'] = intval($object);
            $map['level'] = intval($level);
        }
        $ret = $this->where($map)->find();
        if (!empty($ret)) {
            return;
        }
        /*
          $data = array();
          $data['user_id'] = $map['user_id'];
          $data['question_id'] = $map['question_id'];
          $data['object'] = $map['object'];
         */
        $map['created'] = time();
        $max_sort = $this->where('`user_id`=' . intval($map['user_id']))->field("MAX(sort) as max_sort")->find();
        if (false === $max_sort || empty($max_sort) || $max_sort['max_sort'] == null) {
            $max_sort['max_sort'] = 0;
        }
        $map['sort'] = $max_sort['max_sort'] + 1;
        $this->add($map);
    }

    /**
     * 添加用户查看记录
     * @param int $view_type 
     * @param array $params
     * @return
     */
    public function addViewRecord($view_type = 1, $params) {
        $map = array();
        //游客
        if (!$this->userService->isLogin()) {
            $map['user_id'] = $this->userService->getGusetId();// intval(cookie('english_tourist_id')); //从cookie获取游客id
            //如果不存在游客id，获取最大游客id加1为新游客id
            if ($map['user_id'] == 0) {
                $map['user_id'] = $this->getNewTouristId();
            }
            cookie('english_tourist_id', $map['user_id'], 24 * 60 * 60 * 30); //更新游客id到cookie
            $map['user_id'] = -$map['user_id']; //游客id在数据库表中记录为负数
        } else {
            $map['user_id'] = $this->userService->getUserId(); //用户id为登录用户的对应用户id
        }
        $map['voice'] = intval($params['voice']);
        $map['target'] = intval($params['target']);
        $map['pattern'] = intval($params['pattern']);
        $map['question_id'] = $params['id'];
        if (!in_array($view_type, array(1, 2, 3, 4, 5))) {
            $map['view_type'] = 1;
        }

        if ($view_type == 5) {
            $map['ted'] = intval($params['ted']);
            $map['difficulty'] = intval($params['difficulty']);
        } else if ($view_type == 3) {
            $map['recommend'] = intval($params['recommend']);
            $map['difficulty'] = intval($params['difficulty']);
        } else if ($view_type == 2) {
            $map['subject'] = intval($params['subject']);
            $map['difficulty'] = intval($params['difficulty']);
        } else if ($view_type == 1) {
            $map['object'] = intval($params['object']);
            $map['level'] = intval($params['level']);
        }
        $ret = $this->where($map)->find();
        if (!empty($ret)) {
            return;
        }
        $map['created'] = time();
        $max_sort = $this->where('`user_id`=' . intval($map['user_id']))->field("MAX(sort) as max_sort")->find();
        if (false === $max_sort || empty($max_sort) || $max_sort['max_sort'] == null) {
            $max_sort['max_sort'] = 0;
        }
        $map['sort'] = $max_sort['max_sort'] + 1;
        $this->add($map);
    }

    /**
     * 获取用户看过的题目记录
     * 根据用户当前的题目id，根据上下题的方式获取用户看过的题目记录
     * @param int $question_id [用户当前题目id]
     * @param string $type [查看用户已看题目的方式next下一题/prev上一题]
     * @param int $object [当前科目id]
     * @param int $level [当前等级id]
     * @param int $subject [当前专题id]
     * @param int $$recommend [当前推荐id]
     * @param int $difficulty [当前难度]
     * @param int $voice [当前口音]
     * @param int $target [当前目标]
     * @param int $pattern [当前类型]
     * @param int $view_type [查看方式，1科目等级，2专题难度，3推荐难度，4特别推荐]
     * @return array
     */
    public function getViewedQuestionRecord($question_id, $type = "next", $object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $view_type = 1) {
        $map = array();
        //
        //获取用户id
        $user_id = 0;
        if (!$this->userService->isLogin()) {
            $user_id = $this->userService->getGuestId();//intval(cookie('english_tourist_id')); //从cookie获取游客id
            //如果不存在游客id，返回空
            if ($user_id == 0) {
                return array();
            }
            $user_id = -$user_id;
        } else {
            $user_id = $this->userService->getUserId(); //用户id为登录用户的对应用户id
        }
        //如果不存在用户id，返回空
        if ($user_id == 0) {
            return array();
        }
        $map['question_id'] = $question_id;
        $map['view_type'] = $view_type;
        if ($view_type == 1 && intval($object) > 0) {
            $map['object'] = $object; //科目由于综合包含所有题目，需要区别科目
        }
        if ($view_type == 3 && intval($recommend) > 0) {
            $map['recommend'] = $recommend;
        }

        //
        //本次次的题目历史信息
        $now_question_info = $this->where($map)->find();
        if (false === $now_question_info || empty($now_question_info)) {
            return array();
        }
        $map = array();
        $map['user_id'] = $user_id;
        $map['view_type'] = $view_type;
        if ($view_type == 3) {
            $map['recommend'] = intval($recommend);
            $map['difficulty'] = intval($difficulty);
        } else if ($view_type == 2) {
            $map['subject'] = intval($subject);
            $map['difficulty'] = intval($difficulty);
        } else if ($view_type == 1) {
            $map['object'] = intval($object);
            $map['level'] = intval($level);
        }
        $map['voice'] = intval($voice);
        $map['target'] = intval($target);
        $map['pattern'] = intval($pattern);
        if ($type == "next") {
            if ($view_type == 3) {
                $map['recommend'] = intval($recommend);
                $map['difficulty'] = intval($difficulty);
            } else if ($view_type == 2) {
                $map['subject'] = intval($subject);
                $map['difficulty'] = intval($difficulty);
            } else if ($view_type == 1) {
                $map['object'] = intval($object);
                $map['level'] = intval($level);
            }
            $map['voice'] = intval($voice);
            $map['target'] = intval($target);
            $map['pattern'] = intval($pattern);
            $map['sort'] = array('gt', intval($now_question_info['sort']));
            $order = "`sort` ASC";
        } else {
            $map['sort'] = array('lt', intval($now_question_info['sort']));
            $order = "`sort` DESC";
        }
        $ret = $this->where($map)->order($order)->find();
        //echo $this->getLastSql();exit;
        if (false === $ret) {
            $ret = array();
        }
        return $ret;
    }

    /**
     * 获取用户看过的题目id列表
     * @param int $object [科目id]
     * @param int $level [等级id]
     * @param int $subject [专题id]
     * @param int $recommend [推荐id]
     * @param int $difficulty [难度值，1初级，2中级，3高级]
     * @param int $voice [口音，1美音，2英音]
     * @param int $target [训练目标，1听力，2说力]
     * @param int $pattern [类型，1视频，2音频]
     * @param string $extend_condition [额外条件]
     * @return array
     */
    public function getUserViewQuestionIdList($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $extend_condition = "") {
        //试题id数组初始化
        $question_ids = array();
        if (intval($object)) {
            if (D("EnglishObject")->where("id=" . intval($object))->getField("name") == "综合") {
                $object = 0;
            }
        }
        if (intval($object) > 0) {
            $map['media.object'] = intval($object);
        }
        if (intval($level) > 0) {
            $map['media.level'] = intval($level);
        }
        if (intval($subject) > 0) {
            $map['media.subject'] = intval($subject);
        }
        if (intval($recommend) > 0) {
            $map['media.recommend'] = $recommend;
            //$map['media.recommend'] = intval($recommend);
        }
        if (intval($difficulty) > 0) {
            $map['media.difficulty'] = intval($difficulty);
        }
        if (intval($voice) > 0) {
            $map['media.voice'] = intval($voice);
        }
        if (intval($target) > 0) {
            $map['question.target'] = intval($target);
        }
        if (intval($pattern) > 0) {
            $map['media.pattern'] = intval($pattern);
        }
        if (!empty($extend_condition)) {
            $map['_string'] .= $extend_condition;
        }
        if ($this->userService->isLogin()) {
            $map['record.user_id'] = $this->userService->getUserId();
        } else {
            $map['record.user_id'] = $this->userService->getGuestId();//intval(cookie("english_tourist_id")) > 0 ? -intval(cookie("english_tourist_id")) : 0;
        }
        $ret = $this->alias("record")
                ->field("record.question_id")
                ->join(C("DB_PREFIX") . "english_question question on record.question_id=question.id")
                ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                ->where($map)
                ->order("record.sort")
                ->select();
        if (false !== $ret && !empty($ret)) {
            foreach ($ret as $key => $value) {
                $question_ids[] = intval($value['question_id']);
            }
        }
        return $question_ids;
    }

    /**
     * 获取用户看过的题目id列表
     * @param array $params [科目id]
     * @param string $extend_condition [额外条件]
     * @return array
     */
    public function getUserViewedQuestionIdList($viewType, $params, $extend_condition = "") {
        //试题id数组初始化
        $question_ids = array();
        if ($params == 1) {
            if (intval($params['object'])) {
                if (D("EnglishObject")->where("id=" . intval($params['object']))->getField("name") == "综合") {
                    $params['object'] = 0;
                }
            }
            if (intval($params['object']) > 0) {
                $map['media.object'] = intval($params['object']);
            }
            if (intval($params['level']) > 0) {
                $map['media.level'] = intval($params['level']);
            }
        } else if ($viewType == 2) {
            if (intval($params['subject']) > 0) {
                $map['media.subject'] = intval($params['subject']);
            }
            if (intval($params['difficulty']) > 0) {
                $map['media.difficulty'] = intval($params['difficulty']);
            }
        } else if ($viewType == 3) {
            if (intval($params['recommend']) > 0) {
                $map['media.recommend'] = $params['recommend'];
            }
            if (intval($params['difficulty']) > 0) {
                $map['media.difficulty'] = intval($params['difficulty']);
            }
        } else if ($viewType == 4) {
            $map['media.recommend'] = 0;
            $map['media.difficulty'] = 0;
        } else if ($viewType == 5) {
            if (intval($params['ted']) > 0) {
                $map['media.ted'] = $params['ted'];
            }
            if (intval($params['difficulty']) > 0) {
                $map['media.difficulty'] = intval($params['difficulty']);
            }
        }
        if (intval($params['voice']) > 0) {
            $map['media.voice'] = intval($params['voice']);
        }
        if (intval($params['target']) > 0) {
            $map['question.target'] = intval($params['target']);
        }
        if (intval($params['pattern']) > 0) {
            $map['media.pattern'] = intval($params['pattern']);
        }
        $map['view_type'] = $viewType;
        if (!in_array($map['view_type'], array(1, 2, 3, 4, 5))) {
            $map['view_type'] = 1;
        }
        if (!empty($extend_condition)) {
            $map['_string'] .= $extend_condition;
        }
        if ($this->userService->isLogin()) {
            $map['record.user_id'] = $this->userService->getUserId();
        } else {
            $map['record.user_id'] = $this->userService->getGusetId();// intval(cookie("english_tourist_id")) > 0 ? -intval(cookie("english_tourist_id")) : 0;
        }
        $ret = $this->alias("record")
                ->field("record.question_id")
                ->join(C("DB_PREFIX") . "english_question question on record.question_id=question.id")
                ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                ->where($map)
                ->order("record.sort")
                ->select();
        if (false !== $ret && !empty($ret)) {
            foreach ($ret as $key => $value) {
                $question_ids[] = intval($value['question_id']);
            }
        }
        return $question_ids;
    }

    /**
     * 获取新游客id
     * @return int
     * @author Adam $date2013-08-13$
     */
    public function getNewTouristId() {
        $ret = 0;
        $viewRecordUser = $this->field("MIN(user_id) as max_user_id")->find();
        if (false === $viewRecordUser || empty($viewRecordUser) || $viewRecordUser['max_user_id'] == null || $viewRecordUser['max_user_id'] > 0) {
            $viewRecordUser['max_user_id'] = 0;
        } else {
            $viewRecordUser['max_user_id'] = -$viewRecordUser['max_user_id'];
        }
        $touristRecordUser = D("EnglishTouristRecord")->field("MAX(user_id) as max_user_id")->find();
        if (false === $touristRecordUser || empty($touristRecordUser) || $touristRecordUser['max_user_id'] == null) {
            $touristRecordUser['max_user_id'] = 0;
        }
        $ret = intval($viewRecordUser['max_user_id'] > $touristRecordUser['max_user_id'] ? $viewRecordUser['max_user_id'] : $touristRecordUser['max_user_id']) + 1;
        return $ret;
    }

    /**
     * 获取最后查看ID
     * 
     * @author slate date:2013-09-11
     */
    public function getUserViewQuestionLastId($object, $level, $subject, $recommend, $difficulty, $voice, $target, $pattern, $extend_condition = "") {
        //试题id数组初始化
        $question_ids = array();
        $map['media.status'] = 1;
        $map['question.status'] = 1;
        if (intval($object)) {
            if (D("EnglishObject")->where("id=" . intval($object))->getField("name") == "综合") {
                $object = 0;
            }
        }
        if (intval($object) > 0) {
            $map['media.object'] = intval($object);
        }
        if (intval($level) > 0) {
            $map['media.level'] = intval($level);
        }
        if (intval($subject) > 0) {
            $map['media.subject'] = intval($subject);
        }
        if (intval($recommend) > 0) {
            $map['media.recommend'] = $recommend;
            //$map['media.recommend'] = intval($recommend);
        }
        if (intval($difficulty) > 0) {
            $map['media.difficulty'] = intval($difficulty);
        }
        if (intval($voice) > 0) {
            $map['media.voice'] = intval($voice);
        }
        if (intval($target) > 0) {
            $map['question.target'] = intval($target);
        }
        if (intval($pattern) > 0) {
            $map['media.pattern'] = intval($pattern);
        }
        if (!empty($extend_condition)) {
            $map['_string'] .= $extend_condition;
        }
        if ($this->userService->isLogin()) {
            $map['record.user_id'] = $this->userService->getUserId();
        } else {
            $map['record.user_id'] = $this->userService->getGuestId();//intval(cookie("english_tourist_id")) > 0 ? -intval(cookie("english_tourist_id")) : 0;
        }
        $ret = $this->alias("record")
                ->field("record.question_id")
                ->join(C("DB_PREFIX") . "english_question question on record.question_id=question.id")
                ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                ->where($map)
                ->order("record.sort DESC")
                ->limit(1)
                ->select();

        $question_id = 0;
        if ($ret[0]) {
            $question_id = $ret[0]['question_id'];
        }

        return $question_id;
    }

    public function getUserLastViewedTedQuestionId($ted, $difficulty, $voice, $target, $pattern) {
        //试题id数组初始化
        $question_ids = array();
        if (intval($ted) > 0) {
            $map['media.ted'] = $ted;
        }
        if (intval($difficulty) > 0) {
            $map['media.difficulty'] = intval($difficulty);
        }
        if (intval($voice) > 0) {
            $map['media.voice'] = intval($voice);
        }
        if (intval($target) > 0) {
            $map['question.target'] = intval($target);
        }
        if (intval($pattern) > 0) {
            $map['media.pattern'] = intval($pattern);
        }
        if ($this->userService->isLogin()) {
            $map['record.user_id'] = $this->userService->getUserId();
        } else {
            $map['record.user_id'] = $this->userService->getGuestId();//intval(cookie("english_tourist_id")) > 0 ? -intval(cookie("english_tourist_id")) : 0;
        }
        $ret = $this->alias("record")
                ->field("record.question_id")
                ->join(C("DB_PREFIX") . "english_question question on record.question_id=question.id")
                ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_media media on question.media_id=media.id")
                ->where($map)
                ->order("record.sort DESC")
                ->limit(1)
                ->select();

        $question_id = 0;
        if ($ret[0]) {
            $question_id = $ret[0]['question_id'];
        }

        return $question_id;
    }

}

?>
