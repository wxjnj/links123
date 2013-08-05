<?php
class StatAction extends CommonAction {
	
	public function  index(){
		$stat = D("Stat");
		$listByLevel = $stat->statByLevel();
		$listByObj   =  $stat->statByObject();
		$listLevelJson = json_encode($listByLevel);
		$listByObjJson = json_encode($listByObj);
		$this->assign('listByLevel',$listByLevel);
		$this->assign('listByObj',$listByObj);
		$this->assign('levelJson',$listLevelJson);
		$this->assign('objJson',$listByObjJson);
		$this->display();
		
	}
}