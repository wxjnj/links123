<?php
/**
 * @desc 验证码
 * @name VerifyAction
 * @package Home
 * @author frank UPDATE 2013-08-20
 * @version 0.0.1
 */

import("@.Common.CommonAction");
class VerifyAction extends CommonAction {
	/**
	 * @desc 验证页面
	 * @see VerifyAction::index()
	 */
	public function index() {
		$type = $this->_param('type') != '' ? $this->_param('type') : 'gif';
		import("@.ORG.Image");
		Image::buildImageVerify(3, 5, $type, 48, 28);
	}
}
?>