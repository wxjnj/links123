var AppsTpl = {
	'#J_box_mail' :   '<div class="box-mail" id="J_box_mail"> \
				<form action="https://ssl.mail.163.com/entry/coremail/fcg/ntesdoor2?df=mail163_letter&from=web&funcid=loginone&iframe=1&language=-1&net=t&passtype=1&product=mail163&race=382_92_196_gz&style=-1" method="post" id="J_mailForm" target="_blank"> \
					<ul> \
						<li> \
							<span class="box-mail_input"> \
								<input autocomplete="off" id="mailUserName" placeholder="邮箱帐号">  \
							</span> \
							<span class="box-mail_select" style="-moz-user-select: none;"> \
								<span data-n="0" id="mailSelect">@163.com</span> \
							</span> \
						</li> \
						<li> \
							<span class="box-mail_input box-mail_input_passwd"> \
								<input type="password" id="mailPassWord" placeholder="邮箱密码">  \
							</span>  \
							<span class="box-mail_btn"> \
								<button type="button" id="J_mail_submit">登&nbsp;录</button> \
							</span> \
						</li> \
						<li> \
							<div> \
								<ul selectindex="0" class="mail-list" id="mail_list"> \
									<li dn="0">@163.com</li> \
									<li dn="1">@126.com</li> \
									<li dn="2">@sina.com</li> \
									<li dn="4">@sina.cn</li> \
									<li dn="5">@sohu.com</li> \
									<li dn="6">@yeah.net</li> \
									<li dn="7">@139.com</li> \
									<li dn="8">@21cn.com</li> \
									<li dn="9">@qq.com</li> \
									<li dn="10">@gmail.com</li> \
									<li dn="11">@hotmail.com</li> \
									<li dn="12">@aliyun.com</li> \
									<li dn="13">@yahoo.com</li> \
									<li dn="14">@links123.cn</li> \
								</ul> \
							</div> \
						</li> \
					</ul> \
				</form> \
			</div>',
	'#J_box_calendar':  '<div style="display:none;" id="J_box_calendar" class="box_with_iframe_list"> \
				<div> \
					<ul id="J_box_calendar_list" class="clearfix"> \
						<li> \
							<a href="#" data-url="http://baidu365.duapp.com/wnl.html" title="365日历"><img src="'+$CONFIG.STATIC+'/v3/imgs/sources/340238f487321ab424dd471680e314a3.jpg"></a> \
						</li> \
						<li> \
							<a href="#" data-url="http://apps1.bdimg.com/store/static/kvt/76ba3d209468317581a09f49aa48fef9.swf" title="万年历查询"><img src="'+$CONFIG.STATIC+'/v3/imgs/sources/b18f1f24d6a558e7b340b7be5c9f09b5.jpg"></a> \
						</li> \
						<li> \
							<a href="#" data-url="http://xcalendar.duapp.com/" title="万日历"><img src="'+$CONFIG.STATIC+'/v3/imgs/sources/cb9b3ccff4fcff429a35ed4e753586a8.png"></a> \
						</li> \
						<li> \
							<a href="#" data-url="http://baidu.open.zhwnl.cn/baidu/html/index.html" title="中国万年历"><img src="'+$CONFIG.STATIC+'/v3/imgs/sources/3096a9364b5b6afd3887cb7e1d5e98ef.jpg"></a> \
						</li> \
						<li> \
							<a href="#" data-url="http://saturdaycalendar.duapp.com" title="礼拜六万年历"><img src="'+$CONFIG.STATIC+'/v3/imgs/sources/5a86bbd61be39be1fe46a3bbe4def6fd.jpg"></a> \
						</li> \
						<li> \
							<a href="#" data-url="http://app.yesky.com/cms/wnl?bd_user=2519257532&bd_sig=60decbc778008689c8229834ec7de3c4&canvas_pos=platform" title="天极万年历"><img src="'+$CONFIG.STATIC+'/v3/imgs/sources/ac1e59f87ce2848c746c7d8268b61b14.png"></a> \
						</li> \
					</ul> \
				</div> \
				<iframe id="J_calendar_iframe" width="540px" height="450px" scrolling="no" frameborder="0" style="" allowtransparency="true" src=""></iframe> \
			</div>',
	'#J_box_translate': '<div style="display:none;" id="J_box_translate"> \
				<div id="gt-src-wrap"> \
					<label style="display: none" for="source">翻译文字或网页</label> \
					<div style="width: 100%;"> \
						<div class="gt-hl-layer" dir="ltr"></div> \
						<textarea autocorrect="off" autocomplete="off" autocapitalize="off" \
							spellcheck="false" dir="ltr" tabindex="0" wrap="SOFT" name="text" \
							id="source" class="goog-textarea J_translate_source"></textarea> \
						<div id="clear" class="clear-button goog-toolbar-button" title="清除文&#8203;&#8203;字"> \
							<span class="jfk-button-img J_translate_clear"></span> \
						</div> \
					</div> \
				</div> \
				<div id="gt-langs"> \
					<div id="gt-lang-src"> \
						<input type="hidden" id="gt-sl" name="sl" value="auto"> \
						<div class="goog-inline-block goog-flat-menu-button je" \
							id="gt-sl-gms" role="button" style="-moz-user-select: none;" \
							tabindex="0" aria-haspopup="true"> \
							<div class="goog-inline-block goog-flat-menu-button-caption">源语言： \
								<span id="J_lang_src">自动检测语言</span></div> \
							<div class="goog-inline-block goog-flat-menu-button-dropdown"></div> \
						</div> \
					</div> \
					<div id="gt-lang-swap"> \
						<div \
							class="jfk-button-standard jfk-button-narrow jfk-button jfk-button-disabled trans-swap-button je" \
							id="gt-swap" aria-disabled="true" role="button" \
							style="-moz-user-select: none;" data-tooltip="调转两种语言" \
							aria-label="调转两种语言"> \
							<span class="jfk-button-img"></span> \
						</div> \
					</div> \
					<div id="gt-lang-tgt"> \
						<input type="hidden" id="gt-tl" name="tl" value="zh-CN"> \
						<div class="goog-inline-block goog-flat-menu-button je" \
							id="gt-tl-gms" role="button" style="-moz-user-select: none;" \
							tabindex="0" aria-haspopup="true"> \
							<div class="goog-inline-block goog-flat-menu-button-caption">目标语言： \
								<span id="J_lang_tgt">中文(简体)</span></div> \
							<div class="goog-inline-block goog-flat-menu-button-dropdown"></div> \
						</div> \
					</div> \
					<div id="gt-lang-submit"> \
						<input type="submit" class="jfk-button jfk-button-action" tabindex="0" value="翻译" id="gt-submit"> \
					</div> \
					<div style="clear: float;"></div> \
				</div> \
				<div class="goog-menu goog-menu-vertical" id="gt-sl-gms-menu" role="menu" aria-haspopup="true" aria-activedescendant=":2"> \
					<table><tbody><tr></tr></tbody></table> \
				</div> \
				<div id="gt-res-wrap"> \
					<div class="almost_half_cell" id="gt-res-content"> \
						<div style="zoom: 1" dir="ltr"> \
							<div class="" style="" id="tts_button"> \
								 \
							</div> \
							<span lang="" class="short_text" id="result_box"></span> \
						</div> \
					</div> \
				</div> \
				<div id="gt-res-dict"></div> \
				</div>',
	'#J_box_calc':  '<div style="display:none;" id="J_box_calc" class="box_with_iframe_list"> \
				<div> \
					<ul id="J_box_calc_list" class="clearfix"> \
						<li> \
							<a href="#" data-url="http://qiqiapp3.duapp.com/yuyinjisuanqi/" title="语音计算器"><img src="'+$CONFIG.STATIC+'/v3/imgs/sources/7806700bd0d4e1fa3225eb468a1ac6b6.png"></a> \
						</li> \
						<li> \
							<a href="#" data-url="http://app.yesky.com/cms/jsq/" title="科学计算器"><img src="'+$CONFIG.STATIC+'/v3/imgs/sources/47aeab3335b9d10cb154469dedebb91d.png"></a> \
						</li> \
						<li> \
							<a href="#" data-url="http://apps2.bdimg.com/store/static/kvt/ac05740f5ab0c828596575e2b4aa6bde.swf" title="计算器美女语音版"><img src="'+$CONFIG.STATIC+'/v3/imgs/sources/7424f71bc4833877ecf44025d97d4b23.png"></a> \
						</li> \
						<li> \
							<a href="#" data-url="http://apps.bdimg.com/store/static/kvt/62ba443535a86ee0d8bee5706444b40a.swf" title="科学计算器"><img src="'+$CONFIG.STATIC+'/v3/imgs/sources/065558d9d552d0a1f99fcc856e177ea7.png"></a> \
						</li> \
						<li> \
							<a href="#" data-url="http://app.rong360.com/fangdaical.html" title="按揭房贷计算器"><img src="'+$CONFIG.STATIC+'/v3/imgs/sources/3f45bc4639505e83958abc4aa7ab69bf.png"></a> \
						</li> \
						<li> \
							<a href="#" data-url="http://www.52popsoft.com/CurrencyConvert/Baidu/MainFormV5.htm" title="汇率换算"><img src="'+$CONFIG.STATIC+'/v3/imgs/sources/9016e3a584e5a1d9421e07d7b0a79a33.jpg"></a> \
						</li> \
					</ul> \
				</div> \
				<iframe id="J_calc_iframe" width="540px" height="530px" scrolling="no" frameborder="0" style="" allowtransparency="true" src=""></iframe> \
			</div>',
	'#J_box_note': '<div id="J_box_note" style="display: none" class="J_box_note"> \
				<div class="box_note_header"> \
					<a href="#" class="btn_add">add</a> \
					<div class="colors-wrap"> \
						<a href="#" class="color_1">1</a> \
						<a href="#" class="color_2">2</a> \
						<a href="#" class="color_3">3</a> \
						<a href="#" class="color_4">4</a> \
						<a href="#" class="color_5">5</a> \
						<a href="#" class="color_6">6</a> \
					</div> \
					<a href="#" class="btn_clear">clear</a> \
				</div> \
				<div> \
					<textarea></textarea> \
				</div> \
			</div>',
	'#J_box_weather': '<div style="display:none" id="J_box_weather"> \
				<div class="K_weather_box" style="width: 380px; height: 220px;"></div> \
			</div>',
    '#J_box_music': '<div id="J_box_music" class="normal_music_box" style="display: none">\
            <div class="mini_music_channel_list">\
                <div class="mini_music_channel_select">\
                    <div class="mini_current_channel"></div><i class="mini_music_ang"></i>\
                    <div class="mini_channel_list">\
                    </div>\
                </div>\
                <div class="music-close-tip">听烦了</div>\
                <a class="mini_music_box_close_btn" href="javascript:;">×</a>\
                <a class="mini_music_box_play_btn mini_music_box_play_btn_pause" href="javascript:;"></a>\
                <a class="mini_music_box_size_btn size_mini" href="javascript:;" data-size="mini">小</a>\
                <a class="mini_music_box_size_btn size_normal" href="javascript:;" data-size="normal">中</a>\
                <a class="mini_music_box_size_btn size_fullscreen" href="javascript:;" data-size="fullscreen">大</a>\
            </div>\
            <div class="normal_music_iframe_box">\
                <iframe id="K_303_music_iframe" allowtransparency="true" frameborder="0" style=""></iframe>\
                <div class="normal_music_iframe_box_pause_status">\
                	<a href="javascript:;"></a>\
                	<p>点击继续听...</p>\
                </div>\
            </div>\
            <div class="normal_music_channel_list">\
            	<div class="normal_music_channel_list_toggle"><span class="list-open"></span></div>\
            	<div class="music-close-tip">听烦了</div>\
                <a class="normal_music_box_close_btn" href="javascript:;">×</a>\
                <a class="normal_music_box_play_btn" href="javascript:;"></a>\
                <!--a href="javascript:;" class="normal_music_box_toggle_btn">展开</a-->\
                <ul>\
                </ul>\
                <a class="normal_music_box_size_btn size_nimi" href="javascript:;" data-size="mini">小</a>\
                <a class="normal_music_box_size_btn size_normal" href="javascript:;" data-size="normal">中</a>\
                <a class="normal_music_box_size_btn size_fullscreen" href="javascript:;" data-size="fullscreen">大</a>\
            </div>\
        </div>'
};












