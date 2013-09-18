package com.links123.player.mic 
{
  import com.links123.player.event.RecordingEvent;
  
  import flash.events.ActivityEvent;
  import flash.events.Event;
  import flash.events.EventDispatcher;
  import flash.events.SampleDataEvent;
  import flash.events.TimerEvent;
  import flash.media.Microphone;
  import flash.media.Sound;
  import flash.media.SoundChannel;
  import flash.media.SoundCodec;
  import flash.utils.ByteArray;
  import flash.utils.Dictionary;
  import flash.utils.Endian;
  import flash.utils.Timer;
  import flash.utils.getTimer;
  
  public class MicrophoneRecorder extends EventDispatcher {
    public static var SOUND_COMPLETE:String = "sound_complete";
    public static var PLAYBACK_STARTED:String = "playback_started";
    public static var ACTIVITY:String = "activity";
	public static var QUIET:String = "quiet";
	
	private var _gain:uint;
	private var _rate:uint;
	private var _codec:String;
	private var _silenceLevel:uint;
	private var _timeOut:uint;
	private var _difference:uint;
	private var _microphone:Microphone;
	private var _output:ByteArray;
	
    public var sound:Sound = new Sound();
    public var soundChannel:SoundChannel;
    public var sounds:Dictionary = new Dictionary();
    public var rates:Dictionary = new Dictionary();
    public var currentSoundName:String = "";
    public var currentSoundFilename:String = "";
    public var recording:Boolean = false;
    public var playing:Boolean = false;
    public var samplingStarted:Boolean = false;
    public var latency:Number = 0;
    private var resampledBytes:ByteArray = new ByteArray();
	
	private var _recordingEvent:RecordingEvent = new RecordingEvent( RecordingEvent.RECORDING, 0 );
	
    public function MicrophoneRecorder(microphone:Microphone=null, gain:uint=100, rate:uint=22, silenceLevel:uint=10, timeOut:uint=4000,Codec:String=SoundCodec.NELLYMOSER) {
		_microphone = microphone;
		_gain = gain;
		_rate = rate;
		_silenceLevel = silenceLevel;
		_timeOut = timeOut;
		_codec = Codec;
		
	   this.sound.addEventListener(SampleDataEvent.SAMPLE_DATA, playbackSampleHandler);
	   reset();
    }

	public function get microphone():Microphone
	{
		return _microphone;
	}

	public function set microphone(value:Microphone):void
	{
		_microphone = value;
	}

    public function reset():void {
      this.stop();
      this.sounds = new Dictionary();
      this.rates = new Dictionary();
      this.currentSoundName = "";
      this.recording = false;
      this.playing = false;
    }

    public function record(name:String="wav", filename:String=""):void {
		if (microphone == null )
		{
			microphone = Microphone.getMicrophone();
		}
		if(microphone != null)
		{
			microphone.setSilenceLevel(_silenceLevel, _timeOut);
			microphone.encodeQuality = 10;
			microphone.noiseSuppressionLevel = 0;
			microphone.codec = _codec;
			microphone.gain = _gain;
			microphone.rate = _rate;
			
	      this.stop();
	      this.currentSoundName = name;
	      this.currentSoundFilename = filename;
	      var data:ByteArray = this.getSoundBytes(name, true);
	      data.position = 0;
	      this.rates[name] = microphone.rate;
	      this.samplingStarted = true;
	      microphone.addEventListener(SampleDataEvent.SAMPLE_DATA, micSampleDataHandler);
	      microphone.addEventListener(ActivityEvent.ACTIVITY, onMicrophoneActivity);
	      this.recording = true;
		  mute_timer = getTimer();
		  _difference = getTimer();
		}
    }

    public function playBack(name:String="wav"):void {
      this.stop();
      this.currentSoundName = name;
      this.getSoundBytesResampled(true);
      this.samplingStarted = true;
      this.playing = true;
      this.soundChannel = this.sound.play();
      this.soundChannel.addEventListener(Event.SOUND_COMPLETE, onSoundComplete);
    }

    public function stop():void {
      if(this.soundChannel) {
        this.soundChannel.stop();
		this.soundChannel.removeEventListener(Event.SOUND_COMPLETE, onSoundComplete);
        this.soundChannel = null;
      }

      if(this.playing) {
        this.playing = false;
      }

      if(this.recording) {
        this.microphone.removeEventListener(SampleDataEvent.SAMPLE_DATA, micSampleDataHandler);
        this.microphone.removeEventListener(ActivityEvent.ACTIVITY, onMicrophoneActivity);
        this.recording = false;
      }
    }

    private function onSoundComplete(event:Event):void {
      this.stop();
      dispatchEvent(new Event(MicrophoneRecorder.SOUND_COMPLETE));
    }

    public function getSoundBytes(name:String=null, create:Boolean=false):ByteArray {
      if(! name) {
        name = this.currentSoundName;
      }

      var data:ByteArray = ByteArray(this.sounds[name]);
      if(create) {
        if(data) {
          delete this.sounds[name];
        }
        data = new ByteArray();
        this.sounds[name] = data;
      }

      return data;
    }

    public function getSoundBytesResampled(resampling:Boolean=false):ByteArray {
      if(! resampling) {
        return resampledBytes;
      }

      var targetRate:int = 44100;
      var sourceRate:int = this.frequency();
      var data:ByteArray = this.getSoundBytes();
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

    private function onMicrophoneActivity(event:Event):void {
      dispatchEvent(new Event(MicrophoneRecorder.ACTIVITY));
    }

	/**
	 * 存储无声持续的时间 
	 */		
	private var mute_timer:Number;
	
    private function micSampleDataHandler(event:SampleDataEvent):void {
		var n:Number = Math.abs(event.data.readFloat()*100);
		if(n < 8)
		{
			//安静大于5s提醒
			trace(getTimer()+":"+mute_timer);
			if ((getTimer() - mute_timer)/1000>8)
			{
				trace("已经没声音5秒了");
				mute_timer = getTimer();
				dispatchEvent(new Event(MicrophoneRecorder.QUIET));
			}
		}
		else {
			trace("安静被打断，重新开始记安静时间");
			mute_timer = getTimer();
		}
		
		_recordingEvent.time = (getTimer() - _difference)/1000;
		dispatchEvent( _recordingEvent);
      var data:ByteArray = this.getSoundBytes();
      while(event.data.bytesAvailable) {
        data.writeFloat(event.data.readFloat());
      }
    }

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
        dispatchEvent(new Event(MicrophoneRecorder.PLAYBACK_STARTED));
      }

      var data:ByteArray = this.getSoundBytesResampled();
      for (; i<8192 && data.bytesAvailable && this.playing; i++) {
        sample = data.readFloat();
        event.data.writeFloat(sample);
        event.data.writeFloat(0.0);
      }
    }

    public function duration(name:String=null):Number {
      if(! name) {
        name = this.currentSoundName;
      }
      var data:ByteArray = this.getSoundBytes(name);
      var frequency:int = MicrophoneRecorder.frequency(this.rates[name]);
      var numSamples:uint = data.length / 4;
      return Number(numSamples) / Number(frequency);
    }

    public function rate(name:String=null):int {
      if(! name) {
        name = this.currentSoundName;
      }
      return this.rates[name];
    }

    public function frequency(name:String=null):int {
      return MicrophoneRecorder.frequency(this.rate(name));
    }

    public static function frequency(rate:int):int {
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

	public function get codec():String
	{
		return _codec;
	}
	
	public function set codec(value:String):void
	{
		_codec = value;
	}
	
    public function convertToWav(name:String="wav"):ByteArray {
      return MicrophoneRecorder.convertToWav(this.getSoundBytes(name), MicrophoneRecorder.frequency(this.rates[name]));
    }

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
	
	/**
	 * 
	 * @return 
	 * 
	 */		
	public function get output():ByteArray
	{
		if(codec == SoundCodec.SPEEX)
		{
			return this.getSoundBytes("wav");
		}else
		{
			return convertToWav();
		}
	}
	
  }
}
