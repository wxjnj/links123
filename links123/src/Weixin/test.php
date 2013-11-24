<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-22
 * Time: 上午9:11 update test
 */
include "Api.class.php";


$matches=array();
if(preg_match("/天气@(\W+)/","天气@上海",$matches)){
   // print_r($matches);
    echo Api::getWeather($matches[1]);
}


?>
