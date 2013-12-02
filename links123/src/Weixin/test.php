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
    $url="http://php.weather.sina.com.cn/xml.php?city=".mb_convert_encoding($matches[1], 'GB2312', 'UTF-8')."&password=DJOYnieT8234jlsK&day=0";
    $content=file_get_contents($url);
    echo $content;
    exit;

}
if(preg_match("/翻译@(\W+)/","翻译@上海",$matches)){
    print_r($matches);
    echo Api::translate($matches[1]);
}

?>
