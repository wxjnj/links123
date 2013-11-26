<?php

/**
 * 便签
 * 
 * @author slate date:2012-11-15
 *
 */
class NoteModel extends CommonModel {
    
	public function addNote($saveData) {
		
		return $this->add($saveData);
	}
	
	public function updateNote($id, $user_id, $saveData) {
		
		return	(false === $this->where(array('id' => $id, 'mid' => $user_id))->save($saveData));
	}
	
	public function delNote($id, $user_id) {
		
		return	(false === $this->where(array('id' => $id, 'mid' => $user_id))->save(array('status' => 1)));
	}
	
	public function getNotesByUser($user_id) {
		
		return $this->where(array('mid' => $user_id,'status' =>0))->select();
	}
}