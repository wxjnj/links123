package com.links123.player.Mode
{
	/**
	 * 音标数据模型 
	 * @author Administrator
	 * 
	 */	
	public class PhyClipsVO
	{
		private var _phsy:String;
		private var _phsyurl:String;
		public function PhyClipsVO(obj:Object)
		{
			if(obj != null)
			{
				_phsy = obj["phsy"];
				_phsyurl = obj["phsyurl"];
			}
		}

		public function get phsyurl():String
		{
			return _phsyurl;
		}

		public function get phsy():String
		{
			return _phsy;
		}
	}
}