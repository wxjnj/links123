package com.links123.player.Mode
{
	/**
	 * 单词数据模型 
	 * @author Administrator
	 * 
	 */	
	public class WordClipsVO
	{
	   
		private var _word:String;
		private var _speekurl:String;
		private var _intro:String;
		private var _type:String;
		private var _example:String;
		
		private var _phsyclips:Vector.<PhyClipsVO> = new Vector.<PhyClipsVO>();
		public function WordClipsVO(obj:Object)
		{
			if(obj != null)
			{
				_word = obj["word"];
				_speekurl = obj["speekurl"];
				_intro = obj["intro"];
				_type = obj["type"];
				_example = obj["example"];
				var arr:Array = obj["phsyclips"] as Array;
				for(var i:int=0;i<arr.length;i++)
				{
					var clip:PhyClipsVO = new PhyClipsVO(arr[i] as Object);
					_phsyclips.push(clip);
				}
			}
		}
			
		public function get phsyclips():Vector.<PhyClipsVO>
		{
			return _phsyclips;
		}

		public function get example():String
		{
			return _example;
		}

		public function get type():String
		{
			return _type;
		}

		public function get intro():String
		{
			return _intro;
		}

		public function get speekurl():String
		{
			return _speekurl;
		}

		public function get word():String
		{
			return _word;
		}
	}
}