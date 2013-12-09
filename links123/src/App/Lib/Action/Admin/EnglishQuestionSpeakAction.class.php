<?php

/**
 * 英语角说力试题后台管理类
 */
import("App.Logic.Admin.EnglishQuestionLogic");
import("App.Logic.Admin.EnglishLevelnameLogic");
class EnglishQuestionSpeakAction extends CommonAction {
    
    protected $cEnglishQuestionLogic  = null;
    protected $cEnglishLevelnameLogic = null;
    protected $forbid_reason_options = array();
    protected $del_reason_options = array();
    public function _initialize() {
        $this->cEnglishQuestionLogic  = new EnglishQuestionLogic();
        $this->cEnglishLevelnameLogic = new EnglishLevelnameLogic();
        parent::_initialize();
    }

    public function _filter(&$map, &$param) {
        if (isset($_REQUEST['name'])) {
            $name = ftrim($_REQUEST['name']);
        	$_SESSION['english_questionspeak_search_key'] = $name;
        }
        $search_key = '';//搜索栏保留上次的关键字
        if(isset($_SESSION['english_questionspeak_search_key'])){
        	$search_key = $_SESSION['english_questionspeak_search_key'];
        }
        $this->assign('search_key', $search_key);
        
        $attr_one = -1;
        $attr_two = 0;
        $attr_thr = -1;
        //媒体口音
        if (isset($_REQUEST['voice'])) {
            $attr_one = intval($_REQUEST['voice']) == 1 ? 1 : 0;
            $param['voice'] = intval($_REQUEST['voice']);
        }
        //视频类型
        if (isset($_REQUEST['pattern'])) {
            $attr_thr = intval($_REQUEST['pattern']) == 1 ? 1 : 0;
            $param['pattern'] = intval($_REQUEST['pattern']);
        }
        if($attr_one > -1 && $attr_thr > -1){
            $map['englishCategory.cat_attr_id'] = bindec($attr_one.$attr_two.$attr_thr);
        }elseif($attr_one > -1 && $attr_thr == -1){
            $map['englishCategory.cat_attr_id'] =array(
                bindec($attr_one.$attr_two.'1'),
                bindec($attr_one.$attr_two.'0'),
                "OR") ;
        }elseif($attr_one == -1 && $attr_thr > -1){
            $map['englishCategory.cat_attr_id'] =array(
                bindec('1'.$attr_two.$attr_thr),
                bindec('0'.$attr_two.$attr_thr),
                "OR") ;
        }else{
            $map['englishCategory.cat_attr_id'] = array(
                bindec('1'.$attr_two.'1'),
                bindec('0'.$attr_two.'0'),
                bindec('1'.$attr_two.'0'),
                bindec('0'.$attr_two.'1'),
                "or") ;
        }
        
        if(intval($_REQUEST['level_one']) > 0){
            $param['level_one'] = intval($_REQUEST['level_one']);
            $map['englishCategory.level_one'] = intval($_REQUEST['level_one']);
        }
        if(intval($_REQUEST['level_two']) > 0){
            $param['level_two'] = intval($_REQUEST['level_two']);
            $map['englishCategory.level_two'] = intval($_REQUEST['level_two']);
        }
        if(intval($_REQUEST['level_thr']) > 0){
            $param['level_thr'] = intval($_REQUEST['level_thr']);
            $map['englishCategory.level_thr'] = intval($_REQUEST['level_thr']);
        }
        
        
        //视频特别推荐
        if (isset($_REQUEST['special_recommend'])) {
            $map['englishMedia.special_recommend'] = intval($_REQUEST['special_recommend']);
            $param['special_recommend'] = intval($_REQUEST['special_recommend']);
        }
        
        //状态
        if (isset($_REQUEST['status'])) {
        	if ($_REQUEST['status'] != -2) {
        		$map['englishQuestionSpeak.status'] = intval($_REQUEST['status']);
        	}
        	$param['status'] = intval($_REQUEST['status']);
        	if($param['status'] == 0){
        		$param['forbid_reason'] = isset($_REQUEST['forbid_reason'])?$_REQUEST['forbid_reason']:'';
        		if($param['forbid_reason'] != ''){
        			$map['englishQuestionSpeak.forbid_reason'] = $param['forbid_reason'];
        		}
        	}
        	if($param['status'] == -1){
        		$param['del_reason'] = isset($_REQUEST['del_reason'])?$_REQUEST['del_reason']:'';
        		if($param['del_reason'] != ''){
        			$map['englishQuestionSpeak.del_reason'] = $param['del_reason'];
        		}
        	}
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
            $map['_string'] = "DATE_FORMAT(FROM_UNIXTIME(englishQuestion.`created`),'%Y-%m-%d')='" . $_REQUEST['created'] . "'";
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
            $key['englishQuestionSpeak.content'] = array('like', "%" . $name . "%");
            $key['englishMedia.media_source_url'] = array('like', "%" . $name . "%");
            $key['_logic'] = 'or';
        }
        if (!empty($key)) {
            $map['_complex'] = $key;
        }
        $this->assign('name', $name);
        $param['name'] = $name;
    }
    
    protected function _list($model, $map, $param, $sortBy = '', $asc = false) {
        //排序字段 默认为主键名
        if (isset($_REQUEST ['_order'])) {
            $order = $_REQUEST ['_order'];
        } else {
            $order = !empty($sortBy) ? $sortBy : $model->getPk();
        }
        $param['order'] = $order;
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = $asc ? 'desc' : 'asc';
        }
        $param['sort'] = $sort;
//        dump($param);
        //取得满足条件的记录数
        if ($model->getModelName() == 'NewsView') {
            $count = $model->where($map)->count('news.id');
        } elseif ($model->getModelName() == 'ProductView') {
            $count = $model->where($map)->count('product.id');
        } elseif ($model->getModelName() == 'CasesView') {
            $count = $model->where($map)->count('cases.id');
        } elseif ($model->getModelName() == 'CategoryView') {
            $count = $model->where($map)->count('cat1.id');
        } elseif ($model->getModelName() == 'LinksView') {
            $count = $model->where($map)->count('links.id');
        } elseif ($model->getModelName() == 'AnnouncementView') {
            $count = $model->where($map)->count('announcement.id');
        } elseif ($model->getModelName() == 'SuggestionView') {
            $count = $model->where($map)->count('suggestion.id');
        } elseif ($model->getModelName() == 'CatPicView') {
            $count = $model->where($map)->count('catPic.id');
        } elseif ($model->getModelName() == 'EnglishQuestionView') {
            $count = $model->where($map)->count('DISTINCT(englishQuestion.id)');
        }elseif ($model->getModelName() == 'EnglishQuestionSpeakView') {
            $count = $model->where($map)->count('DISTINCT(englishQuestionSpeak.id)');
        } elseif ($model->getModelName() == 'EnglishMediaView') {
            $count = $model->where($map)->count('englishMedia.id');
        } else {
            $count = $model->where($map)->count('id');
        }
        //echo $model->getlastsql()."<br />";
        if ($count > 0) {
            import("@.ORG.Page");
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
            	if(!empty($_COOKIE['listRows']) && $_COOKIE['listRows'] !=$_REQUEST ['listRows']){
            		$listRows = $_COOKIE ['listRows'];
            	}else{
            		$listRows = $_REQUEST ['listRows'];
            	}
            } else {
            	if(!empty($_COOKIE['listRows'])){
            		$listRows = $_COOKIE ['listRows'];
            	}else{
            		$listRows = '20';
            	}
            }
            $param['listRows'] = $listRows;
            $p = new Page($count, $listRows);
            //分页查询数据
            $voList = $model->where($map)->order("`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->group("englishQuestionSpeak.id")->select();
//            echo $model->getlastsql();
            //分页跳转的时候保证查询条件
            foreach ($param as $key => $val) {
                //$p->parameter .= "$key=" . urlencode ( $val ) . "&";
                $p->parameter .= "$key=" . $val . "&";
            }
            $this->assign('param', $p->parameter);
            $_SESSION[C('SEARCH_PARAMS_KEY')] = $p->parameter . "p=" . $_REQUEST['p'];
            //分页显示
            $page = $p->show();
            //列表排序显示
            $sortImg = $sort; //排序图标
            $sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
            $sort = $sort == 'desc' ? 1 : 0; //排序方式
            //模板赋值显示
            $this->assign('list', $voList);
            $this->assign('sort', $sort);
            $this->assign('order', $order);
            $this->assign('sortImg', $sortImg);
            $this->assign('sortType', $sortAlt);
            $this->assign("page", $page);
            $this->assign("listRows", $listRows);
        }
        cookie('_currentUrl_', __URL__ . '/index?' . $_SESSION[C('SEARCH_PARAMS_KEY')]);
        return;
    }
    
    /**
     * 解析英语角试题删除、禁用原因
     * @author Joseph $date2013-11-19$
     */
    protected  function parseReaseOption($t)
    {
    	$a = array();
    	foreach ($t as $v){
    		$v = trim($v);
    		if($v){
    			$a[] =array('key'=>$v,'name'=>$v);
    		}
    	}
    	return $a;
    }
    /**
     * 获取英语角试题删除、禁用原因
     * @author Joseph $date2013-11-19$
     */
    protected function getStatusReason()
    {
    	$variableModel = D("Variable");
    	$forbid_reason_options = $variableModel->getVariable('english_question_forbid_reason');
    	$del_reason_options = $variableModel->getVariable('english_question_del_reason');
    	if($forbid_reason_options){
    		$t = explode("\n", $forbid_reason_options);
    		$this->forbid_reason_options = $this->parseReaseOption($t);
    	}
    	if($del_reason_options){
    		$t = explode("\n", $del_reason_options);
    		$this->del_reason_options = $this->parseReaseOption($t);
    	}
    	$this->assign("forbid_reason_options", $this->forbid_reason_options);
    	$this->assign("del_reason_options", $this->del_reason_options);
    }

    public function index() {
    	$this->getStatusReason();
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
        //@ 一级类目
        $category["level_one"] = $this->cEnglishLevelnameLogic->getCategoryLevelListBy("1");
        //@ 二级类目
        $categoryModel = D("EnglishCategory");
        $map = array();
        $map['category.level_one'] = $param['level_one'] ? $param['level_one'] : $category["level_one"][0]['id'];
        $group = 'category.level_two';
        $flag = "level_two";
        $category["level_two"] = $categoryModel->alias("category")
                    ->field("category.".$flag." as id,levelname.name")
                    ->join("RIGHT JOIN ".C("DB_PREFIX")."english_levelname levelname on levelname.id=category.".$flag)
                    ->where($map)
                    ->group($group)
                    ->order("category.".$flag."_sort asc")
                    ->select();
        //@ 三级类目
        $map = array();
        $map['category.level_one'] = $param['level_one'] ? $param['level_one'] : $category["level_one"][0]['id'];
        $map['category.level_two'] = $param['level_two'] ? $param['level_two'] : $category["level_two"][0]['id'];
        $group = 'category.level_thr';
        $flag = "level_thr";
        $category["level_thr"] = $categoryModel->alias("category")
                    ->field("category.".$flag." as id,levelname.name")
                    ->join("RIGHT JOIN ".C("DB_PREFIX")."english_levelname levelname on levelname.id=category.".$flag)
                    ->where($map)
                    ->group($group)
                    ->order("category.".$flag."_sort asc")
                    ->select();

        $this->assign("category", $category);
        
        $this->assign("type", 0);//说力
        //listRows_options
        $this->assign("listRows_options", array(
        		array('key'=>5,'name'=>"5"),
        		array('key'=>20,'name'=>"20"),
        		array('key'=>100,'name'=>"100"),
        		array('key'=>200,'name'=>"200"),
        ));
        $this->assign("param", $param);
        foreach ($param as $key => $value) {
            $param_str.=$key . "=" . $value . "&";
        }
        $this->assign("param_str", $param_str);
        $this->display();
        return;
    }

    public function add() {
    	$this->getStatusReason();
    	$this->display();
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
        
        //状态原因
        if($model->status == 0){//禁用
        	$model->fordib_reason = $_REQUEST['fordib_reason'];
        	$model->del_reason = '';
        }
        if($model->status == -1){//删除
        	$model->del_reason = $_REQUEST['del_reason'];
        	$model->fordib_reason = '';
        }
        if($model->status == 1){//启用
        	$model->fordib_reason = '';
        	$model->del_reason = '';
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
                        $temp['start_time'] = floatval($sentence_start_arr[$key]);
                        $temp['end_time'] = floatval($sentence_end_arr[$key]);
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
            if(intval($_POST['return_close']) == 1){
            	echo '<script type="text/javascript">alert("编辑成功！");window.close()</script>';
            }else{
            	$this->success("新增成功！");
            }
            #$this->success('新增成功!', cookie('_currentUrl_'));
        } else {
            $model->rollback();
            //失败提示
            $this->error('新增失败!');
        }
    }
    public function edit() {
    	$this->getStatusReason();
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
            $mediaInfo = $mediaModel->where($mediaMap)->find();
            $mediaId = intval($mediaInfo['id']);
        }
        if ($mediaId == 0 && !empty($_REQUEST['media_name'])) {
            $mediaMap = array();
            $mediaMap['name'] = $_REQUEST['media_name'];
            $mediaInfo = $mediaModel->where($mediaMap)->find();
            $mediaId = intval($mediaInfo['id']);
        }
        //
        //媒体id为零，将试题置为禁用
        $model->media_id = $mediaId;
        if ($model->status == 1 && $mediaId == 0) {
            $model->status = 0;
        }
    	//状态原因
        if($model->status == 0){//禁用
        	$model->fordib_reason = $_REQUEST['fordib_reason'];
        	$model->del_reason = '';
        }
        if($model->status == -1){//删除
        	$model->del_reason = $_REQUEST['del_reason'];
        	$model->fordib_reason = '';
        }
        if($model->status == 1){//启用
        	$model->fordib_reason = '';
        	$model->del_reason = '';
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
                        $temp['start_time'] = floatval($sentence_start_arr[$key]);
                        $temp['end_time'] = floatval($sentence_end_arr[$key]);
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
            //更新分类试题数量
            if($_POST['old_status'] == 1){
                if($_POST['status'] != 1 && $mediaInfo['status'] == 1){
                    if(false == D("EnglishCategory")->updateCategoryQuestionNumByQuestion($id, true, 0)){
                        $model->rollback();
                        $this->error('编辑失败！');
                    }
                }
            }else{
                if($_POST['status'] == 1 && $mediaInfo['status'] == 1){
                    if(false == D("EnglishCategory")->updateCategoryQuestionNumByQuestion($id, false, 0)){
                        $model->rollback();
                        $this->error('编辑失败！');
                    }
                }
            }

            $model->commit();
            if(intval($_POST['return_close']) == 1){
            	echo '<script type="text/javascript">alert("编辑成功！");window.close()</script>';
            }else{
            	$this->success("编辑成功！");
            }
            //$this->success('编辑成功!', cookie('_currentUrl_'));
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
                    $media_info = $mediaModel->field("id,local_path,caption,created")->where(array("media_source_url" => array("like", $media_data['media_source_url'])))->find();
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
                        $data['status'] = 0;//新增视频没有本地来源，试题不可用
                    } else {
                        if ($media_info['caption'] == "") {
                            $map = array("id" => $mediaId);
                            $ret = $mediaModel->where($map)->setField("caption", htmlentities($media_data['caption']));
                            if (false === $ret) {
                                $model->rollback();
                                @unlink($dest);
                                die(json_encode(array("info" => "导入失败，更新字幕到媒体表失败！", "status" => false)));
                            }
                        }
                        if($media_info['local_path'] == ""){
                            $data['status'] = 0;//视频没有本地来源，试题不可用
                        }
                        $media_data['created'] = $media_info['created'];
                    }
                    $data['media_id'] = intval($mediaId);
                    //没有媒体id，题目禁用
                    if ($data['media_id'] == 0) {
                        $data['status'] = 0;
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
                                    $temp['content'] = $captions[$k]['en'];
                                    $temp['start_time'] = intval($captions[$k]['start_time']);
                                    $temp['end_time'] = intval($captions[$k]['end_time']);
                                    $temp['standard_audio'] = date("Ymd",$media_data['created']) . "/" . md5($media_data['media_source_url']) . "_" . ($k+1) . ".wav";
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

    public function property () {
        $question_id = intval($_REQUEST["qid"]);
        $type = 0;
        $page = intval($_REQUEST["page"]);
        $question_property = $this->cEnglishQuestionLogic->getQuestionAndProperty($question_id, $type);
        //$is_recommend = $this->cEnglishQuestionLogic->isQuestionSpecRecommend($question_id);
        $this->assign("question", $question_property["question"]);
        $this->assign("property", $question_property["property"]);
        //$this->assign("is_recommend", $is_recommend);
        $this->assign("page", $page);
        $this->display();
    }

    public function addProperty() {
        
        //@ 一级类目
        $category["level_one"] = $this->cEnglishLevelnameLogic->getCategoryLevelListBy("1");
        //@ 二级类目
        $category["level_two"] = $this->cEnglishLevelnameLogic->getCategoryLevelListBy("2");
        //@ 三级类目
        $category["level_thr"] = $this->cEnglishLevelnameLogic->getCategoryLevelListBy("3");

        $this->assign("category", $category);
        $this->assign("qid", intval($_REQUEST["qid"]));
        $this->display();
    }

    /**
    * 添加题目所属类目的属性
    * author reasono
    */
    public function insertProperty() {
        $question_id = isset($_REQUEST["question_id"]) ? intval($_REQUEST["question_id"]) : 0;
        
        $level_one   = isset($_REQUEST["level_one"]) ? intval($_REQUEST["level_one"]) : 0;
        $level_two   = isset($_REQUEST["level_two"]) ? intval($_REQUEST["level_two"]) : 0;
        $level_thr   = isset($_REQUEST["level_thr"]) ? intval($_REQUEST["level_thr"]) : 0;
        $status      = isset($_REQUEST["status"])    ? intval($_REQUEST["status"])    : 1;
        $type        = isset($_REQUEST["type"])      ? intval($_REQUEST["type"])      : 0;
        $type = 0;//说力
        
        $model = D("EnglishCatquestion");
        $model->startTrans();
        $ret = $this->cEnglishQuestionLogic->saveProperty(
                                                    $question_id, 
                                                    null, 
                                                    null, 
                                                    null, 
                                                    $level_one, 
                                                    $level_two, 
                                                    $level_thr, 
                                                    $status, 
                                                    $type,
                                                    true
                                                );
        if ($ret === false) {
            $model->rollback();
            $this->error($this->cEnglishQuestionLogic->getErrorMessage());
            return;
        }
        $model->commit();
        $this->success('添加分类属性成功');
    }
    
    function editProperty(){
        $model = D("EnglishCategory");
        $cat_id = intval($_REQUEST['id']);
        $question_id = intval($_REQUEST['question_id']);
        if($cat_id == 0 || $question_id == 0){
            $this->error("非法操作");
        }
        $cat_info = $model->find($cat_id);
        $ret = D('EnglishLevelname')->getCategoryLevelListBy();
        $cat_attr_id = sprintf("%03d", decbin($cat_info["cat_attr_id"]));
        $cat_info["voice"]   = substr($cat_attr_id, 0, 1);
        $cat_info["pattern"] = substr($cat_attr_id, 2, 1);

        $this->assign("info",$cat_info);
        $this->assign("question_id",$question_id);
        
        //@ 一级类目
        $category["level_one"] = $this->cEnglishLevelnameLogic->getCategoryLevelListBy("1");
        //@ 二级类目
        $category["level_two"] = $this->cEnglishLevelnameLogic->getCategoryLevelListBy("2");
        //@ 三级类目
        $category["level_thr"] = $this->cEnglishLevelnameLogic->getCategoryLevelListBy("3");

        $this->assign("category", $category);
        
        $this->display();
    }
    
    /**
    * 添加题目所属类目的属性
    * author reasono
    */
    public function updateProperty() {
        $question_id = intval(($_REQUEST["question_id"]));
        if($question_id == 0){
            $this->error("非法操作");
        }
        $cat_id = intval($_REQUEST['cat_id']);
        if($cat_id == 0){
            $this->error("非法操作");
        }
        
        
        
        $level_one   = isset($_REQUEST["level_one"]) ? intval($_REQUEST["level_one"]) : 0;
        $level_two   = isset($_REQUEST["level_two"]) ? intval($_REQUEST["level_two"]) : 0;
        $level_thr   = isset($_REQUEST["level_thr"]) ? intval($_REQUEST["level_thr"]) : 0;
        $status      = isset($_REQUEST["status"])    ? intval($_REQUEST["status"])    : 1;
        $type = 0; //说力
        
        $model = D("EnglishCatquestion");
        $cat_attr_id = D("EnglishCategory")->where(array("cat_id"=>$cat_id))->getField("cat_attr_id");
        if($cat_attr_id !== false){
            $cat_attr_id = sprintf("%03d",decbin($cat_attr_id));
            $voice = substr($cat_attr_id, 0, 1);
            $target = substr($cat_attr_id, 1, 1);
            $pattern = substr($cat_attr_id, 2, 1);
        }
        $model->startTrans();
        $cat_map = array(
            "cat_id"=>$cat_id,
            "question_id"=>$question_id,
            "type"=>$type
        );
        if(false === $model->where($cat_map)->delete()){
            $model->rollback();
            $this->error("操作失败");
        }
        $questionModel =D("EnglishQuestionSpeak");
        $question_info = $questionModel->alias("question")
                ->join(C("DB_PREFIX")."english_media media on question.media_id = media.id")
                ->where(array("question.id"=>$question_id,"question.status"=>1,"media.status"=>1))
                ->find();
        if(!empty($question_info)){
            //@ 需要更新EnglishCategory 题目数-1
            D('EnglishCategory')->where(array('cat_id' => $cat_id))->setDec('question_num');
        }

        $ret = $this->cEnglishQuestionLogic->saveProperty(
                                                    $question_id, 
                                                    $voice, 
                                                    $target, 
                                                    $pattern, 
                                                    $level_one, 
                                                    $level_two, 
                                                    $level_thr, 
                                                    $status, 
                                                    $type,
                                                    false
                                                );
        if ($ret === false) {
            $model->rollback();
            $this->error($this->cEnglishQuestionLogic->getErrorMessage());
            return;
        }
        $model->commit();
        $this->success('编辑分类属性成功');
    }
    public function forbid() {
        $name = $this->getActionName();
        $model = D($name);
        $categoryModel = D("EnglishCategory");
        $pk = $model->getPk();
        $id = $_REQUEST [$pk];
        $forbid_reason = $_REQUEST ['reason'];
        $condition = array($pk => array('in', $id));
        $q_map = array(
            "question.status"=>1,
            "media.status"=>1
        );
        $q_map['question.id'] = array('in',$id);
        $q_list = $model->alias("question")->join(C("DB_PREFIX")."english_media media on media.id=question.media_id")->field("question.id")->where($q_map)->select();
        foreach ($q_list as $value) {
            $question_ids[] = $value['id'];
        }
        $model->startTrans();
        //$list = $model->forbid($condition);
        $list = $model->where($condition)->save(array(
        		'status'=>0,
        		'forbid_reason'=>$forbid_reason,
        		//'updated'=>time()
        ));
        if ($list !== false) {
            if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, true, 0)){
                $model->rollback();
                $this->error('状态禁用失败！');
            }
            $model->commit();
            $this->success('状态禁用成功', $this->getReturnUrl());
        } else {
            $model->rollback();
            $this->error('状态禁用失败！');
        }
    }
    public function resume() {
        $name = $this->getActionName();
        $model = D($name);
        $categoryModel = D("EnglishCategory");
        $pk = $model->getPk();
        $id = $_REQUEST [$pk];
        //获取被禁用的试题列表，为更新分类下的试题数量准备
        $condition = array($pk => array('in', $id));
        $q_map = array(
            "question.status"=>array("neq",1),
            "media.status"=>1
        );
        $q_map['question.id'] = array('in',$id);
        $q_list = $model->alias("question")->join(C("DB_PREFIX")."english_media media on media.id=question.media_id")->field("question.id")->where($q_map)->select();
        foreach ($q_list as $value) {
            $question_ids[] = $value['id'];
        }
        $model->startTrans();
        $list = $model->resume($condition);
        
        if ($list !== false) {
            if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, false, 0)){
                $model->rollback();
                $this->error('状态启用失败！');
            }
            $model->commit();
            $this->success('状态启用成功', $this->getReturnUrl());
        } else {
            $model->rollback();
            $this->error('状态启用失败！');
        }
    }
    public function delete() {
        $name = $this->getActionName();
        $model = D($name);
        $categoryModel = D("EnglishCategory");
        $pk = $model->getPk();
        $id = $_REQUEST [$pk];
        $del_reason = $_REQUEST ['reason'];
        $condition = array($pk => array('in', $id));
        $q_map = array(
            "question.status"=>1,
            "media.status"=>1
        );
        $q_map['question.id'] = array('in',$id);
        $q_list = $model->alias("question")->join(C("DB_PREFIX")."english_media media on media.id=question.media_id")->field("question.id")->where($q_map)->select();
        foreach ($q_list as $value) {
            $question_ids[] = $value['id'];
        }
        $model->startTrans();
        //$list = $model->where($condition)->setField("status",-1);
        $list = $model->where($condition)->save(array(
        		'status'=>-1,
        		'del_reason'=>$del_reason,
        		//'updated'=>time()
        ));
        if ($list !== false) {
            if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, true, 0)){
                $model->rollback();
                $this->error('删除失败！');
            }
            $model->commit();
            $this->success('删除成功', $this->getReturnUrl());
        } else {
            $model->rollback();
            $this->error('删除失败！');
        }
    }
    public function delProperty(){
        $id = $_REQUEST['id'];
        $question_id = intval($_REQUEST['question_id']);
        if($question_id == 0 || empty($id)){
            $this->error("非法操作");
        }
        $ids = explode(",", $id);
        //删除指定记录
        $model = D("EnglishCatquestion");
                $total = $model->alias("a")
                ->join(C("DB_PREFIX")."english_category b on a.cat_id=b.cat_id")->where(array("a.question_id"=>$question_id,"b.level_one"=>array("neq",-1)))->count("a.cat_id");
        //保证需要有一个分类
        if(intval($total) == 1){
            $this->error("最后一个分类，不能删除");
        }
        $cat_condition = array(
            "cat_id" => array('in', $ids),
            );
        $categoryModel = D("EnglishCategory");
        $cat_list = $categoryModel->where($cat_condition)->select();
        foreach($cat_list as $value){
            $level_one_ids[] = $value['level_one'];
        }
            
        //删除默认的,例如 综合
        $map = array(
            "catquestion.question_id"=>$question_id,
            "catquestion.type"=>0,
            "category.level_one"=>array("in",$level_one_ids),
            "level_two_table.default"=>1,
        );
        $ret = $model->alias("catquestion")
                ->field("catquestion.cat_id as cat_id")
                ->join(C("DB_PREFIX")."english_category category on category.cat_id=catquestion.cat_id")
                ->join(C("DB_PREFIX")."english_levelname level_two_table on category.level_two=level_two_table.id")
                ->where($map)->select();
        if(!empty($ret)){
            foreach($ret as $value){
                $ids[] = $value['cat_id'];
            }
        }
        $condition = array(
            "cat_id" => array('in', $ids),
            "question_id"=>$question_id,
            "type"=>0
        );
        $model->startTrans();
        if (false !== $model->where($condition)->delete()) {
            $question_map = array(
                "media.status"=>1,
                "question.status"=> 1,
                "question.id"=>$question_id
            );
            $questionModel = D("EnglishQuestionSpeak");
            $question_info = $questionModel->alias("question")->join(C("DB_PREFIX")."english_media media on media.id=question.media_id")->where($question_map)->find();
            if(!empty($question_info)){
                $cat_map = array(
                    "cat_id" => array('in', $ids)
                );
                if(false === D("EnglishCategory")->where($cat_map)->setDec("question_num")){
                    $model->rollback();
                    $this->error('删除失败！');
                }
            }
            $model->commit();
            $this->success('删除成功！', cookie('_currentUrl_'));
        } else {
            $model->rollback();
            $this->error('删除失败！');
        }
                
    }
    
    public function voice(){
        if($this->isAjax()){
            $id = intval($_REQUEST['id']);
            $model = D("EnglishCatquestion");
            $model->startTrans();
            $ret  = $this->cEnglishQuestionLogic->setQuestionCatAttrId($id,"voice",0);
            if(false === $ret){
                $model->rollback();
                $this->ajaxReturn("",  $this->cEnglishQuestionLogic->getErrorMessage(),false);
            }
            $model->commit();
            $this->ajaxReturn($ret, "操作成功",true);
        }
    }
    public function pattern(){
        if($this->isAjax()){
            $id = intval($_REQUEST['id']);
            $model = D("EnglishCatquestion");
            $model->startTrans();
            $ret  = $this->cEnglishQuestionLogic->setQuestionCatAttrId($id,"pattern",0);
            if(false === $ret){
                $model->rollback();
                $this->ajaxReturn("",  $this->cEnglishQuestionLogic->getErrorMessage(),false);
            }
            $model->commit();
            $this->ajaxReturn($ret, "操作成功",true);
        }
    }
}

?>
