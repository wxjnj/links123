package com.links123.player.components
{
	import com.links123.player.Mode.ClipsVO;
	import com.links123.player.Mode.PlayerVO;
	import com.links123.player.Mode.WordClipsVO;
	import com.links123.player.event.EventSprite;
	import com.links123.player.event.LoadDataEvent;
	import com.links123.player.skins.WordBarSkin;
	
	import flash.display.Sprite;
	import flash.events.DataEvent;
	import flash.events.Event;
	import flash.events.HTTPStatusEvent;
	import flash.events.IOErrorEvent;
	import flash.events.MouseEvent;
	import flash.events.TimerEvent;
	import flash.net.URLLoader;
	import flash.net.URLLoaderDataFormat;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.utils.Timer;
	
	import mx.core.UIComponent;
	
	import org.bytearray.micrecorder.MicRecorder;
	import org.bytearray.micrecorder.encoder.WaveEncoder;
	import org.bytearray.micrecorder.events.RecordingEvent;
	import org.osmf.events.MediaPlayerStateChangeEvent;
	import org.osmf.events.TimeEvent;
	
	import spark.components.Application;
	
	public class Player extends VideoPlayer
	{
		include "../../../../log/Logging/Logger.as";
		
		/**
		 * 当前判断的字幕条 
		 */		
		private var _curWordBar:WordBar = null;
		
		/**
		 * 是否已出现scrubBar参数 
		 */		
		private var _isscrubBar:Boolean = false;
		
		/**
		 * 当前正在播放的字幕条. 
		 */		
		private var _currentWord:WordBar;
		
		/**
		 * 字幕条数组 
		 */		
		private var wordbarvec:Vector.<WordBar> = new Vector.<WordBar>();
		
		/**
		 * 录音类 
		 */		
		private var recorder:MicRecorder;
		
		/**
		 * 上传google类 
		 */		
		private var urloder:URLLoader;
		
		/**
		 * 声音播放类 
		 */		
		private var soundplayer:SoundPlayer = new SoundPlayer();
		
		/**
		 * 录音播放器 
		 */		
		private var recordplayer:RecordPlayer = new RecordPlayer();
		
		/**
		 * 当前字幕条在字幕数组中的位置 
		 */		
		private var curindex:int = -1;
		
		public function Player()
		{
			super();
			Logger.debug("addEventListener DataEvent.UPLOAD_COMPLETE_DATA event!");
			this.addEventListener(LoadDataEvent.LOAD_PLAYER_DATA_COMPLETE,loaddatacomplete);
			this.addEventListener(MediaPlayerStateChangeEvent.MEDIA_PLAYER_STATE_CHANGE,playerstatechange);
			this.addEventListener(TimeEvent.CURRENT_TIME_CHANGE,timehandle);
			this.addEventListener(TimeEvent.COMPLETE,completehandle);
		}
		
		/**
		 * 加载数据完成事件 
		 * @param event
		 * 
		 */		
		protected function loaddatacomplete(event:LoadDataEvent):void
		{
			Logger.debug("starting init data...");
			initData();
		}
		
		/**
		 * 播放器状态改变时间 
		 * @param event
		 * 
		 */		
		protected function playerstatechange(event:MediaPlayerStateChangeEvent):void
		{
			Logger.debug("MediaPlayerStateChangeEvent:{0}",event.state);
			if(event.state == "playing")
			{
				stage.frameRate = 1;
			}
			if(event.state == "paused")
			{
				stage.frameRate = 30;
			}
		}
		
		/**
		 * 播放器播放完成事件 
		 * @param event
		 * 
		 */		
		protected function completehandle(event:TimeEvent):void
		{
			Logger.debug("player play completed");
			dispose();
		}
		
		/**
		 * 初始化数据  显示字幕条
		 * 
		 */		
		private function initData():void
		{
			if(scrubBar.clipcon != null && _isscrubBar == true)
			{
				var per:Number = scrubBar.width/PlayerVO.getInstance().duration;//每一秒的距离
				var clips:Vector.<ClipsVO> = PlayerVO.getInstance().clips;//取得数据数组
				for each(var clip:ClipsVO in clips)
				{
					var wordbar:WordBar = new WordBar(clip);
					var n:Number = clip.endtime-clip.starttime;
					wordbar.width = n*per;
					wordbar.height=10;
					wordbar.x = clip.starttime*per;
					wordbar.y = 0;
					wordbarvec.push(wordbar);
					wordbar.addEventListener(MouseEvent.CLICK,wordbarclickhandle);
					scrubBar.clipcon.addElement(wordbar);
				}
				Logger.debug("scrubBar is initcompleted!");
			}else
			{
				Logger.debug("scrubBar is not initcompleted!");
			}
			
			//传入soundplayer播放数据 初始化声音播放器
			var url:String = PlayerVO.getInstance().mp3url;
			soundplayer.createSound(url,false,true);
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
				if(time>=_curwordvo.starttime && time<_curwordvo.endtime)
				{
					realw = wordbar;
					curindex = i;
					Logger.debug("current clip is:{0}"+i);
					if(wordshow != null && wordCon!=null && realw!= null)
					{
						Logger.debug("正在填充字幕!");
						wordshow.visible = true;
						var _wordvo:ClipsVO = realw.curclip;
						//填充字幕
						filllabelword(_wordvo);
					}
					
				}
			}
			return realw;
		}
		
		/**
		 * 填充字幕 
		 * @param wordvo
		 * 
		 */		
		private function filllabelword(wordvo:ClipsVO):void
		{
			wordCon.removeAllElements();
			var wordvect:Vector.<WordClipsVO> = wordvo.wordclips;
			for(var i:int = 0;i<wordvect.length;i++)
			{
				var wordclip:WordClipsVO = wordvect[i] as WordClipsVO;
				var labelbtn:LabelButton = new LabelButton(wordclip);
				labelbtn.buttonMode = true;
				labelbtn.addEventListener(MouseEvent.ROLL_OVER,rollover);
				labelbtn.addEventListener(MouseEvent.ROLL_OUT,rollover);
				labelbtn.addEventListener(MouseEvent.CLICK,clickhandle);
				wordCon.addElement(labelbtn);
			}
		}
		
		/**
		 * labelbtn点击事件
		 * @param event
		 * 
		 */		
		protected function clickhandle(event:MouseEvent):void
		{
			//点击显示文字解释面板
			var labelbtn:LabelButton = event.target as LabelButton;
			fillIntroWordPanel(labelbtn);
		}
		
		/**
		 * 填充文字说明面板 
		 * @param labelbtn
		 * 
		 */		
		private function fillIntroWordPanel(labelbtn:LabelButton):void
		{
			var wordclipVo:WordClipsVO = labelbtn.wordcli;
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
			this.play();
		}
		
		/**
		 * 鼠标移上文字事件 添加下划线
		 * @param event
		 * 
		 */		
		protected function rollover(event:MouseEvent):void
		{
			if(event.target.getStyle("textDecoration")=="none")
			{
				event.target.setStyle("textDecoration","underline");
				var labelbtn:LabelButton = event.target as LabelButton;
				trace("鼠标移上去播放声音:"+labelbtn.wordcli.speekurl);
			}
			else{
				event.target.setStyle("textDecoration","none");
			} 
		}		
		
		/**
		 * 刷新状态事件 
		 * @param event
		 * 
		 */		
		protected function timehandle(event:TimeEvent):void
		{
			//动态改变当前字幕条状态
			showeverybarstate();
		}
		
		/**
		 * 下一句按钮事件 
		 * @param event
		 * 
		 */		
		protected function nextbtnclick(event:MouseEvent):void
		{
			if(curindex+1<wordbarvec.length)
			{
				var wordbar:WordBar = wordbarvec[curindex+1] as WordBar;
				var _curwordvo:ClipsVO = wordbar.curclip;
				var seeknum:Number = _curwordvo.starttime;
				this.seek(seeknum);
				trace("进入下一句");
			}
		}
		
		/**
		 * 慢放事件 
		 * @param event
		 * 
		 */		
		protected function slowbtnclick(event:MouseEvent):void
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
		protected function prevbtnclick(event:MouseEvent):void
		{
			if(curindex-1>=0)
			{
				var wordbar:WordBar = wordbarvec[curindex-1] as WordBar;
				var _curwordvo:ClipsVO = wordbar.curclip;
				var seeknum:Number = _curwordvo.starttime;
				this.seek(seeknum);
				trace("进入上一句");
			}
		}
		
		/**
		 * 初始化音频容器
		 * 
		 */		
		private function initSoundContainer():void
		{
			var ui:UIComponent = new UIComponent();
			ui.top = 0;
			ui.bottom = 0;
			ui.right = 0;
			ui.left = 0;
			//添加声音对象
			soundplayer.x = ui.width/2;
			soundplayer.y = ui.height/2;
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
				prevbtn.addEventListener(MouseEvent.CLICK,prevbtnclick);
			}
			if(instance == slowbtn)
			{
				slowbtn.addEventListener(MouseEvent.CLICK,slowbtnclick);
			}
			if(instance == nextbtn)
			{
				nextbtn.addEventListener(MouseEvent.CLICK,nextbtnclick);
			}
			if(instance == wordCon)
			{
				
			}
			if(instance == musiccon)
			{
			    if(soundplayer != null)
				{
					//初始化音频容器
					initSoundContainer();
				}
			}
			
			if(instance == recordbtn)
			{
				recordbtn.addEventListener(MouseEvent.CLICK,startrecordhandle);
			}
			
			if(instance == endrecordbtn)
			{
				endrecordbtn.addEventListener(MouseEvent.CLICK,endrecordhandle);
			}
			
			if(instance == playrecordbtn)
			{
				playrecordbtn.addEventListener(MouseEvent.CLICK,playrecordbtnhandle);
			}
			
			if(instance == introWordPanel)
			{
				introWordPanel.goon.addEventListener(MouseEvent.CLICK,Goonclickhandle);
			}
			
		}
		
		/**
		 * 录音回放 
		 * @param event
		 * 
		 */		
		protected function playrecordbtnhandle(event:MouseEvent):void
		{
			recordplayer.playrecord();
		}
		
		/**
		 * 结束录制 
		 * @param event
		 * 
		 */		
		protected function endrecordhandle(event:MouseEvent):void
		{
			recordplayer.endrecord();
		}
		
		/**
		 * 开始录制 
		 * @param event
		 * 
		 */		
		protected function startrecordhandle(event:MouseEvent):void
		{
			recordplayer.startrecord();
		}		
		
		/**
		 * 消除存在变量以及事件 
		 * 
		 */		
		private function dispose():void
		{
			this.removeEventListener(LoadDataEvent.LOAD_PLAYER_DATA_COMPLETE,loaddatacomplete);
			this.removeEventListener(MediaPlayerStateChangeEvent.MEDIA_PLAYER_STATE_CHANGE,playerstatechange);
			this.removeEventListener(TimeEvent.COMPLETE,completehandle);
		}
		
		override protected function partRemoved(partName:String, instance:Object) : void
		{
			super.partRemoved(partName, instance);
		}
		
		override protected function getCurrentSkinState():String
		{
			return super.getCurrentSkinState();
		} 
		
	}
}