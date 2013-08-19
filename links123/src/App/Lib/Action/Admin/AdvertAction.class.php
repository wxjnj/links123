<?php

/**
 * 广告管理
 * @author lawrence
 *
 */
class AdvertAction extends CommonAction {
    //
	protected function _filter(&$map, &$param) {
		//
		if (isset ( $_REQUEST ['name'] )) {
			$name = $_REQUEST ['name'];
		}
		if (! empty ( $name )) {
			$map ['name'] = array (
					'like',
					"%" . $name . "%" 
			);
		}
		$this->assign ( 'name', $name );
		$param ['name'] = $name;
	}
	
	/**
	 * 默认首页
	 *
	 * @author lawrence
	 *        
	 */
	public function index() {
		$map = array ();
		$param = array ();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map, $param );
		}
		$model = D ( "Advert" );
		if (! empty ( $model )) {
			$this->_list ( $model, $map, $param, 'id', true );
		}
		$this->display ();
		return;
	}
	
	/**
	 * 添加 页面
	 *
	 * @author lawrence
	 *        
	 */
	public function add() {
		$this->display ();
		return;
	}
	
	/**
	 * 更新数据
	 *
	 * @author lawrence
	 *        
	 */
	public function update() {
		$model = D ( "Advert" );
		$advNow = $model->getById ( $_POST ['id'] );
		if (! $model->create ()) {
			$this->error ( $model->getError () );
		}
		if (false !== $model->save ()) {
			$this->error ( '编辑失败!' );
		} else {
			if ($_POST ['pic'] != $advNow ['pic']) {
				$path = realpath ( './Public/Uploads/Others/' . $advNow ['pic'] );
				if (! unlink ( $path )) {
					Log::write ( '图片删除失败：' . $path, Log::FILE );
				}
			}
			$this->assign ( 'jumpUrl', cookie ( '_currentUrl_' ) );
			$this->success ( '编辑成功!' );
		}
	}
	
	/**
	 * 排序
	 *
	 * @author lawrence
	 *        
	 */
	public function sort() {
		$model = M ( "Advert" );
		$map = array ();
		if (! empty ( $_GET ['sortId'] )) {
			$map ['id'] = array (
					'in',
					$_GET ['sortId'] 
			);
		} else {
			$params = explode ( "&", $_SESSION [C ( 'SEARCH_PARAMS_KEY' )] );
			foreach ( $params as &$value ) {
				$temp = explode ( "=", $value );
				if (! empty ( $temp [1] ) && $temp [0] != 'sort' && $temp [0] != 'order') {
					$map [$temp [0]] = $temp [1];
				}
			}
		}
		$sortList = $model->where ( $map )->order ( 'sort asc' )->select ();
		foreach ( $sortList as &$value ) {
			$value ['txt_show'] = $value ['name'];
		}
		$this->assign ( "sortList", $sortList );
		$this->display ( "../Public/sort" );
		return;
	}
}
?>