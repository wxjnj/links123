<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-21
 * Time: 下午5:13
 */

class  Api {
    public static  function getWeather($city){
        $url="http://php.weather.sina.com.cn/xml.php?city=".urlencode("$city")."&password=DJOYnieT8234jlsK&day=0";
         $content=file_get_contents($url);
         $xml= simplexml_load_string($content);
         $returnstr=$city."天气预报\n".$xml->Weather["status1"]."\t".$xml->Weather["direction1"].$xml->Weather["power1"]
                    ."级，温度：".$xml->Weather["temperature2"]."~".$xml->Weather["temperature1"]."度，当前温度：".$xml->Weather["tgd1"]
                    ."度";
        return $returnstr;
    }
} 