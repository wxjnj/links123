<?php
/**
 * @package Model
 * @name VariableModel.class.php
 * @author Frank UPDATE 2013-8-17
 */
class VariableModel extends CommonModel {

	/**
	 * @desc 设置Variable某个字段的值
	 * @author Frank UPDATE 2013-08-17
	 * @param string $vname
	 * @param string $value
	 * @param string $explain
	 * @return boolean
	 */
	
    public function setVariable($vname, $value, $explain) {
        $data['explain'] = $explain;
        if (is_numeric($value)) {
            $data['value_int'] = intval($value);
        } else {
            $data['value_varchar'] = $value;
        }
        $ret = $this->where("vname='%s'", $vname)->getField("vname");
        
        if (false !== $ret && empty($ret)) {
            $data['vname'] = $vname;
            if(false === $this->add($data)){
                return false;
            }
        }else{
            $ret = $this->where("vname='%s'", $vname)->save($data);
            if(false === $ret){
                return false;
            }
        }
        return true;
    }

    /**
     * @desc 获取Variable 某个字段的值
     * @author Frank UPDATE 2013-08-17
     * @param string $vname
     * @return string or int
     */
    public function getVariable($vname) {
        $value = '';
        $ret = $this->where("vname='%s'", $vname)->find();
        $value = empty($ret['value_int']) ? $ret['value_varchar'] : intval($ret['value_int']);
        return $value;
    }

}

?>
