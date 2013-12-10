$(function(){
    var TopWeahter = {

        Init: function(){

            var self = this;

            $('.header-weather').find('.region').mouseenter(function(){
                clearTimeout(self.timer);
                self.timer = null;
                if(!$('.weather-find-region-box').is(':hidden')){
                    return;
                }
                $('.weather-find-region-box').show();
                $('.header-weather').find('.region').addClass('region-active');
                self.renderRegion();
            });

            $('.header-weather').find('.region').mouseleave(function(){
                self.timer = setTimeout(function(){
                    $('.weather-find-region-box').hide();
                    $('.header-weather').find('.region').removeClass('region-active');
                }, 200);
            });

            $('.weather-find-region-box').mouseover(function(){
                clearTimeout(self.timer);
                self.timer = null;
            }).mouseleave(function(){
                self.timer = setTimeout(function(){
                    $('.weather-find-region-box').hide();
                    $('.header-weather').find('.region').removeClass('region-active');
                }, 200);
            });

            $('.weather-find-region-box').find('.content').on('click', '.region-tab-li', function(){
                $('.region-tab-li').removeClass('active');
                $(this).addClass('active');
                var cur = $(this).html();
                $('.region-box').hide();
                $('.region-box[data-tab="' + cur + '"]').show();
            }).on('click', '.region-name-li', function(){
                var c = $(this).html();
                self.load(c);
            }).on('keydown', '.region-name-input', function(event){
                if (event.keyCode == 13) {
                    var c = $(this).val();
                    self.load(c);
                    
                }
            });
            self.showIcon();
        },

        load: function(c){
            var self = this;
            $('.weather-find-region-box').hide();
            
            self.show(c, '', 'loading', 'loading');

            $.ajax({
                url: '/Home/Weather/city?city=' + encodeURIComponent(c),
                type: 'get',
                dataType: 'json'
            }).fail(function(){

            }).done(function(d){
                $.cookies.set('weather_region', d.c, { expiresAt: (new Date).add_day(365) });
                if(d.t == 'NA') {
                    d.t = d.d1.l + '-' + d.d1.h;
                }

                self.show(d.n, d.t, d.s, d.i.aq.label);
            });
        },


        show: function(city, temp, sun, air){
            var o = $('.header-weather');
            o.find('.region').html(city);
            o.find('.degree').html(temp + '°');
            o.find('.info').html('<span class="info"><b class="weather-icon"></b><b class="weather-desc">' + sun + '</b>，空气' + air + '</span>');
            this.showIcon();
        },

        showIcon: function(){

            var desc = $('.weather-desc').html();

            if(desc.search('晴间多云') >= 0) {
                $('.weather-icon').addClass('weather-icon-partly-cloudy');
                return;
            }
            if(desc.search('晴') >= 0) {
                if((new Date()).getHours() >= 18 || (new Date()).getHours() <= 6){
                    $('.weather-icon').addClass('weather-icon-night');
                }else{
                    $('.weather-icon').addClass('weather-icon-sunny');
                }
                return;
            }
            if(desc.search('阴') >= 0) {
                if((new Date()).getHours() >= 18 || (new Date()).getHours() <= 6){
                    $('.weather-icon').addClass('weather-icon-night-cloudy');
                }else{
                    $('.weather-icon').addClass('weather-icon-cloudy-day');
                }
                return;
            }
            if(desc.search('多云') >= 0) {
                if((new Date()).getHours() >= 18 || (new Date()).getHours() <= 6){
                    $('.weather-icon').addClass('weather-icon-night-less-cloudy');
                }else{
                    $('.weather-icon').addClass('weather-icon-cloudy');
                }
                return;
            }

            if(desc.search('雷阵雨') >= 0) {
                $('.weather-icon').addClass('weather-icon-thundershowers-sunny');
                return;
            }

            if(desc.search('阵雨') >= 0) {
                $('.weather-icon').addClass('weather-icon-shower');
                return;
            }

            if(desc.search(/(小雨)|(中雨)/) >= 0) {
                $('.weather-icon').addClass('weather-icon-rain');
                return;
            }

            if(desc.search(/(大雨)|(暴雨)/) >= 0) {
                $('.weather-icon').addClass('weather-icon-heavy-rain');
                return;
            }

            if(desc.search(/(小雪)|(中雪)/) >= 0) {
                $('.weather-icon').addClass('weather-icon-snow');
                return;
            }

            if(desc.search(/(大雨)|(暴雨)/) >= 0) {
                $('.weather-icon').addClass('weather-icon-heavy-snow');
                return;
            }    

            if(desc.search('雨夹雪') >= 0) {
                $('.weather-icon').addClass('weather-icon-rain-snow');
                return;
            }  

            if(desc.search('浮尘') >= 0) {
                $('.weather-icon').addClass('weather-icon-dust');
                return;
            }  

            if(desc.search('冻雨') >= 0) {
                $('.weather-icon').addClass('weather-icon-sleet');
                return;
            }  

            if(desc.search(/(沙尘暴)|(扬沙)/) >= 0) {
                $('.weather-icon').addClass('weather-icon-wind');
                return;
            }  

            if(desc.search(/雾|霾/) >= 0) {
                $('.weather-icon').addClass('weather-icon-fog');
                return;
            }  

            // 无匹配时隐藏
            $('.weather-icon').hide();



        },

        renderRegion: function(){
            var self = this;
            var regions = self.regions;
            var tab = '';
            var box = '';
            var city = ''
            $.each(regions, function(k, v){
                tab += '<li class="region-tab-li">' + k + '</li>'
                city = '';
                $.each(v, function(idx, val){
                    city += '<li class="region-name-li">' + val + '</li>';
                });
                box += '<ul class="region-box" style="display:none" data-tab="' + k + '">' + city + '</ul>';
            });
            tab = '<ul class="region-tab">' + tab + '</ul>';
            var o = $('.weather-find-region-box').find('.region-ul');
            o.html(tab + box);
            o.find('.region-tab li:first').addClass('active');
            o.find('.region-box:first').show();

        },


        regions: {
            '热门': [
                '北京', '上海', '昆明', '杭州', '广州', '雅安', '芦山', '天全', '宝兴',
                '钓鱼岛', '西安', '南京', '深圳', '重庆', '长沙', '沈阳', '武汉', '海口', 
                '乌鲁木齐', '成都', '哈尔滨', '三亚', '长春', '南宁', '福州', '郑州', '济南',
                '天津', '太原', '南昌', '拉萨', '西宁', '香港', '澳门', '台北'
            ],
            'A-G': [
                '安庆', '阿勒泰', '安康', '阿克苏', '白山', '包头', '北海', '北京', '百色', '保山', '长治',
                '长春', '常州', '昌都', '朝阳', '常德', '成都', '重庆', '长沙', '赤峰', '大同', '大连', '东营',
                '大庆', '丹东', '大理', '敦煌', '鄂尔多斯', '恩施', '福州', '阜阳', '贵阳', '桂林', '广州', '广元'
            ],
            'H-L': [
                '呼和浩特', '哈密', '黑河', '海拉尔', '哈尔滨', '海口', '黄山', '杭州', '邯郸', '合肥', 
                '黄龙', '汉中', '和田', '晋江', '锦州', '景德镇','嘉峪关', '井冈山', '济宁', '九江', 
                '佳木斯', '济南', '喀什', '昆明', '康定', '克拉玛依', '库尔勒', '库车', '兰州', '洛阳',
                '丽江', '柳州', '泸州', '连云港', '拉萨'
            ],
            'M-T': [
                '牡丹江', '满洲里', '绵阳', '梅县', '漠河', '南京', '南充', '南宁', 
                '南阳', '南通', '南昌', '宁波', '攀枝花', '衢州', '秦皇岛', '庆阳', '且末',
                '齐齐哈尔', '青岛', '汕头', '深圳', '石家庄', '三亚', '沈阳', '上海', '铜仁', '塔城',
                '腾冲', '台州', '天水', '天津', '通辽', '泰安', '太原', '唐山'
            ],
            'W-Z': [
                '威海', '武汉', '梧州', '文山', '无锡', '潍坊', '武夷山', '乌兰浩特', '温州', '乌鲁木齐', '万州',
                '乌海', '兴义', '西昌', '厦门', '香格里拉', '西安', '襄樊', '西宁', '锡林浩特', '徐州', '义乌',
                '永州', '榆林', '延安', '运城', '烟台', '银川', '宜昌', '宜宾', '盐城', '延吉', '玉树', '伊宁', '珠海'
            ],
            '国际': [
                '埃德蒙顿', '阿姆斯特丹', '雅典', '曼谷', '柏林', '布鲁塞尔', '开罗', '喀布尔', '开普敦', '墨西哥城', '哥本哈根',
                '达喀尔', '日内瓦', '河内', '伊斯兰堡', '雅加达', '吉隆坡', '伦敦', '马德里', '莫斯科', '内罗毕', '新德里', '渥太华',
                '巴黎', '平壤', '罗马', '首尔', '新加坡', '卡萨布兰卡', '德黑兰', '乌兰巴托', '维也纳', '万象', '华盛顿特区', '仰光'
            ]
        }
    };

    TopWeahter.Init();
    
});

