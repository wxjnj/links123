<?php
ob_start();
class VerifyAction extends CommonAction {
	public function index() {
		ob_end_clean();
		$type = $this->_param('type') != '' ? $this->_param('type') : 'gif';
		import("@.ORG.Image");
		Image::buildImageVerify(3, 1, $type, 48, 28);
	}
}
?>