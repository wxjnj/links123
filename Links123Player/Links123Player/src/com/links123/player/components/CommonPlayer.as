package com.links123.player.components
{
	import com.adobe.serialization.json.JSON;
	import com.exsky.DataLoader;
	import com.links123.player.Mode.ClipsVO;
	import com.links123.player.Mode.PlayerVO;
	import com.links123.player.Mode.WordClipsVO;
	import com.links123.player.event.EventSprite;
	import com.links123.player.event.LoadDataEvent;
	import com.links123.player.skins.WordBarSkin;
	
	import flash.display.Sprite;
	import flash.events.DataEvent;
	import flash.events.Event;
	import flash.events.HTTPStatusEvent;
	import flash.events.IOErrorEvent;
	import flash.events.MouseEvent;
	import flash.events.TimerEvent;
	import flash.external.ExternalInterface;
	import flash.net.URLLoader;
	import flash.net.URLLoaderDataFormat;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.utils.Timer;
	
	import mx.core.UIComponent;
	import mx.graphics.shaderClasses.ExclusionShader;
	
	import spark.components.Application;
	
	import org.osmf.events.MediaPlayerStateChangeEvent;
	import org.osmf.events.TimeEvent;
	import org.osmf.media.MediaPlayer;
	import org.osmf.media.MediaPlayerState;
	
	/**
	 * 一般播放器 
	 * @author Administrator
	 * 
	 */	
	public class CommonPlayer extends VideoPlayer
	{
		include "../../../../log/Logging/Logger.as";
		
		/**
		 *  当前播放器状态
		 */		
		protected var _CurrentMediaPlayerState:String;
		
		public function CommonPlayer()
		{
			super();
			Logger.debug("addEventListener DataEvent.UPLOAD_COMPLETE_DATA event!");
			this.addEventListener(MediaPlayerStateChangeEvent.MEDIA_PLAYER_STATE_CHANGE,playerstatechange);
			this.addEventListener(TimeEvent.COMPLETE,completehandle);
			ExternalInterface.addCallback("playPause",playPause);
		}
		
		/**
		 * 播放暂停 
		 * 
		 */		
		private function playPause():void
		{
			playPauseButton.dispatchEvent(new MouseEvent(MouseEvent.CLICK));
		}
		
		private var playpausesign:Boolean = false;
		
		/**
		 * 播放器状态改变时间 
		 * @param event
		 * 
		 */		
		protected function playerstatechange(event:MediaPlayerStateChangeEvent):void
		{
			Logger.debug("MediaPlayerStateChangeEvent:{0}",event.state);
			//赋值当前播放器状态
			_CurrentMediaPlayerState = event.state;
			ExternalInterface.call("playerStateChange",event.state);
			if(event.state == "ready" && playpausesign == false)
			{
				playPauseButton.visible = true;
//				if(ProgramConfig.config.PlayerMode==3)
//				{
//					playPauseButton.alpha = 0;
//				}
				playpausesign = true;
			}
			if(event.state == "loading" || event.state == "ready" || event.state == "buffering")
			{
				loading.visible = true;
			}else
			{
				loading.visible = false;
			}
		}
		
		/**
		 * 播放器播放完成事件 
		 * @param event
		 * 
		 */		
		protected function completehandle(event:TimeEvent):void
		{
			Logger.debug("player play completed");
			dispose();
		}	
		
		/**
		 * 消除存在变量以及事件 
		 * 
		 */		
		protected function dispose():void
		{
			loading.visible = false;
			this.removeEventListener(MediaPlayerStateChangeEvent.MEDIA_PLAYER_STATE_CHANGE,playerstatechange);
			this.removeEventListener(TimeEvent.COMPLETE,completehandle);
		}
		
		override protected function partRemoved(partName:String, instance:Object) : void
		{
			super.partRemoved(partName, instance);
		}
		
		override protected function partAdded(partName:String, instance:Object):void
		{
			super.partAdded(partName, instance);
		}
		
		override protected function getCurrentSkinState():String
		{
			return super.getCurrentSkinState();
		} 
		
	}
}