<?php

/**
 * 英语角说力试题后台管理类
 */
class EnglishQuestionSpeakAction extends CommonAction {

    public function _filter(&$map, &$param) {
        if (isset($_REQUEST['name'])) {
            $name = ftrim($_REQUEST['name']);
        }
        //媒体口音
        if (intval($_REQUEST['voice']) > 0) {
            $map['englishMedia.voice'] = intval($_REQUEST['voice']);
            $param['voice'] = intval($_REQUEST['voice']);
        }
        //视频类型
        if (intval($_REQUEST['pattern']) > 0) {
            $map['englishMedia.pattern'] = intval($_REQUEST['pattern']);
            $param['pattern'] = intval($_REQUEST['pattern']);
        }
        //视频科目
        if (intval($_REQUEST['object']) > 0) {
            $map['englishMedia.object'] = intval($_REQUEST['object']);
            $object_info = D("EnglishObject")->find($map['englishMedia.object']);
            if ($object_info['name'] == "综合") {
                unset($map['englishMedia.object']);
            }
            $param['object'] = intval($_REQUEST['object']);
        }
        //视频等级
        if (intval($_REQUEST['level']) > 0) {
            $map['englishMedia.level'] = intval($_REQUEST['level']);
            $param['level'] = intval($_REQUEST['level']);
        }
        //视频专题
        if (intval($_REQUEST['subject']) > 0) {
            $map['englishMedia.subject'] = intval($_REQUEST['subject']);
            $param['subject'] = intval($_REQUEST['subject']);
        }
        //视频难度值
        if (intval($_REQUEST['difficulty']) > 0) {
            $map['englishMedia.difficulty'] = intval($_REQUEST['difficulty']);
            $param['difficulty'] = intval($_REQUEST['difficulty']);
        }
        //视频推荐
        if (isset($_REQUEST['recommend'])) {
            if (intval($_REQUEST['recommend']) == 0) {
                $map['englishMedia.recommend'] = 0;
            } else {
                $map['englishMedia.recommend'] = array("neq", 0);
            }
            $param['recommend'] = intval($_REQUEST['recommend']);
        }
        //是否TED
        if (isset($_REQUEST['ted'])) {
            if (intval($_REQUEST['ted']) == 0) {
                $map['englishMedia.ted'] = 0;
            } else {
                $map['englishMedia.ted'] = array("neq", 0);
            }
            $param['ted'] = intval($_REQUEST['ted']);
        }
        //视频特别推荐
        if (isset($_REQUEST['special_recommend'])) {
            $map['englishMedia.special_recommend'] = intval($_REQUEST['special_recommend']);
            $param['special_recommend'] = intval($_REQUEST['special_recommend']);
        }
        if (isset($_REQUEST['status'])) {
            if ($_REQUEST['status'] != -2) {
                $map['englishQuestionSpeak.status'] = intval($_REQUEST['status']);
            }
            $param['status'] = intval($_REQUEST['status']);
        }
        //是否存在本地视频文件
        if (isset($_REQUEST['has_local_path'])) {
            if (intval($_REQUEST['has_local_path']) == 0) {
                $map['englishMedia.local_path'] = array("eq", "");
            } else {
                $map['englishMedia.local_path'] = array("neq", "");
            }
            $param['has_local_path'] = intval($_REQUEST['has_local_path']);
        }
        //试题创建时间
        if (isset($_REQUEST['created']) && strtotime($_REQUEST['created'])) {
            $map['_string'] = "DATE_FORMAT(FROM_UNIXTIME(englishQuestionSpeak.`created`),'%Y-%m-%d')='" . $_REQUEST['created'] . "'";
            $param['created'] = $_REQUEST['created'];
        }
        //媒体缩略图
        if (isset($_REQUEST['thumb'])) {
            if (intval($_REQUEST['thumb']) == 1) {
                $map['englishMedia.media_thumb_img'] = array("neq", "");
            } else {
                $map['englishMedia.media_thumb_img'] = array("eq", "");
            }
            $param['thumb'] = intval($_REQUEST['thumb']);
        }
        if (!empty($name)) {
            $key['englishQuestionSpeak.id'] = $name;
            $key['englishQuestionSpeak.name'] = array('like', "%" . $name . "%");
            $key['englishMedia.media_source_url'] = array('like', "%" . $name . "%");
            $key['_logic'] = 'or';
        }
        if (!empty($key)) {
            $map['_complex'] = $key;
        }
        $this->assign('name', $name);
        $param['name'] = $name;
    }

    public function index() {
        //列表过滤器，生成查询Map对象
        $map = array();
        $param = array();
        if (method_exists($this, '_filter')) {
            $this->_filter($map, $param);
        }
        $model = new EnglishQuestionSpeakViewModel();
        if (!empty($model)) {
            $this->_list($model, $map, $param, 'id', false);
            //lTrace('Log/lastSql', $this->getActionName(), $model->getLastSql());
        }
        //科目列表
        $object_list = D("EnglishObject")->getList("status=1");
        $this->assign("object_list", $object_list);
        //科目列表
        $level_list = D("EnglishLevel")->getList("status=1", "`sort` ASC");
        $num = intval(D("Variable")->getVariable("english_click_num"));
        $this->assign("english_click_num", $num);
        $this->assign("level_list", $level_list);
        //专题列表
        $subject_list = D("EnglishMediaSubject")->getList("status=1", "`sort` ASC");
        $this->assign("subject_list", $subject_list);

        $this->assign("param", $param);
        foreach ($param as $key => $value) {
            $param_str.=$key . "=" . $value . "&";
        }
        $this->assign("param_str", $param_str);
        $this->display();
        return;
    }

    public function insert() {
        $model = D("EnglishQuestionSpeak");
        $model->startTrans();

        if (false === $model->create()) {
            $model->rollback();
            $this->error($model->getError());
        }
        //
        //根据媒体来源或媒体名称对应到媒体id
        $mediaId = 0;
        $mediaModel = D("EnglishMedia");
        if (!empty($_REQUEST['media_source_url'])) {
            $mediaMap = array();
            $mediaMap['media_source_url'] = ftrim($_REQUEST['media_source_url']);
            $mediaId = intval($mediaModel->where($mediaMap)->getField('id'));
        }
        if ($mediaId == 0 && !empty($_REQUEST['media_name'])) {
            $mediaMap = array();
            $mediaMap['name'] = $_REQUEST['media_name'];
            $mediaId = intval($mediaModel->where($mediaMap)->getField('id'));
        }
        //
        //媒体id为零，将试题置为禁用
        $model->media_id = $mediaId;
        if ($model->status == 1 && $mediaId == 0) {
            $model->status = 0;
        }

        //保存当前数据对象
        $question_id = $model->add();
        if ($question_id !== false) { //保存成功
            //增加跟读句子
            $englishQuestionSpeakSentenceModel = D("EnglishQuestionSpeakSentence");
            $sentence_content_arr = $_POST['sentence_content'];
            $sentence_start_arr = $_POST['sentence_start_time'];
            $sentence_end_arr = $_POST['sentence_end_time'];
            if (!empty($sentence_content_arr)) {
                $datalist = array();
                $time = time();
                foreach ($sentence_content_arr as $key => $value) {
                    if (intval($sentence_start_arr[$key]) > 0 && intval($sentence_end_arr[$key]) > 0) {
                        $temp = array();
                        $temp['question_id'] = $question_id;
                        $temp['created'] = $time;
                        $temp['updated'] = $time;
                        $temp['content'] = $value;
                        $temp['start_time'] = intval($sentence_start_arr[$key]);
                        $temp['end_time'] = intval($sentence_end_arr[$key]);
                        array_push($datalist, $temp);
                    }
                }
                if (!empty($datalist)) {
                    $sentence_ret = $englishQuestionSpeakSentenceModel->addAll($datalist);
                    if (false === $sentence_ret) {
                        $model->rollback();
                        //失败提示
                        $this->error('新增失败!');
                    }
                }
                //不存在跟读句子则禁用
                if (intval($sentence_ret) == 0) {
                    $model->where(array("id" => $question_id))->setField("status", 0);
                }
            }
            $model->commit();
            $this->success('新增成功!', cookie('_currentUrl_'));
        } else {
            $model->rollback();
            //失败提示
            $this->error('新增失败!');
        }
    }

    public function edit() {
        $name = $this->getActionName();
        $model = M($name);
        $id = intval($_REQUEST [$model->getPk()]);
        $vo = $model->alias("question")
                ->field("question.*,media.name as media_name,media.media_source_url as source_url")
                ->join(C("DB_PREFIX") . "english_media media on media.id=question.media_id")
                ->where("question.id=" . intval($id))
                ->find();
        if (!empty($vo['source_url'])) {
            $vo['media_source_url'] = $vo['source_url'];
        }
        $sentence_list = D("EnglishQuestionSpeakSentence")->getSpeakQuestionSentenceList($id);
        $this->assign('sentence_list', $sentence_list);
        $this->assign('vo', $vo);
        $this->assign('doubleQuotes', '"');

        $this->display();
    }

    public function update() {
        $model = D("EnglishQuestionSpeak");
        $englishQuestionSpeakSentenceModel = D("EnglishQuestionSpeakSentence");
        $id = intval($_REQUEST['id']);
        $model->startTrans();
        //删除选项
        $map['question_id'] = $id;
        $englishQuestionSpeakSentenceModel->where($map)->delete();

        if (false === $model->create()) {
            $model->rollback();
            $this->error($model->getError());
        }
        //
        //根据媒体来源或媒体名称对应到媒体id
        $mediaId = 0;
        $mediaModel = D("EnglishMedia");
        if (!empty($_REQUEST['media_source_url'])) {
            $mediaMap = array();
            $mediaMap['media_source_url'] = ftrim($_REQUEST['media_source_url']);
            $mediaId = intval($mediaModel->where($mediaMap)->getField('id'));
        }
        if ($mediaId == 0 && !empty($_REQUEST['media_name'])) {
            $mediaMap = array();
            $mediaMap['name'] = $_REQUEST['media_name'];
            $mediaId = intval($mediaModel->where($mediaMap)->getField('id'));
        }
        //
        //媒体id为零，将试题置为禁用
        $model->media_id = $mediaId;
        if ($model->status == 1 && $mediaId == 0) {
            $model->status = 0;
        }

        //保存当前数据对象
        $ret = $model->save();
        if ($ret !== false) { //保存成功
            //增加跟读句子
            $sentence_content_arr = $_POST['sentence_content'];
            $sentence_start_arr = $_POST['sentence_start_time'];
            $sentence_end_arr = $_POST['sentence_end_time'];
            if (!empty($sentence_content_arr)) {
                $datalist = array();
                $time = time();
                foreach ($sentence_content_arr as $key => $value) {
                    if (intval($sentence_start_arr[$key]) > 0 && intval($sentence_end_arr[$key]) > 0) {
                        $temp = array();
                        $temp['question_id'] = $id;
                        $temp['created'] = $time;
                        $temp['updated'] = $time;
                        $temp['content'] = $value;
                        $temp['start_time'] = intval($sentence_start_arr[$key]);
                        $temp['end_time'] = intval($sentence_end_arr[$key]);
                        array_push($datalist, $temp);
                    }
                }
                if (!empty($datalist)) {
                    $sentence_ret = $englishQuestionSpeakSentenceModel->addAll($datalist);
                    if (false === $sentence_ret) {
                        $model->rollback();
                        //失败提示
                        $this->error('编辑失败!');
                    }
                }
                //不存在跟读句子则禁用
                if ($model->status == 1 && intval($sentence_ret) == 0) {
                    $model->where(array("id" => $id))->setField("status", 0);
                }
            }
            $model->commit();
            $this->success('编辑成功!', cookie('_currentUrl_'));
        } else {
            $model->rollback();
            //失败提示
            $this->error('编辑失败!');
        }
    }

    //excel导入
    public function excel_insert() {
        if ($this->isPost()) {
            import("@.ORG.UploadFile");
            $upload = new UploadFile();
            //设置上传文件大小
            //$upload->maxSize = 3292200;
            //设置上传文件类型
            $upload->allowExts = explode(',', 'xlsx,xls');
            //设置附件上传目录
            $path = realpath('./Public/Uploads/uploads.txt');
            $upload->savePath = str_replace('uploads.txt', 'Excels', $path) . '/';
            //设置上传文件规则
            $upload->saveRule = uniqid;
            if (!$upload->upload()) {
                //捕获上传异常
                $this->ajaxReturn("", $upload->getErrorMsg(), false);
            } else {
                //取得成功上传的文件信息
                $uploadList = $upload->getUploadFileInfo();
            }

            //引入类
            error_reporting(E_ALL);
            date_default_timezone_set('Asia/Shanghai');

            vendor('PHPExcel.Classes.PHPExcel.IOFactory');
            @header('Content-type: text/html;charset=UTF-8');
            //
            //读取excel;
            if ($uploadList[0]['extension'] == "xls") {
                $objReader = PHPExcel_IOFactory::createReader('Excel5');
            } else {
                $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            }
            $path = realpath('./Public/Uploads/uploads.txt');
            $dest = str_replace('uploads.txt', 'Excels/' . $uploadList[0]['savename'], $path);

            $objPHPExcel = $objReader->load($dest);
            //
            //声明模型类
            $model = D("EnglishQuestionSpeak");
            $sentenceModel = D("EnglishQuestionSpeakSentence");
            $levelModel = D("EnglishLevel");
            $mediaModel = D("EnglishMedia");
            $recommendModel = D("EnglishMediaRecommend");
            //
            //提取等级列表备用
            $levels = $levelModel->order("`sort` ASC")->select();
            foreach ($levels as $key => $value) {
                $level_list[$value['name']] = $value['id'];
                $level_name_list_info[$value['name']] = $value;
            }
            foreach ($levels as $key => $value) {
                if ($value['sort'] <= $level_name_list_info['小六']['sort']) {
                    $difficulty_list[$value['name']] = 1;
                } else if ($value['sort'] >= $level_name_list_info['大一']['sort']) {
                    $difficulty_list[$value['name']] = 3;
                } else {
                    $difficulty_list[$value['name']] = 2;
                }
            }
            //
            //读取科目列表备用
            $objectModel = D("EnglishObject");
            $objects = $objectModel->select();
            foreach ($objects as $key => $value) {
                $object_list[$value['name']] = $value['id'];
            }
            //
            //读取专题列表
            $subjectModel = D("EnglishMediaSubject");
            $subjects = $subjectModel->select();
            foreach ($subjects as $key => $value) {
                $subject_list[$value['name']] = $value['id'];
            }
            //推荐列表备用
            $recommendList = $recommendModel->field("id,name,`sort`")->order("`sort` desc")->select();
            foreach ($recommendList as $value) {
                $recommendNameList[$value['name']] = intval($value['id']);
            }
            $recommendSort = intval($recommendList[0]['sort']) + 1;

            //TED列表备用
            $tedModel = D("EnglishMediaTed");
            $tedList = $tedModel->field("id,name,`sort`")->order("`sort` desc")->select();
            foreach ($tedList as $value) {
                $tedNameList[$value['name']] = intval($value['id']);
            }
            $tedSort = intval($recommendList[0]['sort']) + 1;
            $model->startTrans();
            $is_standard_excel = true;
            //
            //循环读取所有表,表迭代器
            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                foreach ($worksheet->getRowIterator() as $row) {
                    //行迭代器
                    $cellIterator = $row->getCellIterator();
                    $data = array(); //保存试题数据的数组
                    $media_data = array(); //保存媒体数据的数组
                    $repeat_ret = false; //题目是否重复
                    $cellIterator->setIterateOnlyExistingCells(false); //单元格为空也迭代
                    foreach ($cellIterator as $cell) {
                        //单元格迭代器
                        if (!is_null($cell)) {
                            //验证表头，验证表格是否符合标准
                            if ($cell->getRow() == 1) {
                                if ($cell->getColumn() == "A") {
                                    if (ftrim($cell->getCalculatedValue()) != "试题名称") {
                                        $is_standard_excel = false;
                                        break;
                                    }
                                } else if ($cell->getColumn() == "L") {
                                    if (ftrim($cell->getCalculatedValue()) != "视频字幕") {
                                        $is_standard_excel = false;
                                        break;
                                    }
                                }
                            } else {
                                if ($cell->getColumn() == "A") {
                                    $data['name'] = ftrim($cell->getCalculatedValue()); //名称
                                } else if ($cell->getColumn() == "B") {
                                    $media_data['voice'] = $cell->getCalculatedValue(); //语种，英音，美音
                                } else if ($cell->getColumn() == "C") {
                                    $media_data['pattern'] = $cell->getCalculatedValue(); //类型，视频，音频
                                } else if ($cell->getColumn() == "D") {
                                    $media_data['level_name'] = ftrim($cell->getCalculatedValue()); //等级
                                } else if ($cell->getColumn() == "E") {
                                    $media_data['object_name'] = ftrim($cell->getCalculatedValue()); //科目
                                } else if ($cell->getColumn() == "F") {
                                    $media_data['subject_name'] = ftrim($cell->getCalculatedValue()); //专题
                                } else if ($cell->getColumn() == "G") {
                                    $media_data['recommend'] = intval($cell->getCalculatedValue()); //推荐
                                } else if ($cell->getColumn() == "H") {
                                    $media_data['special_recommend'] = intval($cell->getCalculatedValue()); //是否特别推荐
                                } else if ($cell->getColumn() == "I") {
                                    $media_data['ted'] = intval($cell->getCalculatedValue()); //是否TED
                                } else if ($cell->getColumn() == "J") {
                                    $data['media_source_url'] = $media_data['media_source_url'] = ftrim($cell->getCalculatedValue()); //媒体内容地址
                                } else if ($cell->getColumn() == "K") {
                                    $data['sentence'] = ftrim($cell->getCalculatedValue()); //跟读句子
                                } else if ($cell->getColumn() == "L") {
                                    $media_data['caption'] = $cell->getCalculatedValue(); //媒体字幕
                                }
                            }
                        }
                    }
                    if (empty($data['name']) || $row->getRowIndex() == 1) {
                        if (false == $is_standard_excel) {
                            @unlink($dest);
                            die(json_encode(array("info" => "导入失败，表格格式错误！", "status" => false)));
                        }
                        continue;
                    }
                    //根据问题内容、视频、科目、等级以及答案内容查询是否有重复
                    /*
                      $condition['question.name'] = array("like", $data['name']);
                      $condition['media.media_source_url'] = array("like", $media_data['media_source_url']);
                      $condition['media.object'] = $object_list[$media_data['object_name']];
                      $condition['media.level'] = $level_list[$media_data['level_name']];
                      $condition['english_sentence.content'] = array("like", $data['option'][$data['answer'] - 1]);
                      $repeat_ret = $model->alias("question")
                      ->join(C("DB_PREFIX") . "english_media media on media.id=question.media_id")
                      ->join(C("DB_PREFIX") . "english_options english_options on question.answer=english_options.id")
                      ->where($condition)
                      ->count();
                      //重复则跳过
                      if (false != $repeat_ret && $repeat_ret > 0) {
                      continue;
                      }
                     */
                    $time = time();
                    //
                    //获取媒体的id
                    $media_info = $mediaModel->field("id,local_path,caption")->where(array("media_source_url" => array("like", $media_data['media_source_url'])))->find();
                    $mediaId = intval($media_info['id']);
                    //
                    //来源地址未匹配到媒体，则添加媒体
                    if ($mediaId == 0) {
                        $media_data['name'] = $data['name'];
                        $media_data['updated'] = $time;
                        $media_data['created'] = $time;
                        //等级、科目、专题的名称换成对应的id
                        $media_data['object'] = intval($object_list[$media_data['object_name']]);
                        $media_data['difficulty'] = intval($difficulty_list[$media_data['level_name']]);
                        $media_data['level'] = intval($level_list[$media_data['level_name']]);
                        $media_data['subject'] = intval($subject_list[$media_data['subject_name']]);
                        if ($media_data['special_recommend'] == 1) {
                            $media_data['recommend'] = 1;
                        }
                        //是推荐
                        if ($media_data['ted'] == 1) {
                            $ted_id = 0;
                            //专题存在
                            if ($media_data['subject'] > 0) {
                                $ted_id = $tedNameList[$media_data['subject_name']];
                                //推荐类存在专题名
                                if (intval($ted_id) == 0) {
                                    $ted_data['sort'] = $tedSort;
                                    $ted_data['name'] = $media_data['subject_name'];
                                    $ted_data['created'] = $time;
                                    $ted_data['updated'] = $time;
                                    $ted_id = $tedModel->add($ted_data);
                                    if (false === $ted_id) {
                                        $model->rollback();
                                        @unlink($dest);
                                        die(json_encode(array("info" => "导入失败，添加专题名到TED失败！", "status" => false)));
                                    }
                                    $tedNameList[$media_data['subject_name']] = $ted_id;
                                    $tedSort++;
                                }
                            } else {
                                //科目存在
                                if ($media_data['object'] > 0) {
                                    $ted_id = $tedNameList[$media_data['object_name']];
                                    //推荐类存在科目名
                                    if (intval($ted_id) == 0) {
                                        $ted_data['sort'] = $tedSort;
                                        $ted_data['name'] = $media_data['object_name'];
                                        $ted_data['created'] = $time;
                                        $ted_data['updated'] = $time;
                                        $ted_id = $tedModel->add($ted_data);
                                        if (false === $ted_id) {
                                            $model->rollback();
                                            @unlink($dest);
                                            die(json_encode(array("info" => "导入失败，添加科目名到TED失败！", "status" => false)));
                                        }
                                        $tedNameList[$media_data['object_name']] = $ted_id;
                                        $tedSort++;
                                    }
                                }
                            }
                            $media_data['ted'] = $ted_id;
                        }

                        //是推荐
                        if ($media_data['recommend'] == 1) {
                            $recommend_id = 0;
                            //专题存在
                            if ($media_data['subject'] > 0) {
                                $recommend_id = $recommendNameList[$media_data['subject_name']];
                                //推荐类存在专题名
                                if (intval($recommend_id) == 0) {
                                    $recommend_data['sort'] = $recommendSort;
                                    $recommend_data['name'] = $media_data['subject_name'];
                                    $recommend_data['created'] = $time;
                                    $recommend_data['updated'] = $time;
                                    $recommend_id = $recommendModel->add($recommend_data);
                                    if (false === $recommend_id) {
                                        $model->rollback();
                                        @unlink($dest);
                                        die(json_encode(array("info" => "导入失败，添加专题名到推荐失败！", "status" => false)));
                                    }
                                    $recommendNameList[$media_data['subject_name']] = $recommend_id;
                                    $recommendSort++;
                                }
                            } else {
                                //科目存在
                                if ($media_data['object'] > 0) {
                                    $recommend_id = $recommendNameList[$media_data['object_name']];
                                    //推荐类存在科目名
                                    if (intval($recommend_id) == 0) {
                                        $recommend_data['sort'] = $recommendSort;
                                        $recommend_data['name'] = $media_data['object_name'];
                                        $recommend_data['created'] = $time;
                                        $recommend_data['updated'] = $time;
                                        $recommend_id = $recommendModel->add($recommend_data);
                                        if (false === $recommend_id) {
                                            $model->rollback();
                                            @unlink($dest);
                                            die(json_encode(array("info" => "导入失败，添加科目名到推荐失败！", "status" => false)));
                                        }
                                        $recommendNameList[$media_data['object_name']] = $recommend_id;
                                        $recommendSort++;
                                    }
                                }
                            }
                            $media_data['recommend'] = $recommend_id;
                        }

                        $mediaId = $mediaModel->add($media_data);
                        if (false === $recommend_id) {
                            $model->rollback();
                            @unlink($dest);
                            die(json_encode(array("info" => "导入失败，添加媒体到媒体表失败！", "status" => false)));
                        }
                    } else {
                        if ($media_info['caption'] != "") {
                            $map = array("id" => $mediaId);
                            $ret = $mediaModel->where($map)->setField("caption", htmlentities($media_data['caption']));
                            if (false === $ret) {
                                $model->rollback();
                                @unlink($dest);
                                die(json_encode(array("info" => "导入失败，更新字幕到媒体表失败！", "status" => false)));
                            }
                        }
                    }
                    $data['media_id'] = intval($mediaId);
                    //没有媒体id，题目禁用
                    if ($data['media_id'] == 0) {
                        $data['status'] = 0;
                    }


                    //如果题目状态非停用，则进行视频来源是否可以解析检测 @author: slate
                    if (!isset($data['status']) || $data['status'] != 0) {
                        $supportWebsite = array(
                            'iqiyi.com' => '_iqiyi',
                            'cntv.cn' => '_cntv',
                            'qq.com' => '_qq',
                            'youku.com' => '_youku',
                            'tudou.com' => '_tudou',
                            'ku6.com' => '_ku6',
                            'sina.com.cn' => '_sina',
                            '56.com' => '_56',
                            'letv.com' => '_letv',
                            'sohu.com' => '_sohu',
                            //'ted.com' => '_ted',
                            '163.com' => '_163',
                            'umiwi.com' => '_umiwi',
                            'about.com' => '_about',
                            'videojug.com' => '_videojug',
                            'hujiang.com' => '_hujiang',
                            // 'kizphonics.com' => '_kizphonics', //
                            '1kejian.com' => '_1kejian',
                            //'britishcouncil.org' => '_britishcouncil', //
                            'ebigear.com' => '_ebigear',
                            'bbc.co.uk' => '_bbc_co',
                            'open.edu' => '_open_edu',
                            'kekenet.com' => '_kekenet',
                            'kumi.cn' => '_kumi',
                            'wimp.com' => '_wimp',
                            'youban.com' => '_youban',
                            'hujiang.com' => '_hujiang',
                            'literacycenter.net' => '_literacycenter',
                            'peepandthebigwideworld.com' => '_peepandthebigwideworld',
                            'ehow.co.uk' => '_ehow_co_uk',
                            'starfall.com' => '_starfall',
                            'kids.beva.com' => '_kids_beva',
                            //'englishcentral.com' => '_englishcentral',
                            'nationalgeographic.com' => '_nationalgeographic'
                        );
                        foreach ($supportWebsite as $k => $v) {
                            if (false !== stripos($data['media_text_url'], $k)) {
                                $data['status'] = 1;
                                break;
                            } else {
                                $data['status'] = 0;
                            }
                        }
                    }
                    $data['created'] = $time;
                    $data['updated'] = $data['created'];

                    //保存当前数据对象
                    $list = $model->add($data);
                    if ($list !== false) { //保存成功
                        if (!empty($media_data['caption'])) {
                            //插入跟读句子
                            $captions = $mediaModel->formatCaptionTextToArray($media_data['caption']);
                            $caption_num = explode(",", $data['sentence']);
                            //根据句子是否有重复，有则题目停用
                            if (count(array_unique($caption_num)) < count($caption_num)) {
                                $model->where(array("id" => $list))->setField("status", 0);
                            }
                            //
                            $datalist = array();
                            foreach ($caption_num as $value) {
                                $k = $value - 1;
                                if (!empty($captions[$k])) {
                                    $temp = array();
                                    $temp['question_id'] = $list;
                                    $temp['created'] = $time;
                                    $temp['updated'] = $time;
                                    $temp['content'] = $captions[$k]['en'] . "|" . $captions[$k]['zh'];
                                    $temp['start_time'] = intval($captions[$k]['start_time']);
                                    $temp['end_time'] = intval($captions[$k]['end_time']);
                                    array_push($datalist, $temp);
                                }
                            }
                            if (!empty($datalist)) {
                                $sentence_ret = $sentenceModel->addAll($datalist);
                                if (false === $sentence_ret) {
                                    $model->rollback();
                                    //失败提示
                                    $this->error('导入失败，保存跟读句子失败!');
                                }
                            }
                        }
                    } else {
                        $model->rollback();
                        @unlink($dest);
                        //失败提示
                        die(json_encode(array("info" => "导入失败，保存试题信息失败！", "status" => false)));
                    }
                }
            }
            $model->commit();
            @unlink($dest);
            die(json_encode(array("info" => "导入成功", "status" => true)));
        }
    }

    /**
     * 
     */
    public function export_excel() {
        $map = array();
        if (isset($_REQUEST['id'])) {
            $map['id'] = array("in", $_REQUEST['id']);
        } else {
            $param = array();
            if (method_exists($this, '_filter')) {
                $this->_filter($map, $param);
            }
        }
        $model = new EnglishQuestionSpeakViewModel();
        $ret = $model->where($map)->select();
        $mediaModel = D("EnglishMedia");
        $sentenceModel = D("EnglishQuestionSpeakSentence");
        foreach ($ret as $key => $value) {
            $sentences = $sentenceModel->where(array("question_id" => $value['id']))->order("sort asc")->select();
            $captions = $mediaModel->formatCaptionTextToArray($value['caption'], false);
            $sentences_index_arr = array();
            foreach ($sentences as $k => $v) {
                $temp = array(
                    "start_time" => intval($v['start_time']),
                    "end_time" => intval($v['end_time']),
                    "en" => $v['content']
                );
                $index = array_search($temp, $captions);
                if (false !== $index) {
                    array_push($sentences_index_arr, $index + 1);
                }
            }
            $ret[$key]["sentences"] = implode(",", $sentences_index_arr);
        }
        if (!empty($ret)) {
            //引入类
            error_reporting(E_ALL);
            date_default_timezone_set('Asia/Shanghai');

            vendor('PHPExcel.Classes.PHPExcel.IOFactory');
            @header('Content-type: text/html;charset=UTF-8');
            $path = str_replace('uploads.txt', 'Temp', realpath('./Public/Uploads/uploads.txt'));
            $objPHPExcel = new PHPExcel();
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            //指定操作的excel工作薄
            $objPHPExcel->setActiveSheetIndex(0);
            //设置表头
            $objSheet = $objPHPExcel->getActiveSheet();
            $objSheet->setCellValue("A1", "试题ID");
            $objSheet->setCellValue("B1", "试题名称");
            $objSheet->setCellValue("C1", "1表示美音， 2表示英音");
            $objSheet->setCellValue("D1", "1表示视频， 2表示音频");
            $objSheet->setCellValue("E1", "年级");
            $objSheet->setCellValue("F1", "学科");
            $objSheet->setCellValue("G1", "专题");
            $objSheet->setCellValue("H1", "推荐");
            $objSheet->setCellValue("I1", "特别推荐");
            $objSheet->setCellValue("J1", "TED");
            $objSheet->setCellValue("K1", "视频源地址");
            $objSheet->setCellValue("L1", "跟读句子");
            $objSheet->setCellValue("M1", "视频字幕");
            $objSheet->setCellValue("N1", "视频本地地址");
            //存入数据
            foreach ($ret as $k => $v) {
                $key = $k + 2;
                $objSheet->setCellValue("A" . $key, $v['id']);
                $objSheet->setCellValue("B" . $key, $v['name']);
                $objSheet->setCellValue("C" . $key, $v['voice']);
                $objSheet->setCellValue("D" . $key, $v['pattern']);
                $objSheet->setCellValue("E" . $key, $v['level_name']);
                $objSheet->setCellValue("F" . $key, $v['object_name']);
                $objSheet->setCellValue("G" . $key, $v['subject_name']);
                $objSheet->setCellValue("H" . $key, $v['recommend'] == 0 ? 0 : 1);
                $objSheet->setCellValue("I" . $key, $v['ted'] == 0 ? 0 : 1);
                $objSheet->setCellValue("J" . $key, intval($v['special_recommend']));
                $objSheet->setCellValue("K" . $key, $v['media_source_url']);
                $objSheet->setCellValue("L" . $key, $v["sentences"]);
                $objSheet->setCellValue("M" . $key, $v["caption"]);
                $objSheet->setCellValue("N" . $key, $v['local_path']);
            }
            $file_name = uniqid() . '.xls';
            if (!is_dir($path)) {
                @mkdir($path);
            }
            $objWriter->save($path . "\\" . $file_name);
            $this->ajaxReturn($file_name, "导出成功", true);
        } else {
            $this->ajaxReturn("", "导出记录为空", false);
        }
    }

    public function download_excel() {
        $file_name = $_REQUEST['filename'];
        $path = str_replace('uploads.txt', 'Temp', realpath('./Public/Uploads/uploads.txt')) . '\\';
        if (!file_exists($path . $file_name)) {
            $this->error("文件不存在");
        } else {
            $fo = fopen($path . $file_name, "rb");
            // 输入文件标签
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . filesize($path . $file_name));
            Header("Content-Disposition: attachment; filename=" . $file_name);
            // 输出文件内容
            $contents = "";
            do {
                $data = fread($fo, 8192);
                if (strlen($data) == 0) {
                    break;
                }
                $contents .= $data;
            } while (true);
            fclose($fo);
            echo $contents;
            @unlink($path . $file_name);
            exit;
        }
    }

}

?>
