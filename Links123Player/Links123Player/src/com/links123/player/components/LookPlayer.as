package com.links123.player.components
{
	import com.exsky.DataLoader;
	import com.links123.player.Mode.ClipsVO;
	import com.links123.player.Mode.PlayerVO;
	import com.links123.player.Mode.WordClipsVO;
	import com.links123.player.event.LoadDataEvent;
	
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.events.TimerEvent;
	import flash.external.ExternalInterface;
	import flash.net.URLLoader;
	import flash.utils.Timer;
	
	import mx.core.UIComponent;
	import mx.effects.Parallel;
	import mx.effects.Sequence;
	import mx.events.ResizeEvent;
	import mx.graphics.shaderClasses.ExclusionShader;
	
//	import org.bytearray.micrecorder.MicRecorder;
	import org.osmf.events.MediaPlayerStateChangeEvent;
	import org.osmf.events.TimeEvent;
	import org.osmf.media.MediaPlayerState;
	
	import spark.components.HGroup;
	import spark.effects.Move;

	/**
	 * 主要控制"看"播放器 
	 * @author Administrator
	 * 
	 */	
	public class LookPlayer extends CommonPlayer
	{	
		/**
		 * 当前判断的字幕条 
		 */		
		private var _curWordBar:WordBar = null;
		
		/**
		 * 是否已出现scrubBar参数 
		 */		
		private var _isscrubBar:Boolean = false;
		
		/**
		 * 字幕条数组 
		 */		
		private var wordbarvec:Vector.<WordBar> = new Vector.<WordBar>();
		
		/**
		 * 上传google类 
		 */		
		private var urloder:URLLoader;
		
		/**
		 * 声音播放类 
		 */		
		protected var soundplayer:SoundPlayer = new SoundPlayer();
		
		/**
		 * 当前字幕条在字幕数组中的位置 
		 */		
		private var curindex:int = -1;
		
		/**
		 * 是否数据加载完成 
		 */		
		private var _isloadDataCompleted:Boolean = false;
		
		public function LookPlayer()
		{
			super();
			this.addEventListener(TimeEvent.CURRENT_TIME_CHANGE,timehandle);
			this.addEventListener(LoadDataEvent.LOAD_PLAYER_DATA_COMPLETE,loaddatacomplete);
			
			ExternalInterface.addCallback("next",nextbtnclick);
			ExternalInterface.addCallback("slow",slowbtnclick);
			ExternalInterface.addCallback("prev",prevbtnclick);
		}
		
		/**
		 * 加载数据完成事件 
		 * @param event
		 * 
		 */		
		protected function loaddatacomplete(event:LoadDataEvent):void
		{
			Logger.debug("starting init data...");
			_isloadDataCompleted = true;
		}
		
		/**
		 * 初始化数据  显示字幕条
		 * 
		 */		
		private function initData():void
		{
			if(scrubBar.clipcon != null && _isscrubBar == true)
			{
				var per:Number = scrubBar.width/this.duration;//PlayerVO.getInstance().duration;//每一秒的距离
				var clips:Vector.<ClipsVO> = PlayerVO.getInstance().clips;//取得数据数组
				for each(var clip:ClipsVO in clips)
				{
					var wordbar:WordBar = new WordBar(clip);
					var n:Number = clip.endtime-clip.starttime;
					wordbar.width = n*per;
					wordbar.height=5;
					wordbar.x = clip.starttime*per;
					wordbar.y = 5;
					wordbarvec.push(wordbar);
					wordbar.addEventListener(MouseEvent.CLICK,wordbarclickhandle);
					scrubBar.clipcon.addElement(wordbar);
				}
				Logger.debug("scrubBar is initcompleted!");
			}else
			{
				Logger.debug("scrubBar is not initcompleted!");
			}
			
			scrubBar.addEventListener(ResizeEvent.RESIZE,resizehandle);
			
			//传入soundplayer播放数据 初始化声音播放器
			var url:String = PlayerVO.getInstance().mp3url;
			soundplayer.createSound(url,false);
		}
		
		/**
		 * 当播放器尺寸变化 
		 * @param event
		 * 
		 */		
		protected function resizehandle(event:ResizeEvent):void
		{
			try{
				if(scrubBar.width>0 && wordbarvec.length>0)
				{
					Logger.debug("scrubBar width is:{0}",scrubBar.width);	
					var per:Number = scrubBar.width/this.duration;
					for(var i:int=0;i<wordbarvec.length;i++)
					{
						var wordbar:WordBar = wordbarvec[i] as WordBar;
						wordbar.x = wordbar.curclip.starttime*per;
					}
				}
			}catch(e:Error)
			{	
				Logger.debug("Resize Error:{0}",e);
			}	
		}
		
		/**
		 * 字幕条点击事件 跳到指定的位置
		 * @param event
		 * 
		 */		
		protected function wordbarclickhandle(event:MouseEvent):void
		{
			var wordbar:WordBar = event.currentTarget as WordBar;
			if(wordbar != null)
			{
				var wordvo:ClipsVO = wordbar.curclip;
				var seeknum:Number = wordvo.starttime;
				this.seek(seeknum);
				Logger.debug("you just click wordbar's seek time:{0}",seeknum);
			}else
			{
				Logger.debug("you just click wordbar's seek time failed!");
			}
		}		
		
		/**
		 * 展现当前字幕条状态 
		 * 
		 */		
		private function showeverybarstate():void
		{
			if(wordbarvec.length>0)
			{
				_curWordBar = getCurrentWordBar(currentTime);
				if(_curWordBar != null)
				{
					if(_curWordBar.currentstate!=3)
					{
						_curWordBar.currentstate = 2;
					}
				}else
				{
					wordshow.visible = false;
				}
				//设置已经过去了的为绿色
				for(var i:int = 0;i<wordbarvec.length;i++)
				{
					var wordbar:WordBar = wordbarvec[i] as WordBar;
					if(i<curindex && wordbar != null)
					{
						wordbar.currentstate = 3;
					}
				}
				
				var wordt:WordBar = wordbarvec[wordbarvec.length-1] as WordBar;
				var wordvo:ClipsVO = wordt.curclip;
				if(currentTime > wordvo.endtime)
				{
					wordt.currentstate = 3;
				}
				
			}
		}
		
		/**
		 * 播放器状态改变 
		 * @param event
		 * 
		 */		
		override protected function playerstatechange(event:MediaPlayerStateChangeEvent):void
		{
			super.playerstatechange(event);
		}
		
		/**
		 * 暂停按钮触发控制释义面板不显示 
		 * @param event
		 * 
		 */		
		override protected function playPauseButton_clickHandler(event:MouseEvent):void
		{
			super.playPauseButton_clickHandler(event);
			if(playing == true)
			{
				if(introWordPanel != null)
				{
					introWordPanel.visible = false;
				}
			}
		}
		
		
		
		/**
		 * 记录上一索引值 
		 */		
		private var _lastindex:int = -1;
		
		/**
		 * 开始说话标示 
		 */		
		private var _speak:Boolean = false;
		
		/**
		 * 根据传入的当前当前时间 取得当前的WordBar
		 * @param idx
		 * @return 
		 * 
		 */	
		private function getCurrentWordBar(time:int):WordBar
		{
			var realw:WordBar = null;
			for(var i:int = 0;i<wordbarvec.length;i++)
			{	
				var wordbar:WordBar = wordbarvec[i] as WordBar;
				var _curwordvo:ClipsVO = wordbar.curclip;
				if(time>=_curwordvo.starttime && time<=_curwordvo.endtime)
				{
					realw = wordbar;
					if(time==_curwordvo.endtime && _speak == false)
					{
						//添加speakPlayer控制
						Logger.debug("Start Speak!");
						takeSpeakControl();
						_speak = true;
					}
					//处理
					if(seeksign == true)
					{
						_lastindex = i-1;
						seeksign = false;
					}
					if(_lastindex != i)
					{
						curindex = i;
						Logger.debug("current clip is:{0},time is:{1},endtime is:{2}",i,time,_curwordvo.endtime);
						if(wordshow != null && wordCon!=null && realw!= null)
						{
							Logger.debug("fill in the word!");
							wordshow.visible = true;
							var _wordvo:ClipsVO = realw.curclip;
							//填充英文字幕
							filllabelword(_wordvo);
							//填充中文字幕
							fillchineseword(_wordvo);
						}
						_speak = false;
						_lastindex = i;
					}
				}
			}
			return realw;
		}
		
		/**
		 * seek标志 
		 */		
		private var seeksign:Boolean = false;
		
		override public function seek(time:Number):void
		{
			super.seek(time);
			seeksign = true;
		}
		
		
		/**
		 * 取得当前的clipsVo 
		 * 
		 */		
		protected function getcurClipsVO():ClipsVO
		{
			var curvo:ClipsVO;
			for(var i:int = 0;i<wordbarvec.length;i++)
			{	
				if(curindex == i)
				{
					var wordbar:WordBar = wordbarvec[i] as WordBar;
					curvo = wordbar.curclip;
				}
			}
			return curvo;
		}
		
		/**
		 * 添加speakplayer控制 
		 * 
		 */		
		protected function takeSpeakControl():void
		{
		}
		
		/**
		 * 填充中文字幕 
		 * @param wordvo
		 * 
		 */		
		private function fillchineseword(wordvo:ClipsVO):void
		{
			if(chinese != null)
			{
				chinese.text = wordvo.chinese;
			}
		}
		
		/**
		 * 默认文字长度 
		 */		
		private var wordlength:int = 10;
		
		/**
		 * 填充字幕 13个英文换行
		 * @param wordvo
		 * 
		 */		
		private function filllabelword(wordvo:ClipsVO):void
		{
			wordCon.removeAllElements();
			disusewitchlength();
			var wordvect:Array = wordvo.wordsarr
			if(wordvect.length<=wordlength)
			{
				var wordcon1:HGroup = new HGroup();
				for(var i:int = 0;i<wordvect.length;i++)
				{
					var labelbtn:LabelButton = new LabelButton(wordvect[i]);
					labelbtn.buttonMode = true;
					labelbtn.addEventListener(MouseEvent.ROLL_OVER,rollover);
					labelbtn.addEventListener(MouseEvent.ROLL_OUT,rollover);
					labelbtn.addEventListener(MouseEvent.CLICK,clickhandle);
					wordcon1.addElement(labelbtn);
				}
				wordCon.addElement(wordcon1);
			}
			if(wordvect.length>wordlength)
			{
				var wordcon2:HGroup = new HGroup();
				var wordcon3:HGroup = new HGroup();
				for(var j:int = 0;j<wordlength;j++)
				{
					var labelbtn1:LabelButton = new LabelButton(wordvect[j]);
					labelbtn1.buttonMode = true;
					labelbtn1.addEventListener(MouseEvent.ROLL_OVER,rollover);
					labelbtn1.addEventListener(MouseEvent.ROLL_OUT,rollover);
					labelbtn1.addEventListener(MouseEvent.CLICK,clickhandle);
					wordcon2.addElement(labelbtn1);
				}
				wordCon.addElement(wordcon2);
				for(var j1:int = wordlength;j1<wordvect.length;j1++)
				{
					var labelbtn2:LabelButton = new LabelButton(wordvect[j1]);
					labelbtn2.buttonMode = true;
					labelbtn2.addEventListener(MouseEvent.ROLL_OVER,rollover);
					labelbtn2.addEventListener(MouseEvent.ROLL_OUT,rollover);
					labelbtn2.addEventListener(MouseEvent.CLICK,clickhandle);
					wordcon3.addElement(labelbtn2);
				}
				wordCon.addElement(wordcon3);
			}
		}
		
		/**
		 * 判断使用哪个长度 
		 * 
		 */		
		private function disusewitchlength():void
		{
			if(stage != null)
			{
				if(stage.stageWidth<520)
				{
					wordlength = 10;
				}else if(stage.stageWidth>520)
				{
					wordlength = 13;
				}
			}
		}
		
		/**
		 * 弹出文字框时记录暂停前的状态 
		 */		
		private var _recordState:String;
		
		/**
		 * labelbtn点击事件
		 * @param event
		 * 
		 */		
		protected function clickhandle(event:MouseEvent):void
		{
			//记录暂停前的状态
			_recordState = _CurrentMediaPlayerState;
			//点击显示文字解释面板
			this.pause();
			var labelbtn:LabelButton = event.target as LabelButton;
			var dat:DataLoader = new DataLoader();
			dat.Host = ProgramConfig.config.WORDHOST;
			dat.Load(makeparam(labelbtn.wordtitle),function callback(data:*):void
			{
				Logger.debug("get word data is:{0}",data);
				if(data!=null)
				{
					var word:WordClipsVO = new WordClipsVO(data);
					fillIntroWordPanel(word);
				}
			},function onError(e:Error):void
			{
				Logger.debug("get word data failed");
			});
			//请求获取该单词的信息
		}
		
		/**
		 * 参数制造 
		 * @param w
		 * @return 
		 * 
		 */		
		private function makeparam(w:String):Object
		{
			var obj:Object = new Object();
			obj.word = w;
			return obj
		}
		
		/**
		 * 填充文字说明面板 
		 * @param labelbtn
		 * 
		 */		
		private function fillIntroWordPanel(wordvo:WordClipsVO):void
		{
			var wordclipVo:WordClipsVO = wordvo;
			introWordPanel.typestr = wordclipVo.type;
			introWordPanel.examplestr = wordclipVo.example;
			introWordPanel.wordstr = wordclipVo.word;
			introWordPanel.introstr = wordclipVo.intro;
			introWordPanel.visible = true;
			this.pause();
		}
		
		/**
		 * 继续按钮事件 
		 * @param event
		 * 
		 */		
		protected function Goonclickhandle(event:MouseEvent):void
		{
			introWordPanel.visible = false;
			if(_recordState == MediaPlayerState.PLAYING)
			{
				this.play();
			}else if(_recordState == MediaPlayerState.PAUSED)
			{
				this.pause();
			}
		}
		
		/**
		 * 记录鼠标以上去之前,播放器状态 
		 */		
		private var _recordstate:String;
		
		/**
		 *定时器 
		 */		
		private var timer:Timer = new Timer(1000,1);
		
		/**
		 * 当前选中对象 
		 */		
		private var _currentTarget:Object = null;
		
		/**
		 * 鼠标移上文字事件 添加下划线
		 * @param event
		 * 
		 */		
		protected function rollover(event:MouseEvent):void
		{
			_currentTarget = event.target;
			if(event.type == MouseEvent.ROLL_OVER)
			{
				Logger.debug("count start!");
				timer.addEventListener(TimerEvent.TIMER_COMPLETE,timercomplete);
				timer.start();
			}else
			{
				Logger.debug("count stop!");
				timer.stop();
			}
			
			if(event.target.getStyle("textDecoration")=="none")
			{
				_recordstate = _CurrentMediaPlayerState;
				event.target.setStyle("textDecoration","underline");
				//this.pause();
			}
			else{
				event.target.setStyle("textDecoration","none");
//				if(_recordstate == MediaPlayerState.PLAYING)
//				{
//					this.play();
//				}
			} 
		}		
		
		/**
		 * 时间完成事件 
		 * @param event
		 * 
		 */		
		protected function timercomplete(event:TimerEvent):void
		{
			Logger.debug("pass two seconds sound can play!");
			var labelbtn:LabelButton = _currentTarget as LabelButton;
			var reg:RegExp = /###/g;
			var surl:String = ProgramConfig.config.Mp3Host.replace(reg,labelbtn.wordtitle);
			soundplayer.createSound(surl);
		}
		
		/**
		 * 刷新状态事件 
		 * @param event
		 * 
		 */		
		protected function timehandle(event:TimeEvent):void
		{
			if(_isloadDataCompleted == true && isNaN(this.duration) == false && this.duration != 0)
			{
				Logger.debug("this.duration is:{0}",this.duration);
				initData();
				_isloadDataCompleted = false;
			}
			//动态改变当前字幕条状态
			showeverybarstate();
		}
		
		/**
		 * 下一句按钮事件 
		 * @param event
		 * 
		 */		
		protected function nextbtnclick():void
		{
			introWordPanel.visible = false;
			if(curindex+1<wordbarvec.length)
			{
				var wordbar:WordBar = wordbarvec[curindex+1] as WordBar;
				var _curwordvo:ClipsVO = wordbar.curclip;
				var seeknum:Number = _curwordvo.starttime;
				if(this.videoDisplay.isCanseekto(seeknum))
				{
					if(_CurrentMediaPlayerState==MediaPlayerState.PAUSED && _recordState != MediaPlayerState.PAUSED)
					{
						this.play();
					}
					this.seek(seeknum);
					trace("进入下一句");
					Logger.debug("go to next clip");
				}
			}
			//处理到达最后一条 点击下一句
			if(curindex+1==wordbarvec.length)
			{
//				var wordbar1:WordBar = wordbarvec[wordbarvec.length-1] as WordBar;
//				var _curwordvo1:ClipsVO = wordbar1.curclip;
//				var seeknum1:Number = _curwordvo1.endtime;
//				if(this.videoDisplay.isCanseekto(seeknum1))
//				{
//				if(_CurrentMediaPlayerState==MediaPlayerState.PAUSED)
//				{
//					this.play();
//				}
//				this.seek(seeknum1);
//				trace("进入下一句");
//				Logger.debug("go to next clip");
//				}
				this.seek(0);
				this.pause();
			}
		}
		
		/**
		 * 慢放事件 
		 * @param event
		 * 
		 */		
		protected function slowbtnclick():void
		{
			Logger.debug("slow btn is clicked!");
			this.pause();
			var wordbar:WordBar = wordbarvec[curindex] as WordBar;
			var _curwordvo:ClipsVO = wordbar.curclip;
			if(soundplayer != null && _curwordvo != null)
			{
				soundplayer.play(_curwordvo.starttime,_curwordvo.endtime);
			}
		}
		
		/**
		 * 上一句触发 
		 * @param event
		 * 
		 */		
		protected function prevbtnclick():void
		{
			introWordPanel.visible = false
			if(curindex-1>=0)
			{
				_lastindex = _lastindex - 1;
				var wordbar:WordBar = wordbarvec[curindex-1] as WordBar;
				var _curwordvo:ClipsVO = wordbar.curclip;
				var seeknum:Number = _curwordvo.starttime;
				if(_CurrentMediaPlayerState==MediaPlayerState.PAUSED && _recordState != MediaPlayerState.PAUSED)
				{
					this.play();
				}
				this.seek(seeknum);
				trace("进入上一句");
				Logger.debug("go to prev clip");
			}
			//逻辑 当正在播放第一分片时点击上一句
			if(curindex==0)
			{
				_lastindex = -1;
				var wordbar1:WordBar = wordbarvec[0] as WordBar;
				var _curwordvo1:ClipsVO = wordbar1.curclip;
				var seeknum1:Number = _curwordvo1.starttime;
				if(_CurrentMediaPlayerState==MediaPlayerState.PAUSED && _recordState != MediaPlayerState.PAUSED)
				{
					this.play();
				}
				if(currentTime > _curwordvo1.endtime)
				{
					this.seek(seeknum1);
				}else
				{
					this.seek(0);
				}
				trace("进入上一句");
				Logger.debug("go to prev clip");
			}
		}
		
		/**
		 * 初始化音频容器
		 * 
		 */		
		private function initSoundContainer():void
		{
			var ui:UIComponent = new UIComponent();
			//ui.top = 0;
			ui.bottom = 0;
			ui.right = 0;
			ui.left = 0;
			ui.verticalCenter = 0;
			ui.horizontalCenter = 0;
			//添加声音对象
//			soundplayer.x = ui.width/2;
//			soundplayer.y = ui.height/2;
			ui.addChild(soundplayer);
			musiccon.addElement(ui);
		}
		
		/**
		 * 组件添加时调用 
		 * @param partName
		 * @param instance
		 * 
		 */		
		override protected function partAdded(partName:String, instance:Object) : void
		{
			super.partAdded(partName, instance);
			if(instance == scrubBar)
			{
				_isscrubBar = true;
			}
			if(instance == prevbtn)
			{
				prevbtn.addEventListener(MouseEvent.CLICK,prevclick);
			}
			if(instance == nextbtn)
			{
				nextbtn.addEventListener(MouseEvent.CLICK,nextclick);
			}
			if(instance == closebtn)
			{
				closebtn.addEventListener(MouseEvent.CLICK,closebtnclick);
			}
			if(instance == musiccon)
			{
				musiccon.visible = true;
				if(soundplayer != null)
				{
					//初始化音频容器
					initSoundContainer();
				}
			}
			
			if(instance == wordshow)
			{
				if(stage!=null)
				{
					wordshow.y = stage.stageHeight-110;
					stage.addEventListener(Event.RESIZE,stageresizehandle);
				}
			}
			
			if(instance == introWordPanel)
			{
				introWordPanel.visible = false;
				introWordPanel.goon.addEventListener(MouseEvent.CLICK,Goonclickhandle);
				introWordPanel.speak.addEventListener(MouseEvent.ROLL_OVER,speakoverhandle);
			}
			
			if(instance == chinesebtn)
			{
				chinesebtn.addEventListener(MouseEvent.CLICK,chineseclick);
			}
			
			if(instance == englishbtn)
			{
				englishbtn.addEventListener(MouseEvent.CLICK,englishclick);
			}
		}
		
		/**
		 * 舞台变化 
		 * @param event
		 * 
		 */		
		protected function stageresizehandle(event:Event):void
		{
			if(stage!=null && wordshow != null)
			{
				wordshow.y = stage.stageHeight-110;
			}
		}
		
		/**
		 * 英文图标点击 
		 * @param event
		 * 
		 */		
		protected function englishclick(event:MouseEvent):void
		{
			wordCon.visible = true;
			chinese.visible = false;
			showwordbar();
		}
		
		/**
		 * 中文图标点击 
		 * @param event
		 * 
		 */		
		protected function chineseclick(event:MouseEvent):void
		{
			wordCon.visible = false;
			chinese.visible = true;
			showwordbar();
		}
		
		/**
		 * 显示字幕 
		 * 
		 */		
		private function showwordbar():void
		{
			if(wordshow != null)
			{
				var mov:Move = new Move(wordshow);
				mov.yTo = stage.stageHeight-110;
				mov.play();
			}
		}
		
		/**
		 * 隐藏字幕 
		 * 
		 */		
		private function hidewordbar():void
		{
			if(wordshow != null)
			{
				var mov:Move = new Move(wordshow);
				mov.yTo = stage.stageHeight-58;
				mov.play();
			}
		}
		
		/**
		 * 鼠标移到speak音标按钮上播放语音 
		 * @param event
		 * 
		 */		
		protected function speakoverhandle(event:MouseEvent):void
		{
			var reg:RegExp = /###/g;
			var surl:String = ProgramConfig.config.Mp3Host.replace(reg,introWordPanel.wordstr);
			soundplayer.createSound(surl);
		}
		
		/**
		 * 下一句 
		 * @param event
		 * 
		 */		
		protected function nextclick(event:MouseEvent):void
		{
			this.nextbtnclick();
		}
		
		/**
		 * 上一句 
		 * @param event
		 * 
		 */		
		protected function prevclick(event:MouseEvent):void
		{
			this.prevbtnclick();
		}
		
		/**
		 * 关闭按钮 
		 * @param event
		 * 
		 */		
		protected function closebtnclick(event:MouseEvent):void
		{
			hidewordbar();
		}
		
		/**
		 * 销毁 
		 * 
		 */		
		override protected function dispose():void
		{
			super.dispose();
			this.removeEventListener(LoadDataEvent.LOAD_PLAYER_DATA_COMPLETE,loaddatacomplete);
		}
		
	}
}