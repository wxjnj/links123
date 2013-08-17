<?php 
/**
 * @desc 404错误页面
 * @name EmptyAction.class.php
 * @package　Home
 * @author Frank UPDATE 2013-08-17
 * @version 0.0.1
 */
import("@.Common.CommonAction");
class EmptyAction extends CommonAction { 
	/*
	 * @desc 404错误页面
	 * @author Frank UPDATE 2013-08-17
	 */
	public function _empty() { 
		header("HTTP/1.0 404 Not Found");
		$this->title = "真的很抱歉，我们搞丢了页面……";
		$this->display(C('404_PAGE'));
	}
}