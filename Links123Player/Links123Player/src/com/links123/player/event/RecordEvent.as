package com.links123.player.event
{
	import flash.events.Event;
	
	/**
	 * 录音事件 
	 * @author Administrator
	 * 
	 */	
	public class RecordEvent extends Event
	{
		/**
		 * 状态改变事件 
		 */		
		public static var GETSCORE_COMPLETED:String="get score complete";
		
		private var _data:Object = null;
		
		public function RecordEvent(type:String,dat:Object,bubbles:Boolean=false, cancelable:Boolean=false)
		{
			super(type, bubbles, cancelable);
			_data = dat;
		}
		
		public function get data():Object
		{
			return _data;
		}
	}
}