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
        array("created", "time", 3, "function")
    );

    /**
     * 设置媒体的推荐
     * @param string $id [将要设置的媒体id,多个逗号分隔]
     * @param int $target_recommend [指定是否推荐]
     * @param int $target_subject [指定的专题id]
     * @return boolean|string
     * @author Adam $date2013.09.01$
     */
    public function setRecommend($id, $target_recommend, $target_subject = 0) {
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
        $data['updated'] = $time;
        foreach ($ret as $media) {
            $data['id'] = intval($media['id']);
            $recommend = intval($media['recommend']);
            if (isset($target_recommend)) {
                if ($target_recommend == 0 && $recommend == 0) {
                    continue;
                }
            }
            $object_name = $media['object_name'];
            if ($target_subject_name) {
                $subject_name = $target_subject_name;
            } else {
                $subject_name = $media['subject_name'];
            }
            if ($recommend == 0 || $target_recommend == 1) {
                $recommend_ids = array();
                //科目存在
                if ($object_name) {
                    $recommend_id_a = $recommendNameList[$object_name];
                    //推荐类存在科目名
                    if (intval($recommend_id_a) == 0) {
                        $recommend_data['sort'] = $recommendSort;
                        $recommend_data['name'] = $object_name;
                        $recommend_data['created'] = $time;
                        $recommend_data['updated'] = $time;
                        $recommend_id_a = $recommendModel->add($recommend_data);
                        if (false === $recommend_id_a) {
                            $this->rollback();
                            return false;
                        }
                        $recommendNameList[$object_name] = $recommend_id_a;
                    }
                    $recommendSort++;
                    array_push($recommend_ids, $recommend_id_a);
                }
                //专题存在
                if ($subject_name) {
                    $recommend_id_b = $recommendNameList[$subject_name];
                    //推荐类存在专题名
                    if (intval($recommend_id_b) == 0) {
                        $recommend_data['sort'] = $recommendSort;
                        $recommend_data['name'] = $subject_name;
                        $recommend_data['created'] = $time;
                        $recommend_data['updated'] = $time;
                        $recommend_id_b = $recommendModel->add($recommend_data);
                        if (false === $recommend_id_b) {
                            $this->rollback();
                            return false;
                        }
                        $recommendNameList[$subject_name] = $recommend_id_b;
                    }
                    $recommendSort++;
                    array_push($recommend_ids, $recommend_id_b);
                }
                if (empty($recommend_ids)) {
                    $this->rollback();
                    return false;
                }
                sort($recommend_ids);
                $recommend = implode(",", $recommend_ids);
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
        $ret = $this->alias("media")->field("id,name,media_thumb_img")
                ->join("RIGHT JOIN " . C("DB_PREFIX") . "english_question question on question.media_id=media.id")
                ->where("media.special_recommend=1 and media.media_thumb_img!='' AND media.status=1 AND question.status=1")
//                ->limit($limit)
                ->order("difficulty desc")
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
            $data['url'] = C("WEB_HOST_URL") . $media_info['real_path'];
            $data['mp3url'] = C("WEB_HOST_URL") . C("VIDEO_UPLOAD_PATH") . $media_info['slow_audio']; //慢放的mp3
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
            $data['url'] = C("WEB_HOST_URL") . $media_info['real_path'];
            $data['mp3url'] = C("WEB_HOST_URL") . C("VIDEO_UPLOAD_PATH") . $media_info['slow_audio']; //慢放的mp3
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
    public function formatCaptionTextToArray($caption_text) {
        $captions = array();
        if (!empty($caption_text)) {
            $ret = preg_split("/\n+/", $caption_text, -1, PREG_SPLIT_NO_EMPTY);
            $row = 0;
            $is_row = true;
            foreach ($ret as $key => $value) {
                $index = $key % 4;
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
                                $captions[$row]['zh'] = $lan_temp[1];
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
            $condition['_string'] = "FIND_IN_SET(" . $recommend . ",media.recommend)";
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

}
?>
