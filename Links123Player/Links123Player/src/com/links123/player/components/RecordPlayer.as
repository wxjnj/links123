package com.links123.player.components
{
	import com.links123.player.Mode.ClipsVO;
	import com.links123.player.Mode.PlayerVO;
	import com.links123.player.event.EventSprite;
	import com.links123.player.event.RecordEvent;
	import com.links123.player.event.RecordingEvent;
	import com.links123.player.mic.MicRecorder;
	import com.links123.player.mic.MicrophoneRecorder;
	
	import flash.events.Event;
	import flash.events.EventDispatcher;
	import flash.events.HTTPStatusEvent;
	import flash.events.IOErrorEvent;
	import flash.events.MouseEvent;
	import flash.events.TimerEvent;
	import flash.media.Microphone;
	import flash.media.SoundCodec;
	import flash.net.URLLoader;
	import flash.net.URLLoaderDataFormat;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.net.URLVariables;
	import flash.system.Security;
	import flash.utils.ByteArray;
	import flash.utils.Timer;
	
	import mx.controls.Alert;
	import mx.utils.Base64Encoder;

	public class RecordPlayer extends EventDispatcher
	{
		include "../../../../log/Logging/Logger.as";
//		/**
//		 * 录音类 
//		 */		
//		public var recorder:MicRecorder;
		
		/**
		 * 录音类 
		 */		
		public var recorder:MicrophoneRecorder;
		
		/**
		 * 录制状态 
		 */		
		private var RecordStatus:int = 0;//0,还未开始录制  1,正在录制  2,录制完成
		
		private var _recordData:ByteArray;
		
		/**
		 * 加载数据类 
		 */		
		private var urloder:URLLoader;
		
		/**
		 * 回放声音播放器 
		 */		
		//private var player:WavSound;
		
		private var mic:Microphone;
		
		/**
		 * 超时 
		 */		
		public static var OVERTIME:String = "overtime";
		
		/**
		 * 无话筒
		 */		
		public static var NOMIC:String = "no microphone";
		
		/**
		 * 录音过短 
		 */		
		public static var SHORT:String = "short";
		
		/**
		 * 录音播放器 
		 * 
		 */		
		public function RecordPlayer()
		{
			mic = Microphone.getMicrophone();
			if( mic != null )
			{
				recorder = new MicrophoneRecorder();
				recorder.microphone = mic;
				if(ProgramConfig.config.RATE != 0)
				{
					//recorder.rate = ProgramConfig.config.RATE;
					recorder.rate();
				}
				if(ProgramConfig.config.CODEC == 1)
				{
					recorder.codec = SoundCodec.NELLYMOSER;
				}else if(ProgramConfig.config.CODEC == 2)
				{
					recorder.codec = SoundCodec.SPEEX;
				}
				recorder.addEventListener(Event.COMPLETE,onRecordComplete);
				recorder.addEventListener(RecordingEvent.RECORDING,onRecording);
			}else
			{
				alertNoMicMess();
				Logger.debug("NOT find the Microphone divice!");
			}
		}
		
		/**
		 * 弹出警告提示框 
		 * 
		 */		
		private function alertNoMicMess():void
		{
			dispatchEvent(new Event(RecordPlayer.NOMIC));
		}
		
		/**
		 * 录制完成事件 
		 * @param event
		 * 
		 */		
		protected function onRecordComplete(event:Event):void
		{
			Logger.debug("record completed!");
		}
		
		/**
		 * 录制时间 
		 */		
		private var recordtime:Number = 0;
		
		/**
		 * 正在录制触发 
		 * @param event
		 * 
		 */		
		protected function onRecording(event:RecordingEvent):void
		{
			Logger.debug("it already record:{0},ms",event.time);
			//trace(event.time);
			recordtime = event.time;
			if(event.time>30)
			{
				Logger.debug("超过30s停止录制.....");
				dispatchEvent(new Event(RecordPlayer.OVERTIME));
			}
		}
		
		/**
		 * 开始录音 
		 * 
		 */		
		public function startrecord():void
		{
			Logger.debug("start recording!");
			RecordStatus = 1;
			recorder.record();
		}		
		
		/**
		 * 停止录制 
		 * 
		 */		
		public function endrecord(curvo:ClipsVO):void
		{	
			if(RecordStatus == 1)
			{	
				recorder.stop();
				//保存录音数据
				_recordData = recorder.output;
				//sendGoogle();
				if(recordtime > 1)
				{
					Logger.debug("录音时长大于1发送php服务器");
					sendDataToPhp(curvo);
				}else
				{
					Logger.debug("当前录音过短(小于等于1秒)");
					dispatchEvent(new Event(RecordPlayer.SHORT));
				}
				RecordStatus = 2;
				//player = new WavSound(recordData);
			}
			
		}
		
		/**
		 * 录制回放 播放与暂停
		 * 
		 */		
		public function playrecord():void
		{
			Logger.debug("start repeat play!");
			try{
				if(recorder != null)
				{
					recorder.playBack();
				}
			}catch(e:Error)
			{
				Logger.debug("play record wav filed:{0}",e.errorID);
			}
			
		}

		/**
		 * 停止录制 
		 * 
		 */		
		public function stoprecord():void
		{
			Logger.debug("stop repeat play!");
			try{
				if(recorder != null)
				{
					//recorder.stopplayBack();
					recorder.stop();
				}
			}catch(e:Error)
			{
				Logger.debug("play record wav filed:{0}",e.errorID);
			}
			
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
		 * 往php服务端发送数据 
		 * 
		 */		
		private function sendDataToPhp(curvo:ClipsVO):void
		{
			Logger.debug("enter php http request:{0}",ProgramConfig.config.SendDataHost);
			var request:URLRequest = new URLRequest(ProgramConfig.config.SendDataHost);//音频数据传输接口
			request.method=URLRequestMethod.POST;
			var baseStr:Base64Encoder = new Base64Encoder()
			baseStr.encodeBytes(recordData);
			var vr:URLVariables = new URLVariables();
			
			vr.mp3data = baseStr;
			vr.questionid = PlayerVO.getInstance().questionid;
			vr.clipid=curvo.sid;
			request.data = vr;
	
			urloder = new URLLoader();
			urloder.load(request);
			urloder.addEventListener(Event.COMPLETE,sendUploadComplete);
			urloder.addEventListener(HTTPStatusEvent.HTTP_STATUS,sendHTTPStatusheader);
			urloder.addEventListener(IOErrorEvent.IO_ERROR,sendUploadError);
		}
		
		/**
		 * 当前请求php失败 
		 * @param event
		 * 
		 */		
		protected function sendUploadError(event:IOErrorEvent):void
		{
			Logger.debug("current php http is failed!");
			var obj:Object = makeparam("phpHttpError",0);
			EventSprite.getInstance().dispatchEvent(new RecordEvent(RecordEvent.GETSCORE_COMPLETED,obj));
		}
		
		/**
		 * 返回当前请求php状态 
		 * @param event
		 * 
		 */	
		protected function sendHTTPStatusheader(event:HTTPStatusEvent):void
		{
			Logger.debug("current php http status:{0}",event.status);
		}
		
		/**
		 * 服务器返回数据 
		 * @param event
		 * 
		 */		
		protected function sendUploadComplete(event:Event):void
		{
			Logger.debug("php server back data:{0}",event.target.data);
			var num:int = int(event.target.data);
			var obj:Object = makeparam("OK",num);
			EventSprite.getInstance().dispatchEvent(new RecordEvent(RecordEvent.GETSCORE_COMPLETED,obj));
		}
		
		/**
		 * 录音数据 
		 */
		public function get recordData():ByteArray
		{
			return _recordData;
		}
		
	}
}