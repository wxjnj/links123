<?php
/**
 * @desc 目录管理
 * @name CategoryAction.class.php
 * @package Admin
 * @author Frank UPDATE 2013-09-13
 * @version 1.0
 */
class CategoryAction extends CommonAction {
	
	protected function _filter(&$map, &$param){
		$cat_name = $this->_param('cat_name');
		$prt_id = $this->_param('prt_id');
		$flag = $this->_param('flag');
		$status = $this->_param('status');
		
		if (!empty($cat_name)) {
			$map['cat_name'] = array('like',"%".$cat_name."%");
		}
		$this->assign('cat_name', $cat_name);
		$param['cat_name'] = $cat_name;
		
		if ($prt_id != '') {
			$map['prt_id'] = $prt_id;
		}
		$this->assign('prt_id', $map['prt_id']);
		$param['prt_id'] = $map['prt_id'];
		
		if (!empty($flag)) {
			$map['flag'] = $flag;
			$this->assign('flag', $map['flag']);
			$param['flag'] = $map['flag'];
		}
		if (!empty($status)) {
			$map['status'] = $status;
			$this->assign('status', $map['status']);
			$param['status'] = $map['status'];
		}else {
			$map['status'] = 1;
		}
	}
	
	/**
	 * @desc 列表过滤器，生成查询Map对象
	 * @author Frank UPDATE 2013-09-13
	 */
	public function index() {
		$map = array();
		$param = array();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map, $param );
		}
		$model = new CategoryViewModel();
		if (! empty( $model )) {
			$this->_list( $model, $map, $param, 'path', true );
			//echo $model->getLastSql();
		}
		$this->getRootCats();
		$this->display();
		return;
	}
	
	/**
	 * @desc 添加
	 * @author Frank UPDATE 2013-09-13
	 */
	public function add() {
		$model = M("Category");
		$vo = $model->getById( $model->max('id') );
		$this->assign( 'vo', $vo );
		$this->getRootCats();
		$this->display();
		return;
	}
	
	/**
	 * @desc 创建数据对象
	 * @author Frank UPDATE 2013-09-14
	 */
	public function insert() {
		
		$prt_id = $this->_post('prt_id');
		$model = D("Category");
		$this->checkPost();
		
		if ( $prt_id > 0 ) {
			$prtCat = $model->getById($prt_id);
			$tempary = explode('-', $prtCat['path']);
			if ( empty($tempary[count($tempary)-1]) ) {
				if ($prtCat['id'] < 10) {
					$prtCat['id'] = '0'.$prtCat['id'];
				}
				$prtCat['path'] = $prtCat['path'].$prtCat['id'];
				$model->save($prtCat);
			}
		}
		
		if(!$model->create()) {
			$this->error($model->getError());
		}else {
			if ($prt_id == 0) {
				$model->__set('level', 1);
			}else {
				$model->__set('level', $prtCat['level']+1);
				$path = $prtCat['path'].'-';
			}
			$model->__set('path', $path);
			
			if( false !== $model->add() ) {
				if ($prt_id == 0) {
					$idNow = $model->getLastInsID();
					$id = $idNow;
					if ($id < 10) {
						$id = '0'.$id;
					}
					$model->where('id='.$idNow)->setField('path', $id);
				}
				$this->success('目录添加成功！');
			}
			else {
				Log::write('目录添加失败：'.$model->getLastSql(), Log::SQL);
				$this->error('目录添加失败！');
			}
		}
	}
	
	/**
	 * @desc 安全验证
	 * @author Frank UPDATE 2013-09-14
	 */
	protected function checkPost() {
        $_POST['cat_name'] = htmlspecialchars($_POST['cat_name']);
	}
	
	/**
	 * @desc 编辑
	 * @author Frank UPDATE 2013-09-14
	 */
	function edit() {
		$id = $this->_param('id');
		$model = M("Category");
		$vo = $model->getById($id);
		$this->assign( 'vo', $vo );
		$this->getRootCats();
		$this->display();
		return;
	}
    
	/**
	 * @desc 更新数据
	 * @author Frank UPDATE 2013-09-14
	 */
	public function update() {
		$prt_id = $this->_param('prt_id');
		$id = $this->_param('id');
		$model = D("Category");
        $this->checkPost();
        
        if ( $prt_id > 0 ) {
        	$prtCat = $model->getById( $prt_id );
        	$tempary = explode('-', $prtCat['path']);
        	if ( empty($tempary[count($tempary)-1]) ) {
        		$prtCat['path'] = $prtCat['path'].$prtCat['id'];
        		$model->save($prtCat);
        	}
        }
        
        $havesub = false;
        if ( $model->where("prt_id = %d", $id)->find() ) {
        	$havesub = true;
        }
        
		if ( false === $model->create() ) {
			$this->error( $model->getError() );
		}
		
		if ( $id < 10 ) {
			$id = '0'.$id;
		}
		
		$path = $id;
		$level = 1;
		if ( $prt_id == 0 ) {
			$model->__set('level', $level);
		}
		else {
			$level = $prtCat['level']+1;
			$model->__set('level', $level);
			if ( $havesub ) {
				$path = $prtCat['path'].'-'.$id;
			}
			else {
				$path = $prtCat['path'].'-';
			}
		}
		$model->__set('path', $path);
		if ( false !== $model->save() ) {
			if ( $havesub ) {
				$path .= '-';
				$model->where("prt_id = %d", $id)->setField('path', $path);
			}
			
			$this->assign ( 'jumpUrl', __URL__.'/index?'.$_SESSION[C('SEARCH_PARAMS_KEY')] );
			$this->success ('目录编辑成功!');
		} else {
			$this->error ('目录编辑失败!');
		}
	}
	
	/**
	 * @desc 排序
	 * @author Frank UPDATE 2013-09-14
	 */
	public function sort(){
		$sortId = $this->_param('sortId');
		
		$model = M("Category");
		$map = array();
		$map['status'] = 1;
        if(!empty($sortId)) {
            $map['id'] = array('in', $sortId);
        }else{
			$params = explode("&", $_SESSION[C('SEARCH_PARAMS_KEY')]);
			foreach ($params as &$value) {
				$temp = explode("=", $value);
				if ( ($temp[1] != '' ) && $temp[0] != 'sort' && $temp[0] != 'order' ) {
					$map[$temp[0]] = $temp[1];
				}
			}
        }
        $sortList = $model->where($map)->order('sort ASC')->select();
        foreach ($sortList as &$value) {
        	$value['txt_show'] = $value['cat_name']."　　　　　";
        }
        $this->assign("sortList", $sortList);
        $this->display("../Public/sort");
        return;
    }
    
    /**
     * @desc 排序
     * @author Frank UPDATE 2013-09-14
     */
	function saveSort() {
		$seqNoList = $this->_post('seqNoList');
		if (! empty ( $seqNoList )) {
			//更新数据对象
			$model = M("Category");
			$col = explode ( ',', $seqNoList );
			//启动事务
			$model->startTrans ();
			$result = true;
			
			foreach ( $col as $val ) {
				$val = explode ( ':', $val );
				$sort = $model->where("id = %d", $val[0])->getField('sort');
				if ($sort == $val[1])	continue;
				$model->id = $val[0];
				$model->sort = $val[1];
				if ( false === $model->save() )	{
					$result = false;
					Log::write('保存排序失败：'.$model->getLastSql(), Log::SQL);
				}
			}
				
			if ($result) {
				$model->commit ();
				//采用普通方式跳转刷新页面
				$this->success ('更新成功');
			} else {
				// 回滚事务
				$model->rollback();
				$this->error ( $model->getError () );
			}
		}
	}
    
	/**
	 * @desc 删除
	 * @author Frank UPDATE 2013-09-14
	 */
	public function foreverdelete() {
		//删除指定记录
		$id = $this->_param('id');
		$model = M("Category");
		if (! empty ( $model )) {
			if (isset ($id)) {
				if ( $model->where('prt_id = %d', $id)->find() ) {
					$this->error ('该目录下有子目录，无法删除！');
				}
				if ( M("Product")->where('category = %d', $id)->find() ) {
					$this->error ('该目录下有产品，无法删除！');
				}
				if (false !== $model->where('id = %d', $id)->delete()) {
					//echo $model->getlastsql();
					$this->success ('删除成功！');
				} else {
					$this->error ('删除失败！');
				}
			} else {
				$this->error ( '非法操作' );
			}
		}
	}
	
	/**
	 * @desc 目录复制
	 * @author Frank UPDATE 2013-09-14
	 */
	public function copyto() {
		$cat_id = $this->_post('cat_id');
		$copyto = $this->_post('copyto');
		$lan = $this->_post('lan');
		
		if ( empty($cat_id) || $cat_id == 'undefined' || empty($copyto) || $copyto == 'undefined' ) {
			echo "参数丢失！";
			return false;
		}
		
		$cat = M("Category");
		$catNow = $cat->getById($cat_id);
		if ( $catNow['level'] == 1 ) {
			echo "一级目录无法copy！";
			return false;
		}
		if ( $catNow['prt_id'] == $copyto && $catNow['flag'] == $lan ) {
			echo "同一目录下无法copy！";
			return false;
		}
		
		$links = M("Links");
		$list = $links->where('status = 1 and category = %s', $cat_id)->select();
		
		$result = true;
		$errlist = '';
		
		$aim = $cat->where("flag = %s and prt_id = %s and cat_name = %s", $lan, $copyto, $catNow['cat_name'])->find();
		if ( empty($aim) ) {
			$data = array();
			$data['cat_name'] = $catNow['cat_name'];
			$data['prt_id'] = $_POST['copyto'];
			$data['level'] = 2;
			$data['path'] = $catNow['cat_name']."-";
			$data['intro'] = $catNow['intro'];
			$data['flag'] = $_POST['lan'];
			$data['status'] = 1;
			$data['uid'] = $_SESSION[C('USER_AUTH_KEY')];
			$cat->add($data);
			$idNow = $cat->getLastInsID();
			
			foreach ($list as &$value) {
				unset($value['id']);
				$value['category'] = $idNow;
				$value['language'] = $lan;
				if (false === $links->add($value) ) {
					if ( empty($errlist) ) {
						$errlist = $value['id'];
					}
					else {
						$errlist .= ','.$value['id'];
					}
					$result = false;
				}
			}
		}else {
			$olds = $links->where('status = 1 and category = %s', $aim['id'])->select();
			foreach ($list as &$value) {
				unset($value['id']);
				$value['category'] = $aim['id'];
				$value['language'] = $lan;
				$add = true;
				foreach ($olds as &$val) {
					if ( $value['link'] == $val['link'] ) {
						$add = false;
						$value['id'] = $val['id'];
						break;
					}
				}
				if ( $add ) {
					if (false === $links->add($value) ) {
						if ( empty($errlist) ) {
							$errlist = $value['id'];
						} else {
							$errlist .= ','.$value['id'];
						}
						$result = false;
					}
				} else {
					if (false === $links->save($value) ) {
						if ( empty($errlist) ) {
							$errlist = $value['id'];
						} else {
							$errlist .= ','.$value['id'];
						}
						$result = false;
					}
				}
			}
		}
		
		if ( $result ) {
			echo "copyOK";
		} else {
			echo "copy失败链接id列表：".$errlist;
		}
	}


}
?>