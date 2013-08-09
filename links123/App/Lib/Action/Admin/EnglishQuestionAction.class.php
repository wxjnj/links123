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
            $map['englishQuestion.pattern'] = intval($_REQUEST['pattern']);
            $param['pattern'] = intval($_REQUEST['pattern']);
        }
        if (intval($_REQUEST['object']) > 0) {
            $map['englishQuestion.object'] = intval($_REQUEST['object']);
            $object_info = D("EnglishObject")->find($map['englishQuestion.object']);
            if ($object_info['name'] == "综合") {
                unset($map['englishQuestion.object']);
            }
            $param['object'] = intval($_REQUEST['object']);
        }
        if (intval($_REQUEST['level']) > 0) {
            $map['englishQuestion.level'] = intval($_REQUEST['level']);
            $param['level'] = intval($_REQUEST['level']);
        }
        if (isset($_REQUEST['status'])) {
            if ($_REQUEST['status'] != -2) {
                $map['englishQuestion.status'] = intval($_REQUEST['status']);
            }
            $param['status'] = intval($_REQUEST['status']);
        }
        if (isset($_REQUEST['created']) && strtotime($_REQUEST['created'])) {
            $map['_string'] = "DATE_FORMAT(FROM_UNIXTIME(`created`),'%Y-%m-%d')='" . $_REQUEST['created'] . "'";
            $param['created'] = $_REQUEST['created'];
        }
        if (!empty($name)) {
            $key['englishQuestion.name'] = array('like', "%" . $name . "%");
            $key['englishQuestion.content'] = array('like', "%" . $name . "%");
            $key['englishQuestion.media_text_url'] = array('like', "%" . $name . "%");
            $englishOptionsModel = D("EnglishOptions");
            $option_list = $englishOptionsModel->where("`content` like '%{$name}%'")->group("question_id")->select();
            $question_id[0] = 0;
            foreach ($option_list as $value) {
                $question_id[] = $value['question_id'];
            }
            $key['englishQuestion.id'] = array('in', $question_id);
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
        //记住用户本次的选择
        cookie("admin_english_object", intval($_REQUEST['object']));
        cookie("admin_english_level", intval($_REQUEST['level']));
        $model = D("EnglishQuestion");
//        if (empty($_POST['option']) || empty($_POST['option'][0]) || empty($_POST['option'][1]) || empty($_POST['option'][2]) || empty($_POST['option'][3])) {
//            $this->error("四个选项都不能为空！");
//        }
        $answer = intval($_POST['answer']);
//        if ($answer <= 0) {
//            $this->error("请选择正确答案！");
//        }
        $optionModel = D("EnglishOptions");
        $model->startTrans();

        //判断题目是否需要随机打乱，存在一些规则无法随机
        $is_rand = true;
        $is_double_true = false; //是否为True文字选项
        $is_double_false = false; //是否为False文字选项
        foreach ($_POST['option'] as $key => $value) {
            //不能随机打乱的判断
            $d_1 = preg_match("/all\sof\sthe\sabove.?/i", $value);
            $d_2 = preg_match("/none\sof\sthe\sabove.?/i", $value);
            $d_3 = preg_match("/either\sB\sor\sC.?/i", $value);
            $d_4 = preg_match("/both\sB\sand\sC.?/i", $value);
            $c_1 = preg_match("/both\sA\sand\sB.?/i", $value);
            $c_2 = preg_match("/either\sA\sor\sB.?/i", $value);
            if (preg_match("/True/i", $value)) {
                $is_double_true = true;
            }
            if (preg_match("/False/i", $value)) {
                $is_double_false = true;
            }
            if ($d_1 || $d_2 || $d_3 || $d_4 || $c_1 || $c_2 || ($is_double_false && $is_double_true)) {
                $is_rand = false;
                break;
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
                $d_4 = preg_match("/both\sB\sand\sC.?/i", $value);
                $c_1 = preg_match("/both\sA\sand\sB.?/i", $value);
                $c_2 = preg_match("/either\sA\sor\sB.?/i", $value);
                $option_data['content'] = $value;
                if ($is_rand) {
                    $option_data['sort'] = rand(1, 4); //随机打乱选项
                } else {
                    if ($d_1 || $d_2 || $d_3 || $d_4) {
                        $option_data['sort'] = 4; //D
                    } else if ($c_1 || $c_2) {
                        $option_data['sort'] = 3; //C
                    } else {
                        $option_data['sort'] = $index; //除去特殊项目，其他自动顶上
                        $index++;
                    }
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
        //选项是否有重复，有则题目停用
        if (count(array_unique($_POST['option'])) < count($_POST['option']) && !($is_double_false && $is_double_true)) {
            $model->status = 0;
        }

        if (!empty($_POST['media_local_url'])) {
            if (false === @copy("./Public/Uploads/Temp/" . $_POST['media_local_url'], "./Public/Uploads/English/" . $_POST['media_local_url'])) {
                $model->rollback();
                $this->error('新增失败1!');
            }
            @unlink("./Public/Uploads/Temp/" . $_POST['media_local_url']);
            $model->media_local_url = $_POST['media_local_url'];
        }
        $model->answer = $option_id[$answer - 1];
        if ($answer <= 0 || empty($option_id) || empty($option_id[$answer - 1])) {
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
        $vo = $model->getById($id);
        $option_list = D("EnglishOptions")->getQuestionOptionList($id);
        $this->assign('option_list', $option_list);
        $this->assign('vo', $vo);

        $object_list = D("EnglishObject")->where("`status`=1")->order("sort")->select();
        $this->assign("object_list", $object_list);
        $level_list = D("EnglishLevel")->where("`status`=1")->order("sort")->select();
        $this->assign("level_list", $level_list);

        $this->display();
    }

    public function update() {
        //记住用户本次的选择
        cookie("admin_english_object", intval($_REQUEST['object']));
        cookie("admin_english_level", intval($_REQUEST['level']));
//        if (empty($_POST['option']) || empty($_POST['option'][0]) || empty($_POST['option'][1]) || empty($_POST['option'][2]) || empty($_POST['option'][3])) {
//            $this->error("四个选项都不能为空！");
//        }
        $answer = intval($_POST['answer']);
//        if ($answer <= 0) {
//            $this->error("请选择正确答案！");
//        }
        $optionModel = D("EnglishOptions");
        $id = intval($_REQUEST['id']);
        $optionModel->startTrans();
        //删除选项
        $optionModel->where("question_id={$id}")->delete();

        //判断题目是否需要随机打乱，存在一些规则无法随机
        $is_rand = true;
        $is_double_true = false; //是否为True文字选项
        $is_double_false = false; //是否为False文字选项
        foreach ($_POST['option'] as $key => $value) {
            //不能随机打乱的判断
            $d_1 = preg_match("/all\sof\sthe\sabove.?/i", $value);
            $d_2 = preg_match("/none\sof\sthe\sabove.?/i", $value);
            $d_3 = preg_match("/either\sB\sor\sC.?/i", $value);
            $d_4 = preg_match("/both\sB\sand\sC.?/i", $value);
            $c_1 = preg_match("/both\sA\sand\sB.?/i", $value);
            $c_2 = preg_match("/either\sA\sor\sB.?/i", $value);
            if (preg_match("/True/i", $value)) {
                $is_double_true = true;
            }
            if (preg_match("/False/i", $value)) {
                $is_double_false = true;
            }
            if ($d_1 || $d_2 || $d_3 || $d_4 || $c_1 || $c_2 || ($is_double_false && $is_double_true)) {
                $is_rand = false;
                break;
            }
        }
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
                $d_4 = preg_match("/both\sB\sand\sC.?/i", $value);
                $c_1 = preg_match("/both\sA\sand\sB.?/i", $value);
                $c_2 = preg_match("/either\sA\sor\sB.?/i", $value);
                $option_data['content'] = $value;
                if ($is_rand) {
                    $option_data['sort'] = rand(1, 4); //随机打乱选项
                } else {
                    if ($d_1 || $d_2 || $d_3 || $d_4) {
                        $option_data['sort'] = 4; //D
                    } else if ($c_1 || $c_2) {
                        $option_data['sort'] = 3; //C
                    } else {
                        $option_data['sort'] = $index; //除去特殊项目，其他自动顶上
                        $index++;
                    }
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
        //选项是否有重复，有则题目停用
        if ($model->status == 1 && count(array_unique($_POST['option'])) < count($_POST['option']) && !($is_double_false && $is_double_true)) {
            $model->status = 0;
        }
        if (!empty($_POST['media_local_url'])) {
//            $ext = substr($_POST['media_local_url'], strpos($_POST['media_local_url'], "."));
            if (false === @copy("./Public/Uploads/Temp/" . $_POST['media_local_url'], "./Public/Uploads/English/" . $_POST['media_local_url'])) {
                $model->rollback();
                $this->error('编辑失败!');
            }
            @unlink("./Public/Uploads/English/" . $_POST['media_local_url_old']);
            @unlink("./Public/Uploads/Temp/" . $_POST['media_local_url']);
            $model->media_local_url = $_POST['media_local_url'];
        } else {
            $model->media_local_url = $_POST['media_local_url_old'];
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
            //读取excel;
            if ($uploadList[0]['extension'] == "xls") {
                $objReader = PHPExcel_IOFactory::createReader('Excel5');
            } else {
                $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            }
            $path = realpath('./Public/Uploads/uploads.txt');
            $dest = str_replace('uploads.txt', 'Excels/' . $uploadList[0]['savename'], $path);

            $objPHPExcel = $objReader->load($dest);
            $model = D("EnglishQuestion");
            $optionModel = D("EnglishOptions");
            $levelModel = D("EnglishLevel");
            //提取等级列表
            $levels = $levelModel->select();
            foreach ($levels as $key => $value) {
                $level_list[$value['name']] = $value['id'];
            }
            //读取科目列表
            $objectModel = D("EnglishObject");
            $objects = $objectModel->select();
            foreach ($objects as $key => $value) {
                $object_list[$value['name']] = $value['id'];
            }
            $model->startTrans();
            //循环读取所有表,表迭代器
            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                foreach ($worksheet->getRowIterator() as $row) {
                    //行迭代器
                    $cellIterator = $row->getCellIterator();
                    $data = array(); //暂时保存数据的数组
                    $repeat_ret = false; //题目是否重复
                    $cellIterator->setIterateOnlyExistingCells(false); //单元格为空也迭代
                    foreach ($cellIterator as $cell) {
                        //单元格迭代器
                        if (!is_null($cell)) {
                            if ($cell->getColumn() == "A") {
                                $data['name'] = ftrim($cell->getCalculatedValue()); //名称
                            } else if ($cell->getColumn() == "B") {
                                $data['voice'] = $cell->getCalculatedValue(); //语种，英音，美音
                            } else if ($cell->getColumn() == "C") {
                                $data['pattern'] = $cell->getCalculatedValue(); //类型，视频，音频
                            } else if ($cell->getColumn() == "D") {
                                $data['target'] = $cell->getCalculatedValue(); //目标，听力，说力
                            } else if ($cell->getColumn() == "E") {
                                $data['level'] = ftrim($cell->getCalculatedValue()); //等级名称
                            } else if ($cell->getColumn() == "F") {
                                $data['object'] = ftrim($cell->getCalculatedValue()); //科目名称
                            } else if ($cell->getColumn() == "G") {
                                $data['media_text_url'] = ftrim($cell->getCalculatedValue()); //媒体内容地址
                            } else if ($cell->getColumn() == "H") {
                                $data['content'] = ftrim($cell->getCalculatedValue()); //题目内容
                            } else if ($cell->getColumn() == "I") {
                                $data['answer'] = ftrim($cell->getCalculatedValue()); //题目答案
                            } else if ($cell->getColumn() == "J") {
                                $data['option'][0] = ftrim($cell->getCalculatedValue()); //题目选项一
                            } else if ($cell->getColumn() == "K") {
                                $data['option'][1] = ftrim($cell->getCalculatedValue()); //题目选项二
                            } else if ($cell->getColumn() == "L") {
                                $data['option'][2] = ftrim($cell->getCalculatedValue()); //题目选项三
                            } else if ($cell->getColumn() == "M") {
                                $data['option'][3] = ftrim($cell->getCalculatedValue()); //题目选项四
                            } else if ($cell->getColumn() == "N") {
                                $data['media_text'] = ftrim($cell->getCalculatedValue()); //媒体内容
                            }
                        }
                    }
                    if (empty($data['name']) || $data['name'] == "试题名称") {
                        continue;
                    }
                    $condition['content'] =  $data['content'];
                    $repeat_ret = $model->where($condition)->find(); //根据问题内容查询是否有重复
                    //重复条件下删除原选项
                    if ($repeat_ret) {
                        $optionModel->where("question_id=" . intval($repeat_ret['id']))->delete();
                    }
                    //插入答案
                    $option_id = array();
                    //判断题目是否需要随机打乱，存在一些规则无法随机
                    $is_rand = true;
                    $is_double_true = false; //是否为True文字选项
                    $is_double_false = false; //是否为False文字选项
                    foreach ($data['option'] as $key => $value) {
                        //不能随机打乱的判断
                        $d_1 = preg_match("/all\sof\sthe\sabove.?/i", $value);
                        $d_2 = preg_match("/none\sof\sthe\sabove.?/i", $value);
                        $d_3 = preg_match("/either\sB\sor\sC.?/i", $value);
                        $d_4 = preg_match("/both\sB\sand\sC.?/i", $value);
                        $c_1 = preg_match("/both\sA\sand\sB.?/i", $value);
                        $c_2 = preg_match("/either\sA\sor\sB.?/i", $value);
                        if (preg_match("/True.?/i", $value)) {
                            $is_double_true = true;
                        }
                        if (preg_match("/False.?/i", $value)) {
                            $is_double_false = true;
                        }
                        if ($d_1 || $d_2 || $d_3 || $d_4 || $c_1 || $c_2 || ($is_double_false && $is_double_true)) {
                            $is_rand = false;
                            break;
                        }
                    }
                    //选项是否有重复，有则题目停用
                    if (count(array_unique($data['option'])) < count($data['option']) && !($is_double_false && $is_double_true)) {
                        $data['status'] = 0;
                    }
                    //依次存入选项，不知道问题id
                    $option_data['created'] = time();
                    $index = 1;
                    foreach ($data['option'] as $key => $value) {
                        if (!empty($value)) {
                            $d_1 = preg_match("/all\sof\sthe\sabove.?/i", $value);
                            $d_2 = preg_match("/none\sof\sthe\sabove.?/i", $value);
                            $d_3 = preg_match("/either\sB\sor\sC.?/i", $value);
                            $d_4 = preg_match("/both\sB\sand\sC.?/i", $value);
                            $c_1 = preg_match("/both\sA\sand\sB.?/i", $value);
                            $c_2 = preg_match("/either\sA\sor\sB.?/i", $value);
                            $option_data['content'] = $value;
                            if ($is_rand) {
                                $option_data['sort'] = rand(1, 4); //随机打乱选项
                            } else {
                                if ($d_1 || $d_2 || $d_3 || $d_4) {
                                    $option_data['sort'] = 4; //D
                                } else if ($c_1 || $c_2) {
                                    $option_data['sort'] = 3; //C
                                } else {
                                    $option_data['sort'] = $index; //除去特殊项目，其他自动顶上
                                    $index++;
                                }
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
                    //等级和科目
                    $data['object'] = intval($object_list[$data['object']]);
                    $data['level'] = intval($level_list[$data['level']]);

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
                    $data['created'] = time();
                    $data['updated'] = $data['created'];

                    if ($repeat_ret) {
                        $list = $model->where("id=" . intval($repeat_ret['id']))->save($data);
                    } else {
                        //保存当前数据对象
                        $list = $model->add($data);
                    }
                    if ($list !== false) { //保存成功
                        if (!empty($option_id)) {
                            if ($repeat_ret) {
                                $list = $repeat_ret['id'];
                            }
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