<?php
// 设置模型
class WebSettingsModel extends CommonModel {
	public $_validate =	array(
		array('setting_value', 'require', '设置内容必须'),
		array('setting_name', 'require', '设置名称必须')
	);
	
	public $_auto =	array(
		array('create_time', 'time', self::MODEL_INSERT, 'function'),
	);
	public function updatewebSettings(){
		$webSettingsArr=$this->select();
		S("webSettingsArr",$webSettingsArr);
		return $webSettingsArr;
	}
	//得到网站设置:
	public function getwebSettings($settingMark=null){
		$webSettingsArr = S("webSettingsArr");
		if (empty($webSettingsArr)){
			$webSettingsArr = $this->updatewebSettings();
		}
		if (empty($settingMark)){
			return $webSettingsArr ;
		}
		foreach ($webSettingsArr as $k=>$v) {
			if ($v['setting_mark'] == $settingMark){
				return $v['setting_value'];
			}
		}
		$this->error = L('_NO_DB_CONFIG_');
		return false ;
	}
	
}
?>
