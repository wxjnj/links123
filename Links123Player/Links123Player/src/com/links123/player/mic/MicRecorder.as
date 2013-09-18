package com.links123.player.mic
{
	import flash.events.ActivityEvent;
	import flash.events.Event;
	import flash.events.EventDispatcher;
	import flash.events.SampleDataEvent;
	import flash.events.StatusEvent;
	import flash.media.Microphone;
	import flash.media.Sound;
	import flash.media.SoundChannel;
	import flash.media.SoundCodec;
	import flash.utils.ByteArray;
	import flash.utils.Endian;
	import flash.utils.getTimer;
	import com.links123.player.event.RecordingEvent;
	
//	import org.bytearray.micrecorder.encoder.WaveEncoder;
//	import org.bytearray.micrecorder.events.RecordingEvent;
	
	/**
	 * Dispatched during the recording of the audio stream coming from the microphone.
	 *
	 * @eventType org.bytearray.micrecorder.RecordingEvent.RECORDING
	 *
	 * * @example
	 * This example shows how to listen for such an event :
	 * <div class="listing">
	 * <pre>
	 *
	 * recorder.addEventListener ( RecordingEvent.RECORDING, onRecording );
	 * </pre>
	 * </div>
	 */
	[Event(name='recording', type='com.links123.player.event.RecordingEvent')]
	
	/**
	 * Dispatched when the creation of the output file is done.
	 *
	 * @eventType flash.events.Event.COMPLETE
	 *
	 * @example
	 * This example shows how to listen for such an event :
	 * <div class="listing">
	 * <pre>
	 *
	 * recorder.addEventListener ( Event.COMPLETE, onRecordComplete );
	 * </pre>
	 * </div>
	 */
	[Event(name='complete', type='flash.events.Event')]
	
	/**
	 * This tiny helper class allows you to quickly record the audio stream coming from the Microphone and save this as a physical file.
	 * A WavEncoder is bundled to save the audio stream as a WAV file
	 * @author Thibault Imbert - bytearray.org
	 * @version 1.1
	 * 
	 */	
	public final class MicRecorder extends EventDispatcher
	{
		private var _gain:uint;
		private var _rate:uint;
		private var _codec:String;
		private var _silenceLevel:uint;
		private var _timeOut:uint;
		private var _difference:uint;
		private var _microphone:Microphone;
		private var _buffer:ByteArray = new ByteArray();
		private var _output:ByteArray;
		//private var _encoder:IEncoder;
		
		private var _completeEvent:Event = new Event ( Event.COMPLETE );
		private var _recordingEvent:RecordingEvent = new RecordingEvent( RecordingEvent.RECORDING, 0 );
		
		public var sound:Sound = new Sound();
		public var soundChannel:SoundChannel;
		
		public var playing:Boolean = false;
		public var samplingStarted:Boolean = false;
		public var latency:Number = 0;
		private var resampledBytes:ByteArray = new ByteArray();
		
		public static var SOUND_COMPLETE:String = "sound_complete";
		public static var PLAYBACK_STARTED:String = "playback_started";
		public static var ACTIVITY:String = "activity";
		public static var QUIET:String = "quiet";
		
		
		/**
		 * 
		 * @param encoder The audio encoder to use
		 * @param microphone The microphone device to use
		 * @param gain The gain
		 * @param rate Audio rate
		 * @param silenceLevel The silence level
		 * @param timeOut The timeout
		 * 
		 */		
		public function MicRecorder(microphone:Microphone=null, gain:uint=100, rate:uint=22, silenceLevel:uint=10, timeOut:uint=4000,Codec:String=SoundCodec.NELLYMOSER)
		{
			_microphone = microphone;
			_gain = gain;
			_rate = rate;
			_silenceLevel = silenceLevel;
			_timeOut = timeOut;
			_codec = Codec;
			
			//播放录音数据
			this.sound.addEventListener(SampleDataEvent.SAMPLE_DATA, playbackSampleHandler);
		}
		
		/**
		 * 录音回放 
		 * 
		 */		
		public function playBack():void {
			this.getSoundBytesResampled(true);
			this.samplingStarted = true;
			this.playing = true;
			this.soundChannel = this.sound.play();
			this.soundChannel.addEventListener(Event.SOUND_COMPLETE, onSoundComplete);
		}
		
		/**
		 * 停止录音回放 
		 * 
		 */		
		public function stopplayBack():void
		{
			if(this.soundChannel) {
				this.soundChannel.stop();
				this.soundChannel.removeEventListener(Event.SOUND_COMPLETE, onSoundComplete);
				this.soundChannel = null;
			}
			if(this.playing) {
				this.playing = false;
			}
		}
		
		/**
		 * 录音回放完成 
		 * @param event
		 * 
		 */		
		private function onSoundComplete(event:Event):void {
			stopplayBack()
			dispatchEvent(new Event(MicRecorder.SOUND_COMPLETE));
		}
		
		/**
		 * 播放录制声音 
		 * @param event
		 * 
		 */	
		private function playbackSampleHandler(event:SampleDataEvent):void {
			var i:int = 0;
			var sample:Number = 0.0;
			if(!this.soundChannel) {
				for (; i<3072; i++) {
					event.data.writeFloat(sample);
					event.data.writeFloat(sample);
				}
				return;
			}
			
			if(this.samplingStarted && this.soundChannel) {
				this.samplingStarted = false;
				this.latency = (event.position * 2.267573696145e-02) - this.soundChannel.position;
				dispatchEvent(new Event(MicRecorder.PLAYBACK_STARTED));
			}
			
			//取得声音加载数据
			var data:ByteArray = this.getSoundBytesResampled();
			for (; i<8192 && data.bytesAvailable && this.playing; i++) {
				sample = data.readFloat();
				event.data.writeFloat(sample);
				event.data.writeFloat(0.0);
			}
		}
		
		/**
		 * 取得声音加载数据 
		 * @param resampling
		 * @return 
		 * 
		 */	
		public function getSoundBytesResampled(resampling:Boolean=false):ByteArray {
			if(! resampling) {
				return resampledBytes;
			}
			
			var targetRate:int = 44100;
			var sourceRate:int = this.frequency(_rate);
			var data:ByteArray = _buffer;//this.getSoundBytes();
			data.position = 0;
			
			// nothing todo here
			if(sourceRate == 44100) {
				resampledBytes = data;
				resampledBytes.position = 0;
				return resampledBytes;
			}
			
			resampledBytes = new ByteArray();
			
			var multiplier:Number = targetRate / sourceRate;
			
			// convert the data
			var measure:int = targetRate;
			var currentSample:Number = data.readFloat();
			var nextSample:Number = data.readFloat();
			
			resampledBytes.writeFloat(currentSample);
			
			// taken from http://code.google.com/p/as3wavsound/ in Wav.as from resampleSamples()
			while(data.bytesAvailable) {
				var increment:Number = (nextSample - currentSample) / multiplier;
				var times:int = 0;
				while(measure >= sourceRate) {
					times += 1;
					resampledBytes.writeFloat(currentSample + (increment * times));
					measure -= sourceRate;
				}
				
				currentSample = nextSample;
				nextSample = data.readFloat();
				measure += targetRate;
			}
			
			resampledBytes.writeFloat(nextSample);
			resampledBytes.position = 0;
			
			return resampledBytes;
		}
		
		public function get codec():String
		{
			return _codec;
		}

		public function set codec(value:String):void
		{
			_codec = value;
		}
		
		/**
		 * 传入采样率 
		 * @param rate
		 * @return 
		 * 
		 */		
		public function frequency(rate:int):int {
			switch(rate) {
				case 44:
					return 44100;
				case 22:
					return 22050;
				case 11:
					return 11025;
				case 8:
					return 8000;
				case 5:
					return 5512;
			}
			return 0;
		}
		
		/**
		 * Starts recording from the default or specified microphone.
		 * The first time the record() method is called the settings manager may pop-up to request access to the Microphone.
		 */		
		public function record():void
		{
			if (microphone == null )
			{
				microphone = Microphone.getMicrophone();
			}
			if(microphone != null)
			{
				_difference = getTimer();
				microphone.setSilenceLevel(_silenceLevel, _timeOut);
				microphone.encodeQuality = 10;
				microphone.noiseSuppressionLevel = 0;
				microphone.codec = _codec;
				microphone.gain = _gain;
				microphone.rate = _rate;
				_buffer.length = 0;
				microphone.addEventListener(SampleDataEvent.SAMPLE_DATA, onSampleData);
				microphone.addEventListener(StatusEvent.STATUS, onStatus);
				microphone.addEventListener(ActivityEvent.ACTIVITY, onMicrophoneActivity);
				this.samplingStarted = true;
			}
		}
		
		/**
		 * 话筒活动检测 
		 * @param event
		 * 
		 */		
		private function onMicrophoneActivity(event:Event):void {
			dispatchEvent(new Event(MicRecorder.ACTIVITY));
		}
		
		private function onStatus(event:StatusEvent):void
		{
			trace(event.code);
			_difference = getTimer();
			trace(_difference);
		}
		
		/**
		 * 存储无声持续的时间 
		 */		
		private var mute_timer:Number=getTimer();
		
		/**
		 * Dispatched during the recording.
		 * @param event
		 */		
		private function onSampleData(event:SampleDataEvent):void
		{
			var n:Number = Math.abs(event.data.readFloat()*100);
			if(n < 8)
			{
				//安静大于5s提醒
				trace(getTimer()+":"+mute_timer);
				if ((getTimer() - mute_timer)/1000>20)
				{
					trace("已经没声音5秒了");
					mute_timer = getTimer();
					dispatchEvent(new Event(MicRecorder.QUIET));
				}
			}
		    else {
				trace("安静被打断，重新开始记安静时间");
				mute_timer = getTimer();
			}
			
			_recordingEvent.time = (getTimer() - _difference)/1000;
			dispatchEvent( _recordingEvent);
			while(event.data.bytesAvailable)
			{
				_buffer.writeFloat(event.data.readFloat());
			}
		}
		
		/**
		 * Stop recording the audio stream and automatically starts the packaging of the output file.
		 */		
		public function stop():void
		{
			_microphone.removeEventListener(SampleDataEvent.SAMPLE_DATA, onSampleData);
			
			_buffer.position = 0;
			if(codec == SoundCodec.NELLYMOSER)
			{
				_output = _buffer;//_encoder.encode(_buffer, 1);
			}else if(codec == SoundCodec.SPEEX)
			{
				_output = _buffer;
			}
			dispatchEvent( _completeEvent );
		}
		
		/**
		 * 
		 * @return 
		 * 
		 */		
		public function get gain():uint
		{
			return _gain;
		}

		/**
		 * 
		 * @param value
		 * 
		 */		
		public function set gain(value:uint):void
		{
			_gain = value;
		}

		/**
		 * 
		 * @return 
		 * 
		 */		
		public function get rate():uint
		{
			return _rate;
		}

		/**
		 * 
		 * @param value
		 * 
		 */		
		public function set rate(value:uint):void
		{
			_rate = value;
		}
		
		/**
		 * 
		 * @return 
		 * 
		 */		
		public function get silenceLevel():uint
		{
			return _silenceLevel;
		}

		/**
		 * 
		 * @param value
		 * 
		 */		
		public function set silenceLevel(value:uint):void
		{
			_silenceLevel = value;
		}

		/**
		 * 
		 * @return 
		 * 
		 */		
		public function get microphone():Microphone
		{
			return _microphone;
		}

		/**
		 * 
		 * @param value
		 * 
		 */		
		public function set microphone(value:Microphone):void
		{
			_microphone = value;
		}

		/**
		 * 
		 * @return 
		 * 
		 */		
		public function get output():ByteArray
		{
			return convertToWav(_output, rate);
		}
		
		/**
		 * 编译成wav 
		 * @param soundBytes
		 * @param sampleRate
		 * @return 
		 * 
		 */		
		public static function convertToWav(soundBytes:ByteArray, sampleRate:int):ByteArray {
			var data:ByteArray = new ByteArray();
			data.endian = Endian.LITTLE_ENDIAN;
			
			var numBytes:uint = soundBytes.length / 2; // soundBytes are 32bit floats, we are storing 16bit integers
			var numChannels:int = 1;
			var bitsPerSample:int = 16;
			
			// The following is from https://ccrma.stanford.edu/courses/422/projects/WaveFormat/
			
			data.writeUTFBytes("RIFF"); // ChunkID
			data.writeUnsignedInt(36 + numBytes); // ChunkSize
			data.writeUTFBytes("WAVE"); // Format
			data.writeUTFBytes("fmt "); // Subchunk1ID
			data.writeUnsignedInt(16); // Subchunk1Size // 16 for PCM
			data.writeShort(1); // AudioFormat 1 Mono, 2 Stereo (Microphone is mono)
			data.writeShort(numChannels); // NumChannels
			data.writeUnsignedInt(sampleRate); // SampleRate
			data.writeUnsignedInt(sampleRate * numChannels * bitsPerSample/8); // ByteRate
			data.writeShort(numChannels * bitsPerSample/8); // BlockAlign
			data.writeShort(bitsPerSample); // BitsPerSample
			data.writeUTFBytes("data"); // Subchunk2ID
			data.writeUnsignedInt(numBytes); // Subchunk2Size
			
			soundBytes.position = 0;
			while(soundBytes.bytesAvailable > 0) {
				var sample:Number = soundBytes.readFloat(); // The sample is stored as a sine wave, -1 to 1
				var val:int = sample * 32768; // Convert to a 16bit integer
				data.writeShort(val);
			}
			
			return data;
		}
		
	}
}