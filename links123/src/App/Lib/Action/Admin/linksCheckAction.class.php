<?php

// 判断url中否有效
class LinksCheckAction extends CommonAction {

    public function index() {
        $sql = "select id,title,link from lnk_links order by id asc limit 10";
        $list = M()->query($sql);
        foreach ($list as $slist) {
            $url = 'http://' . $slist['link'];
            $a = $this->is_yxurl($url);
            if ($a == '1') {
                $sq = "update lnk_links set is_url=0 where id={$slist['id']}";
                M()->execute($sq);
            }
            //sleep(3);
        }
    }

    //当跳转到114里面里也是无效URL
    private function is_yxurl($url) {
        //先判断状态码
        $exists = $this->check_remote_file_exists($url);
        if ($exists) {
            //再判断是否转到114页面
            $arr = get_headers($url, 0);
            if ($arr[4] == 'Content-Length: 701') {
                $ar = '1';
            } else {
                $ar = '0';
            }
        } else {
            $ar = '0';
        }
        return $ar;
    }

    //判断状态码是否为200 如果是200表示有效的URL
    private function check_remote_file_exists($url) {
        $curl = curl_init($url);
        // 不取回数据 
        curl_setopt($curl, CURLOPT_NOBODY, true);
        // 发送请求 
        $result = curl_exec($curl);
        $found = false;
        // 如果请求没有发送失败 
        if ($result !== false) {
            // 再检查http响应码是否为200 
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                $found = true;
            }
        }
        curl_close($curl);

        return $found;
    }

}

?>