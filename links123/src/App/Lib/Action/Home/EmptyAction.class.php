<?php 
class EmptyAction extends CommonAction{ 
	//404页面
	public function _empty(){ 
		header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码 
		$this->title = "真的很抱歉，我们搞丢了页面……";
		$this->display(C('404_PAGE'));
	}
}