<?php

/**
 * 英语角媒体管理控制类
 *
 * @author Adam $date2013.08.26$
 */
class EnglishMediaAction extends CommonAction {
	protected $forbid_reason_options = array();
	protected $del_reason_options = array();
	public function _initialize() {
		$this->forbid_reason_options = array(
				array('key'=>1,'name'=>"没有本地视频"),
				array('key'=>2,'name'=>"视频错误"),
				array('key'=>3,'name'=>"外链变更"),
				array('key'=>4,'name'=>"图像不清楚"),
				array('key'=>5,'name'=>"其他原因"),
		);
		$this->del_reason_options = array(
				array('key'=>1,'name'=>"无视频"),
				array('key'=>2,'name'=>"重复"),
				array('key'=>3,'name'=>"外链变更"),
				array('key'=>4,'name'=>"不能播放"),
				array('key'=>5,'name'=>"错误"),
				array('key'=>6,'name'=>"其他原因"),
		);
		parent::_initialize();
	}
    public function _filter(&$map, &$param) {
        if (isset($_REQUEST['name'])) {
            $name = ftrim($_REQUEST['name']);
        }
        //视频类型
        if (intval($_REQUEST['pattern']) > 0) {
            $map['englishMedia.pattern'] = intval($_REQUEST['pattern']);
            $param['pattern'] = intval($_REQUEST['pattern']);
        }
        //视频口音
        if (intval($_REQUEST['voice']) > 0) {
            $map['englishMedia.voice'] = intval($_REQUEST['voice']);
            $param['voice'] = intval($_REQUEST['voice']);
        }
        //媒体科目
        if (intval($_REQUEST['object']) > 0) {
            $map['englishMedia.object'] = intval($_REQUEST['object']);
            $object_info = D("EnglishObject")->find($map['englishMedia.object']);
            if ($object_info['name'] == "综合") {
                unset($map['englishMedia.object']);
            }
            $param['object'] = intval($_REQUEST['object']);
        }
        //媒体专题
        if (intval($_REQUEST['subject']) > 0) {
            $map['englishMedia.subject'] = intval($_REQUEST['subject']);
            $param['subject'] = intval($_REQUEST['subject']);
        }
        //媒体难度
        if (intval($_REQUEST['difficulty']) > 0) {
            $map['englishMedia.difficulty'] = intval($_REQUEST['difficulty']);
            $param['difficulty'] = intval($_REQUEST['difficulty']);
        }
        //媒体年级
        if (intval($_REQUEST['level']) > 0) {
            $map['englishMedia.level'] = intval($_REQUEST['level']);
            $param['level'] = intval($_REQUEST['level']);
        }
        //媒体状态
        if (isset($_REQUEST['status'])) {
        	if ($_REQUEST['status'] != -2) {
        		$map['englishMedia.status'] = intval($_REQUEST['status']);
        	}
        	$param['status'] = intval($_REQUEST['status']);
        	if($param['status'] == 0){
        		$param['forbid_reason'] = isset($_REQUEST['forbid_reason'])?intval($_REQUEST['forbid_reason']):0;
        		if($param['forbid_reason'] > 0){
        			$map['englishMedia.forbid_reason'] = $param['forbid_reason'];
        		}
        	}
        	if($param['status'] == -1){
        		$param['del_reason'] = isset($_REQUEST['del_reason'])?intval($_REQUEST['del_reason']):0;
        		if($param['del_reason'] > 0){
        			$map['englishMedia.del_reason'] = $param['del_reason'];
        		}
        	}
        }
        
        //媒体推荐
        if (isset($_REQUEST['recommend'])) {
            if (intval($_REQUEST['recommend']) == 0) {
                $map['englishMedia.recommend'] = 0;
            } else {
                $map['englishMedia.recommend'] = array("neq", 0);
            }
            $param['recommend'] = intval($_REQUEST['recommend']);
        }
        //媒体特别推荐
        if (isset($_REQUEST['special_recommend'])) {
            $map['englishMedia.special_recommend'] = intval($_REQUEST['special_recommend']);
            $param['special_recommend'] = intval($_REQUEST['special_recommend']);
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
        //omg
        if (isset($_REQUEST['omg'])) {
        	$omg = intval($this->_param('omg'));
        	$map['englishMedia.omg'] = $omg;
        	$param['omg'] = $omg;
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
        //是否存在本地视频文件
        if (isset($_REQUEST['has_local_path'])) {
            if (intval($_REQUEST['has_local_path']) == 0) {
                $map['englishMedia.local_path'] = array("eq", "");
            } else {
                $map['englishMedia.local_path'] = array("neq", "");
            }
            $param['has_local_path'] = intval($_REQUEST['has_local_path']);
        }
        //媒体添加时间
        if (isset($_REQUEST['created']) && strtotime($_REQUEST['created'])) {
            $map['_string'] = "DATE_FORMAT(FROM_UNIXTIME(englishMedia.created),'%Y-%m-%d')='" . $_REQUEST['created'] . "'";
            $param['created'] = $_REQUEST['created'];
        }
        if (!empty($name)) {
            $key['englishMedia.id'] = intval($name);
            $key['englishMedia.name'] = array('like', "%" . $name . "%");
            $key['englishMedia.media_source_url'] = array('like', "%" . $name . "%");
            $key['englishMedia.play_code'] = array('like', "%" . $name . "%");
            $key['_logic'] = 'or';
        }
        if (!empty($key)) {
            $map['_complex'] = $key;
        }
        $this->assign('name', $name);
        $param['name'] = $name;
        $this->assign("name", $name);
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
    		$count = $model->where($map)->count('englishQuestion.id');
    	}elseif ($model->getModelName() == 'EnglishQuestionSpeakView') {
    		$count = $model->where($map)->count('englishQuestionSpeak.id');
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
    		$p = new Page($count, $listRows);
    		//分页查询数据
    		$voList = $model->where($map)->order("`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select();
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
    public function index() {
        //列表过滤器，生成查询Map对象
        $map = array();
        $param = array();
        if (method_exists($this, '_filter')) {
            $this->_filter($map, $param);
        }
        $model = new EnglishMediaViewModel();
        if (!empty($model)) {
            $this->_list($model, $map, $param, 'id', false);
            //lTrace('Log/lastSql', $this->getActionName(), $model->getLastSql());
        }
        //
        //科目列表
        $object_list = D("EnglishObject")->getList("status=1", "`sort` ASC");
        $this->assign("object_list", $object_list);
        //等级列表
        $level_list = D("EnglishLevel")->getList("status=1", "`sort` ASC");
        $this->assign("level_list", $level_list);
        //专题列表
        $subject_list = D("EnglishMediaSubject")->getList("status=1", "`sort` ASC");
        $this->assign("subject_list", $subject_list);
        //推荐列表
        $recommend_list = D("EnglishMediaRecommend")->getList("status=1", "`sort` ASC");
        $this->assign("recommend_list", $recommend_list);
        //
        //listRows_options
        $this->assign("listRows_options", array(
        		array('key'=>5,'name'=>"5"),
        		array('key'=>20,'name'=>"20"),
        		array('key'=>100,'name'=>"100"),
        		array('key'=>200,'name'=>"200"),
        ));
        $this->assign("forbid_reason_options", $this->forbid_reason_options);
        $this->assign("del_reason_options", $this->del_reason_options);
        $this->assign("param", $param);
        foreach ($param as $key => $value) {
            $param_str.=$key . "=" . $value . "&";
        }
        $this->assign("param_str", $param_str);
        $this->display();
        return;
    }

    public function foreverdelete() {
        //删除指定记录
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST [$pk];
            if (isset($id)) {
                $ids = explode(',', $id);
                $model->startTrans();
                $condition = array();
                foreach ($ids as $key => $value) {
                    $condition[$pk] = $value;
                    $path = "./Public/Uploads/Video/" . $model->where($condition)->getField("path");
                    if (false !== $model->where($condition)->delete()) {
                        @unlink($path);
                    } else {
                        $model->rollback();
                        $this->error("删除失败！");
                    }
                }
                $model->commit();
                $this->success('删除成功！', cookie('_currentUrl_'));
            } else {
                $this->error('非法操作');
            }
        }
    }

    public function add() {
        //科目列表
        $object_list = D("EnglishObject")->where("`status`=1")->order("sort")->select();
        $this->assign("object_list", $object_list);
        //等级列表
        $level_list = D("EnglishLevel")->where("`status`=1")->order("sort")->select();
        $this->assign("level_list", $level_list);
        //专题列表
        $subject_list = D("EnglishMediaSubject")->where("`status`=1")->order("`sort`")->select();
        $this->assign("subject_list", $subject_list);
        //推荐分类列表
        $recommend_list = D("EnglishMediaRecommend")->where("`status`=1")->order("`sort`")->select();
        $this->assign("recommend_list", $recommend_list);
        $this->assign("forbid_reason_options", $this->forbid_reason_options);
        $this->assign("del_reason_options", $this->del_reason_options);
        $this->display();
    }

    public function insert() {
        $name = $this->getActionName();
        $model = D($name);
        $model->startTrans();
        if (false === $model->create()) {
            $this->error($model->getError());
        }
       
        $media['media_source_url'] = $model->media_source_url;
        $media['name'] = $model->name;
        if($model->status == 0){//禁用
        	$model->fordib_reason = intval(empty($_REQUEST['fordib_reason'])?0:$_REQUEST['fordib_reason']);
        	$model->del_reason = 0;
        }
        if($model->status == -1){//禁用
        	$model->del_reason = intval(empty($_REQUEST['del_reason'])?0:$_REQUEST['del_reason']);
        	$model->fordib_reason = 0;
        }
        if($model->status == 1){//启用
        	$model->fordib_reason = 0;
        	$model->del_reason = 0;
        }
        //保存当前数据对象
        $list = $model->add();
        
        $media_id = $media['media_id'] = $list;
        if ($list !== false) { //保存成功
            if(false === $model->setSpecialRecommend($media, $_POST['special_recommend'])){
                $model->rollback();
                $this->error("编辑失败");
            }
            $model->commit();
           
            //TODO 推荐视频解析 @author slate date 20131001
            $this->analysisMediaPlayCode($media_id);
            
            $this->success('新增成功!', cookie('_currentUrl_'));
        } else {
            $model->rollback();
            //失败提示
            $this->error('新增失败!');
        }
    }

    public function edit() {
        $model = D("EnglishMedia");
        $id = intval($_REQUEST["id"]);
        $vo = $model->find($id);
        $vo['name'] = htmlspecialchars($vo['name']);
        $this->assign('vo', $vo);

        //科目列表
        $object_list = D("EnglishObject")->where("`status`=1")->order("sort")->select();
        $this->assign("object_list", $object_list);
        //等级列表
        $level_list = D("EnglishLevel")->where("`status`=1")->order("sort")->select();
        $this->assign("level_list", $level_list);
        //专题列表
        $subject_list = D("EnglishMediaSubject")->where("`status`=1")->order("`sort`")->select();
        $this->assign("subject_list", $subject_list);
        //推荐分类列表
        $recommend_list = D("EnglishMediaRecommend")->where("`status`=1")->order("`sort`")->select();
        $this->assign("recommend_list", $recommend_list);
        $this->assign("forbid_reason_options", $this->forbid_reason_options);
        $this->assign("del_reason_options", $this->del_reason_options);
        $this->display();
    }

    public function update() {
        $name = $this->getActionName();
        $model = D($name);
        $model->startTrans();
        $media_info = $model->find($_POST['id']);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        if (!empty($_FILES['img']['name'])) {
            if(!preg_match("/^((http)|(https):\/\/)/i", $media_info['media_source_url'])){
                $media_info['media_text_url'] = "http://".$media_info['media_source_url'];
            }
            $host = explode('.', parse_url($media_info['media_source_url'], PHP_URL_HOST));
            if(!isset($host[1])){
                $host[1] = "others";
            }
            $upload_path = $host[1]."/".date("Ymd",$media_info['created'])."/";
            $file_name = md5($media_info['media_source_url']);
            $token = md5($upload_path.$file_name.date("Ymd")."!@#$%");
            import("@.ORG.UploadFile");
            $upload = new UploadFile();
            $upload->maxSize = 11000000; // 设置附件上传大小
            $upload->allowExts = array('jpeg', 'jpg', 'png', 'gif'); // 设置附件上传类型
            $upload->saveRule = uniqid();
            $upload->savePath = "./Public/Uploads/Temp/"; // 设置附件上传目录
            if (!$upload->upload()) {// 上传错误提示错误信息
                $this->error($upload->getErrorMsg());
            } else {// 上传成功 获取上传文件信息
                $info = $upload->getUploadFileInfo();
                
                
                $ch = curl_init();
                $data = array('file'=>'@'. realpath($info[0]['savepath'] . $info[0]['savename']),'token'=>$token,"file_name"=>$file_name,"path"=>$upload_path);
                curl_setopt($ch,CURLOPT_URL,C("VIDEO_UPLOAD_PATH")."upload_image.php");
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
                curl_setopt($ch,CURLOPT_POST,true);
                curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
                $result = curl_exec($ch);
                curl_close($ch);
                $ret = json_decode($result);
                if($ret.status === false){
                    $model->rollback();
                    $this->error($ret.info);
                }
                $model->media_thumb_img = C("VIDEO_UPLOAD_PATH") . $upload_path . $file_name . "." . $info[0]['extension'];
            }
        }
        
        $media_id = $media['media_id'] = $model->id;
        $media['media_source_url'] = $model->media_source_url;
        $media['name'] = $model->name;
        
        if($model->status == 0){//禁用
        	$model->fordib_reason = intval(empty($_REQUEST['fordib_reason'])?0:$_REQUEST['fordib_reason']);
        	$model->del_reason = 0;
        }
        if($model->status == -1){//禁用
        	$model->del_reason = intval(empty($_REQUEST['del_reason'])?0:$_REQUEST['del_reason']);
        	$model->fordib_reason = 0;
        }
        if($model->status == 1){//启用
        	$model->fordib_reason = 0;
        	$model->del_reason = 0;
        }
        
        // 更新数据
        $list = $model->save();
        if (false !== $list) {
            if(false === $model->setSpecialRecommend($media, $_POST['special_recommend'])){
                $model->rollback();
                $this->error("编辑失败");
            }
            
            $categoryModel = D("EnglishCategory");
            $questionModel = D("EnglishQuestion");
            $questionSpeakModel = D("EnglishQuestionSpeak");
            $q_map['media_id'] = array("in", $media_id);
            $q_map['status'] = 1;
            $question_list = $questionModel->field("id")->where($q_map)->select();
            $question_speak_list = $questionSpeakModel->field("id")->where($q_map)->select();
            //更新分类试题数量
            if($_POST['old_status'] == 1 && $_POST['status'] != 1){
                if(!empty($question_speak_list)){
                    $question_ids = array();
                    foreach($question_speak_list as $value){
                        $question_ids[] = $value['id'];
                    }
                    if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, true, 0)){
                        $model->rollback();
                        $this->error('编辑失败！');
                    }
                }
                if(!empty($question_list)){
                    $question_ids = array();
                    foreach($question_list as $value){
                        $question_ids[] = $value['id'];
                    }
                    if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, true, 1)){
                        $model->rollback();
                        $this->error('编辑失败！');
                    }
                }
            }elseif($_POST['old_status'] != 1 && $_POST['status'] == 1){
                if(!empty($question_speak_list)){
                    $question_ids = array();
                    foreach($question_speak_list as $value){
                        $question_ids[] = $value['id'];
                    }
                    if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, false, 0)){
                        $model->rollback();
                        $this->error('编辑失败！');
                    }
                }
                if(!empty($question_list)){
                    $question_ids = array();
                    foreach($question_list as $value){
                        $question_ids[] = $value['id'];
                    }
                    if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, false, 1)){
                        $model->rollback();
                        $this->error('编辑失败！');
                    }
                }
            }
            $model->commit();
            //TODO 推荐视频解析 @author slate date 20131001
            if (!$model->play_code) {
	            
	            $this->analysisMediaPlayCode($media_id);
            }
            
            //成功提示
            $this->success('编辑成功!', cookie('_currentUrl_'));
        } else {
            $model->rollback();
            //错误提示
            $this->error('编辑失败!');
        }
    }
    private function analysisMediaPlayCode($media_id) {
        $model = D("EnglishMedia");
        $media = $model->find($media_id);
        $media['media_local_path'] = $media['local_path'];
        $saveData = array();
        $saveData['id'] = $media['id'];
        $saveData['priority_type'] = empty($media['priority_type'])? 1 : $media['priority_type'];
        if (strpos($media['media_source_url'], 'http://www.youtube.com') !== FALSE && $media['local_path']) {
            $saveData['priority_type'] = 2;
        }
        //优先播放本地，且本地视频存在
        if ($saveData['priority_type'] == 2 && $media['local_path']) {
            $saveData['play_code'] = $media['local_path'];
            if (strtolower(end(explode(".", $media['local_path']))) == "swf") {
                $saveData['play_type'] = 0;
            } else {
                $saveData['play_type'] = 4;
            }
        } else {
            //play_code为空，则进行视频解析
            if (!$media['play_code']) {
                if($media['play_type'] == 5){
                    $media['play_code'] = $media['media_source_url'];
                    $saveData['play_code'] = $media['media_source_url'];
                }else{
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
                        $saveData['media_thumb_img'] = $media_thumb_img;
                        $saveData['play_code'] = $play_code;
                        $saveData['play_type'] = $play_type;
                    } else {
                        if ($media['media_local_path']) {
                            $saveData['priority_type'] = 2;
                            $saveData['play_type'] = 4;
                            $saveData['play_code'] = $media['local_path'];
                        } else {
                            $saveData['status'] = 0;
                        }
                    }
                }
            } else {
                if (strpos($media['media_source_url'], 'britishcouncil.org') !== FALSE) {
                    $saveData['play_code'] = preg_replace('/<!--<!\[endif\]-->(.*)/is', '</object></object>', $media['play_code']);
                    $saveData['play_code'] = str_replace('width=585&amp;height=575', 'width=100%&amp;height=100%', $media['play_code']);
                }
                $saveData['play_code'] = preg_replace(array('/width="(.*?)"/is', '/height="(.*?)"/is', '/width=300 height=280/is', '/width=600 height=400/is'), array('width="100%"', 'height="100%"', 'width="100%" height="100%"', 'width="100%" height="100%"'), $media['play_code']);
            }
        }
        $model->save($saveData);
    }

    public function pointSubject() {
        if ($this->isAjax()) {
            $id = $_REQUEST['id'];
            $target = intval($_REQUEST['target']);
            if ($target >= 0) {
                $map['id'] = array("in", $id);
                $model = D("EnglishMedia");
                $model->startTrans();
                $info = $model->field("recommend,ted,object")->where($map)->find();
                if ($info['recommend'] > 0) {
                    $recommendId = D("EnglishMediaRecommend")->getRecommendIdByObjectOrSubject($info['object'], $target);
                    if (intval($recommendId) > 0) {
                        $data['recommend'] = intval($recommendId);
                    }
                }
                if ($info['ted'] > 0) {
                    $tedId = D("EnglishMediaTed")->getTedIdByObjectOrSubject($info['object'], $target);
                    if (intval($tedId) > 0) {
                        $data['ted'] = intval($tedId);
                    }
                }
                $data['subject'] = $target;
                $ret = $model->where($map)->save($data);
                if (false !== $ret) {
                    $model->commit();
                    $this->ajaxReturn($target, "操作成功", true);
                }
            }
            $model->rollback();
            $this->ajaxReturn("", "操作失败", false);
        }
    }

    public function pointObject() {
        if ($this->isAjax()) {
            $id = $_REQUEST['id'];
            $target = intval($_REQUEST['target']);
            if ($target >= 0) {
                $map['id'] = array("in", $id);
                $model = D("EnglishMedia");
                $model->startTrans();
                $info = $model->field("recommend,subject")->where($map)->find();
                if ($info['recommend'] > 0) {
                    $recommendId = D("EnglishMediaRecommend")->getRecommendIdByObjectOrSubject($target, $info['subject']);
                    if (intval($recommendId) > 0) {
                        $data['recommend'] = intval($recommendId);
                    }
                }
                if ($info['ted'] > 0) {
                    $tedId = D("EnglishMediaTed")->getTedIdByObjectOrSubject($target,$info['subject']);
                    if (intval($tedId) > 0) {
                        $data['ted'] = intval($tedId);
                    }
                }
                $data['object'] = $target;
                $ret = $model->where($map)->save($data);
                if (false !== $ret) {
                    $model->commit();
                    $this->ajaxReturn($target, "操作成功", true);
                }
            }
            $model->rollback();
            $this->ajaxReturn("", "操作失败", false);
        }
    }

    public function pointLevel() {
        if ($this->isAjax()) {
            $target = intval($_REQUEST['target']);
            if ($target >= 0) {
                $model = D("EnglishMedia");
                $data = array();
                $data['id'] = $_REQUEST['id'];
                $data['level'] = $target;
                $levels = D("EnglishLevel")->order("`sort` ASC")->select();
                foreach ($levels as $value) {
                    $level_list[$value['id']] = $value;
                    $level_name_list_info[$value['name']] = $value;
                }
                if ($data['level'] == 0) {
                    $data['difficulty'] = 0;
                } else {
                    if ($level_list[intval($data['level'])]['sort'] <= $level_name_list_info['小六']['sort']) {
                        $data['difficulty'] = 1;
                    } else if ($level_list[intval($data['level'])]['sort'] >= $level_name_list_info['大一']['sort']) {
                        $data['difficulty'] = 3;
                    } else {
                        $data['difficulty'] = 2;
                    }
                }
                $ret = $model->save($data);
                if (false !== $ret) {
                    $this->ajaxReturn($data['difficulty'], "操作成功", true);
                }
            }
            $this->ajaxReturn("", "操作失败", false);
        }
    }

    /**
     * 批量设置
     * @return
     * @author  Adam $date2013.08.30$
     */
    public function groupSet() {
        if ($this->isAjax()) {
            $id = $_REQUEST['id'];
            //指定的专题
            if (intval($_REQUEST['targetSubject']) >= 0) {
                $data['subject'] = intval($_REQUEST['targetSubject']);
            }
            //指定特别推荐
            if (isset($_REQUEST['targetSpecialRecommend'])) {
                $data['special_recommend'] = intval($_REQUEST['targetSpecialRecommend']);
                if ($data['special_recommend'] == 1) {
                    $data['recommend'] = 1;
                }
            }
            if (isset($_REQUEST['targetRecommend'])) {
                $data['recommend'] = intval($_REQUEST['targetRecommend']);
                if ($data['recommend'] == 0) {
                    $data['special_recommend'] = 0;
                }
            }
            if (isset($_REQUEST['targetDifficulty'])) {
            	$data['difficulty'] = intval($_REQUEST['targetDifficulty']);
            }
            
            if (isset($_REQUEST['targetTed'])) {
                $data['ted'] = intval($_REQUEST['targetTed']);
            }
            $englishMediaModel = D("EnglishMedia");
            $englishMediaRecommendModel = D("EnglishMediaRecommend");
            $englishMediaTedModel = D("EnglishMediaTed");
            $map['id'] = array("in", $id);
            $data['updated'] = time();
            $medias_info = $englishMediaModel->field("id,recommend,special_recommend,object,subject")->where($map)->select();
            if (!empty($data) && !empty($medias_info)) {
                foreach ($medias_info as $value) {
                    $data['id'] = $value['id'];
                    $englishMediaModel->startTrans();
                    if ($data['subject'] > 0) {
                        $value['subject'] = $data['subject'];
                    }
                    if ($data['recommend'] > 0 || (!isset($data['recommend']) && $value['recommend'] > 0)) {
                        $data['recommend'] = $englishMediaRecommendModel->getRecommendIdByObjectOrSubject($value['object'], $value['subject']);
                    }
                    if($data['ted']>0){
                        $data['ted'] = $englishMediaTedModel->getTedIdByObjectOrSubject($value['object'], $value['subject']);
                    }
                    $ret = $englishMediaModel->save($data);
                    if (false === $ret) {
                        $englishMediaModel->rollback();
                        $this->ajaxReturn("", "操作失败", false);
                    }
                }
                $englishMediaModel->commit();
                $this->ajaxReturn("", "操作成功", true);
            } else {
                $englishMediaModel->rollback();
                $this->ajaxReturn("", "操作对象或记录不存在", false);
            }
        }
    }

    /**
     * 设置特别推荐
     * @author Adam $date2013.08.30$
     */
    public function setSpecialRecommend() {
        if ($this->isAjax()) {
            $model = D("EnglishMedia");
            $model->startTrans();
            $data = array();
            $map['id'] = $_REQUEST['id'];
            $now_special_recommend = $model->where($map)->getField("special_recommend");
            if($now_special_recommend == 1){
                $special_recommend = 0;
            }else{
                $special_recommend = 1;
            }
            if(false === $model->where($map)->setField("special_recommend",$special_recommend)){
                $model->rollback();
                $this->ajaxReturn("", "操作失败", false);
            }
            $media = $model->find($map['id']);
            if(false === $model->setSpecialRecommend($media, $special_recommend)){
                $model->rollback();
                $this->ajaxReturn("", "操作失败", false);
            }
            $model->commit();
            $data['special_recommend'] = $special_recommend;
            $this->ajaxReturn($data, "操作成功", true);
        }
    }

    /**
     * 设置媒体优先播放类型
     * @author Adam $date2013.09.1$
     */
    public function setPriorityType() {
        if ($this->isAjax()) {
            $id = $_REQUEST['id'];
            $model = D("EnglishMedia");
            $priority_type = $model->where(array("id" => $id))->getField("priority_type");
            if (intval($priority_type) == 1) {
                $priority_type = 2;
            } else {
                $priority_type = 1;
            }
            if (false === $model->where(array("id" => $id))->setField("priority_type", $priority_type)) {
                $this->ajaxReturn("", "操作失败", false);
            } else {
                $this->ajaxReturn($priority_type, "操作成功", true);
            }
        }
    }

    /**
     * 设置推荐
     * @author Adam $date2013.08.31$
     */
    public function setRecommend() {
        if ($this->isAjax()) {
            $model = D("EnglishMedia");
            $data = array();
            $data['id'] = $_REQUEST['id'];
            $info = $model->field("special_recommend,recommend,subject,object")->find($data['id']);

            if (empty($info)) {
                $this->ajaxReturn("", "操作记录不存在", false);
            }
            if (intval($info['recommend']) == 0) {
                $data['recommend'] = D("EnglishMediaRecommend")->getRecommendIdByObjectOrSubject($info['object'], $info['subject']);
            } else {
                $data['special_recommend'] = 0;
                $data['recommend'] = 0;
            }
            $ret = $model->save($data);
            if (false === $ret) {
                $model->rollback();
                $this->ajaxReturn("", "操作失败", false);
            } else {
                $model->commit();
                $this->ajaxReturn($data['special_recommend'], "操作成功", true);
            }
        }
    }

    public function setTed() {
        if ($this->isAjax()) {
            $model = D("EnglishMedia");
            $data = array();
            $data['id'] = $_REQUEST['id'];
            $info = $model->field("ted,subject,object")->find($data['id']);

            if (empty($info)) {
                $this->ajaxReturn("", "操作记录不存在", false);
            }
            $model->startTrans();
            if (intval($info['ted']) == 0) {
                $data['ted'] = D("EnglishMediaTed")->getTedIdByObjectOrSubject($info['object'], $info['subject']);
            } else {
                $data['ted'] = 0;
            }
            $ret = $model->save($data);
            if (false === $ret) {
                $model->rollback();
                $this->ajaxReturn("", "操作失败", false);
            } else {
                $model->commit();
                $this->ajaxReturn($data['ted'], "操作成功", true);
            }
        }
    }
     public function forbid() {
        $name = $this->getActionName();
        $model = D($name);
        $categoryModel = D("EnglishCategory");
        $questionModel = D("EnglishQuestion");
        $questionSpeakModel = D("EnglishQuestionSpeak");
        $pk = $model->getPk();
        $id = $_REQUEST [$pk];
        $forbid_reason = $_REQUEST ['reason'];
        $condition = array($pk => array('in', $id));
        $old_media_list = $model->field("id,status")->where($condition)->select();
        $model->startTrans();
        //$list = $model->forbid($condition);
        $list = $model->where($condition)->save(array(
        		'status'=>0,
        		'forbid_reason'=>$forbid_reason,
        		//'updated'=>time()
        ));
        if ($list !== false) {
            foreach($old_media_list as $value){
                if($value['status'] != 1){
                    continue;
                }
                $media_ids[] = $value['id'];
            }
            $q_map['media_id'] = array("in", $media_ids);
            $q_map['status'] = 1;
            $question_list = $questionModel->field("id,1 as type")->where($q_map)->select();
            $question_speak_list = $questionSpeakModel->field("id,0 as type")->where($q_map)->select();
            if(!empty($question_speak_list)){
                $question_ids = array();
                foreach($question_speak_list as $value){
                    $question_ids[] = $value['id'];
                }
                if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, true, 0)){
                    $model->rollback();
                    $this->error('状态禁用失败！');
                }
            }
            if(!empty($question_list)){
                $question_ids = array();
                foreach($question_list as $value){
                    $question_ids[] = $value['id'];
                }
                if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, true, 1)){
                    $model->rollback();
                    $this->error('状态禁用失败！');
                }
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
        $questionModel = D("EnglishQuestion");
        $questionSpeakModel = D("EnglishQuestionSpeak");
        $pk = $model->getPk();
        $id = $_REQUEST [$pk];
        $condition = array($pk => array('in', $id));
        $old_media_list = $model->field("id,status")->where($condition)->select();
        $model->startTrans();
        $list = $model->resume($condition);
        
        if ($list !== false) {
            foreach($old_media_list as $value){
                if($value['status'] == 1){
                    continue;
                }
                $media_ids[] = $value['id'];
            }
            $q_map['media_id'] = array("in", $media_ids);
            $q_map['status'] = 1;
            $question_list = $questionModel->field("id,1 as type")->where($q_map)->select();
            $question_speak_list = $questionSpeakModel->field("id,0 as type")->where($q_map)->select();
            if(!empty($question_speak_list)){
                $question_ids = array();
                foreach($question_speak_list as $value){
                    $question_ids[] = $value['id'];
                }
                if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, false, 0)){
                    $model->rollback();
                    $this->error('状态启用失败！');
                }
            }
            if(!empty($question_list)){
                $question_ids = array();
                foreach($question_list as $value){
                    $question_ids[] = $value['id'];
                }
                if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, false, 1)){
                    $model->rollback();
                    $this->error('状态启用失败！');
                }
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
        $questionModel = D("EnglishQuestion");
        $questionSpeakModel = D("EnglishQuestionSpeak");
        $pk = $model->getPk();
        $id = $_REQUEST [$pk];
        $del_reason = $_REQUEST ['reason'];
        $condition = array($pk => array('in', $id));
        $old_media_list = $model->field("id,status")->where($condition)->select();
        $model->startTrans();
        //$list = $model->where($condition)->setField("status",-1);
        $list = $model->where($condition)->save(array(
        		'status'=>-1,
        		'del_reason'=>$del_reason,
        		//'updated'=>time()
        ));
        if ($list !== false) {
            foreach($old_media_list as $value){
                if($value['status'] != 1){
                    continue;
                }
                $media_ids[] = $value['id'];
            }
            $q_map['media_id'] = array("in", $media_ids);
            $q_map['status'] = 1;
            $question_list = $questionModel->field("id,1 as type")->where($q_map)->select();
            $question_speak_list = $questionSpeakModel->field("id,0 as type")->where($q_map)->select();
            if(!empty($question_speak_list)){
                $question_ids = array();
                foreach($question_speak_list as $value){
                    $question_ids[] = $value['id'];
                }
                if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, true, 0)){
                    $model->rollback();
                    $this->error('删除失败！');
                }
            }
            if(!empty($question_list)){
                $question_ids = array();
                foreach($question_list as $value){
                    $question_ids[] = $value['id'];
                }
                if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, true, 1)){
                    $model->rollback();
                    $this->error('删除失败！');
                }
            }
            
            $model->commit();
            $this->success('删除成功', $this->getReturnUrl());
        } else {
            $model->rollback();
            $this->error('删除失败！');
        }
    }
    
    /**
	 * @desc 排序
	 */
	public function special_recommend_sort(){
		$sortId = $this->_param('sortId');
		$model = D("EnglishMedia");
		$map = array();
		$map['status'] = 1;
        $map['special_recommend'] = 1;
        $thumb = $this->_param("thumb");
        if(isset($thumb)){
            if($thumb == 1){
                $map['media_thumb_img'] = array("neq","");
            }else{
                $map['media_thumb_img'] = array("eq","");
            }
        }else{
            $thumb = -1;
        }
        $this->assign("thumb",$thumb);
        $sortList = $model->where($map)->order('special_recommend_sort ASC,id asc')->select();
        foreach ($sortList as &$value) {
        	$value['txt_show'] = $value['name']."　　　　　";
        }
        $this->assign("sortList", $sortList);
        $this->display("special_recommend_sort");
        return;
    }
    public function saveSort() {
        $seqNoList = $_POST ['seqNoList'];
        if (!empty($seqNoList)) {
            //更新数据对象
            $name = $this->getActionName();
            $model = D($name);
            $col = explode(',', $seqNoList);
            //启动事务
            $model->startTrans();
            $result = true;
            foreach ($col as $val) {
                $val = explode(':', $val);
                $sort = $model->where("id = '%s'", $val[0])->getField('special_recommend_sort');
                if ($sort == $val[1]) {
                    continue;
                }
                $model->id = $val[0];
                $model->special_recommend_sort = $val[1];
                $temp_result = $model->save();
                if (!$temp_result) {
                    $result = false;
                    Log::write('保存排序失败：' . $model->getLastSql(), Log::SQL);
                }
            }

            if ($result) {
                $model->commit();
                //采用普通方式跳转刷新页面
                $this->success('更新成功');
            } else {
                // 回滚事务
                $model->rollback();
                $this->error($model->getError());
            }
        }
    }

}

?>
