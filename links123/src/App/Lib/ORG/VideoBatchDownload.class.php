<?php

/**
 * 批量下载视频类
 *
 * @author Adam $date2013-07-18$
 */
class VideoBatchDownload {

    const USER_AGENT = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:19.0) Gecko/20100101 Firefox/19.0 Chromium/18.0.1025.168 Chrome/18.0.1025.168 Safari/535.19';

    private $_error; //错误信息
    private $_urls; //下载的视频网址数组
    private $_realUrls; //视频下载的真实源文件地址数组
    private $_savePath; //视频下载的目录
    //支持下载的网站地址列表
    private $_supportWebsite = array(
        'youku.com' => "_youku",
        "peepandthebigwideworld.com" => "_peepandthebigwideworld"
    );
    private $_supportType = array('swf', 'flv', 'mp4', 'mp3'); //可下载视频的类型

    public function __construct() {
        //初始化默认保存路径./Public/Uploads/English/
        $path = realpath('./Public/Uploads/uploads.txt');
        $this->_savePath = str_replace('uploads.txt', 'English', $path) . '\\';
        if (!is_dir($this->_savePath)) {
            @mkdir($this->_savePath);
        }
        set_time_limit(240);
    }

    public function download($urls) {
        if (!is_array($urls) || empty($urls)) {
            return false;
        }
        $this->_urls = $urls;
        $this->analysis();
        $fileInfos = $this->curl_multi_download();
    }

    public function analysis() {
        $htmls = array();
        $mh = curl_multi_init();
        foreach ($this->_urls as $key => $value) {
            if (!empty($value)) {
                $ch[$key] = curl_init();
                curl_setopt($ch[$key], CURLOPT_USERAGENT, self::USER_AGENT);
                curl_setopt($ch[$key], CURLOPT_URL, $value);
                curl_setopt($ch[$key], CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch[$key], CURLOPT_TIMEOUT, 0);
                curl_multi_add_handle($mh, $ch[$key]);
            }
        }
        $active = null;
        do {
            $n = curl_multi_exec($mh, $active);
        } while ($active);

        foreach ($this->_urls as $key => $value) {
            if ($ch[$key]) {
                $htmls[$key] = curl_multi_getcontent($ch[$key]);
                curl_close($ch[$key]);
                curl_multi_remove_handle($mh, $ch[$key]);
            }
        }
        curl_multi_close($mh);
        $realUrls = array();
        foreach ($this->_urls as $key => $value) {
            $website = false;
            foreach ($this->_supportWebsite as $k => $v) {
                if (false !== stripos($value, $k)) {
                    $website = $v;
                    break;
                }
            }
            if (!$website) {
                $realUrls[$key] = '';
                continue;
            }
            $realUrls[$key] = $this->$website($htmls[$key]);
        }
        $this->_realUrls = $realUrls;
    }

    public function curl_multi_download() {
        $mh = curl_multi_init();
        $fileInfos = array();
        //自动按照年数+月份为文件夹保存
        $yearDate = date("Ym");
        if (!is_dir($this->_savePath . $yearDate . '\\')) {
            @mkdir($this->_savePath . $yearDate . '\\');
        }
        //循环添加curl子线程
        foreach ($this->_realUrls as $key => $value) {
            $fileInfos[$key]['source_url'] = $value;
            $fileInfos[$key]['web_url'] = $this->_urls[$key];
            $videoMineType = end(explode('.', $value)); //根据$videoRealUrl取文件后缀名
            //视频类型不支持
            if (empty($videoMineType) || !in_array($videoMineType, $this->_supportType)) {
                $this->setError("FILE IS NOT ALLOW DOWNLOAD");
                continue;
            }
            $fileInfos[$key]['mineType'] = $videoMineType;
            $fileInfos[$key]['name'] = $yearDate . "/" . uniqid() . "." . $videoMineType;
            $fileInfos[$key]['path'] = $this->_savePath;
            $ch[$key] = curl_init($value);
//            if (!$fp[$key] = fopen($this->_savePath . $fileInfos[$key]['name'], "w")) {
//                $this->setError("SAVE ERROR");
//                continue;
//            }

            curl_setopt($ch[$key], CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($ch[$key], CURLOPT_FILE, $fp[$key]);
            curl_setopt($ch[$key], CURLOPT_HEADER, 0);
            curl_setopt($ch[$key], CURLOPT_TIMEOUT, 0);
            curl_multi_add_handle($mh, $ch[$key]);
        }
        do {
            $n = curl_multi_exec($mh, $active);
        } while ($active);
        
        foreach ($this->_realUrls as $key => $value) {
//            fclose($fp[$key]);
            curl_close($ch[$key]);
            curl_multi_remove_handle($mh, $ch[$key]);
        }
        curl_multi_close($mh);
        return $fileInfos;
    }

    private function _peepandthebigwideworld($html) {
        $realUrl = "";
        if (!$html) {
            return $realUrl;
        }
        // 获取视频源地址
        $flashvars = $this->match('/<param name=\"flashvars\" value=\"epis=(.*)&amp;cc=0\" \/\>/i', $html, 1);
        $swfUrl = $this->match('/<embed.*src=(\'|\")(.*\.swf)(\'|\")/i', $html, 2);
        $baseUrl = substr($swfUrl, 0, strpos($swfUrl, "video_player.swf"));
        // <embed src="http://d21na5cfk0jewa.cloudfront.net/videos/media/video_player.swf">
        //视频源：http://d21na5cfk0jewa.cloudfront.net/videos/media/patternplay.flv
        $realUrl = $baseUrl . $flashvars . ".flv";
        return $realUrl;
    }

    /**
     * 使用正则匹配获取字符串
     * @param string $pattern 需要匹配的正则表达式
     * @param string $subject 用于匹配的字符串
     * @param integer $num 需要获取匹配的第几个值
     * @return string
     */
    public static function match($pattern, $subject, $num = 1) {
        $boolean = preg_match($pattern, $subject, $matches);
        $str = '';
        if ($boolean) {
            $str = $matches[$num];
        }
        return $str;
    }

    public static function parsmEncode($params, $isRetStr = true) {
        $fieldStr = '';
        $spr = '';
        $result = array();
        foreach ($params as $key => $value) {
            $value = urlencode($value);
            $fieldStr .= $spr . $key . '=' . $value;
            $spr = '&';
            $result[$key] = $value;
        }
        return $isRetStr ? $fieldStr : $result;
    }

    public function getError() {
        return $this->_error;
    }

    private function setError($error) {
        $this->_error = $error;
    }

    public function setSavePath($path) {
        $this->_savePath = $path;
    }

}

?>
