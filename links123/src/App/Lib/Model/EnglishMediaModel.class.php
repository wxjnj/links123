<?php

/**
 * 英语角媒体表模型类
 *
 * @author Adam $date2013.7.25$
 */
class EnglishMediaModel extends CommonModel {

    protected $_validate = array(
        array("name", "require", "名称必须"),
        array("object", "require", "科目必须"),
        array("level", "require", "等级必须"),
        array("media_source_url", "require", "来源地址必须"),
        array("media_source_url", "unique", "来源地址已存在", 1, "unique", 1),
    );
    protected $_auto = array(
        array("updated", "time", 3, "function"),
        array("created", "time", 1, "function")
    );

    /**
     * 设置媒体的推荐
     * @param string $id [将要设置的媒体id,多个逗号分隔]
     * @param int $target_recommend [指定是否推荐]
     * @param int $target_subject [指定的专题id]
     * @return boolean|string
     * @author Adam $date2013.09.01$
     */
    public function setRecommend($id, $target_recommend, $target_subject = 0, $target_object = 0) {
        $ids = explode(",", $id);
        if (empty($ids)) {
            return false;
        }
        $recommendModel = D("EnglishMediaRecommend");
        $recommendList = $recommendModel->field("id,name,`sort`")->where("status=1")->order("`sort` desc")->select();
        foreach ($recommendList as $value) {
            $recommendNameList[$value['name']] = intval($value['id']);
        }
        $recommendSort = intval($recommendList[0]['sort']) + 1;
        $ret = $this->alias("media")
                ->field("media.id,media.recommend,object.name as object_name,subject.name as subject_name")
                ->join(C("DB_PREFIX") . "english_object object on media.object=object.id")
                ->join(C("DB_PREFIX") . "english_media_subject subject on media.subject=subject.id")
                ->where(array("media.id" => array('in', $ids)))
                ->select();
        $time = time();
        if (intval($target_subject) > 0) {
            $target_subject_name = D("EnglishMediaSubject")->where(array("id" => $target_subject))->getField("name");
        }
        if (intval($target_object) > 0) {
            $target_object_name = D("EnglishObject")->where(array("id" => $target_object))->getField("name");
        }
        $data['updated'] = $time;
        foreach ($ret as $media) {
            $data['id'] = intval($media['id']);
            $recommend = intval($media['recommend']);
            if (isset($target_recommend)) {
                if ($target_recommend == 0 && $recommend == 0) {
                    continue;
                }
            }
            if ($target_object_name) {
                $object_name = $target_object_name;
            } else {
                $object_name = $media['object_name'];
            }
            if ($target_subject > -1) {
                if ($target_subject_name) {
                    $subject_name = $target_subject_name;
                } else {
                    $subject_name = $media['subject_name'];
                }
            }
            if ($recommend == 0 || $target_recommend == 1) {
                $recommend_id = 0;
                //专题存在
                if ($subject_name) {
                    $recommend_id_subject = $recommendNameList[$subject_name];
                    //推荐类存在专题名
                    if (intval($recommend_id_subject) == 0) {
                        $recommend_data['sort'] = $recommendSort;
                        $recommend_data['name'] = $subject_name;
                        $recommend_data['created'] = $time;
                        $recommend_data['updated'] = $time;
                        $recommend_id_subject = $recommendModel->add($recommend_data);
                        if (false === $recommend_id_subject) {
                            $this->rollback();
                            return false;
                        }
                        $recommendNameList[$subject_name] = $recommend_id_subject;
                    }
                    $recommend_id = $recommend_id_subject;
                    $recommendSort++;
                } else {
                    //科目存在
                    if ($object_name) {
                        $recommend_id_object = $recommendNameList[$object_name];
                        //推荐类存在科目名
                        if (intval($recommend_id_object) == 0) {
                            $recommend_data['sort'] = $recommendSort;
                            $recommend_data['name'] = $object_name;
                            $recommend_data['created'] = $time;
                            $recommend_data['updated'] = $time;
                            $recommend_id_object = $recommendModel->add($recommend_data);
                            if (false === $recommend_id_object) {
                                $this->rollback();
                                return false;
                            }
                            $recommendNameList[$object_name] = $recommend_id_object;
                        }
                        $recommend_id = $recommend_id_object;
                        $recommendSort++;
                    }
                }
                if ($recommend_id == 0) {
                    $this->rollback();
                    return false;
                }
                $recommend = $recommend_id;
            } else {
                $recommend = 0;
                $data['special_recommend'] = 0;
            }
            $data['recommend'] = $recommend;
            if (false === $this->save($data)) {
                $this->rollback();
                return false;
            }
        }
        if (count($ids) == 1) {
            return $recommend;
        } else {
            return true;
        }
    }

    /**
     * 获取特别推荐的视频列表
     * @return max
     * @author Adam $date2013.09.01$
     */
    public function getSpecialRecommendMediaList($limit = 20) {
        $ret = $this->alias("media")->field("media.id,media.name,media.media_thumb_img")
                ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_question question on question.media_id=media.id")
                ->where("media.special_recommend=1 and media.media_thumb_img!='' AND media.status=1 AND question.status=1")
//                ->limit($limit)
                ->order("question.id asc")
                ->select();
        return $ret;
    }

    public function getMediaInfo($media_id) {
        $ret = $this->find($media_id);
        if (false === $ret) {
            $ret = array();
        }
        if (!empty($ret)) {
            $ret['real_path'] = C("VIDEO_UPLOAD_PATH") . $ret['local_path'];
            $ret['captions'] = $this->formatCaptionTextToArray($ret['caption']);
        }
        return $ret;
    }

    public function formatMdeiaInfoToFlash($media_info) {
        $data = array();
        if (!empty($media_info)) {
            $data['title'] = $media_info['name'];
            $data['question_id'] = $media_info['question_id'];
            $data['url'] = $media_info['real_path'];
            $data['mp3url'] = C("VIDEO_UPLOAD_PATH") . $media_info['slow_audio']; //慢放的mp3
            $data['clips'] = array();
            foreach ($media_info['captions'] as $key => $value) {
                $data['clips'][$key]['title'] = "clip " . $key;
                $data['clips'][$key]['starttime'] = $value['start_time'];
                $data['clips'][$key]['endtime'] = $value['end_time'];
                $data['clips'][$key]['english'] = $value['en'];
                $data['clips'][$key]['chinese'] = $value['zh'];
            }
            if (!empty($media_info['sentences'])) {
                foreach ($media_info['sentences'] as $key => $value) {
                    $data['sentences'][$key]['id'] = $value['id'];
                    $data['sentences'][$key]['title'] = "clip " . $key;
                    $data['sentences'][$key]['starttime'] = $value['start_time'];
                    $data['sentences'][$key]['endtime'] = $value['end_time'];
                    $data['sentences'][$key]['english'] = $value['content'];
                }
            }
        }
        $data = json_encode($data);
        $data = preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $data);
        $data = str_replace("\\", "", $data);
        $data = preg_replace("/\'/", "\\'", $data);
        $data = preg_replace("/\"/", "\\\"", $data);
        return $data;
    }

    public function formatMediaInfo($media_info) {
        $data = array();
        if (!empty($media_info)) {
            $data['title'] = $media_info['name'];
            $data['question_id'] = $media_info['question_id'];
            $data['url'] = $media_info['real_path'];
            $data['mp3url'] = C("VIDEO_UPLOAD_PATH") . $media_info['slow_audio']; //慢放的mp3
            $data['clips'] = array();
            foreach ($media_info['captions'] as $key => $value) {
                $data['clips'][$key]['title'] = "clip " . $key;
                $data['clips'][$key]['starttime'] = $value['start_time'];
                $data['clips'][$key]['endtime'] = $value['end_time'];
                $data['clips'][$key]['english'] = $value['en'];
                $data['clips'][$key]['chinese'] = $value['zh'];
            }
            $data['sentences'] = array();
            if (!empty($media_info['sentences'])) {
                foreach ($media_info['sentences'] as $key => $value) {
                    $data['sentences'][$key]['id'] = $value['id'];
                    $data['sentences'][$key]['title'] = "clip " . $key;
                    $data['sentences'][$key]['starttime'] = $value['start_time'];
                    $data['sentences'][$key]['endtime'] = $value['end_time'];
                    $data['sentences'][$key]['english'] = $value['content'];
                }
            }
        }
        return $data;
    }

    /**
     * 格式化字幕字符串为数组
     * @param string $caption_text
     * @return array [数组格式：'start_time','end_time','en','zh']
     */
    public function formatCaptionTextToArray($caption_text,$need_zh=true) {
        $captions = array();
        if (!empty($caption_text)) {
            $ret = preg_split("/\n+/", $caption_text, -1, PREG_SPLIT_NO_EMPTY);
            $row = 0;
            $is_row = true;
            //是否已经跳过空行
            if (intval($ret[3]) > 0) {
                $mod = 3;
            }else{
                $mod = 4;
            }
            foreach ($ret as $key => $value) {
                $index = $key % $mod;
                switch ($index) {
                    case 0:
                        $is_row = true;
                        if (intval($value) > 0) {
                            $row = intval($value) - 1;
                        } else {
                            $is_row = false;
                        }
                        break;
                    case 1:
                        if ($is_row) {
                            $time_temp = explode('-->', $value);
                            if (!empty($time_temp) && !empty($time_temp[0]) && !empty($time_temp[1])) {
                                $captions[$row]['start_time'] = $this->getTimeStrToSecond(current(explode(",", $time_temp[0])));
                                $captions[$row]['end_time'] = $this->getTimeStrToSecond(current(explode(",", $time_temp[1])));
                            } else {
                                $is_row = false;
                            }
                        }
                        break;
                    case 2:
                        if ($is_row) {
                            $lan_temp = explode('|', $value);
                            if (!empty($lan_temp)) {
                                $captions[$row]['en'] = $lan_temp[0];
                                if($need_zh){
                                    $captions[$row]['zh'] = $lan_temp[1];
                                }
                            } else {
                                $is_row = false;
                            }
                        }
                        break;

                    default:
                        break;
                }
            }
        }
        return $captions;
    }

    /**
     * 将字符串时间，转换为秒数
     * @param [string] $time_str [时间字符串例如00:10:33]
     * @return int
     * @author Adam $date2013-08-02$
     */
    public function getTimeStrToSecond($time_str) {
        $time = 0;
        if (!empty($time_str)) {
            $array = array_reverse(explode(":", $time_str));
            $time = intval($array[0]) + intval($array[1]) * 60 + intval($array[2]) * 60 * 60;
        }
        return $time;
    }

    public function getRecommendQuestionNum($target = 1, $voice = 1, $pattern = 1, $recommend = -1) {
        $english_question_table_name = "english_question";
        if ($target == 2) {
            $english_question_table_name = "english_question_speak";
        }
        $condition = array();
        $condition['question.status'] = 1;
        $condition['media.status'] = 1;
        $condition['media.voice'] = $voice;
        $condition['media.pattern'] = $pattern;
        if ($recommend == 0) {
            $condition['media.special_recommend'] = 1;
        } else if ($recommend == -1) {
            $condition['media.recommend'] = array("neq", 0);
        } else {
            $condition['recommend'] = $recommend;
        }
        $num = $this->alias("media")
                ->join(C("DB_PREFIX") . $english_question_table_name . " question on question.media_id=media.id")
                ->where($condition)
                ->count("question.id");
        return intval($num);
    }

    public function getSubjectQuestionNum($target = 1, $voice = 1, $pattern = 1, $subject = 0) {
        $english_question_table_name = "english_question";
        if ($target == 2) {
            $english_question_table_name = "english_question_speak";
        }
        $condition = array();
        $condition['question.status'] = 1;
        $condition['media.status'] = 1;
        $condition['media.voice'] = $voice;
        $condition['media.pattern'] = $pattern;
        if ($subject == 0) {
            $condition['media.subject'] = array("neq", 0);
        } else {
            $condition['media.subject'] = $subject;
        }
        $num = $this->alias("media")
                ->join(C("DB_PREFIX") . $english_question_table_name . " question on question.media_id=media.id")
                ->where($condition)
                ->count("question.id");
        return intval($num);
    }

    public function getTedQuestionNum($target = 1, $voice = 1, $pattern = 1, $ted = 0) {
        $english_question_table_name = "english_question";
        if ($target == 2) {
            $english_question_table_name = "english_question_speak";
        }
        $condition = array();
        $condition['question.status'] = 1;
        $condition['media.status'] = 1;
        $condition['media.voice'] = $voice;
        $condition['media.pattern'] = $pattern;
        if ($ted == 0) {
            $condition['media.ted'] = array("neq", 0);
        } else {
            $condition['media.ted'] = $ted;
        }
        $num = $this->alias("media")
                ->join(C("DB_PREFIX") . $english_question_table_name . " question on question.media_id=media.id")
                ->where($condition)
                ->count("question.id");
        return intval($num);
    }

    public function analysisMediaPlayCode(&$media) {
        if (strpos($media['media_source_url'], 'http://www.youtube.com') !== FALSE && $media['media_local_path']) {
            $media['priority_type'] = 2;
        }
        $media['isAboutVideo'] = 0;
        //优先播放本地，且本地视频存在
        if ($media['priority_type'] == 2 && $media['media_local_path']) {
            $media['play_code'] = $media['media_local_path'];
            $media['isAboutVideo'] = 0;
            if (strtolower(end(explode(".", $media['media_local_path']))) == "swf") {
                $media['play_type'] = 0;
            } else {
                $media['play_type'] = 4;
            }
            return;
        } else {
            //play_code为空，则进行视频解析
            if (!$media['play_code']) {
                //视频解析库
                import("@.ORG.VideoHooks");
                $videoHooks = new VideoHooks();

                $media['media_source_url'] = trim(str_replace(' ', '', $media['media_source_url']));
                $videoInfo = $videoHooks->analyzer($media['media_source_url']);

                $play_code = $videoInfo['swf'];

                $media_thumb_img = $videoInfo['img'];

                //解析成功，保存视频解析地址
                if (!$videoHooks->getError() && $play_code) {

                    $play_type = $videoInfo['media_type'];
                    $saveData = array(
                        'id' => $media['media_id'],
                        'media_thumb_img' => $media_thumb_img,
                        'play_code' => $play_code,
                        'play_type' => $play_type
                    );
                    D("EnglishMedia")->save($saveData);
                    $media['play_code'] = $play_code;

                    $media['media_thumb_img'] = $media_thumb_img;

                    $media['play_type'] = $play_type;
                    //判断是否为about.com视频
                    if (strpos($media['media_source_url'], 'http://video.about.com') !== FALSE && $media['target'] == 1) {
                        $media['isAboutVideo'] = 1;
                    }
                    if (strpos($media['media_source_url'], 'britishcouncil.org') !== FALSE) {
                        $media['play_code'] = preg_replace('/<!--<!\[endif\]-->(.*)/is', '</object></object>', $media['play_code']);
                        $media['play_code'] = str_replace('width=585&amp;height=575', 'width=100%&amp;height=100%', $media['play_code']);
                    }
                    $media['play_code'] = preg_replace(array('/width="(.*?)"/is', '/height="(.*?)"/is', '/width=300 height=280/is', '/width=600 height=400/is'), array('width="100%"', 'height="100%"', 'width="100%" height="100%"', 'width="100%" height="100%"'), $media['play_code']);
                    return;
                } else {
                    if ($media['media_local_path']) {
                        $media['priority_type'] = 2;
                        $media['play_type'] = 4;
                        $media['play_code'] = $media['media_local_path'];
                        return;
                    } else {
                        $media['play_code'] = FALSE;
                        $saveData = array(
                            'id' => $media['media_id'],
                            'status' => 0
                        );
                        D("EnglishMedia")->save($saveData);
                    }
                }
            } else {
                //判断是否为about.com视频
                if (strpos($media['media_source_url'], 'http://video.about.com') !== FALSE && $media['target'] == 1) {
                    $media['isAboutVideo'] = 1;
                }
                if (strpos($media['media_source_url'], 'britishcouncil.org') !== FALSE) {
                    $media['play_code'] = preg_replace('/<!--<!\[endif\]-->(.*)/is', '</object></object>', $media['play_code']);
                    $media['play_code'] = str_replace('width=585&amp;height=575', 'width=100%&amp;height=100%', $media['play_code']);
                }
                $media['play_code'] = preg_replace(array('/width="(.*?)"/is', '/height="(.*?)"/is', '/width=300 height=280/is', '/width=600 height=400/is'), array('width="100%"', 'height="100%"', 'width="100%" height="100%"', 'width="100%" height="100%"'), $media['play_code']);
                return;
            }
        }
        return;
    }
	/**
	 * 通过关键字搜索视频，返回搜索结果
	 *
	 * @param $keyword string
	 *       	 [搜索视频的关键词]
	 * @param $start int
	 *       	 [搜索结果集起始序号]
	 * @param $limit int
	 *       	 [搜索结果集最大数目]
	 * @return array [视频搜索结果集]
	 *        
	 * @author Rachel $date2013.10.1$
	 */
	public function getMediasByKeyword($keyword, $start = 0, $limit = 16) {
		$model = new Model ();
		$searchResult = $model->query ( "select m.id, s.name as subject, m.name,l.name as level,
    			m.updated, m.created, m.media_thumb_img
    			from lnk_english_media as m left join lnk_english_media_subject as s
    			on m.subject = s.id left join lnk_english_level as l on 
				m.level=l.id where m.name like '%%%s%%' or
    			s.name like '%%%s%%' limit $start, $limit;", $keyword, $keyword );
		foreach ( $searchResult as $index => $video ) {
			$timespan = time () - $video ['created'];
			if ($timespan < 60) { // time unit is second
				$searchResult [$index] ['created'] = $timespan . "秒前";
			} else if ($timespan >= 60 && $timespan < 3600) { // time unit is minute
				$timespan = ( int ) floor ( $timespan / 60 );
				$searchResult [$index] ['created'] = $timespan . "分钟前";
			} else if ($timespan >= 3600 && $timespan < 3600 * 24) { // time unit is hour
				$timespan = ( int ) floor ( $timespan / 3600 );
				$searchResult [$index] ['created'] = $timespan . "小时前";
			} else { // time unit is day
				$timespan = ( int ) floor ( $timespan / (3600 * 24) );
				$searchResult [$index] ['created'] = $timespan . "天前";
			}
		}
		return $searchResult;
	}
	
	/**
	 * 获得具有指定关键字的视频数
	 *
	 * @param $keyword string
	 *       	 [搜索视频的关键词]
	 * @return int [视频搜索结果数目]
	 *        
	 * @author Rachel $date2013.10.3$
	 */
	public function getMediaSearchCount($keyword) {
		$model = new Model ();
		$count = $model->query ( "select count(*) as count
    			from lnk_english_media as m left join lnk_english_media_subject as s
    			on m.subject = s.id where m.name like '%%%s%%';", $keyword );
		return $count [0] ['count'];
	}
	
	/**
	 * 生成视频搜索下拉框即时提示列表数据
	 *
	 * @param $keyword string
	 *       	 [搜索视频的关键词]
	 * @param $limit int
	 *       	 [搜索下拉框列表行数]
	 * @return array [视频即时提示列表集]
	 *        
	 * @author Rachel $date2013.10.3$
	 */
	public function getMediaPrompts($keyword, $limit = "30") {
		$model = new Model ();
		$prompts = $model->query ( "select name from lnk_english_media
    			where name like '%%%s%%' limit $limit;", $keyword );		
		return $prompts;
	}
}

?>
