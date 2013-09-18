package com.links123.player.components
{
	import com.links123.player.event.EventSprite;
	import com.links123.player.event.RecordEvent;
	import com.links123.player.mic.MicRecorder;
	import com.links123.player.mic.MicrophoneRecorder;
	import com.links123.player.utils.SecuritySettings;
	
	import flash.display.StageAlign;
	import flash.display.StageScaleMode;
	import flash.events.ActivityEvent;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.events.StatusEvent;
	import flash.media.Microphone;
	import flash.system.Security;
	import flash.system.SecurityPanel;
	
	import mx.controls.Alert;
	import mx.events.CloseEvent;
	
	import org.osmf.events.MediaPlayerStateChangeEvent;

	/**
	 * "说"播放器 
	 * @author Administrator
	 * 
	 */	
	public class SpeakPlayer extends LookPlayer
	{
		/**
		 * 录音类 
		 */		
		private var recorder:MicRecorder;
		
		private var _currentRecodState:int = 0;//0:还未开始录音 1:开始录音 2结束录音 3录音对比
		
		/**
		 * 录音播放器 
		 */		
		private var recordplayer:RecordPlayer = new RecordPlayer();
		
		//默认麦克风为不允许
		private var _currentMicState:String = "Microphone.Muted";
		
		public function SpeakPlayer()
		{
			super();
			this.addEventListener(Event.ADDED_TO_STAGE,addstagehandle);
		}
		
		private var _isfocus:Boolean = false;
		
		/**
		 * 话筒 
		 */		
		private var mic:Microphone;
		
		protected function addstagehandle(event:Event):void
		{
			if(stage != null)
			{
				stage.align=StageAlign.TOP_LEFT;
				stage.scaleMode=StageScaleMode.NO_SCALE; 
				//测试是否有话筒
				addstage();
			}
		}
		
		private function addstage(event:CloseEvent=null):void
		{
			if(recordplayer.recorder.microphone.muted == false)
			{
				_currentMicState = "Microphone.Unmuted";
			}
			if(recordplayer.recorder.microphone != null && _isfocus == false && recordplayer.recorder.microphone.muted == true)
			{
				stage.focus = this;
				recordplayer.recorder.microphone.addEventListener(StatusEvent.STATUS,microphonehandle);
				//显示设置面板
				SecuritySettings.show(SecurityPanel.DEFAULT,onClosed);
				_isfocus = true;
			}
			//注册话筒是否存在事件
			recordplayer.addEventListener(RecordPlayer.NOMIC,nomichandle);
		}
		
		/**
		 * 没有话筒触发 
		 * @param event
		 * 
		 */		
		protected function nomichandle(event:Event):void
		{
			Alert.okLabel="确定";
			Alert.show("当前不存在话筒,请插入话筒后,刷新页面.","提示",4,this);
		}
		
		private var pausesign:Boolean = false;
		
		/**
		 * 保存当前mic的状态 
		 */		
		private var currentMistate:String;
		
		override protected function playerstatechange(event:MediaPlayerStateChangeEvent):void
		{
			super.playerstatechange(event);
			//if((event.state == "playing" && pausesign == false && recordplayer.recorder.microphone.muted == true)||mic == null)
			if(event.state == "playing" && pausesign == false && recordplayer.recorder.microphone.muted == true)
			{
				this.pause();
				pausesign = true;
			}
		}
		
		/**
		 * 面板关闭事件 
		 * 
		 */		
		private function onClosed():void
		{
			trace(_currentMicState);
			MicStateHandle(_currentMicState);
		}
		
		/**
		 * 取得麦克风状态 
		 * @param event
		 * 
		 */		
		protected function microphonehandle(event:StatusEvent):void
		{
			Logger.debug("StatusEvent code Info:{0}",event.code);
			_currentMicState = event.code;
		}
		
		/**
		 * 是否开始录制标识 
		 */		
		private var startRecordLog:Boolean = false;
		
		/**
		 * 麦克风状态改变 
		 * 
		 */		
		private function MicStateHandle(state:String):void
		{
			if(state == "Microphone.Unmuted")
			{
				if(startRecordLog == true)
				{
					recordplayer.startrecord();	
					currentRecodState = 2;//执行开启话筒
					soundplayer.isShowMovie = true;
					startRecordLog = false;
					_startrecord = true;
				}else
				{
					this.play();
				}
				Logger.debug("allow use mic!");
				
			}else if(state == "Microphone.Muted")
			{
				if(startRecordLog == true)
				{
					playPauseButton.alpha = 1;
				}
				Logger.debug("not allow use mic!");
				alertMicMess();
			}
		}
		
		/**
		 * 弹出警告提示框 
		 * 
		 */		
		private function alertMicMess():void
		{
			Alert.yesLabel="再试一次";
			Alert.noLabel="忽略";
			Alert.show("麦克风还没有被激活.\n为了继续进行,请再尝试一次.","提示",3,this,deleteCallBack);
		}
		
		/**
		 * 关闭提示 
		 * @param event
		 * 
		 */		
		private function deleteCallBack(event:CloseEvent):void
		{
			if(event.detail == Alert.YES)
			{
				stage.focus = this;
				SecuritySettings.show(SecurityPanel.DEFAULT,onClosed);
			}else if(event.detail == Alert.NO)
			{
				Logger.debug("go on play!");
				//进入播放界面
				currentMistate = "Microphone.Muted";
				this.play();
				currentRecodState = 0;
				scorepanel.visible = false;
			}
		}
		
		/**
		 * 当前录制状态 
		 */
		public function get currentRecodState():int
		{
			return _currentRecodState;
		}

		/**
		 * @private
		 */
		public function set currentRecodState(value:int):void
		{
			switch(value)
			{
				case 0:
					recordbtn.visible = false;
					endrecordbtn.visible = false;
					pologizebtn.visible = false;
					goonplaybtn.visible = false;
					break;
				case 1:
					recordbtn.visible = true;
					goonplaybtn.visible = true;
					endrecordbtn.visible = false;
					pologizebtn.visible = false;
					break;
				case 2:
					recordbtn.visible = false;
					endrecordbtn.visible = true;
					pologizebtn.visible = false;
					goonplaybtn.visible = false;
					break;
				case 3:
					recordbtn.visible = false;
					endrecordbtn.visible = false;
					pologizebtn.visible = true;
					goonplaybtn.visible = false;
					break;
			}
			_currentRecodState = value;
		}
		
		override protected function playPauseButton_clickHandler(event:MouseEvent):void
		{
			if(_startrecord == false)
			{
				super.playPauseButton_clickHandler(event);
				if(playing == true)
				{
					currentRecodState = 0;
					scorepanel.visible = false;
					playPauseButton.alpha = 1;
					soundplayer.isShowMovie = false;
				}
			}
		}
		
		override protected function wordbarclickhandle(event:MouseEvent):void
		{
			super.wordbarclickhandle(event);
			cleanbtnerror();
		}
		
		override protected function nextbtnclick():void
		{
			super.nextbtnclick();
			cleanbtnerror();
		}
		
		override protected function prevbtnclick():void
		{
			super.prevbtnclick();
			cleanbtnerror();
		}
		
		/**
		 * 开始拖动滚动条 
		 * @param event
		 * 
		 */		
		override protected function scrubBar_changeStartHandler(event:Event):void
		{
			super.scrubBar_changeStartHandler(event);
			cleanbtnerror();
		}
		
		
		
		/**
		 * 清除一些按钮触发 画面错乱 
		 * 
		 */		
		private function cleanbtnerror():void
		{
			this.play();
			currentRecodState = 0;
			scorepanel.visible = false;
			playPauseButton.alpha = 1;
			soundplayer.isShowMovie = false;
		}
		
		/**
		 * 组件添加 
		 * @param partName
		 * @param instance
		 * 
		 */		
		override protected function partAdded(partName:String, instance:Object):void
		{
			super.partAdded(partName, instance);
			if(instance == recordbtn)
			{
				recordbtn.addEventListener(MouseEvent.CLICK,startrecordhandle);
			}
			
			if(instance == playPauseButton)
			{
				playPauseButton.visible = false;
			}
			
			if(instance == endrecordbtn)
			{
				endrecordbtn.addEventListener(MouseEvent.CLICK,endrecordhandle);
			}
		
			if(instance == goonplaybtn)
			{
				goonplaybtn.addEventListener(MouseEvent.CLICK,goonplaybtnhandle);
			}
			
			if(instance == scrubBar)
			{
				scrubBar.track.enabled = false;
			}
		}
		
		/**
		 * 继续播放 
		 * @param event
		 * 
		 */		
		protected function goonplaybtnhandle(event:MouseEvent):void
		{
			Logger.debug("go on play!");
			this.play();
			currentRecodState = 0;
			scorepanel.visible = false;
			playPauseButton.alpha = 1;
		}
		
		override protected function takeSpeakControl():void
		{
			super.takeSpeakControl();
			this.pause();//暂停
			currentRecodState = 1;//开启话筒
			playPauseButton.alpha = 0;
		}
		
		/**
		 * 结束录制 
		 * @param event
		 * 
		 */		
		protected function endrecordhandle(event:MouseEvent):void
		{
			EventSprite.getInstance().addEventListener(RecordEvent.GETSCORE_COMPLETED,getScore);
			recordplayer.endrecord(getcurClipsVO());
			soundplayer.isShowMovie = false;
			currentRecodState = 3;
			recordplayer.recorder.removeEventListener(MicRecorder.QUIET,quiethandle);
			recordplayer.recorder.removeEventListener(MicRecorder.QUIET,quiethandle);
		}
		
		
		/**
		 * 取得分数 
		 * @param event
		 * 
		 */		
		protected function getScore(event:RecordEvent):void
		{
			var obj:Object = event.data;
			Logger.debug("state is:{0},finally score is:{1}",obj.state,obj.score);
			scorepanel.state = obj.state;
			scorepanel.sco = String(obj.score);
			scorepanel.visible = true;
			scorepanel.bitrecord.addEventListener(MouseEvent.CLICK,bitrecordClickhandle);
			scorepanel.repeatrecord.addEventListener(MouseEvent.CLICK,repeatrecordClickhandle);
			scorepanel.goon.addEventListener(MouseEvent.CLICK,goonClickhandle);
			currentRecodState = 0;
		}
		
		/**
		 * 继续 
		 * @param event
		 * 
		 */		
		protected function goonClickhandle(event:MouseEvent):void
		{
			Logger.debug("go on practice!");
			this.play();
			currentRecodState = 0;
			scorepanel.visible = false;
			playPauseButton.alpha = 1;
			_startrecord = false;
			soundplayer.isShowMovie = false;
		}
		
		/**
		 * 重新跟读 
		 * @param event
		 * 
		 */		
		protected function repeatrecordClickhandle(event:MouseEvent):void
		{
			Logger.debug("enter repeat speekmode!");
			currentRecodState = 1;
			scorepanel.visible = false;
			soundplayer.isShowMovie = false;
			_startrecord = false;
		}
		
		private var _isstartrecord:Boolean = false;
		/**
		 * 进入录音对比模块 
		 * @param event
		 * 
		 */		
		protected function bitrecordClickhandle(event:MouseEvent):void
		{
			Logger.debug("enter bat mode!");
			if(_isstartrecord == false)
			{
				Logger.debug("play record");
				recordplayer.playrecord();
	//			scorepanel.visible = false;
				soundplayer.isShowMovie = true;
				_isstartrecord = true;
			}else if(_isstartrecord == true)
			{
				Logger.debug("stop record");
				recordplayer.stoprecord();
	//			scorepanel.visible = false;
				soundplayer.isShowMovie = false;
				_isstartrecord = false;
			}
			
		}
		
		private var _startrecord:Boolean = false;
		
		/**
		 * 开始录制 
		 * @param event
		 * 
		 */		
		protected function startrecordhandle(event:MouseEvent):void
		{
			if(currentMistate == "Microphone.Muted")
			{
				stage.focus = this;
				SecuritySettings.show(SecurityPanel.DEFAULT,onClosed);
				startRecordLog = true;
			}else
			{
				recordplayer.startrecord();	
				currentRecodState = 2;//执行开启话筒
				soundplayer.isShowMovie = true;
				_startrecord = true;
				recordplayer.addEventListener(RecordPlayer.OVERTIME,overTimerHandle);
				recordplayer.addEventListener(RecordPlayer.SHORT,shorthandle);
				recordplayer.recorder.addEventListener(MicRecorder.QUIET,quiethandle);
				recordplayer.recorder.addEventListener(MicrophoneRecorder.SOUND_COMPLETE,soundCompletehandle);
			}
		}	
		
		/**
		 * 回放播放完成事件 
		 * @param event
		 * 
		 */		
		protected function soundCompletehandle(event:Event):void
		{
			Logger.debug("录音播放完成");
			_isstartrecord = false;
		}
		
		/**
		 * 录音过短 
		 * @param event
		 * 
		 */		
		protected function shorthandle(event:Event):void
		{
			Alert.okLabel="确定";
			Alert.show("录音不应该小于1s","提示",4,this);
			var obj:Object = makeparam("timeshort",0);
			EventSprite.getInstance().dispatchEvent(new RecordEvent(RecordEvent.GETSCORE_COMPLETED,obj));
		}
		
		/**
		 * 安静5s后会退出 
		 * @param event
		 * 
		 */		
		protected function quiethandle(event:Event):void
		{
			endrecordbtn.dispatchEvent(new MouseEvent(MouseEvent.CLICK));
			Logger.debug("已经安静了5s了,请说话");
			Alert.okLabel="确定";
			Alert.show("话筒5秒内没有接收到声音,请大声朗读.","提示",4,this);
			var obj:Object = makeparam("quiet",0);
			EventSprite.getInstance().dispatchEvent(new RecordEvent(RecordEvent.GETSCORE_COMPLETED,obj));
		}
		
		/**
		 * 制造参数 两个字段score state
		 * @return 
		 * 
		 */		
		private function makeparam(state:String,score:int):Object
		{
			var obj:Object = new Object();
			obj.score = score;
			obj.state = state;
			return obj;
		}
		
		/**
		 * 录制超时事件
		 * @param event
		 * 
		 */		
		protected function overTimerHandle(event:Event):void
		{
			endrecordbtn.dispatchEvent(new MouseEvent(MouseEvent.CLICK));
			Logger.debug("录制时长已超时30s");
		}
	}
}