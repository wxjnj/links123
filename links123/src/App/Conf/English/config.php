<?php

return array(
	'SHOW_PAGE_TRACE'         =>	false,
    'ENGLISH_DICT_SEARCH_URL' => 'http://dict.youdao.com/search?q=###', //英语角词典查询地址###为占位符
    'ENGLISH_DICT_SPEAK_URL'  => U('English/Index/getAudio',array('word'=>'###','type'=>'1')), //英语角单词读音获取地址###为占位符
    //'ENGLISH_DICT_SPEAK_URL' => 'http://dict.youdao.com/dictvoice?audio=###&type=1', //英语角单词读音获取地址###为占位符
    'ENGLISH_SPEECH_API_URL'  => 'http://www.google.com/speech-api/v1/recognize?xjerr=1&client=chromium&lang=en-US&maxresults=5', //英语角语音识别API地址
   // 'ENGLISH_REDIRECT_URL' => 'http://en.links123.cn/',	//英语角重定向地址
);
?>
