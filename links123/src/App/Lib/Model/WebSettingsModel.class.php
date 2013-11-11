<?php
// 设置模型
class WebSettingsModel extends CommonModel {
	public $_validate =	array(
		array('setting_value', 'require', '设置内容必须'),
		array('setting_name', 'require', '设置名称必须')
	);
	static private $_webSettingsArr = array();
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
	
	//得到网站设置:
	public function getwebSettings($settingMark=null){
		//$webSettingsArr = S("webSettingsArr");
		if (empty(static::$_webSettingsArr)){
			static::$_webSettingsArr=$this->select();
			//S("webSettingsArr",$webSettingsArr);
		}
		if (empty($settingMark)){
			return static::$_webSettingsArr ;
		}
		foreach (static::$_webSettingsArr as $k=>$v) {
			if ($v['setting_mark'] == $settingMark){
				return $v['setting_value'];
			}
		}
		$this->error = L('_NO_DB_CONFIG_');
		return false ;
	}
	
}
?>
