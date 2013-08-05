<?php

class VariableModel extends CommonModel {

    public function setVariable($vname, $value, $explain) {
        $data = array();
        if (is_numeric($value)) {
            $data['value_int'] = intval($value);
        } else {
            $data['value_varchar'] = $value;
        }
        $data['explain'] = $explain;
        $ret = $this->where("`vname`='{$vname}'")->save($data);
        if (false !== $ret && $ret < 1) {
            $data['vname'] = $vname;
            $this->add($data);
        }
    }

    public function getVariable($vname) {
        $value = '';
        $ret = $this->where("`vname`='{$vname}'")->find();
        if (!empty($ret['value_int'])) {
            $value = intval($ret['value_int']);
        } else {
            $value = $ret['value_varchar'];
        }
        return $value;
    }

}

?>
