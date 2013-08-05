<?php
class StatModel extends Model{
	private $tableQuestion = "lnk_english_question";
	private $tableObj = "lnk_english_object";
	private  $tableLevel = "lnk_english_level";

	public function statByLevel(){
		$list = $this->table($this->tableLevel)->
		field(array($this->tableLevel.'.id',$this->tableLevel.'.name','count('.$this->tableQuestion.'.id)'=>'count'))
		->join($this->tableQuestion.' on ' . $this->tableQuestion.'.level =' .$this->tableLevel.'.id')->
		order("sort")->group($this->tableLevel.'.id')->select();
		return $list;
	}

	public function statByObject(){
		$list = $this->table($this->tableObj)->
		field(array($this->tableObj.'.id',$this->tableObj.'.name','count('.$this->tableQuestion.'.id)'=>'count'))
		->join($this->tableQuestion.' on ' . $this->tableQuestion.'.object =' .$this->tableObj.'.id')->
		order("sort")->group($this->tableObj.'.id')->select();
		
		$list[0]['count'] =  $this->table($this->tableQuestion)->count("id");
		return $list;
	}
	
	public function statByVoice(){
		$list;
	}
	
	public function statByPatten(){
		
	}

}
