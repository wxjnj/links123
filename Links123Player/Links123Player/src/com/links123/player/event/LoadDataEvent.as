package com.links123.player.event
{
	import flash.events.Event;
	
	public class LoadDataEvent extends Event
	{
		public static var LOAD_PLAYER_DATA_COMPLETE:String="load player data complete";
		public function LoadDataEvent(type:String, bubbles:Boolean=false, cancelable:Boolean=false)
		{
			super(type, bubbles, cancelable);
		}
	}
}