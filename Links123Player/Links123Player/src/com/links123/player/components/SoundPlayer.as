package com.links123.player.components
{
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.events.TimerEvent;
	import flash.filters.BitmapFilterQuality;
	import flash.filters.GlowFilter;
	import flash.media.Sound;
	import flash.media.SoundChannel;
	import flash.media.SoundLoaderContext;
	import flash.media.SoundMixer;
	import flash.net.URLRequest;
	import flash.utils.ByteArray;
	import flash.utils.Timer;

/**
 * 声音播放类 
 * @author Administrator
 * 
 */		
public class SoundPlayer extends Sprite 
{
	include "../../../../log/Logging/Logger.as";
	private var sound:Sound;
	private var soundCh:SoundChannel;
	private var soundCon:SoundLoaderContext;
	private var position:Number;
	private var isPlaying:Boolean;
	private var isPause:Boolean;
	private var _starttime:Number = 0;
	private var _endtime:Number = 0;
	//private static var BUFFERTIME:Number = 10000;
    private var timer:Timer;
   
	private var byte:ByteArray = new ByteArray(); //音频谱
	private var n:Number=10;
	private var _isShowMovie:Boolean = false;//默认不显示音谱
	          
	/**
	 * 初始化 
	 * 
	 */
   public function SoundPlayer()
   {
      isPause = false;
      isPlaying = false;
      //SoundMixer.bufferTime = BUFFERTIME; 
   }

	/**
	 * 创建一个声音对象
	 * @param url 媒体地址
	 * @param playNow 是否马上播放，默认为真
	 * @isShowMovie 是否显示音频谱
	 * 
	 */      
    public function createSound(url:String,playNow:Boolean = true,isShowMovie:Boolean=false):void
    {              
		Logger.debug("init sound object!");          
        dispose();
        sound = new Sound();
        sound.load(new URLRequest(url));
        sound.addEventListener(IOErrorEvent.IO_ERROR,errorHandler);             
        if(playNow)
		{
           play();    
		}
		if(isShowMovie==true)
		{
			this.addEventListener(Event.ENTER_FRAME,enterframehandle);
		}                        
    }

	/**
	 * 显示音频图 
	 * @param event
	 * 
	 */
	protected function enterframehandle(event:Event):void
	{
		playMovie();
	}
             
	/**
	 *  播放
	 * @param offset 声音从哪开始
	 * 
	 */
    public function play(offset:Number = 0,endtime:Number=0):void
    {
		Logger.debug("playNew offset:{0},endtime:{1}",offset,endtime);   
		_starttime = offset;
		_endtime = endtime;
        if(isPause)
		{
           soundCh = sound.play(position);
		}
        else
		{
           soundCh = sound.play(offset*1000);
           isPlaying = true;
           isPause = false;
		}
		if(endtime != 0)
		{
			var dur:Number = (endtime-offset)*1000;
			timer = new Timer(dur,1);
			timer.addEventListener(TimerEvent.TIMER_COMPLETE,timercompletehandle);		
			timer.start();
		}
    }

	/**
	 * 完成事件 
	 * @param event
	 * 
	 */
	protected function timercompletehandle(event:TimerEvent):void
	{
		stop();
		timer.stop();
		timer.removeEventListener(TimerEvent.TIMER_COMPLETE,timercompletehandle);
		timer = null;
	}
	
	/**
	 *  暂停 
	 * 
	 */
	public function pause():void
	{
	   if(isPlaying)
	   {                        
	       position = soundCh.position;
	       stop();        
	       isPause = true;
	   }
	}
	
	/**
	 * 停止 
	 * 
	 */
	public function stop():void
	{
	   if(isPlaying)
	   {
	       soundCh.stop();
	       isPlaying = false;                                
	   }
	}
	
	/**
	 *  播放位置    
	 * @return 
	 * 
	 */
	public function get Position():Number
	{
	    if(soundCh == null)
	         return 0;                    
	    return Math.round(soundCh.position);
	}
	
	/**
	 *  声音对象长度  
	 * @return 
	 * 
	 */
	public function get Length():Number
	{
	     if(sound == null)
	           return 0;
	     return Math.round(sound.length*sound.bytesTotal/sound.bytesLoaded);
	}
	             
	/**
	 * 声音对象总共字节 
	 * @return 
	 * 
	 */
	public function get BytesTotal():Number
	{
	      if(sound == null)
	              return 0;
	       return sound.bytesTotal;
	}
	
	/**
	 * 声音对象加载字节
	 * @return 
	 * 
	 */
	public function get BytesLoaded():Number
	{
	     if(sound == null)
	           return 0;
	     return sound.bytesLoaded;
	}
	
	/**
	 *  设置缓冲时间 
	 * @param time
	 * 
	 */
	public function set BufferTime(time:Number):void
	{
	     SoundMixer.bufferTime=time;
	}
	
	/**
	 *  中途换歌的时候用的
	 * 
	 */
	private function dispose():void
	{
		  this.removeEventListener(Event.ENTER_FRAME,enterframehandle);
	      if(sound == null)
	             return ;
	      if(sound.isBuffering)
	              sound.close();
	              stop();                 
	              sound = null;
	}
	
	/**
	 * 播放音谱 
	 * 
	 */		
	private function playMovie():void
	{
		this.graphics.clear();
		SoundMixer.computeSpectrum(byte,true,1);//将当前声音输出为ByteArray  
		for (var i:int=0; i <1000; i=i+5) {  
			n = byte.readFloat()*60;//把数据流读取成浮点数并扩大其值  
			this.graphics.lineStyle(3,0xFFFFFF,1,true,"noSacle","none");  
			this.graphics.moveTo(27+i,50);  
			this.graphics.lineTo(27+i,50-n);  
			this.graphics.lineStyle(3,0xFFFFFF,0.2,true,"noSacle","none");  
			this.graphics.lineTo(27+i,50+n);  
		}
		var glow:GlowFilter = new GlowFilter(); 
		glow.color = 0x009922;
		glow.alpha = 1; 
		glow.blurX = 25; 
		glow.blurY = 25; 
		glow.quality = BitmapFilterQuality.MEDIUM;
		this.filters = [glow];
	}

	/**
	 * 处理错误用
	 * @param e
	 * 
	 */
	private function errorHandler(e:IOErrorEvent):void
	{
	    sound.removeEventListener(IOErrorEvent.IO_ERROR,errorHandler);
	    sound = null;
	}                
}
} 