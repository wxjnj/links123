package com.links123.player.event
{
	import flash.events.Event;

	public class WordBarStateChangeEvent extends Event
	{
		/**
		 * 状态改变事件 
		 */		
		public static var WORDBAR_STATE_CHANGE:String="state change";
		
		private var _data:Object;
		public function WordBarStateChangeEvent(type:String,obj:Object=null, bubbles:Boolean=false, cancelable:Boolean=false)
		{
			_data = obj;
			super(type, bubbles, cancelable);
		}

		public function get data():Object
		{
			return _data;
		}

	}
}