package com.links123.player.components
{
	import flash.events.Event;
	import flash.events.HTTPStatusEvent;
	import flash.events.IOErrorEvent;
	import flash.events.MouseEvent;
	import flash.net.URLLoader;
	import flash.net.URLLoaderDataFormat;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.utils.ByteArray;
	
	import org.as3wavsound.WavSound;
	import org.bytearray.micrecorder.MicRecorder;
	import org.bytearray.micrecorder.encoder.WaveEncoder;
	import org.bytearray.micrecorder.events.RecordingEvent;
	import org.httpclient.events.HttpStatusEvent;

	public class RecordPlayer
	{
		include "../../../../log/Logging/Logger.as";
		/**
		 * 录音类 
		 */		
		private var recorder:MicRecorder;
		
		/**
		 * 录制状态 
		 */		
		private var RecordStatus:int = 0;//0,还未开始录制  1,正在录制  2,录制完成
		
		private var _recordData:ByteArray;
		
		/**
		 * google 语音识别 api接口 
		 */		
		private var googleapi:String="http://www.google.com/speech-api/v1/recognize?xjerr=1&client=chromium&lang=zh-CN&maxresults=1";
		
		/**
		 * google请求头 
		 */		
		private var googleContentType:String = "audio/speex;rate=16000";
		
		/**
		 * 加载数据类 
		 */		
		private var urloder:URLLoader;
		
		/**
		 * 回放声音播放器 
		 */		
		private var player:WavSound;
		
		/**
		 * 录音播放器 
		 * 
		 */		
		public function RecordPlayer()
		{
			recorder = new MicRecorder(new WaveEncoder());
			recorder.rate = 16;
			recorder.silenceLevel=0;
			recorder.gain=100;
			recorder.addEventListener(Event.COMPLETE,onRecordComplete);
			recorder.addEventListener(RecordingEvent.RECORDING,onRecording);
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
		 * 正在录制触发 
		 * @param event
		 * 
		 */		
		protected function onRecording(event:RecordingEvent):void
		{
			Logger.debug("it already record:"+event.time+"ms");
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
		public function endrecord():void
		{
			if(RecordStatus == 1)
			{
				recorder.stop();
				//保存录音数据
				_recordData = recorder.output;
				sendGoogle();
				RecordStatus = 2;
			}
		}
		
		/**
		 * 录制回放 
		 * 
		 */		
		public function playrecord():void
		{
			Logger.debug("开始回放");
			player = new WavSound(recordData);
			player.play();
		}
		
		/**
		 * 向google发送数据 请求识别 
		 * 
		 */		
		protected function sendGoogle():void
		{
			//请求google
			var request:URLRequest = new URLRequest(googleapi);
			request.contentType = googleContentType; 
			request.method=URLRequestMethod.POST;
			request.data=recordData;
			
			urloder = new URLLoader();
			urloder.dataFormat = URLLoaderDataFormat.BINARY;
			urloder.load(request);
			urloder.addEventListener(Event.COMPLETE,uploadComplete);
			urloder.addEventListener(HTTPStatusEvent.HTTP_STATUS,HTTPStatusheader);
			urloder.addEventListener(IOErrorEvent.IO_ERROR,uploadError);
		}
		
		/**
		 * 返回当前请求google状态 
		 * @param event
		 * 
		 */		
		protected function HTTPStatusheader(event:HTTPStatusEvent):void
		{
			Logger.debug("current google http status:{0}"+event.status);
		}
		
		/**
		 * 当前请求google失败 
		 * @param event
		 * 
		 */		
		protected function uploadError(event:IOErrorEvent):void
		{
			Logger.debug("current google http is failed!");
		}
		
		/**
		 * 上传成功返回取得google返回数据 
		 * @param event
		 * 
		 */		
		protected function uploadComplete(event:Event):void
		{
			Logger.debug("Google server back data:"+event.target.data);
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