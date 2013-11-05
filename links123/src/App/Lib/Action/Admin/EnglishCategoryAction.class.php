<?php


/**
 * Description of EnglishCategoryAction
 *
 * @author Adam
 */
class EnglishCategoryAction extends CommonAction{
    
    
    public function _filter(&$map, &$param) {
        if(isset($_REQUEST['name'])){
            $map['levelname.name'] = $_REQUEST['name'];
            $param['name'] = $_REQUEST['name'];
            $this->assign("name", $_REQUEST['name']);
        }
    }
    
    
    protected function _list($model, $map, $param, $sortBy = '', $asc = false) {
        //排序字段 默认为主键名
        if (isset($_REQUEST ['_order'])) {
            $order = $_REQUEST ['_order'];
        } else {
            $order = !empty($sortBy) ? $sortBy : "levelname.sort";
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
        $group = 'category.level_one';
        $flag = "level_one";
        $field = 'category.*,category.cat_id as id,levelname.name as name, levelname.id as level_id, sum(category.question_num) as num,category.level_one_sort as sort,levelname.`default`';
        if ($param['level_one'] > 0) {
            $field = 'category.*,category.cat_id as id,levelname.name as name, levelname.id as level_id, sum(category.question_num) as num,category.level_two_sort as sort,levelname.`default`';
            $group .= ' , category.level_two';
            $flag = "level_two";
        }

        if ($param['level_two'] > 0) {
            $field = 'category.*,category.cat_id as id,levelname.name as name, levelname.id as level_id, sum(category.question_num) as num,category.level_thr_sort as sort,levelname.`default`';
            $group .= ' , category.level_thr';
            $flag = "level_thr";
        }

        $count =  $model->alias("category")
            ->join("RIGHT JOIN ".C("DB_PREFIX")."english_levelname levelname on levelname.id=category.".$flag)
            ->where($map)
//            ->group($group)
            ->order("`" . $order . "` " . $sort)
            ->count("DISTINCT(category.".$flag.")");
        //echo $model->getlastsql()."<br />";
        if ($count > 0) {
            import("@.ORG.Page");
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = '20';
            }
            $p = new Page($count, $listRows);
            $map['a.default'] = array(array('neq',1),array('exp',"is null"),'or') ;

            //分页查询数据
            $voList = $model->alias("category")
                    ->field($field)
                    ->join("RIGHT JOIN ".C("DB_PREFIX")."english_levelname levelname on levelname.id=category.".$flag)
                    ->join("left JOIN ".C("DB_PREFIX")."english_levelname a on a.id=category.level_two")
					->where($map)
					->group($group)
                    ->order("`" . $order . "` " . $sort)
                    ->limit($p->firstRow . ',' . $p->listRows)
					->select();
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
        }
        cookie('_currentUrl_', __URL__ . '/index?' . $_SESSION[C('SEARCH_PARAMS_KEY')]);
        return;
    }
    
    public function index(){
        //列表过滤器，生成查询Map对象
        $map = array();
        $param = array();
        if (method_exists($this, '_filter')) {
            $this->_filter($map, $param);
        }
        $level = intval($_REQUEST['level']) ? intval($_REQUEST['level']) : 1;
        $param['level'] = $level;
        $level_one = intval($_REQUEST['level_one']);
        $level_two = intval($_REQUEST['level_two']);
        if($level_one > 0){
            $map['category.level_one'] = $level_one;
            $param['level_one'] = $level_one;
            $level_one_info = D("EnglishLevelname")->find($level_one);
            $this->assign("level_one_info", $level_one_info);
        }else{
            $map['category.level_one'] = array("egt",0);
        }
        if($level_two > 0){
            $map['category.level_two'] = $level_two;
            $param['level_two'] = $level_two;
            $level_two_info = D("EnglishLevelname")->find($level_two);
            $this->assign("level_two_info", $level_two_info);
        }else{
            $map['category.level_two'] = array("egt",0);
        }
        $model = D("EnglishCategory");
        if (!empty($model)) {
            $this->_list($model, $map, $param, 'cat_id', false);
            //lTrace('Log/lastSql', $this->getActionName(), $model->getLastSql());
        }

        $this->assign("level", $level);
        $this->assign("param", $param);
        foreach ($param as $key => $value) {
            $param_str.=$key . "=" . $value . "&";
        }
        $this->assign("param_str", $param_str);
        $this->display();
        return;
    }
    public function add() {
        $level_one = intval($_GET['level_one']);
        if($level_one > 0){
            $this->assign("level_one_info", D("EnglishLevelname")->find($level_one));
        }
        $level_two = intval($_GET['level_two']);
        if($level_two > 0){
            $this->assign("level_two_info", D("EnglishLevelname")->find($level_two));
        }
        $this->display();
        return;
    }
    public function insert() {
        $level = intval($_POST['level']);
        if($level == 0){
            $this->error("非法操作！");
        }
        if(empty($_POST['name'])){
            $this->error("名称不能为空");
        }
        if(intval($_POST['sort']) == 0){
            $this->error("排序不能为空或非数字");
        }
        $data = array();
        $data['name'] = $_POST['name'];
        $data['sort'] = intval($_POST['sort']);
        $data['level'] = $level;
        $data['status'] = intval($_POST['status']);
        $time = time();
        $data['created'] = $data['updated'] = $time;
        $levelNameModel  = D("EnglishLevelname");
        $categoryModel = D("EnglishCategory");
        $levelNameModel->startTrans();
        $new_info = $levelNameModel->where(array("name",array("like",$data['name']),"level"=>$level))->find();
        if(!empty($new_info)){
            $this->error("需要添加的分类已存在！");
        }
        
        //添加1级分类
        if($level == 1){
            $new_level_one = $levelNameModel->add($data);
            if(false === $new_level_one){
                $this->error("添加失败");
            }
            //添加一个初始化分类到category表
            $cat_data = array();
            $cat_data['cat_attr_id'] = 0;
            $cat_data['level_one'] = $new_level_one;
            $cat_data['level_one_sort'] = $data['sort'];
            $cat_data['status'] = $data['status'];
            $cat_data['question_num'] = 0;
            $cat_data['created'] = $cat_data['updated'] =  $time;
            $new_cat_id = $categoryModel->add($cat_data);
            if(false === $new_cat_id){
                $categoryModel->rollback();
                $this->error("添加失败");
            }
        }else if($level == 2){
            $level_one = intval($_POST['level_one']);
            $level_thr_list = $categoryModel
                                ->field("DISTINCT(level_thr),level_thr_sort,level_one,level_one_sort")
                                ->where(array("level_one"=>$level_one,'level_two'=>array("gt",0)))
                                ->select();
            if(empty($level_thr_list)){
                $level_one_info = $levelNameModel->find($level_one);
                $map['levelname.name']  = array(array('like','初级'), array('like','中级'), array('like','高级'),'or'); 
                $level_thr_list = $categoryModel
                        ->alias("category")
                        ->join(C("DB_PREFIX")."english_levelname levelname on levelname.id=category.level_thr")
                        ->field("DISTINCT(level_thr),level_thr_sort,'".$level_one."' as level_one,'".$level_one_info['sort']."' as level_one_sort")
                        ->where($map)
                        ->select();
            }
            $categoryModel->startTrans();
            $new_level_two = $levelNameModel->add($data);
            if(false === $new_level_two){
                $categoryModel->rollback();
                $this->error("添加失败");
            }
            //循环添加到category表
            $cat_attr_id_list = array(0,1,2,3,4,5,6,7);
            foreach ($cat_attr_id_list as $cat_attr_id){
                foreach($level_thr_list as $value){
                    $cat_data = array();
                    $cat_data['cat_attr_id'] = $cat_attr_id;
                    $cat_data['level_one'] = $value['level_one'];;
                    $cat_data['level_one_sort'] = $value['level_one_sort'];
                    $cat_data['level_two'] = $new_level_two;
                    $cat_data['level_two_sort'] = $data['sort'];
                    $cat_data['level_thr'] = $value['level_thr'];
                    $cat_data['level_thr_sort'] = $value['level_thr_sort'];
                    $cat_data['status'] = $data['status'];
                    $cat_data['question_num'] = 0;
                    $cat_data['created'] = $cat_data['updated'] =  $time;
                    $new_cat_id = $categoryModel->add($cat_data);
                    if(false === $new_cat_id){
                        $categoryModel->rollback();
                        $this->error("添加失败");
                    }
                }
            }
        }else if($level == 3){
            $new_level_thr = $levelNameModel->add($data);
            if(false == $new_level_thr){
                $levelNameModel->rollback();
                $this->error("添加失败");
            }
            //循环添加到category表
            $cat_attr_id_list = array(0,1,2,3,4,5,6,7);
            $level_one = intval($_POST['level_one']);
            $level_two = intval($_POST['level_two']);
            $level_sort_info = $categoryModel->where(array("level_one"=>$level_one,"level_two"=>$level_two))->find();
            foreach ($cat_attr_id_list as $cat_attr_id){
                $cat_data = array();
                $cat_data['cat_attr_id'] = $cat_attr_id;
                $cat_data['level_one'] = $level_one;
                $cat_data['level_one_sort'] = $level_sort_info['level_one_sort'];
                $cat_data['level_two'] = $level_two;
                $cat_data['level_two_sort'] = $level_sort_info['level_two_sort'];
                $cat_data['level_thr'] = $new_level_thr;
                $cat_data['level_thr_sort'] = $data['sort'];
                $cat_data['status'] = $data['status'];
                $cat_data['question_num'] = 0;
                $cat_data['created'] = $cat_data['updated'] =  $time;
                $new_cat_id = $categoryModel->add($cat_data);
                if(false === $new_cat_id){
                    $categoryModel->rollback();
                    $this->error("添加失败");
                }
            }
        }
        $levelNameModel->commit();
        $this->success("添加成功",__URL__."/index/level/".$level."/level_one/".$level_one."/level_two/".$level_two);
    }
    
    public function edit() {
        if(intval($_REQUEST['cat_id']) == 0){
            $this->error("非法操作");
        }
        $model = D("EnglishLevelname");
        $id = $_REQUEST ["id"];
        $vo = $model->find($id);
        $this->assign('category', $vo);
        $cat_info = D("EnglishCategory")->find($_REQUEST['cat_id']);
        $this->assign('cat_info', $cat_info);
        $this->display();
        return;
    }
    public function update(){
        $level =intval($_POST['level']);
        if($level == 0){
            $this->error("非法操作1");
        }
        $level_one = intval($_REQUEST['level_one']);
        if($level_one == 0){
            $this->error("非法操作2");
        }
        $level_two = intval($_REQUEST['level_two']);
        if($level > 1 && $level_two == 0){
            $this->error("非法操作3");
        }
        $level_thr = intval($_REQUEST['level_thr']);
        if($level > 2 && $level_thr == 0){
            $this->error("非法操作4");
        }
        $id = intval($_POST['id']);
        if(empty($_POST['name'])){
            $this->error('名称不能为空');
        }
        if(intval($_POST['sort']) == 0){
            $this->error("排序不能为空或非数字");
        }
        $time = time();
        $levelnameModel = D("EnglishLevelname");
        $categoryModel = D("EnglishCategory");
        $new_info = $levelnameModel->where(array("name"=>array("like",$_POST['name']),"level"=>$level,"id"=>array("neq",$id)))->find();
        if(!empty($new_info)){
            $this->error("需要添加的分类名已存在！");
        }
        if(intval($_POST['status']) == 0 && intval($levelnameModel->where(array('id'=>$id))->getField("default")) == 1){
            $this->error("不能禁用默认的分类");
        }
        $levelnameModel->startTrans();
        //
        $data = array();
        $data['status'] = intval($_POST['status']);
        $data['sort'] = intval($_POST['sort']);
        $data['name'] = $_POST['name'];
        $data['id'] = $id;
        $data['updated'] = $time;
        if(false === $levelnameModel->save($data)){
            $levelnameModel->rollback();
            $this->error("编辑失败");
        }
        //
        $map= array();
        $cat_data['status'] = $data['status'];
        $cat_data['updated'] = $time;
        if($level == 1){
            $cat_data['level_one_sort'] = $data['sort'];
            $map['level_one'] = $level_one;
        }else if($level == 2){
            $cat_data['level_two_sort'] = $data['sort'];
            $map['level_one'] = $level_one;
            $map['level_two'] = $level_two;
        }else if($level == 3){
            $cat_data['level_thr_sort'] = $data['sort'];
            $map['level_one'] = $level_one;
            $map['level_two'] = $level_two;
            $map['level_thr'] = $level_thr;
        }
        if(false === $categoryModel->where($map)->save($cat_data)){
            $levelnameModel->rollback();
            $this->error("编辑失败");
        }
        $levelnameModel->commit();
        $url = __URL__."/index/level/".$level;
        if($level > 1){
            $url .= "/level_one/".$level_one;
        }
        if($level > 2){
            $url .="/level_two/".$level_two;
        }
        $this->success("编辑成功", $url);
    }
    public function forbid() {
        $cat_id = $_REQUEST['id'];
        $level = intval($_REQUEST['level']);
        if($level == 0){
            $this->error('非法操作');
        }
        $categoryModel = D("EnglishCategory");
        $levelnameModel = D("EnglishLevelname");
        $map['cat_id'] = array("in",$cat_id);
        $cat_info = $categoryModel->where($map)->select();
        $levelnameModel->startTrans();
        foreach($cat_info as $value){
            $level_id = 0;
            if($level == 1){
                $level_id = $value['level_one'];
            }elseif($level == 2){
                $level_id = $value['level_two'];
            }elseif($level == 3){
                $level_id = $value['level_thr'];
            }
            $level_info = $levelnameModel->find($level_id);
            if($level_info === false || empty($level_info)){
                $this->error('非法操作');
            }
            if($level_info['default'] == 1){
                $this->error('不能禁用默认分类');
            }
            //
            $cat_map =array();
            $cat_map['level_one'] = $value['level_one'];
            if($level > 1){
                $cat_map['level_two'] = $value['level_two'];
            }
            if($level > 2){
                $cat_map['level_thr'] = $value['level_thr'];
            }
            if(false === $categoryModel->where($cat_map)->setField("status",0)){
                $levelnameModel->rollback();
                $this->error('操作失败');
            }
        }
        $levelnameModel->commit();
        $this->success('操作成功');
    }
    public function resume() {
        $cat_id = $_REQUEST['id'];
        $level = intval($_REQUEST['level']);
        if($level == 0){
            $this->error('非法操作');
        }
        $categoryModel = D("EnglishCategory");
        $levelnameModel = D("EnglishLevelname");
        $map['cat_id'] = array("in",$cat_id);
        $cat_info = $categoryModel->where($map)->select();
        $levelnameModel->startTrans();
        foreach($cat_info as $value){
            //
            $cat_map =array();
            $cat_map['level_one'] = $value['level_one'];
            if($level > 1){
                $cat_map['level_two'] = $value['level_two'];
            }
            if($level > 2){
                $cat_map['level_thr'] = $value['level_thr'];
            }
            if(false === $categoryModel->where($cat_map)->setField("status",1)){
                $levelnameModel->rollback();
                $this->error('操作失败');
            }
        }
        $levelnameModel->commit();
        $this->success('操作成功');
    }
    public function foreverdelete() {
        $cat_id = $_REQUEST['id'];
        $level = intval($_REQUEST['level']);
        if($level == 0){
            $this->error('非法操作');
        }
        $categoryModel = D("EnglishCategory");
        $levelnameModel = D("EnglishLevelname");
        $catquestionModel  = D("EnglishCatquestion");
        $map['cat_id'] = array("in",$cat_id);
        $cat_info = $categoryModel->where($map)->select();
        $levelnameModel->startTrans();
        if(false === $catquestionModel->where($map)->delete()){
            $levelnameModel->rollback();
            $this->error("操作失败");
        }
        foreach($cat_info as $value){
            $level_id = 0;
            if($level == 1){
                $level_id = $value['level_one'];
            }elseif($level == 2){
                $level_id = $value['level_two'];
            }elseif($level == 3){
                $level_id = $value['level_thr'];
            }
            $level_info = $levelnameModel->find($level_id);
            if($level_info === false || empty($level_info)){
                $this->error('非法操作');
            }
            if($level_info['default'] == 1){
                $this->error('不能删除默认分类');
            }
            if($level == 1){
                if(false === $levelnameModel->delete($level_id)){
                    $levelnameModel->rollback();
                    $this->error('操作失败');
                }
            }
            //
            $cat_map =array();
            $cat_map['level_one'] = $value['level_one'];
            if($level > 1){
                $cat_map['level_two'] = $value['level_two'];
            }
            if($level > 2){
                $cat_map['level_thr'] = $value['level_thr'];
            }
            $cat_list = $categoryModel->field("group_concat(cat_id) as cat_id")->where($cat_map)->group("level_one")->find();
            if(!empty($cat_list['cat_id'])){
                $cat_question_map = array();
                $cat_question_map['cat_id'] = array("in",$cat_list['cat_id']);
                if(false === $catquestionModel->where($cat_question_map)->delete()){
                    $levelnameModel->rollback();
                    $this->error('操作失败');
                }
            }
            if(false === $categoryModel->where($cat_map)->delete()){
                $levelnameModel->rollback();
                $this->error('操作失败');
            }
        }
        $levelnameModel->commit();
        $this->success('操作成功');
    }
    function resetCategoryQuestionNum(){
        set_time_limit(0);
        $time = time();
        $englishQuestionModel = D("EnglishQuestion");
        $englishQuestionSpeakModel = D("EnglishQuestionSpeak");
        $englishCategoryModel = D("EnglishCategory");
        
        $englishCategoryModel->startTrans();
        $field = "a.*,(select group_concat(b.question_id) from ".
                C("DB_PREFIX")."english_catquestion b where b.cat_id=a.cat_id and b.type=1 group by b.cat_id) as question_id,(select group_concat(c.question_id) from ".
                C("DB_PREFIX")."english_catquestion c where c.cat_id=a.cat_id and c.type=0 group by c.cat_id) as question_speak_id";
        $where = "((select group_concat(c.question_id) from ".
                C("DB_PREFIX")."english_catquestion c where c.cat_id=a.cat_id and c.type=0 group by c.cat_id) is not null)".
                "OR".
                "((select group_concat(b.question_id) from ". C("DB_PREFIX")."english_catquestion b where b.cat_id=a.cat_id and b.type=1 group by b.cat_id) is not null)";
        $categoryList = $englishCategoryModel->alias("a")->field($field)->where($where)->select();
        foreach($categoryList as $key=>$value){
            $question_num =0;
            if(!empty($value['question_id'])){
                $question_map = array();
                $question_map['question.id'] = array("in",$value['question_id']);
                $question_map['question.status'] = 1;
                $question_map['media.status'] = 1;
                $question_num = intval($englishQuestionModel->alias("question")->join("RIGHT JOIN ".C("DB_PREFIX")."english_media media on media.id=question.media_id")->where($question_map)->count("question.id"));
            }
            
            $question_speak_num = 0;
            if(!empty($value['question_speak_id'])){
                $question_map = array();
                $question_map['question.id'] = array("in",$value['question_speak_id']);
                $question_map['question.status'] = 1;
                $question_map['media.status'] = 1;
                $question_speak_num = intval($englishQuestionSpeakModel->alias("question")->join("RIGHT JOIN ".C("DB_PREFIX")."english_media media on media.id=question.media_id")->where($question_map)->count("question.id"));
            }
            $new_question_num = $categoryList[$key]['new_question_num'] =  intval($question_num + $question_speak_num);
            $cat_map = array(
                "cat_id"=>$value['cat_id']
            );
            $data =array(
                "updated"=>$time,
                "question_num"=>$new_question_num
            );
            $ret = $englishCategoryModel->where($cat_map)->save($data);
            Log::write($englishCategoryModel->getLastSql(), LOG::SQL);
            if(false === $ret){
                $englishCategoryModel->rollback();
                $this->error("执行失败");
            }
            $englishCategoryModel->commit();
            $this->success("执行成功");
        }
    }
}

?>
