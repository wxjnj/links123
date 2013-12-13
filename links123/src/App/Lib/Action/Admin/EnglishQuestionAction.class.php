<?php
import("App.Logic.Admin.EnglishQuestionLogic");
import("App.Logic.Admin.EnglishLevelnameLogic");
class EnglishQuestionAction extends CommonAction {

    protected $cEnglishQuestionLogic  = null;
    protected $cEnglishLevelnameLogic = null;
    protected $forbid_reason_options = array();
    protected $del_reason_options = array();
    
    public function _initialize() {
        $this->cEnglishQuestionLogic  = new EnglishQuestionLogic();
        $this->cEnglishLevelnameLogic = new EnglishLevelnameLogic();
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
       	    $_SESSION['english_question_search_key'] = $name;
        }
        $search_key = '';//搜索栏保留上次的关键字
        if(isset($_SESSION['english_question_search_key'])){
        	$search_key = $_SESSION['english_question_search_key'];
        }
        $this->assign('search_key', $search_key);
        
        $attr_one = -1;
        $attr_two = 1;
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
                array("EXP","IS NULL"),
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
        if (isset($_REQUEST['status'])) {
            if ($_REQUEST['status'] != -2) {
                $map['englishQuestion.status'] = intval($_REQUEST['status']);
            }
            $param['status'] = intval($_REQUEST['status']);
            if($param['status'] == 0){
            	$param['forbid_reason'] = isset($_REQUEST['forbid_reason'])?intval($_REQUEST['forbid_reason']):0;
            	if($param['forbid_reason'] > 0){
            		$map['englishQuestion.forbid_reason'] = $param['forbid_reason'];
            	}
            }
            if($param['status'] == -1){
            	$param['del_reason'] = isset($_REQUEST['del_reason'])?intval($_REQUEST['del_reason']):0;
            	if($param['del_reason'] > 0){
            		$map['englishQuestion.del_reason'] = $param['del_reason'];
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
            $key['englishQuestion.id'] = $name;
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
            $count = $model->where($map)->count('englishQuestionSpeak.id');
        } elseif ($model->getModelName() == 'EnglishMediaView') {
            $count = $model->where($map)->count('englishMedia.id');
        } else {
            $count = $model->where($map)->count('id');
        }
//        echo $model->getlastsql()."<br />";
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
            $voList = $model->where($map)->order("`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->group("englishQuestion.id")->select();
           
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
        $model = new EnglishQuestionViewModel();
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
        $this->assign("type", 1);//听力
        
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
        $this->assign("forbid_reason_options", $this->forbid_reason_options);
        $this->assign("del_reason_options", $this->del_reason_options);
        $this->display();
        return;
    }
    public function getLevelList(){
        if($this->isAjax()){
            $level = intval($_REQUEST['level']) > 0 ? intval($_REQUEST['level']) : 1;
            $level_one = intval($_REQUEST['level_one']);
            $level_two = intval($_REQUEST['level_two']);
            
            if($level == 1){
                $ret = D("EnglishLevelname")->where("level=1 and status=1")->order("sort asc")->select();
            }elseif ($level == 2) {
                $map['category.level_one'] = $level_one;
                $group = 'category.level_two';
                $flag = "level_two";
            }elseif ($level == 3) {
                $map['category.level_one'] = $level_one;
                $map['category.level_two'] = $level_two;
                $group = 'category.level_thr';
                $flag = "level_thr";
            }
            if(!empty($map)){
                $model = D("EnglishCategory");
                $ret = $model->alias("category")
                        ->field("category.".$flag." as id,levelname.name")
                        ->join("RIGHT JOIN ".C("DB_PREFIX")."english_levelname levelname on levelname.id=category.".$flag)
                        ->where($map)
                        ->group($group)
                        ->order("category.".$flag."_sort asc")
                        ->select();
                
            }
            $this->ajaxReturn($ret,$model->getLastSql(),true);
        }
    }


    public function property () {
        $question_id = intval($_REQUEST["qid"]);
        $type = 1;
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
        $type        = isset($_REQUEST["type"])      ? intval($_REQUEST["type"])      : 1;
        $pattern        = isset($_REQUEST["pattern"])      ? intval($_REQUEST["pattern"])      : 1;

        $model = D("EnglishCatquestion");
        $model->startTrans();
        $ret = $this->cEnglishQuestionLogic->saveProperty(
                                                    $question_id, 
                                                    null, 
                                                    null, 
                                                    $pattern, 
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
        //$type        = isset($_REQUEST["type"])      ? intval($_REQUEST["type"])      : 1;
        $type = 1; //听力
        
        $model = D("EnglishCatquestion");
        $cat_map = array(
            "cat_id"=>$cat_id,
            "question_id"=>$question_id,
            "type"=>$type
        );
        $cat_attr_id = D("EnglishCategory")->where(array("cat_id"=>$cat_id))->getField("cat_attr_id");
        if($cat_attr_id !== false){
            $cat_attr_id = sprintf("%03d",decbin($cat_attr_id));
            $voice = substr($cat_attr_id, 0, 1);
            $target = substr($cat_attr_id, 1, 1);
            $pattern = substr($cat_attr_id, 2, 1);
        }
        $model->startTrans();
        if(false === $model->where($cat_map)->delete()){
            $model->rollback();
            $this->error("操作失败");
        }
        $questionModel =D("EnglishQuestion");
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
            "catquestion.type"=>1,
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
            "type"=>1
        );
        $model->startTrans();
        if (false !== $model->where($condition)->delete()) {
            $question_map = array(
                "media.status"=>1,
                "question.status"=> 1,
                "question.id"=>$question_id
            );
            $questionModel = D("EnglishQuestion");
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

    public function add() {
        //@ 一级类目
        $category["level_one"] = $this->cEnglishLevelnameLogic->getCategoryLevelListBy("1");
        //@ 二级类目
        $category["level_two"] = $this->cEnglishLevelnameLogic->getCategoryLevelListBy("2");
        
        $category["level_thr"] = $this->cEnglishLevelnameLogic->getCategoryLevelListBy("3");

        $this->assign("category", $category);
        $this->assign("forbid_reason_options", $this->forbid_reason_options);
        $this->assign("del_reason_options", $this->del_reason_options);
        $this->display();
    }

    public function insert() {
        $model = D("EnglishQuestion");
        $answer = intval($_POST['answer']);
        $optionModel = D("EnglishOptions");
        $is_empty_question  = intval($_POST['is_empty_question']) == 1 ? true : false;
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
        $index = array(1, 2, 3, 4);
        foreach ($_POST['option'] as $key => $value) {
            if (!empty($value)) {
                $d_1 = preg_match("/all(\s)+of(\s)+the(\s+)above.?/i", $value);
                $d_2 = preg_match("/none(\s)+of(\s)+the(\s)+above.?/i", $value);
                $d_3 = preg_match("/either(\s)+B(\s)+or(\s)+C.?/i", $value);
                $d_4 = preg_match("/(both(\s)+)?B(\s)+and(\s)+C.?/i", $value);
                $c_1 = preg_match("/(both(\s)+A)?(\s)+and(\s)+B.?/i", $value);
                $c_2 = preg_match("/either(\s)+A(\s)+or(\s)+B.?/i", $value);
                $option_data['content'] = $value;
                $option_data['sort'] = current($index);
                if ($d_1 || $d_2 || $d_3 || $d_4) {
                    $option_data['sort'] = 4; //D
                } else if ($c_1 || $c_2) {
                    $option_data['sort'] = 3; //C
                }
                unset($index[array_search($option_data['sort'], $index)]); //已排序的序号删除

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
            $mediaMap['media_source_url'] = array("like",$_REQUEST['media_source_url']);
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
        if (!$is_empty_question && $model->status == 1 && count(array_unique($_POST['option'])) < count($_POST['option']) && !($is_double_false && $is_double_true)) {
            $model->status = 0;
        }

        $model->answer = intval($option_id[$answer - 1]);
        if (!$is_empty_question && ($model->status == 1 && $answer <= 0 || empty($option_id) || empty($option_id[$answer - 1]))) {
            $model->status = 0;
            $model->answer = 0;
        }
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
        if ($list !== false) { //保存成功
            if(!empty($option_id)){
                if (false === $optionModel->where("id in (" . implode(",", $option_id) . ")")->setField("question_id", $list)) {
                    $model->rollback();
                    $this->error('新增失败!');
                }
            }
            $question_id = $list;
        
            $voice       = intval($_REQUEST["voice"]) ;
            $pattern     = intval($_REQUEST["pattern"]);
            $level_one   = intval($_REQUEST["level_one"]);
            $level_two   = intval($_REQUEST["level_two"]);
            $level_thr   = intval($_REQUEST["level_thr"]);
            $status      = intval($_REQUEST["status"]);
            $type        = 1;//听力
            $target      = 1;//听力
	
			
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
                                                        true
                                                    );
            if ($ret === false) {
                $model->rollback();
                $this->error($this->cEnglishQuestionLogic->getErrorMessage());
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
        $vo['media_name'] = htmlspecialchars($vo['media_name']);
        $vo['name'] = htmlspecialchars($vo['name']);
        $option_list = D("EnglishOptions")->getQuestionOptionList($id, false);
        if(empty($vo['content'])&& intval($vo['answer']) == 0 && empty($option_list)){
            $this->assign("is_empty_question",1);
        }
        $this->assign('option_list', $option_list);
        $this->assign('vo', $vo);
        $this->assign('doubleQuotes', '"');

        $object_list = D("EnglishObject")->where("`status`=1")->order("sort")->select();
        $this->assign("object_list", $object_list);
        $level_list = D("EnglishLevel")->where("`status`=1")->order("sort")->select();
        $this->assign("level_list", $level_list);
        $this->assign("forbid_reason_options", $this->forbid_reason_options);
        $this->assign("del_reason_options", $this->del_reason_options);
        $this->display();
    }

    public function update() {
        $answer = intval($_POST['answer']);
        $optionModel = D("EnglishOptions");
        $id = intval($_REQUEST['id']);
        $optionModel->startTrans();
        $is_empty_question  = intval($_POST['is_empty_question']) == 1 ? true : false;
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
        $index = array(1, 2, 3, 4);
        foreach ($_POST['option'] as $key => $value) {
            if (!empty($value)) {
                $d_1 = preg_match("/all(\s)+of(\s)+the(\s+)above.?/i", $value);
                $d_2 = preg_match("/none(\s)+of(\s)+the(\s)+above.?/i", $value);
                $d_3 = preg_match("/either(\s)+B(\s)+or(\s)+C.?/i", $value);
                $d_4 = preg_match("/(both(\s)+)?B(\s)+and(\s)+C.?/i", $value);
                $c_1 = preg_match("/(both(\s)+A)?(\s)+and(\s)+B.?/i", $value);
                $c_2 = preg_match("/either(\s)+A(\s)+or(\s)+B.?/i", $value);
                $option_data['content'] = $value;
                $option_data['sort'] = current($index); //获取最前面的序号
                if ($d_1 || $d_2 || $d_3 || $d_4) {
                    $option_data['sort'] = 4; //D
                } else if ($c_1 || $c_2) {
                    $option_data['sort'] = 3; //C
                }

                unset($index[array_search($option_data['sort'], $index)]); //已排序的序号删除

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
        //选项是否有重复，有则题目停用
        if (!$is_empty_question && $model->status == 1 && count(array_unique($_POST['option'])) < count($_POST['option']) && !($is_double_false && $is_double_true)) {
            $model->status = 0;
        }
        $model->answer = $answer_id;
        if (!$is_empty_question && $model->status == 1 && ($answer <= 0 || $answer_id == 0)) {
            $model->status = 0;
            $model->answer = 0;
        }
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
        //$model->updated = time();
        //保存当前数据对象
        if (false === $model->save()) {
            $model->rollback();
            $this->error('编辑失败！');
        }
        //更新分类试题数量
        if($_POST['old_status'] == 1){
            if($_POST['status'] != 1 && $mediaInfo['status'] == 1){
                if(false == D("EnglishCategory")->updateCategoryQuestionNumByQuestion($id, true, 1)){
                    $model->rollback();
                    $this->error('编辑失败！');
                }
            }
        }else{
            if($_POST['status'] == 1 && $mediaInfo['status'] == 1){
                if(false == D("EnglishCategory")->updateCategoryQuestionNumByQuestion($id, false, 1)){
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
            /**$上传excel文件 开始*/
            //
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
            /*上传excel文件 结束$**/

            
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
            
            Log::write("导入听力试题，excel表为:".$dest, Log::INFO);

            $objPHPExcel = $objReader->load($dest);
            
            /**$数据准备 开始*/
            //
            //声明模型类
            $model = D("EnglishQuestion");
            $optionModel = D("EnglishOptions");
            $mediaModel = D("EnglishMedia");
            $categoryModel =D("EnglishCategory");
            $levelnameModel = D("EnglishLevelname");
            $englishCatquestionModel = D('EnglishCatquestion');

            //@ 建立类目字典
            $level_name_list = array();
            $levelnames = D('EnglishLevelname')->select();
            $level_one_list = array();
            $excel_level_one_list =array();
            $level_two_max_sort = 0;
            $object_level_one_id = 0;//选择课程 分类的id
            $target = 1;//听力
            $level_sort = array();
            $difficulty_list = array();//新分类的默认难度id
            foreach($levelnames as $key=>$each_lv) {
                $levelnames[$key]["name"] = $each_lv["name"] = preg_replace("/\s+/", '', $each_lv["name"]); //将开头或结尾的一个或多个半角空格转换为空
                if(!$level_name_list[$each_lv["name"]]){
                    $level_name_list[$each_lv["name"]] = $each_lv["id"];
                }
                $level_sort[$each_lv["id"]] = $each_lv["sort"];
                if($each_lv['level'] == 1){
                    $level_one_list[$each_lv["name"]] = $each_lv["id"];
                    if($each_lv['default'] == 1){
                        $object_level_one_id = $each_lv['id'];
                    }
                }else if($each_lv['level'] == 2){
                    $level_two_max_sort = $level_two_max_sort > $each_lv['sort'] ? $level_two_max_sort : $each_lv['sort'];
                }else if($each_lv['level'] == 3){
                    if($each_lv['name'] == "初级"){
                        $difficulty_list["初级"] = $each_lv['id'];
                    }else if($each_lv['name'] == "中级"){
                        $difficulty_list["中级"] = $each_lv['id'];
                    }else if($each_lv['name'] == "高级"){
                        $difficulty_list["高级"] = $each_lv['id'];
                    }
                }
            }
            //年级列表
            $level_map = array(
                "cat_attr_id"=>7,
                "level_one"=>$object_level_one_id,
                "level_two"=>array("gt",0),
                "level_thr"=>array("gt",0)
            );
            $level_map['b.name'] = array(array('exp','not like "%初级%"'), array('exp','not like "%中级%"'), array('exp','not like "%高级%"'),'and'); 
            $level_thr_ret = $categoryModel
                    ->alias("cat")
                    ->join(C("DB_PREFIX")."english_levelname b on cat.level_thr=b.id")
                    ->field("cat.level_thr as id,b.name,cat.level_thr_sort as sort")
                    ->where($level_map)
                    ->group("cat.level_thr")
                    ->order("cat.level_thr_sort asc")
                    ->select();
            $grade_name_list = array();
            foreach($level_thr_ret as $value){
                $grade_list[] = $value['id'];
                $grade_name_list[$value['name']] = $value;
            }
            $difficulty_from_grade_list = array();
            foreach ($grade_name_list as $value) {
                if ($value['sort'] <= $grade_name_list['小六']['sort']) {
                    $difficulty_from_grade_list[$value['name']] = "初级";
                } else if ($value['sort'] >= $grade_name_list['大一']['sort']) {
                    $difficulty_from_grade_list[$value['name']] = "高级";
                } else {
                    $difficulty_from_grade_list[$value['name']] = "中级";
                }
            }
            
            $model->startTrans();
            $time = time();
            Log::write("导入听力试题，时间戳：".$time, Log::INFO);
            $is_standard_excel = true;
            $total = 0;
            $skip_repeat = 0;
            $success = 0;
            /**数据准备 结束$*/
            //
            //循环读取所有表,表迭代器
            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                foreach ($worksheet->getRowIterator() as $row) {
                    //行迭代器
                    $cellIterator = $row->getCellIterator();
                    $data = array(); //保存试题数据的数组
                    $data['status'] = 1;
                    $media_data = array(); //保存媒体数据的数组
                    $media_data['status'] = 1;
                    $repeat_ret = false; //题目是否重复
                    $cellIterator->setIterateOnlyExistingCells(false); //单元格为空也迭代
                    foreach ($cellIterator as $cell) {
                        //单元格迭代器
                        if (!is_null($cell)) {
                            //表头
                            if($cell->getRow() == 1){
                                //验证表格是否符合标准
                                if ($cell->getColumn() == "A") {
                                    if(ftrim($cell->getCalculatedValue())!="试题名称"){
                                        $is_standard_excel = false;
                                        break;
                                    }
                                }  else if ($cell->getColumn() == "N") {
                                    if(ftrim($cell->getCalculatedValue())!="视频来源地址"){
                                        $is_standard_excel = false;
                                        break;
                                    }
                                } else if ($cell->getColumn() == "T") {
                                    if(ftrim($cell->getCalculatedValue())!="选项D内容"){
                                        $is_standard_excel = false;
                                        break;
                                    }
                                }
                                //根据excel的表头对应到一级分类id
                                if(intval($level_one_list[ftrim($cell->getCalculatedValue())]) > 0){
                                    $excel_level_one_list[$cell->getColumn()] = intval($level_one_list[ftrim($cell->getCalculatedValue())]);
                                }
                            }else{
                                if ($cell->getColumn() == "A") {
                                    $data['name'] = ftrim($cell->getCalculatedValue()); //名称
                                } else if ($cell->getColumn() == "B") {
                                    $data['voice'] = intval($cell->getCalculatedValue()) == 2 ? 0 : 1; //语种，英音，美音
                                } else if ($cell->getColumn() == "C") {
                                    $data['pattern'] = intval($cell->getCalculatedValue()) == 2 ? 0 : 1; //类型，视频，音频
                                } else if ($cell->getColumn() == "D") {
                                    //说力听力，无用
                                } else if ($cell->getColumn() == "E") {
                                    //年级
                                    $data['grade'] = ftrim($cell->getCalculatedValue()); //二级分类，年级，选择课程顶级分类使用
                                    if($difficulty_from_grade_list[$data['grade']]){
                                        $data['difficulty'] = $difficulty_from_grade_list[$data['grade']];
                                    }
                                } else if ($cell->getColumn() == "I") {
                                    $media_data['special_recommend'] = intval($cell->getCalculatedValue()); //特别推荐
                                } else if ($cell->getColumn() == "N") {
                                    $data['media_text_url'] = $media_data['media_source_url'] = ftrim($cell->getCalculatedValue()); //媒体内容地址
                                } else if ($cell->getColumn() == "O") {
                                    $data['content'] = ftrim($cell->getCalculatedValue()); //题目内容
                                } else if ($cell->getColumn() == "P") {
                                    $data['answer'] = intval($cell->getCalculatedValue()); //题目答案
                                } else if ($cell->getColumn() == "Q") {
                                    $data['option'][0] = ftrim($cell->getCalculatedValue()); //题目选项一
                                } else if ($cell->getColumn() == "R") {
                                    $data['option'][1] = ftrim($cell->getCalculatedValue()); //题目选项一
                                } else if ($cell->getColumn() == "S") {
                                    $data['option'][2] = ftrim($cell->getCalculatedValue()); //题目选项一
                                } else if ($cell->getColumn() == "T") {
                                    $data['option'][3] = ftrim($cell->getCalculatedValue()); //题目选项一
                                } else if (intval($excel_level_one_list[$cell->getColumn()]) > 0) {
                                    $calculatedValue = ftrim($cell->getCalculatedValue());
                                    if(!empty($calculatedValue)){
                                        //收集分类信息，存在多个分类情况
                                        $level_one = intval($excel_level_one_list[$cell->getColumn()]);
                                        $data['category'][$level_one]['level_one'] = $level_one;//一级分类id
                                        $data['category'][$level_one]['level_two_name'] = ftrim($cell->getCalculatedValue());//二级分类名称
                                        if($level_one == $object_level_one_id){
                                            $data['category'][$level_one]['level_thr'] = intval($level_name_list[$data['grade']]);//三级分类
                                        }else{
                                            $data['category'][$level_one]['level_thr'] = intval($level_name_list[$data['difficulty']]);//三级分类
                                        }
                                        //如果二级分类名为1，则分类名和选择课程的名称一样
                                        if(intval($data['category'][$level_one]['level_two_name']) == 1){
                                            $data['category'][$level_one]['level_two_name'] = $data['category'][$object_level_one_id]['level_two_name'];
                                            $data['category'][$level_one]['level_thr'] = intval($level_name_list[$data['difficulty']]);//三级分类
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $data['cat_attr_id'] = bindec($data['voice']."".$target."".$data['pattern']);//分类顶级分类id
                    //跳过第一行，并判断表格是否标准
                    if (empty($data['name']) || $row->getRowIndex()==1) {
                        if(false == $is_standard_excel){
                            Log::write("导入失败，表格格式错误！：", Log::ERR);
                            die(json_encode(array("info" => "导入失败，表格格式错误！", "status" => false)));
                        }
                        continue;
                    }
                    $total++;
                    $data['cat_id'] = array();
                    //处理分类信息
                    if(!empty($data['category'])){
                        foreach ($data['category'] as $key=>$value){
                            $cat_id = 0;//本次对应的分类id
                            //@ 检查二级分类是否存在，不存在则添加类目，并添加到category表
                            $level_name_key = preg_replace("/\s+/", '', $value['level_two_name']);
                            if (intval($level_name_list[$level_name_key]) == 0) {
                                $new_levelname_data = array(
                                    "name" => $value['level_two_name'], 
                                    "level" => 2,
                                    "created" => $time, 
                                    "updated" => $time, 
                                    "default" => "0", 
                                    "sort" => ++$level_two_max_sort
                                );
                                $new_id = $levelnameModel->add($new_levelname_data);
                                if(FALSE === $new_id){
                                    $model->rollback();
                                    Log::write("导入失败，新增分类名称失败！：".$levelnameModel->getLastSql(), Log::ERR, true);
                                    die(json_encode(array("info" => "导入失败，新增分类名称失败！","status" => false)));
                                }
                                Log::write("新增levelname:".$levelnameModel->getLastSql(), Log::INFO);
                                $level_name_list[$level_name_key] = $new_id;
                            }
                            //逐一添加二级分类下的三级分类
                            //默认三级分类列表为难度列表
                            $level_thr_list = $difficulty_list;
                            if($value['level_one'] == $object_level_one_id){
                                $level_thr_list = $grade_list;//如果是选择课程的新分类，逐一增加年级
                            }
                            $sort = 0;
                            foreach ($level_thr_list as $key=>$level_thr){
                                $cat_data = array();
                                $cat_data['cat_attr_id'] = $data['cat_attr_id'];
                                $cat_data['level_one'] = $value['level_one'];
                                $cat_data['level_two'] = $level_name_list[$level_name_key];
                                $cat_data['level_thr'] = $level_thr;
                                $this_cat_id = $categoryModel->where($cat_data)->getField("cat_id");
                                Log::write("查询【".$value['level_two_name']."】的三级分类:".$categoryModel->getLastSql(), Log::INFO);
                                if(intval($this_cat_id) == 0){
                                    $cat_data['level_thr_sort'] = ++$sort;
                                    $cat_data['level_two_sort'] = ++$level_two_max_sort;
                                    $cat_data['level_one_sort'] = $level_sort[$value['level_one']];
                                    $cat_data['updated'] = $cat_data['created'] = $time;
                                    $new_cat_id = $categoryModel->add($cat_data);
                                    if(FALSE === $new_cat_id){
                                        $model->rollback();
                                        Log::write("导入失败，新增分类失败！：".$categoryModel->getLastSql(), Log::ERR);
                                        die(json_encode(array("info" => "导入失败，新增分类失败！", "status" => false)));
                                    }
                                    Log::write("新增【".$value['level_two_name']."】的三级分类:".$categoryModel->getLastSql(), Log::INFO);
                                    //获取本次试题对应的分类id
                                    if($value['level_thr'] == $level_thr){
                                        $cat_id = $new_cat_id;
                                    }
                                }else{
                                    if($value['level_thr'] == $level_thr){
                                        $cat_id = $this_cat_id;
                                    }
                                }
                            }
                            //保存分类id，用于后面更新分类下的试题数量
                            $data['cat_id'][] = $cat_id;
                        } 
                    }else{
                        $data['status'] = 0;//分类信息为空，试题锁住
                    }

                    //根据问题内容、视频、科目、等级以及答案内容查询是否有重复
                    $condition['media.media_source_url'] = array("like", $media_data['media_source_url']);
                    if(intval($data['answer']) > 0){
                        $condition['english_options.content'] = array("like", $data['option'][$data['answer'] - 1]);
                        $condition['question.content'] = array("like", $data['content']);
                    }
                    $repeat_ret = $model->alias("question")
                            ->join(C("DB_PREFIX") . "english_media media on media.id=question.media_id")
                            ->join(C("DB_PREFIX") . "english_options english_options on question.answer=english_options.id")
                            ->where($condition)
                            ->count();
                    //重复则跳过
                    if (false != $repeat_ret && $repeat_ret > 0) {
                        Log::write("导入听力试题，跳过重复的记录：".$data['name'], Log::INFO);
                        $skip_repeat++;
                        continue;
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
                            'nationalgeographic.com' => '_nationalgeographic',
//                            'youtube.com' => '_youtube'
                        );
                        foreach ($supportWebsite as $k => $v) {
                            if (false !== stripos($data['media_text_url'], $k)) {
                                $data['status'] = 1;
                                $media_data['status'] = 1;
                                break;
                            } else {
                                $data['status'] = 0;
                                $media_data['status'] = 0;
                            }
                        }
                    }
                    //
                    //获取媒体的id
                    $media_info = $mediaModel->field("id,local_path")->where(array("media_source_url" => array("like", $media_data['media_source_url'])))->find();
                    $mediaId = intval($media_info['id']);
                    //
                    //来源地址未匹配到媒体，则添加媒体
                    if ($mediaId == 0) {
                        $media_data['name'] = $data['name'];
                        $media_data['updated'] = $time;
                        $media_data['created'] = $time;
                        //等级、科目、专题的名称换成对应的id
                        if ($media_data['special_recommend'] == 1) {
                            $media_data['recommend'] = 1;
                        }
                        
                        $mediaId = $mediaModel->add($media_data);
                        if (false === $mediaId) {
                            $model->rollback();
                            Log::write("导入失败，添加媒体到媒体表失败：".$mediaModel->getLastSql(), Log::ERR);
                            die(json_encode(array("info" => "导入失败，添加媒体到媒体表失败！", "status" => false)));
                        }
                    }
                    $data['media_id'] = intval($mediaId);
                    //没有媒体id，题目禁用
                    if ($data['media_id'] == 0) {
                        $data['status'] = 0;
                        Log::write("导入听力试题，未找到对应的媒体：".$media_data['media_source_url'], Log::INFO);
                    }
                    //插入答案
                    $option_id = array();
                    if($data['answer'] != -1){
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
                            Log::write("导入听力试题，有重复选项，锁住：".$data['name'], Log::INFO);
                        }
                        //
                        //依次存入选项，不知道问题id
                        $option_data['created'] = $time;
                        $index = array(1, 2, 3, 4); //选择序号数组
                        $hasBandCspecialOption = false;//Both B and C或者either B or C选项
                        foreach ($data['option'] as $key => $value) {
                            if (!empty($value)) {
                                $d_1 = preg_match("/all(\s)+of(\s)+the(\s+)above.?/i", $value);
                                $d_2 = preg_match("/none(\s)+of(\s)+the(\s)+above.?/i", $value);
                                $d_3 = preg_match("/either(\s)+B(\s)+or(\s)+C.?/i", $value);
                                $d_4 = preg_match("/(both(\s)+)?B(\s)+and(\s)+C.?/i", $value);
                                $c_1 = preg_match("/(both(\s)+A)?(\s)+and(\s)+B.?/i", $value);
                                $c_2 = preg_match("/either(\s)+A(\s)+or(\s)+B.?/i", $value);
                                if($d_3 || $d_4){
                                    $hasBandCspecialOption = true;
                                }
                                $option_data['content'] = $value;
                                $option_data['sort'] = current($index); //选项排序等于当前最前面序号
                                if($hasBandCspecialOption && $key == 3){
                                    $option_data['sort'] = 1;
                                }else if($hasBandCspecialOption && $key == 1){
                                    $option_data['sort'] = 2;
                                }else if($hasBandCspecialOption && $key == 2){
                                    $option_data['sort'] = 3;
                                }
                                
                                if ($d_1 || $d_2 || $d_3 || $d_4) {
                                    $option_data['sort'] = 4; //D
                                } else if ($c_1 || $c_2) {
                                    $option_data['sort'] = 3; //C
                                }
                                unset($index[array_search($option_data['sort'], $index)]); //已排序的序号删除

                                $ret = $optionModel->add($option_data);
                                if (false === $ret) {
                                    $model->rollback();
                                    Log::write("导入失败，添加试题选项失败：".$optionModel->getLastSql(), Log::ERR);
                                    die(json_encode(array("info" => "导入失败，添加试题选项失败！", "status" => false)));
                                }
                                Log::write("添加试题选项：".$optionModel->getLastSql(), Log::INFO);
                                array_push($option_id, $ret); //保存增加的id数组，用于更新选项对应的问题id
                            }
                        }
                        //答案id
                        $data['answer'] = $option_id[$data['answer'] - 1];
                        //没有答案或者不是双选下选项小于4
                        if ($data['answer'] == 0 || (count($option_id) < 4 && !($is_double_false && $is_double_true))) {
                            $data['status'] = 0;
                            $data['answer'] = 0;
                            Log::write("导入听力试题，选项小于四个或没有答案，锁住：".$data['name'], Log::INFO);
                        }
                    }

                    
                    $data['created'] = $time;
                    $data['updated'] = $data['created'];

                    //保存当前数据对象
                    $list = $model->add($data);
                    Log::write("新增试题:".$model->getLastSql(), Log::INFO);
                    if ($list !== false) { //保存成功
                        if (!empty($option_id)) {
                            if (false === $optionModel->where("id in (" . implode(",", $option_id) . ")")->setField("question_id", $list)) {
                                //更新答案对应的题目id
                                $model->rollback();
                                Log::write("导入失败，更新选项和试题关联失败:".$optionModel->getLastSql(), Log::ERR);
                                die(json_encode(array("info" => "导入失败，更新选项和试题关联失败！", "status" => false)));
                            }
                            Log::write("关联选项:".$optionModel->getLastSql(), Log::INFO);
                        }
                        //@ 添加类目id和题目id到对应表
                        if(!empty($data['cat_id'])){
                            foreach ($data['cat_id'] as $value){
                                $cat_question_data = array("cat_id" => $value, "question_id" => $list, "created" => $time,"type" => 1, "status" => 1);
                                $new_cat_question = $englishCatquestionModel->add($cat_question_data);
                                if($new_cat_question === false){
                                    $model->rollback();
                                    Log::write("导入失败，添加分类和试题关联失败:".$englishCatquestionModel->getLastSql(), Log::ERR);
                                    die(json_encode(array("info" => "导入失败，更新分类和试题关联失败！", "status" => false)));
                                }
                                Log::write("新增试题分类关联:".$englishCatquestionModel->getLastSql(), Log::INFO);
                                //更新分类下的有效试题数量
                                if($data['status'] == 1 && $media_data['status'] == 1){
                                    if(false === $categoryModel->where(array("cat_id"=>$value))->setInc("question_num")){
                                        $model->rollback();
                                        Log::write("导入失败，更新分类题目数量失败:".$categoryModel->getLastSql(), Log::ERR);
                                        die(json_encode(array("info" => "导入失败，更新分类题目数量失败！", "status" => false)));
                                    }
                                    Log::write("更新分类题目数量：".$categoryModel->getLastSql(), Log::INFO);
                                }
                            }
                        }
                        //如果拥有科目，则添加试题关联到综合
                        if(!empty($data['category'][$object_level_one_id])){
                            //查找分类id
                            $new_cat = array();
                            $new_cat['cat_attr_id'] = $data['cat_attr_id'];
                            $new_cat['level_one'] = $object_level_one_id;
                            $new_cat['level_two'] = $level_name_list['综合'];
                            $new_cat['level_thr'] = $data['category'][$object_level_one_id]['level_thr'];
                            $new_cat_id = D("EnglishCategory")->where($new_cat)->getField("cat_id");
                            //关联分类
                            $data = array("cat_id" => $new_cat_id, "question_id" => $list, "created" => $time,"type" => 1, "status" => 1);
                            $new_cat_question = $englishCatquestionModel->add($data);
                            if($new_cat_question === false){
                                $model->rollback();
                                Log::write("导入失败，添加分类和试题关联失败:".$englishCatquestionModel->getLastSql(), Log::ERR);
                                die(json_encode(array("info" => "导入失败，更新分类和试题关联失败！", "status" => false)));
                            }
                            Log::write("新增试题综合分类关联:".$englishCatquestionModel->getLastSql(), Log::INFO);
                            //更新分类下的有效试题数量
                            if($data['status'] == 1 && $media_data['status'] == 1){
                                if(false === $categoryModel->where(array("cat_id"=>$new_cat_id))->setInc("question_num")){
                                    $model->rollback();
                                    Log::write("导入失败，更新分类题目数量失败:".$categoryModel->getLastSql(), Log::ERR);
                                    die(json_encode(array("info" => "导入失败，更新分类题目数量失败！", "status" => false)));
                                }
                                Log::write("更新试题综合分类题目数量:".$categoryModel->getLastSql(), Log::INFO);
                            }
                        }
                        $success++;
                    } else {
                        $model->rollback();
                        //失败提示
                        Log::write("导入失败，保存试题信息失败:".$model->getLastSql(), Log::ERR);
                        die(json_encode(array("info" => "导入失败，保存试题信息失败！", "status" => false)));
                    }
                }
            }
//            exit;
            $model->commit();
            Log::write("导入成功,共".$total."个，成功".$success."个，跳过重复".$skip_repeat."个。", Log::INFO);
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
        $categoryModel = D("EnglishCategory");
        $catquestionModel = D("EnglishCatquestion");
        $level_one_list = D("EnglishLevelname")->field("name,id,default")->where("level=1")->order("sort asc")->select();
        foreach ($level_one_list as $value){
            if($value['default'] == 1){
                $object_level_one_id = $value['id'];
            }
        }
        $ret = $model->where($map)->group("englishQuestion.id")->select();
        $optionsModel = D("EnglishOptions");
        foreach ($ret as $key => $value) {
            $options = $optionsModel->where("question_id=" . $value['id'])->order("sort asc")->select();
            foreach ($options as $k => $v) {
                if ($v['id'] == $value['answer']) {
                    $ret[$key]['answer_index'] = $k + 1;
                }
                $ret[$key]['option'][$k] = $v['content'];
            }
            $cate_map = array(
                "catquestion.question_id"=>$value['id'],
                "catquestion.type"=>1,
                "b.default"=>0
                );
            $ret[$key]['categorys'] = $catquestionModel
                    ->alias("catquestion")
                    ->field("category.cat_attr_id,category.level_one,a.name as level_one_name,b.name as level_two_name,c.name as level_thr_name")
                    ->join(C("DB_PREFIX")."english_category category on category.cat_id=catquestion.cat_id")
                    ->join(C("DB_PREFIX")."english_levelname a on a.id=category.level_one")
                    ->join(C("DB_PREFIX")."english_levelname b on b.id=category.level_two")
                    ->join(C("DB_PREFIX")."english_levelname c on c.id=category.level_thr")
                    ->where($cate_map)
                    ->select();
            $ret[$key]['cat_attr_id'] = decbin(intval($ret[$key]['categorys'][0]['cat_attr_id']));
            $ret[$key]['voice'] = substr($ret[$key]['cat_attr_id'], 0,1);
            $ret[$key]['pattern'] = substr($ret[$key]['cat_attr_id'], 2,1);
        }
        if (!empty($ret)) {
            //引入类
            error_reporting(E_ALL);
            date_default_timezone_set('Asia/Shanghai');

            vendor('PHPExcel.Classes.PHPExcel.IOFactory');
            ob_end_clean();
            ob_start();
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
            $objSheet->setCellValue("E1", "1表示听力， 2表示说力");
            $objSheet->setCellValue("F1", "特别推荐");
            $objSheet->setCellValue("G1", "视频源地址");
            $objSheet->setCellValue("H1", "问题");
            $objSheet->setCellValue("I1", "正确答案序号，1对应A ，2对应B等");
            $objSheet->setCellValue("J1", "选项A内容");
            $objSheet->setCellValue("K1", "选项B内容");
            $objSheet->setCellValue("L1", "选项C内容");
            $objSheet->setCellValue("M1", "选项D内容");
            $objSheet->setCellValue("N1", "本地视频地址");
            $objSheet->setCellValue("O1", "年级");
            $objSheet->setCellValue("P1", "难度");
            foreach ($level_one_list as $key=>$value){
                $level_one_list[$value['name']]['column'] = PHPExcel_Cell::stringFromColumnIndex(PHPExcel_Cell::columnIndexFromString("P")+(1+$key));
                $column = $level_one_list[$value['name']]['column']."1";
                $objSheet->setCellValue($column, $value['name']);
            }
            //存入数据
            foreach ($ret as $k => $v) {
                $key = $k + 2;
                $v['local_path'] = date("Ym", $v['media_created']) . "/" . md5($v['media_source_url']);
                $objSheet->setCellValue("A" . $key, $v['id']);
                $objSheet->setCellValue("B" . $key, $v['name']);
                $objSheet->setCellValue("C" . $key, $v['voice']);
                $objSheet->setCellValue("D" . $key, $v['pattern']);
                $objSheet->setCellValue("E" . $key, $v['target']);
                $objSheet->setCellValue("F" . $key, intval($v['special_recommend']));
                $objSheet->setCellValue("G" . $key, $v['media_source_url']);
                $objSheet->setCellValue("H" . $key, $v['content']);
                $objSheet->setCellValue("I" . $key, intval($v['answer_index']));
                $objSheet->setCellValue("J" . $key, $v['option'][0]);
                $objSheet->setCellValue("K" . $key, $v['option'][1]);
                $objSheet->setCellValue("L" . $key, $v['option'][2]);
                $objSheet->setCellValue("M" . $key, $v['option'][3]);
                $objSheet->setCellValue("N" . $key, $v['local_path']);
                foreach ($v['categorys'] as $category) {
                    $col = $level_one_list[$category['level_one_name']]['column'];
                    if(!empty($col)){
                        if($category['level_one'] == $object_level_one_id){
                            $objSheet->setCellValue("O" . $key, $category['level_thr_name']);
                        }else{
                            $objSheet->setCellValue("P" . $key, $category['level_thr_name']);
                        }
                        $objSheet->setCellValue($col . $key, $category['level_two_name']);
                    }
                }
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
            ob_end_clean();
            ob_start();
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
        #$list = $model->forbid($condition);
        $list = $model->where($condition)->save(array(
        		'status'=>0,
        		'forbid_reason'=>$forbid_reason,
        		//'updated'=>time()
        ));
        if ($list !== false) {
            if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, true, 1)){
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
            if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, false, 1)){
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
        $list = $model->where($condition)->save(array(
        	'status'=>-1,
        	'del_reason'=>$del_reason,
        	//'updated'=>time()
        ));
        
        if ($list !== false) {
            if(false === $categoryModel->updateCategoryQuestionNumByQuestion($question_ids, true, 1)){
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

    public function voice(){
        if($this->isAjax()){
            $id = intval($_REQUEST['id']);
            $model = D("EnglishCatquestion");
            $model->startTrans();
            $ret  = $this->cEnglishQuestionLogic->setQuestionCatAttrId($id,"voice",1);
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
            $cat_id = intval($_REQUEST['id']);
            $question_id = intval($_REQUEST['question_id']);
            if(intval($cat_id) == 0 || intval($question_id) == 0){
                $this->ajaxReturn("", "非法操作",false);
            }
            $model = D("EnglishCatquestion");
            $model->startTrans();
            $categoryModel = D("EnglishCategory");
            //是否是有效试题
            $question_info = D("EnglishQuestion")->alias("a")
                ->join(C("DB_PREFIX")."english_media b on a.media_id = b.id")
                ->where(array("a.id"=>$question_id,"a.status"=>1,"b.status"=>1))
                ->find();
            //分类信息
            $cat_info = $categoryModel->find($cat_id);
            if(empty($cat_info)){
                $this->ajaxReturn("", "记录不存在",false);
            }
            $time=time();
            //准备字典
            $levelnames = D('EnglishLevelname')->order("sort asc")->select();
            $difficulty_list = array();//难度列表
            $grad_list = array();//年级列表
            $object_level_one_id = 0;//使用年级的一级分类id
            $object_level_two_id = 0;//使用年级的二级分类id
            //找到难度列表、年级列表和使用年级的一级分类id
            foreach($levelnames as $each_lv) {
                if($each_lv['level'] == 1){
                    if($each_lv['default'] == 1){
                        $object_level_one_id = $each_lv['id'];
                        $object_level_one_sort = $each_lv['sort'];
                    }
                }elseif($each_lv['level'] == 2){
                    if($each_lv['default'] == 1){
                        $object_level_two_id = $each_lv['id'];
                        $object_level_two_sort = $each_lv['sort'];
                    }
                }elseif($each_lv['level'] == 3){
                    if($each_lv['name'] == "初级"){
                        $difficulty_list["初级"] = $each_lv['id'];
                    }else if($each_lv['name'] == "中级"){
                        $difficulty_list["中级"] = $each_lv['id'];
                    }else if($each_lv['name'] == "高级"){
                        $difficulty_list["高级"] = $each_lv['id'];
                    }else{
                        $grad_list[$each_lv['name']] = $each_lv['id'];
                    }
                }
            }
            //获取新的分类attr_id
            $voice = substr(sprintf("%03d",decbin($cat_info['cat_attr_id'])), 0,1);
            $target = substr(sprintf("%03d",decbin($cat_info['cat_attr_id'])), 1,1);
            $pattern = substr(sprintf("%03d",  decbin($cat_info['cat_attr_id'])), 2,1);
            if($pattern == 1){
                $pattern = 0;
            }else{
                $pattern = 1;
            }
            $new_cat_attr_id = bindec($voice."".$target."".$pattern);
            $map = array(
                "cat_attr_id" => $new_cat_attr_id,
                "level_one" => $cat_info['level_one'],
                "level_two" => $cat_info['level_two'],
                "level_thr" => $cat_info['level_thr']
            );
            //删除旧分类关联
            if(false == $model->where(array("cat_id"=>$cat_id,"question_id"=>$question_id,"type"=>1))->delete()){
                $model->rollback();
                $this->ajaxReturn("", "删除旧分类关联失败",false);
            }
            //原来分类的有效试题数更新
            if(!empty($question_info)){
                if(false === $categoryModel->where(array("cat_id"=>$cat_id))->setDec("question_num")){
                    $model->rollback();
                    $this->ajaxReturn("", "更新分类试题数量失败",false);
                }
                Log::write("更新分类试题数量：".$categoryModel->getLastSql(), log::SQL);
            }
            //新分类id
            $new_cat_id = $categoryModel->where($map)->getField("cat_id");
            //不存在新分类则增加
            if(intval($new_cat_id) == 0){
                if($cat_info['level_one'] == $object_level_one_id){
                    $level_thr_list = $grad_list;
                }else{
                    $level_thr_list = $difficulty_list;
                }
                $k = 0;
                foreach($level_thr_list as $level_thr){
                    $map['level_thr'] = $level_thr;
                    if(intval($categoryModel->where($map)->getField("cat_id")) > 0){
                        continue;
                    }
                    $map['created'] = $map['updated'] = $time;
                    $map['level_one_sort'] = $cat_info['level_one_sort'];
                    $map['level_two_sort'] = $cat_info['level_two_sort'];
                    $map['level_thr_sort'] = ++$k;
                    if($level_thr == $cat_info['level_thr'] && !empty($question_info)){
                        $new_cat_map['question_num'] = 1;
                    }else{
                        $new_cat_map['question_num'] = 0;
                    }
                    $new_id = $categoryModel->add($new_cat_map);
                    Log::write("增加分类：".$categoryModel->getLastSql(), log::SQL);
                    if(false === $new_id){
                        $model->rollback();
                        $this->ajaxReturn("", "增加新分类失败",false);
                    }
                    if($level_thr == $cat_info['level_thr']){
                        $new_cat_id = $new_id;
                    }
                }
            }else{
                if(!empty($question_info)){
                    if(false === $categoryModel->where(array("cat_id"=>$new_cat_id))->setInc("question_num")){
                        $model->rollback();
                        $this->ajaxReturn("", "更新分类试题数量失败",false);
                    }
                    Log::write("更新分类试题数量：".$categoryModel->getLastSql(), log::SQL);
                }
            }
            $cat_question_data=array(
                "cat_id"=>$new_cat_id,
                "question_id"=>$question_id,
                "type"=>1,
                "created"=>$time
            );
            if(intval($model->where(array("cat_id"=>$new_cat_id,"question_id"=>$question_id,"type"=>1))->getField("cat_id")) > 0){
                $this->ajaxReturn("", "新的分类已存在!",false);
            }
            if(false === $model->add($cat_question_data)){
                $model->rollback();
                $this->ajaxReturn("", "增加新分类关联失败",false);
            }
            //课程下的分类,同时操作综合
            if($cat_info['level_one'] == $object_level_one_id){
                $object_cat_map = array(
                        "cat_attr_id" => $cat_info['cat_attr_id'],
                        "level_one" => $object_level_one_id,
                        "level_two" => $object_level_two_id,
                        "level_thr" => $cat_info['level_thr']
                );
                $object_cat_id = $categoryModel->where($object_cat_map)->getField("cat_id");
                //删除旧分类关联
                if(false == $model->where(array("cat_id"=>$object_cat_id,"question_id"=>$question_id,"type"=>1))->delete()){
                    $model->rollback();
                    $this->ajaxReturn("", "删除旧分类关联失败",false);
                }
                //原来分类的有效试题数更新
                if(!empty($question_info)){
                    if(false === $categoryModel->where(array("cat_id"=>$object_cat_id))->setDec("question_num")){
                        $model->rollback();
                        $this->ajaxReturn("", "更新分类试题数量失败",false);
                    }
                    Log::write("更新分类试题数量：".$categoryModel->getLastSql(), log::SQL);
                }
                $map = array(
                        "cat_attr_id" => $new_cat_attr_id,
                        "level_one" => $object_level_one_id,
                        "level_two" => $object_level_two_id,
                        "level_thr" => $cat_info['level_thr']
                );
                $new_object_cat_id = $categoryModel->where($map)->getField("cat_id");
                if(intval($new_object_cat_id) == 0){
                    $k = 0;
                    foreach($grad_list as $level_thr){
                        $map['level_thr'] = $level_thr;
                        if(intval($categoryModel->where($map)->getField("cat_id")) > 0){
                            continue;
                        }
                        $map['created'] = $map['updated'] = $time;
                        $map['level_one_sort'] = $object_level_one_sort;
                        $map['level_two_sort'] = $object_level_two_sort;
                        $map['level_thr_sort'] = ++$k;
                        if($level_thr == $cat_info['level_thr'] && !empty($question_info)){
                            $new_cat_map['question_num'] = 1;
                        }else{
                            $new_cat_map['question_num'] = 0;
                        }
                        $new_id = $categoryModel->add($new_cat_map);
                        Log::write("增加分类：".$categoryModel->getLastSql(), log::SQL);
                        if(false === $new_id){
                            $model->rollback();
                            $this->ajaxReturn("", "增加新分类失败",false);
                        }
                        if($level_thr == $cat_info['level_thr']){
                            $new_object_cat_id = $new_id;
                        }
                    }
                }else{
                    if(!empty($question_info)){
                        if(false === $categoryModel->where(array("cat_id"=>$new_object_cat_id))->setInc("question_num")){
                            $model->rollback();
                            $this->ajaxReturn("", "更新分类试题数量失败",false);
                        }
                        Log::write("更新分类试题数量：".$categoryModel->getLastSql(), log::SQL);
                    }
                }
                $cat_question_data=array(
                    "cat_id"=>$new_object_cat_id,
                    "question_id"=>$question_id,
                    "type"=>1,
                    "created"=>$time
                );
                if(false === $model->add($cat_question_data)){
                    $model->rollback();
                    $this->ajaxReturn("", "增加新分类关联失败",false);
                }
            }
            $model->commit();
            $this->ajaxReturn($pattern, "操作成功",true);
        }
    }

}

?>
