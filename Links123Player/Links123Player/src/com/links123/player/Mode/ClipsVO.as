package com.links123.player.Mode
{
	/**
	 * 分片数据模型 
	 * @author Administrator
	 * 
	 */	
	public class ClipsVO
	{
	   private var _cliptitle:String;
	   private var _starttime:Number;
	   private var _endtime:Number;
	   private var _english:String;
	   private var _chinese:String;
	   private var _wordclips:Vector.<WordClipsVO> = new Vector.<WordClipsVO>();
		public function ClipsVO(obj:Object)
		{
			if(obj != null)
			{
				_cliptitle = obj["title"];
				_starttime = obj["starttime"];
				_endtime = obj["endtime"];
				_english = obj["english"];
				_chinese = obj["chinese"];
				var arr:Array = obj["wordclips"] as Array;
				for(var i:int=0;i<arr.length;i++)
				{
					var clip:WordClipsVO = new WordClipsVO(arr[i] as Object);
					_wordclips.push(clip);
				}
			}
		}
		
	   /**
	    * 单词 
	    * @return 
	    * 
	    */		
	   public function get wordclips():Vector.<WordClipsVO>
	   {
		   return _wordclips;
	   }

	   /**
	    * 取得汉语翻译 
	    * @return 
	    * 
	    */		
	   public function get chinese():String
	   {
		   return _chinese;
	   }

	   /**
	    * 取得英语 
	    * @return 
	    * 
	    */	   
	   public function get english():String
	   {
		   return _english;
	   }

	   /**
	    * 结束时间 
	    * @return 
	    * 
	    */	   
	   public function get endtime():Number
	   {
		   return _endtime;
	   }

	   /**
	    * 开始时间 
	    * @return 
	    * 
	    */	   
	   public function get starttime():Number
	   {
		   return _starttime;
	   }

	   /**
	    * 标题 
	    * @return 
	    * 
	    */	   
	   public function get cliptitle():String
	   {
		   return _cliptitle;
	   }

//	   private static var instance:ClipsVO = null;
//		/**
//		 * 取得单例 
//		 * @return 
//		 * 
//		 */		
//		public static function getInstance():ClipsVO
//		{
//			if(instance==null)
//			{
//				instance = new ClipsVO(); 
//			}
//			return instance
//		}
	}
}