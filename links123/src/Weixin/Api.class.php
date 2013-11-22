<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-21
 * Time: 下午5:13
 */

class  Api {
    public static  function getWeather($city){
       // $url="http://php.weather.sina.com.cn/xml.php?city=".$city."&password=DJOYnieT8234jlsK&day=0";
        $url="http://php.weather.sina.com.cn/xml.php?city=".mb_convert_encoding($city, 'GB2312', 'UTF-8')."&password=DJOYnieT8234jlsK&day=0";
      //  echo $url."<br>";
         $content=file_get_contents($url);
        //echo $content."<br>";
         $xml= simplexml_load_string($content);
          // var_dump($xml);
         $returnstr=$city."天气预报\n".$xml->Weather->status1."\t".$xml->Weather->direction1.$xml->Weather->power1
                    ."级\n温度：".$xml->Weather->temperature2."~".$xml->Weather->temperature1."度，"."\n紫外线："
                    .$xml->Weather->zwx_l."\n洗车指数：".$xml->Weather->xcz_s."\n穿衣指数：".$xml->Weather->chy_shuoming;
        return $returnstr;
    }
} 