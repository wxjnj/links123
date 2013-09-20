<?php
class NodeAction extends CommonAction {
	//
	protected function _filter(&$map, &$param){
		//
		if ( isset($_REQUEST['title']) ) {
			$title = $_REQUEST['title'];
		}
		if ( !empty($title) ) {
			$map['title'] = array('like',"%".$title."%");
		}
		$this->assign('title', $title);
		$param['title'] = $title;
		//
		/**
		 * 显示全部的节点管理
		 * @author slate 2013-09-20
		 */
// 		if (isset($_REQUEST['group_id']) && $_REQUEST['group_id']!='') {
//                     $map['group_id'] = $_REQUEST['group_id'];
// 		}
		$this->assign('group_id', $map['group_id']);
                $this->assign("nowGroup",D("group")->find(intval($map['group_id'])));
		$param['group_id'] = $map['group_id'];
		//
		if (isset($_REQUEST['pid']) && $_REQUEST['pid']!='') {
			$map['pid'] = $_REQUEST['pid'];
		}
		else {
			$map['pid'] = 1;
		}
		$this->assign('pid', $map['pid']);
		$param['pid'] = $map['pid'];
		$_SESSION['currentNodeId'] = $map['pid'];
		//获取上级节点
		$node  = M("Node");
		if( $node->getById($map['pid']) ) {
			$this->assign('level',$node->level+1);
			$this->assign('nodeName',$node->name);
		}
	}

    public function _before_index() {
        $model	=	M("Group");
        $list	=	$model->where('status=1')->getField('id,title');
        $this->assign('groupList',$list);
    }
    
    //
    public function index() {
    	//列表过滤器，生成查询Map对象
    	$map = array();
    	$param = array();
    	if (method_exists( $this, '_filter' )) {
    		$this->_filter( $map, $param );
    	}
    	$model = D("Node");
    	if (! empty ( $model )) {
    		$this->_list ( $model, $map, $param, 'sort', true );
    		//lTrace('Log/lastSql', $this->getActionName(), $model->getLastSql());
    	}
    	$this->display();
    	return;
    }
    
    //
    protected function getNames() {
    	$names = array('index','add','insert','edit','update','delete','resume','foreverdelete','sort','saveSort','uploadPic','uploadAtt');
    	return $names;
    }
    
    //
    public function add() {
    	$this->assign('group_id',$_GET['group_id']);
    	$this->display();
    	return;
    }
    
    // 获取配置类型
    public function _before_add() {
        $model	=	M("Group");
        $list	=	$model->where('status=1')->select();
        $this->assign('list',$list);
        $node	=	M("Node");
        $node->getById($_SESSION['currentNodeId']);
        $this->assign('pid',$node->id);
        $this->assign('level',$node->level+1);
    }
    
    // 批量新增
    public function grpAdd() {
    	$model	=	M("Group");
    	$list	=	$model->where('status=1')->select();
    	$this->assign('list',$list);
    	$node	=	M("Node");
    	$node->getById($_SESSION['currentNodeId']);
    	$this->assign('pid',$node->id);
    	$this->assign('level',$node->level+1);
    	//
    	$this->assign("names", $this->getNames());
    	//
    	$this->assign('group_id',$_GET['group_id']);
    	$this->display();
    	return;
    }
    
    //
    public function grpInsert() {
    	if ( empty($_POST['names']) ) {
    		$this->error("操作名丢失！");
    	}
    	//
    	$model = D("Node");
    	$names = $this->getNames();
    	$titles = array('列表','新增','保存新增','编辑','保存编辑','删除','恢复','永久删除','排序','保存排序','上传图片','上传附件');
    	//
    	$model->startTrans();
    	$result = true;
    	$reason = "未知";
    	//
    	foreach ($_POST['names'] as &$value) {
    		$_POST['name'] = $names[$value-1];
    		$_POST['title'] = $titles[$value-1];
    		//
    		$map['name'] = $_POST['name'];
    		$map['pid']	= isset($_POST['pid'])?$_POST['pid']:0;
    		$map['status'] = 1;
    		if ( $model->where($map)->find() ) {
    			$reason = "节点已经存在！";
    			continue;
    		}
    		//
    		if ( false === $model->add($_POST) ) {
    			$result = false;
    			$reason = "新增节点失败！";
    		}
    	}
    	//
    	if ( $result ) {
    		$model->commit();
    		$this->success('批量新增成功!');
    	}
    	else {
    		$model->rollback();
    		$this->error('批量新增失败!');
    	}
    }
	
    //
    public function _before_patch() {
        $model	=	M("Group");
        $list	=	$model->where('status=1')->select();
        $this->assign('list',$list);
        $node	=	M("Node");
        $node->getById($_SESSION['currentNodeId']);
        $this->assign('pid',$node->id);
        $this->assign('level',$node->level+1);
    }
    
    public function _before_edit() {
        $model	=	M("Group");
        $list	=	$model->where('status=1')->select();
        $this->assign('list',$list);
    }
    
    function edit() {
    	$model = M("Node");
    	$vo = $model->getById($_REQUEST['id']);
    	$this->assign('vo', $vo);
    	//
    	$this->assign('group_id',$_GET['group_id']);
    	//
    	$this->display();
    	return;
    }
    
    // 排序
    public function sort(){
    	$model = M("Node");
    	$map = array();
    	if (!empty($_GET['sortId'])) {
    		$map['id'] = array('in', $_GET['sortId']);
    	}
    	else {
    		$params = explode("&", $_SESSION[C('SEARCH_PARAMS_KEY')]);
    		foreach ($params as &$value) {
    			$temp = explode("=", $value);
    			if ( !empty($temp[1]) && $temp[0] != 'sort' && $temp[0] != 'order' ) {
    				$map[$temp[0]] = $temp[1];
    			}
    		}
    	}
    	$sortList = $model->where($map)->order('sort asc')->select();
    	foreach ($sortList as &$value) {
    		$value['txt_show'] = $value['title'];
    	}
    	$this->assign("sortList", $sortList);
    	$this->display("../Public/sort");
    	return;
    }
    
    // 永久删除
    public function foreverdelete() {
    	//删除指定记录
    	$model = D("Node");
    	if (! empty ( $model )) {
    		$id = $_REQUEST['id'];
    		if ( isset($id) ) {
    			$nodeNow = $model->getById($id);
    			if ($nodeNow['level']==1) {
    				$this->error("不可删除组级别节点！");
    			}
    			$condition = array();
    			if ($nodeNow['level']==2) {
    				$ids = $model->where('pid='.$id)->select();
    				$ary_ids = array();
    				foreach ($ids as &$value) {
    					array_push($ary_ids, $value['id']);
    				}
    				array_push($ary_ids, $id);
    				$condition['id'] = array('in', $ary_ids);
    			}
    			if ($nodeNow['level']==3) {
    				$condition['id'] = $id;
    			}
    			//
    			if ( false !== $model->where($condition)->delete() ) {
    				//
    				$access = M("Access");
    				$condition2 = array();
    				$condition2['node_id'] = $condition['id'];
    				if ( false !== $access->where($condition2)->delete() ) {
    					$this->success ('删除节点成功！');
    				}
    				else {
    					Log::write('删除访问权限失败：'.$access->getLastSql(), Log::SQL);
    					$this->error ('删除访问权限失败！');
    				}
    			}
    			else {
    				Log::write('删除节点失败：'.$model->getLastSql(), Log::SQL);
    				$this->error ('删除节点失败！');
    			}
    		}
    		else {
    			$this->error ( '非法操作' );
    		}
    	}
    }
}