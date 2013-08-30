<?php

class EnglishQuestionAction extends CommonAction {

    public function _filter(&$map, &$param) {
        if (isset($_REQUEST['name'])) {
            $name = ftrim($_REQUEST['name']);
        }
        if (intval($_REQUEST['voice']) > 0) {
            $map['englishQuestion.voice'] = intval($_REQUEST['voice']);
            $param['voice'] = intval($_REQUEST['voice']);
        }
        if (intval($_REQUEST['target']) > 0) {
            $map['englishQuestion.target'] = intval($_REQUEST['target']);
            $param['target'] = intval($_REQUEST['target']);
        }
        if (intval($_REQUEST['pattern']) > 0) {
            $map['englishMedia.pattern'] = intval($_REQUEST['pattern']);
            $param['pattern'] = intval($_REQUEST['pattern']);
        }
        if (intval($_REQUEST['object']) > 0) {
            $map['englishMedia.object'] = intval($_REQUEST['object']);
            $object_info = D("EnglishObject")->find($map['englishMedia.object']);
            if ($object_info['name'] == "综合") {
                unset($map['englishMedia.object']);
            }
            $param['object'] = intval($_REQUEST['object']);
        }
        if (intval($_REQUEST['level']) > 0) {
            $map['englishMedia.level'] = intval($_REQUEST['level']);
            $param['level'] = intval($_REQUEST['level']);
        }
        if (isset($_REQUEST['status'])) {
            if ($_REQUEST['status'] != -2) {
                $map['englishQuestion.status'] = intval($_REQUEST['status']);
            }
            $param['status'] = intval($_REQUEST['status']);
        }
        if (isset($_REQUEST['created']) && strtotime($_REQUEST['created'])) {
            $map['_string'] = "DATE_FORMAT(FROM_UNIXTIME(englishQuestion.`created`),'%Y-%m-%d')='" . $_REQUEST['created'] . "'";
            $param['created'] = $_REQUEST['created'];
        }
        if (!empty($name)) {
            $key['englishQuestion.name'] = array('like', "%" . $name . "%");
            $key['englishQuestion.content'] = array('like', "%" . $name . "%");
            $key['englishMedia.media_source_url'] = array('like', "%" . $name . "%");
            $englishOptionsModel = D("EnglishOptions");
            $option_list = $englishOptionsModel->where("`content` like '%{$name}%'")->group("question_id")->select();
            if (!empty($option_list)) {
                foreach ($option_list as $value) {
                    $question_id[] = $value['question_id'];
                }
                $question_id[] = 0;
                $key['englishQuestion.id'] = array('in', $question_id);
            }
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
        $model = new EnglishQuestionViewModel();
        if (!empty($model)) {
            $this->_list($model, $map, $param, 'id', false);
            //lTrace('Log/lastSql', $this->getActionName(), $model->getLastSql());
        }
        //科目列表
        $object_list = D("EnglishObject")->getList("status=1");
        $this->assign("object_list", $object_list);
        //科目列表
        $level_list = D("EnglishLevel")->getList("status=1");
        $num = intval(D("Variable")->getVariable("english_click_num"));
        $this->assign("english_click_num", $num);
        $this->assign("level_list", $level_list);
        $this->assign("param", $param);
        foreach ($param as $key => $value) {
            $param_str.=$key . "=" . $value . "&";
        }
        $this->assign("param_str", $param_str);
        $this->display();
        return;
    }

    public function add() {
        $object_list = D("EnglishObject")->where("`status`=1")->order("sort")->select();
        $this->assign("object_list", $object_list);
        $level_list = D("EnglishLevel")->where("`status`=1")->order("sort")->select();
        $this->assign("level_list", $level_list);

        $this->display();
    }

    public function insert() {
        $model = D("EnglishQuestion");
        $answer = intval($_POST['answer']);
        $optionModel = D("EnglishOptions");
        $model->startTrans();

        //判断题目是否为判断题
        $is_double_true = false; //是否为True文字选项
        $is_double_false = false; //是否为False文字选项
        foreach ($_POST['option'] as $key => $value) {
            if (preg_match("/True/i", $value)) {
                $is_double_true = true;
                continue;
            }
            if (preg_match("/False/i", $value)) {
                $is_double_false = true;
                continue;
            }
        }
        $option_id = array();
        //依次存入选项，不知道问题id
        $option_data['created'] = time();
        $index = 1;
        foreach ($_POST['option'] as $key => $value) {
            if (!empty($value)) {
                $d_1 = preg_match("/all\sof\sthe\sabove.?/i", $value);
                $d_2 = preg_match("/none\sof\sthe\sabove.?/i", $value);
                $d_3 = preg_match("/either\sB\sor\sC.?/i", $value);
                $d_4 = preg_match("/(both\s)?B\sand\sC.?/i", $value);
                $c_1 = preg_match("/(both\sA)?\sand\sB.?/i", $value);
                $c_2 = preg_match("/either\sA\sor\sB.?/i", $value);
                $option_data['content'] = $value;
                if ($d_1 || $d_2 || $d_3 || $d_4) {
                    $option_data['sort'] = 4; //D
                } else if ($c_1 || $c_2) {
                    $option_data['sort'] = 3; //C
                } else {
                    $option_data['sort'] = $index; //除去特殊项目，其他自动顶上
                    $index++;
                }
                $ret = $optionModel->add($option_data);
                if (false === $ret) {
                    $model->rollback();
                    $this->error("添加失败");
                }
                array_push($option_id, $ret); //保存增加的id数组，用于更新选项对应的问题id
            }
        }
        if (false === $model->create()) {
            $model->rollback();
            $this->error($model->getError());
        }
        //
        //根据媒体来源或媒体名称对应到媒体id
        $mediaId = 0;
        $mediaModel = D("EnglishMedia");
        if (!empty($_REQUEST['media_source_url'])) {
            $this->media_text_url = $_REQUEST['media_source_url'];
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
        //选项是否有重复，有则题目停用
        if ($model->status == 1 && count(array_unique($_POST['option'])) < count($_POST['option']) && !($is_double_false && $is_double_true)) {
            $model->status = 0;
        }

        $model->answer = $option_id[$answer - 1];
        if ($model->status == 1 && $answer <= 0 || empty($option_id) || empty($option_id[$answer - 1])) {
            $model->status = 0;
            $model->answer = 0;
        }
        //保存当前数据对象
        $list = $model->add();
        if ($list !== false) { //保存成功
            if (false === $optionModel->where("id in (" . implode(",", $option_id) . ")")->setField("question_id", $list)) {
                $model->rollback();
                $this->error('新增失败2!');
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
//        $vo = $model->getById($id);
        $vo = $model->alias("question")->field("question.*,media.name as media_name,media.media_source_url as media_source_url")->join(C("DB_PREFIX") . "english_media media on media.id=question.media_id")->where("question.id=" . intval($id))->find();
        $option_list = D("EnglishOptions")->getQuestionOptionList($id);
        $this->assign('option_list', $option_list);
        $this->assign('vo', $vo);
        $this->assign('doubleQuotes', '"');

        $object_list = D("EnglishObject")->where("`status`=1")->order("sort")->select();
        $this->assign("object_list", $object_list);
        $level_list = D("EnglishLevel")->where("`status`=1")->order("sort")->select();
        $this->assign("level_list", $level_list);

        $this->display();
    }

    public function update() {
        $answer = intval($_POST['answer']);
        $optionModel = D("EnglishOptions");
        $id = intval($_REQUEST['id']);
        $optionModel->startTrans();
        //删除选项
        $map['question_id'] = $id;
        $optionModel->where($map)->delete();


        //判断题目是否为判断题
        $is_double_true = false; //是否为True文字选项
        $is_double_false = false; //是否为False文字选项
        foreach ($_POST['option'] as $key => $value) {
            if (preg_match("/True/i", $value)) {
                $is_double_true = true;
                continue;
            }
            if (preg_match("/False/i", $value)) {
                $is_double_false = true;
                continue;
            }
        }
        //
        //依次存入选项，不知道问题id
        $option_data['question_id'] = $id;
        $option_data['created'] = time();
        $answer_id = 0;
        $index = 1;
        foreach ($_POST['option'] as $key => $value) {
            if (!empty($value)) {
                $d_1 = preg_match("/all\sof\sthe\sabove.?/i", $value);
                $d_2 = preg_match("/none\sof\sthe\sabove.?/i", $value);
                $d_3 = preg_match("/either\sB\sor\sC.?/i", $value);
                $d_4 = preg_match("/(both\s)?B\sand\sC.?/i", $value);
                $c_1 = preg_match("/(both\s)?A\sand\sB.?/i", $value);
                $c_2 = preg_match("/either\sA\sor\sB.?/i", $value);
                $option_data['content'] = $value;
                if ($d_1 || $d_2 || $d_3 || $d_4) {
                    $option_data['sort'] = 4; //D
                } else if ($c_1 || $c_2) {
                    $option_data['sort'] = 3; //C
                } else {
                    $option_data['sort'] = $index; //除去特殊项目，其他自动顶上
                    $index++;
                }
                $ret = $optionModel->add($option_data);
                if (false === $ret) {
                    $optionModel->rollback();
                    $this->error("编辑失败");
                }
                if ($answer == ($key + 1)) {
                    $answer_id = $ret;
                }
            }
        }
        $model = D("EnglishQuestion");
        if (false === $model->create()) {
            $model->rollback();
            $this->error($model->getError());
        }
        //
        //根据媒体来源或媒体名称对应到媒体id
        $mediaId = 0;
        $mediaModel = D("EnglishMedia");
        if (!empty($_REQUEST['media_source_url'])) {
            $this->media_text_url = ftrim($_REQUEST['media_source_url']);
            $mediaMap = array();
            $mediaMap['media_source_url'] = $_REQUEST['media_source_url'];
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
        //选项是否有重复，有则题目停用
        if ($model->status == 1 && count(array_unique($_POST['option'])) < count($_POST['option']) && !($is_double_false && $is_double_true)) {
            $model->status = 0;
        }
        $model->answer = $answer_id;
        if ($model->status == 1 && ($answer <= 0 || $answer_id == 0)) {
            $model->status = 0;
            $model->answer = 0;
        }
        //保存当前数据对象
        if (false === $model->save()) {
            $model->rollback();
            $this->error('编辑失败！');
        }
        $model->commit();
        $this->success("编辑成功！");
    }

    public function upload() {
        import("@.ORG.UploadFile");
        $upload = new UploadFile();
        $upload->maxSize = 1044528000; // 设置附件上传大小
        $upload->allowExts = array('mp4', 'rm', 'rmvb', 'flv', 'avi', 'mp3', 'wam'); // 设置附件上传类型
        $upload->saveRule = time();
        $upload->savePath = './Public/Uploads/Temp/'; // 设置附件上传目录
        if (!$upload->upload()) {// 上传错误提示错误信息
            $this->ajaxReturn("", $upload->getErrorMsg(), false);
        } else {// 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            $this->ajaxReturn($info, "上传成功", true);
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
            $model = D("EnglishQuestion");
            $optionModel = D("EnglishOptions");
            $levelModel = D("EnglishLevel");
            $mediaModel = D("EnglishMedia");
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
            //
            //读取推荐分类列表
            $recommendModel = D("EnglishMediaRecommend");
            $recommends = $recommendModel->select();
            foreach ($recommends as $key => $value) {
                $recommend_list[$value['name']] = $value['id'];
            }
            $model->startTrans();
            //
            //循环读取所有表,表迭代器
            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                foreach ($worksheet->getRowIterator() as $row) {
                    //行迭代器
                    $cellIterator = $row->getCellIterator();
                    $data = array(); //暂时保存数据的数组
                    $media_data = array();
                    $repeat_ret = false; //题目是否重复
                    $cellIterator->setIterateOnlyExistingCells(false); //单元格为空也迭代
                    foreach ($cellIterator as $cell) {
                        //单元格迭代器
                        if (!is_null($cell)) {
                            if ($cell->getColumn() == "A") {
                                $data['name'] = ftrim($cell->getCalculatedValue()); //名称
                            } else if ($cell->getColumn() == "B") {
                                $media_data['voice'] = $cell->getCalculatedValue(); //语种，英音，美音
                            } else if ($cell->getColumn() == "C") {
                                $media_data['pattern'] = $cell->getCalculatedValue(); //类型，视频，音频
                            } else if ($cell->getColumn() == "D") {
                                $data['target'] = $cell->getCalculatedValue(); //目标，听力，说力
                            } else if ($cell->getColumn() == "E") {
                                $media_data['level'] = ftrim($cell->getCalculatedValue()); //等级
                            } else if ($cell->getColumn() == "F") {
                                $media_data['object'] = ftrim($cell->getCalculatedValue()); //科目
                            } else if ($cell->getColumn() == "G") {
                                $media_data['subject'] = ftrim($cell->getCalculatedValue()); //专题
                            } else if ($cell->getColumn() == "H") {
                                //$media_data['difficulty'] = intval($cell->getCalculatedValue()); //难度
                                $media_data['recommend'] = ftrim($cell->getCalculatedValue()); //推荐
                            } else if ($cell->getColumn() == "I") {
                                $media_data['special_recommend'] = intval($cell->getCalculatedValue()); //是否特别推荐
                            } else if ($cell->getColumn() == "J") {
                                $data['media_text_url'] = $media_data['media_source_url'] = ftrim($cell->getCalculatedValue()); //媒体内容地址
                            } else if ($cell->getColumn() == "K") {
                                $data['content'] = ftrim($cell->getCalculatedValue()); //题目内容
                            } else if ($cell->getColumn() == "L") {
                                $data['answer'] = ftrim($cell->getCalculatedValue()); //题目答案
                            } else if ($cell->getColumn() == "M") {
                                $data['option'][0] = ftrim($cell->getCalculatedValue()); //题目选项一
                            } else if ($cell->getColumn() == "N") {
                                $data['option'][1] = ftrim($cell->getCalculatedValue()); //题目选项二
                            } else if ($cell->getColumn() == "O") {
                                $data['option'][2] = ftrim($cell->getCalculatedValue()); //题目选项三
                            } else if ($cell->getColumn() == "P") {
                                $data['option'][3] = ftrim($cell->getCalculatedValue()); //题目选项四
                            }
                        }
                    }
                    if (empty($data['name']) || $data['name'] == "试题名称") {
                        continue;
                    }
                    $time = time();
                    //
                    //获取媒体的id
                    $mediaId = intval($mediaModel->where("media_source_url='" . $media_data['media_source_url'] . "'")->getField("id"));
                    //
                    //来源地址未匹配到媒体，则添加媒体
                    if ($mediaId == 0) {
                        $media_data['name'] = $data['name'];
                        $media_data['updated'] = $time;
                        $media_data['created'] = $time;
                        //等级、科目、专题的名称换成对应的id
                        $media_data['object'] = intval($object_list[$media_data['object']]);
                        $media_data['difficulty'] = intval($difficulty_list[$media_data['level']]);
                        $media_data['level'] = intval($level_list[$media_data['level']]);
                        $media_data['subject'] = intval($subject_list[$media_data['subject']]);
                        $media_data['recommend'] = intval($recommend_list[$media_data['recommend']]);
                        $mediaId = $mediaModel->add($media_data);
                    }
                    $data['media_id'] = intval($mediaId);
                    //没有媒体id，题目禁用
                    if ($data['media_id'] == 0) {
                        $data['status'] = 0;
                    }
                    //根据问题内容、视频、科目、等级以及答案内容查询是否有重复
                    $condition['question.content'] = array("like", $data['content']);
                    $condition['media.media_source_url'] = array("like", $media_data['media_source_url']);
                    $condition['media.object'] = $object_list[$media_data['object']];
                    $condition['media.level'] = $level_list[$media_data['level']];
                    $condition['english_options.content'] = array("like", $data['option'][$data['answer'] - 1]);
                    $repeat_ret = $model->alias("question")
                            ->join(C("DB_PREFIX") . "english_media media on media.id=question.media_id")
                            ->join(C("DB_PREFIX") . "english_options english_options on question.answer=english_options.id")
                            ->where($condition)
                            ->count();
                    //重复则跳过
                    if (false != $repeat_ret && $repeat_ret > 0) {
                        continue;
//                        $optionModel->where("question_id=" . intval($repeat_ret['id']))->delete();
                    }
                    //插入答案
                    $option_id = array();
                    //判断题目是否是判断题
                    $is_double_true = false; //是否为True文字选项
                    $is_double_false = false; //是否为False文字选项
                    foreach ($data['option'] as $key => $value) {
                        if (preg_match("/True.?/i", $value)) {
                            $is_double_true = true;
                        }
                        if (preg_match("/False.?/i", $value)) {
                            $is_double_false = true;
                        }
                    }
                    //选项是否有重复，有则题目停用
                    if (count(array_unique($data['option'])) < count($data['option']) && !($is_double_false && $is_double_true)) {
                        $data['status'] = 0;
                    }
                    //
                    //依次存入选项，不知道问题id
                    $option_data['created'] = $time;
                    $index = 1;
                    foreach ($data['option'] as $key => $value) {
                        if (!empty($value)) {
                            $d_1 = preg_match("/all\sof\sthe\sabove.?/i", $value);
                            $d_2 = preg_match("/none\sof\sthe\sabove.?/i", $value);
                            $d_3 = preg_match("/either\sB\sor\sC.?/i", $value);
                            $d_4 = preg_match("/(both\s)?B\sand\sC.?/i", $value);
                            $c_1 = preg_match("/(both\s)?A\sand\sB.?/i", $value);
                            $c_2 = preg_match("/either\sA\sor\sB.?/i", $value);
                            $option_data['content'] = $value;
                            if ($d_1 || $d_2 || $d_3 || $d_4) {
                                $option_data['sort'] = 4; //D
                            } else if ($c_1 || $c_2) {
                                $option_data['sort'] = 3; //C
                            } else {
                                $option_data['sort'] = $index; //除去特殊项目，其他自动顶上
                                $index++;
                            }

                            $ret = $optionModel->add($option_data);
                            if (false === $ret) {
                                $model->rollback();
                                die(json_encode(array("info" => "导入失败", "status" => false)));
                            }
                            array_push($option_id, $ret); //保存增加的id数组，用于更新选项对应的问题id
                        }
                    }
                    //答案id
                    $data['answer'] = $option_id[$data['answer'] - 1];
                    if ($data['answer'] == 0 || (count($option_id) < 4 && !($is_double_false && $is_double_true))) {
                        $data['status'] = 0;
                        $data['answer'] = 0;
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
                            'ted.com' => '_ted',
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

//                    if ($repeat_ret) {
//                        $list = $model->where("id=" . intval($repeat_ret['id']))->save($data);
//                    } else {
                    //保存当前数据对象
                    $list = $model->add($data);
//                    }
                    if ($list !== false) { //保存成功
                        if (!empty($option_id)) {
//                            if ($repeat_ret) {
//                                $list = $repeat_ret['id'];
//                            }
                            if (false === $optionModel->where("id in (" . implode(",", $option_id) . ")")->setField("question_id", $list)) {
                                //更新答案对应的题目id
                                $model->rollback();
                                die(json_encode(array("info" => "导入失败", "status" => false)));
                            }
                        }
                    } else {
                        $model->rollback();
                        //失败提示
                        die(json_encode(array("info" => "导入失败", "status" => false)));
                    }
                }
            }
            $model->commit();
            die(json_encode(array("info" => "导入成功", "status" => true)));
        }
    }

    /**
     * 英语角视频题库修复
     * 
     * @author slate date: 2013-7-13
     */
    public function fix_video() {

        //本次修复为视频无法播放题库修复为停用
        set_time_limit(1000);
        $englishQuestionModel = new EnglishQuestionModel();
        $questionList = $englishQuestionModel->where("`status` = 1 AND `media_url` = ''")->getField('`id`, `media_text_url`');

        foreach ($questionList as $id => $url) {
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
                'ted.com' => '_ted',
                '163.com' => '_163',
                'umiwi.com' => '_umiwi',
                'about.com' => '_about',
                'videojug.com' => '_videojug',
                'hujiang.com' => '_hujiang',
                'kizphonics.com' => '_kizphonics', //
                '1kejian.com' => '_1kejian',
                'britishcouncil.org' => '_britishcouncil', //
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
                if (false !== stripos($url, $k)) {
                    $status = 1;
                    break;
                } else {
                    $status = 0;
                }
            }

            $englishQuestionModel->where("`id` = $id")->save(array('status' => $status));
        }

        echo 'fix end!';
        exit();
    }

    public function match_media() {

        set_time_limit(0);

        import("@.ORG.VideoHooks");

        $englishQuestionModel = new EnglishQuestionModel();
        $questionList = $englishQuestionModel->where("`media_url` = '' and media_text_url  like '%videojug.com%'")->getField('`id`, `media_text_url`');

        $videoHooks = new VideoHooks();

        $startTime = time();

        foreach ($questionList as $id => $url) {

            $url = trim(str_replace(' ', '', $url));
            $videoInfo = $videoHooks->analyzer($url);

            $media_url = $videoInfo['swf'];

            $media_img_url = $videoInfo['img'];

            $media_type = $videoInfo['media_type'];

            //解析成功，保存视频解析地址
            if ($media_url) {

                $saveData = array(
                    'media_img_url' => $media_img_url,
                    'media_type' => $media_type
                );

                if ($media_type) {

                    $saveData['media'] = $media_url;
                } else {

                    $saveData['media_url'] = $media_url;
                }

                $englishQuestionModel->where("id=$id")->save($saveData);
            } else {
                //if ($videoHooks->getError()) {
                var_dump($id, $url);
                var_dump($videoInfo);
                var_dump($videoHooks->getError());
                //$englishQuestionModel->where("id=$id")->save(array('media' => 'error'));
                //exit();
                //}
            }
        }

        $endTime = time();

        echo 'start:' . date('Y-m-d H:i:s', $startTime) . '</br>';
        echo 'end:' . date('Y-m-d H:i:s', $endTime);
        exit();
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
        $model = new EnglishQuestionViewModel();
        $ret = $model->where($map)->select();
        $optionsModel = D("EnglishOptions");
        foreach ($ret as $key => $value) {
            $options = $optionsModel->where("question_id=" . $value['id'])->order("sort asc")->select();
            foreach ($options as $k => $v) {
                if ($v['id'] == $value['answer']) {
                    $ret[$key]['answer_index'] = $k + 1;
                }
                $ret[$key]['option'][$k] = $v['content'];
            }
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
            $objSheet->setCellValue("A1", "试题名称");
            $objSheet->setCellValue("B1", "1表示美音， 2表示英音");
            $objSheet->setCellValue("C1", "1表示视频， 2表示音频");
            $objSheet->setCellValue("D1", "1表示听力， 2表示说力");
            $objSheet->setCellValue("E1", "年级");
            $objSheet->setCellValue("F1", "学科");
            $objSheet->setCellValue("G1", "查看文本所在的外链地址页面");
            $objSheet->setCellValue("H1", "问题");
            $objSheet->setCellValue("I1", "正确答案序号，1对应A ，2对应B等");
            $objSheet->setCellValue("J1", "选项A内容");
            $objSheet->setCellValue("K1", "选项B内容");
            $objSheet->setCellValue("L1", "选项C内容");
            $objSheet->setCellValue("M1", "选项D内容");
            $objSheet->setCellValue("N1", "视频或音频对应的文本");
            //存入数据
            foreach ($ret as $k => $v) {
                $key = $k + 2;
                $objSheet->setCellValue("A" . $key, $v['name']);
                $objSheet->setCellValue("B" . $key, $v['voice']);
                $objSheet->setCellValue("C" . $key, $v['pattern']);
                $objSheet->setCellValue("D" . $key, $v['target']);
                $objSheet->setCellValue("E" . $key, $v['level_name']);
                $objSheet->setCellValue("F" . $key, $v['object_name']);
                $objSheet->setCellValue("G" . $key, $v['media_text_url']);
                $objSheet->setCellValue("H" . $key, $v['content']);
                $objSheet->setCellValue("I" . $key, intval($v['answer_index']));
                $objSheet->setCellValue("J" . $key, $v['option'][0]);
                $objSheet->setCellValue("K" . $key, $v['option'][1]);
                $objSheet->setCellValue("L" . $key, $v['option'][2]);
                $objSheet->setCellValue("M" . $key, $v['option'][3]);
                $objSheet->setCellValue("N" . $key, $v['media_text']);
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
