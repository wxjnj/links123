<?php

class CommonModel extends Model {

    // 获取当前用户的ID
    public function getMemberId() {
        return isset($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : 0;
    }

    /*
     * 获取记录列表
     * @author adam 2013.5.28
     * @param string/array $condition [条件]
     * @param string $order [排序方式]
     * @param string $limit [获取数量]
     * @return array [列表数组]
     */

    public function getList($condition, $order, $limit) {
        $ret = $this->where($condition)->order($order)->limit($limit)->select();
        if (false === $ret) {
            return array();
        }
        return $ret;
    }

    /*
     * 根据记录id获取记录信息
     * 
     */

    public function getInfoById($id) {
        $ret = $this->find($id);
        if (false === $ret) {
            return array();
        }
        return $ret;
    }

    /**
      +----------------------------------------------------------
     * 根据条件禁用表数据
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @param array $options 条件
      +----------------------------------------------------------
     * @return boolen
      +----------------------------------------------------------
     */
    public function forbid($options, $field = 'status') {

        if (FALSE === $this->where($options)->setField($field, 0)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

    /**
      +----------------------------------------------------------
     * 根据条件批准表数据
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @param array $options 条件
      +----------------------------------------------------------
     * @return boolen
      +----------------------------------------------------------
     */
    public function checkPass($options, $field = 'status') {
        if (FALSE === $this->where($options)->setField($field, 1)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

    /**
      +----------------------------------------------------------
     * 根据条件恢复表数据
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @param array $options 条件
      +----------------------------------------------------------
     * @return boolen
      +----------------------------------------------------------
     */
    public function resume($options, $field = 'status') {
        if (FALSE === $this->where($options)->setField($field, 1)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

    /**
      +----------------------------------------------------------
     * 根据条件恢复表数据
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @param array $options 条件
      +----------------------------------------------------------
     * @return boolen
      +----------------------------------------------------------
     */
    public function recycle($options, $field = 'status') {
        if (FALSE === $this->where($options)->setField($field, 0)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

    public function recommend($options, $field = 'is_recommend') {
        if (FALSE === $this->where($options)->setField($field, 1)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

    public function unrecommend($options, $field = 'is_recommend') {
        if (FALSE === $this->where($options)->setField($field, 0)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return True;
        }
    }

}

?>