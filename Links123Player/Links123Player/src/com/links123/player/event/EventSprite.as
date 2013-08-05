package com.links123.player.event
{
	import flash.display.Sprite;
	
	public class EventSprite extends Sprite
	{
		public function EventSprite()
		{
			super();
		}
		private static var instance:EventSprite = null;
		/**
		 * 取得单例 
		 * @return 
		 * 
		 */		
		public static function getInstance():EventSprite
		{
			if(instance==null)
			{
				instance = new EventSprite(); 
			}
			return instance
		}
	}
}