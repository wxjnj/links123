package com.links123.player.Mode
{
	/**
	 * 播放器数据模型 
	 * @author Administrator
	 * 
	 */	
	public class PlayerVO
	{	
		include "../../../../log/Logging/Logger.as";
		private var _title:String;
		private var _duration:int;
		private var _url:String;
		private var _mp3url:String;
		private var _clips:Vector.<ClipsVO> = new Vector.<ClipsVO>();
		/**
		 * 初始化 
		 * 
		 */		
		public function PlayerVO()
	    {
			
		}
		
		/**
		 * 初始化数据 
		 * @param obj
		 * 
		 */		
		public function init(obj:Object):void
		{
			if(obj != null)
			{
				_title = obj["title"];
				_duration = obj["duration"];
				_url = obj["url"];
				_mp3url = obj["mp3url"]
				var arr:Array = obj["clips"] as Array;
				for(var i:int=0;i<arr.length;i++)
				{
					var clip:ClipsVO = new ClipsVO(arr[i] as Object);
					clips.push(clip);
				}
			}
			Logger.debug("init PlayerVO Completed!");
		}
		
		/**
		 * 取得视频分片 
		 * @return 
		 * 
		 */		
		public function get clips():Vector.<ClipsVO>
		{
			return _clips;
		}

		/**
		 * 取得mp3播放地址 
		 * @return 
		 * 
		 */		
		public function get mp3url():String
		{
			return _mp3url;
		}

		/**
		 * 取得视频播放地址 
		 * @return 
		 * 
		 */		
		public function get url():String
		{
			return _url;
		}

		/**
		 * 取得总时长 
		 * @return 
		 * 
		 */		
		public function get duration():int
		{
			return _duration;
		}

		/**
		 * 取得标题 
		 * @return 
		 * 
		 */		
		public function get title():String
		{
			return _title;
		}

		private static var instance:PlayerVO = null;
		/**
		 * 取得单例 
		 * @return 
		 * 
		 */		
		public static function getInstance():PlayerVO
		{
			if(instance==null)
			{
				instance = new PlayerVO(); 
			}
			return instance
		}
	}
}