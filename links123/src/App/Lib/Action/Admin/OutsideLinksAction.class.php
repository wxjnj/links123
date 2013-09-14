<?php
/**
 * @name OutsideLinksAction.class.php
 * @package Admin
 * @desc 友情链接
 * @author lawrence UPDATE 2013-09-14
 * @version 1.0
 */
class OutsideLinksAction extends CommonAction {

	protected function _filter(&$map, &$param){
		$title = $this->_param('title');
		
		if (!empty($title)) {
			$map['title'] = array('like', "%".$title."%");
		}
		$this->assign('title',$title);
		$param['title'] = $title;
	}
	
	/**
	 * @desc 列表
	 * @see OutsideLinksAction::index()
	 */
	public function index() {
		$map = array();
		$param = array();
		if (method_exists($this,'_filter')) {
			$this->_filter($map,$param );
		}
		$model = M("OutsideLinks");
		if (!empty($model)) {
			$this->_list($model, $map, $param, 'sort', true);
		}
		$this->display();
		return;
	}
	
	/**
	 * @desc 添加页面
	 * @see OutsideLinksAction::add()
	 */
	public function add() {
		$this->display();
		return;
	}

	/**
	 * @desc 添加操作
	 * @see OutsideLinksAction::insert()
	 */
	public function insert() {
		$this->checkPost();
		// 创建数据对象
		$model = D("OutsideLinks");
		if( false === $model->create()) {
			$this->error($model->getError());
			exit(0);
		}
		// 写入数据
		if( false !== $model->add()) {
			$this->success('友情链接添加成功！');
		}else {
			Log::write('友情链接添加失败：'.$model->getLastSql(), Log::SQL);
			$this->error('友情链接添加失败！');
		}
	}
	
	/**
	 * @desc 安全验证
	 * @see OutsideLinksAction::checkPost()
	 */
	protected function checkPost() {
        $_POST['title']=htmlspecialchars(trim($_POST['title']));
        $_POST['url']=str_replace('http://', '', htmlspecialchars(trim($_POST['url'])));
        $_POST['url']=str_replace('https://', '', $_POST['url']);
	}
	
	/**
	 * @desc 编辑页面
	 * @see OutsideLinksAction::edit()
	 */
	function edit() {
		$id = $this->_param('id');
		$model = M("OutsideLinks");
		$vo = $model->getById($id);
		$this->assign('vo',$vo);
		$this->display();
		return;
	}
    
	/**
	 * @desc 编辑操作
	 * @see OutsideLinksAction::update()
	 */
	public function update() {
		$id = $this->_param('id');
        $this->checkPost();
        $model = D("OutsideLinks");
        $OutsideLinksNow = $model->getById($id);
		if (false === $model->create()) {
			$this->error($model->getError());
		}
		if (false !== $model->save()) {
			if ($_POST['pic'] != $OutsideLinksNow['pic']) {
				$path = realpath('./Public/Uploads/OutsideLinks/'.$OutsideLinksNow['pic']);
				if ( !unlink($path) ) {
					Log::write('图片删除失败：'.$path, Log::FILE);
				}
			}
			$this->assign('jumpUrl', cookie('_currentUrl_'));
			$this->success('友情链接编辑成功!');
		} else {
			Log::write('友情链接编辑失败：'.$model->getLastSql(), Log::SQL);
			$this->error('友情链接编辑失败!');
		}
	}
	
	/**
	 * @desc 永久删除
	 * @see OutsideLinksAction::foreverdelete()
	 */
	public function foreverdelete() {
		$model = D("OutsideLinks");
		if (!empty($model)) {
			$id = $this->_param('id');
			if (isset($id)) {
				$condition = array();
				$condition['id'] = array('in', explode(',', $id ));
				$rcds = $model->field('pic')->where($condition)->select();
				$pics = array();
				foreach ($rcds as &$value) {
					array_push($pics,$value['pic']);
				}
				if (false !== $model->where($condition)->delete()) {
					foreach ($pics as &$value) {
						$path = realpath('./Public/Uploads/OutsideLinks/'.$value);
						if (!unlink($path)) {
							Log::write('图片删除失败：'.$path, Log::FILE);
						}
					}
					$this->success('删除友情链接成功！',cookie('_currentUrl_'));
				} else {
					Log::write('删除友情链接失败：'.$model->getLastSql(), Log::SQL);
					$this->error('删除友情链接失败！');
				}
			} else {
				$this->error('非法操作');
			}
		}
	}
	 
	/**
	 * @desc 排序
	 * @see OutsideLinksAction::sort()
	 */
	public function sort(){
		$sortId = $this->_param('sortId');
		$model = M("OutsideLinks");
		$map = array();
		if (!empty($sortId)) {
			$map['id'] = array('in', $sortId);
		} else {
			$params =explode("&",$_SESSION[C('SEARCH_PARAMS_KEY')]);
			foreach($params as &$value) {
				$temp=explode("=",$value);
				if (!empty($temp[1]) && $temp[0] != 'sort' && $temp[0] != 'order') {
					$map[$temp[0]] = $temp[1];
				}
			}
		}
		$sortList = $model->where($map)->order('sort ASC')->select();
		foreach ($sortList as &$value) {
			$value['txt_show'] = $value['title'];
		}
		$this->assign("sortList",$sortList);
		$this->display("../Public/sort");
		return;
	}
}
?>