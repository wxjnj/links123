package Versioncom
{
	public class Version
	{
		private var _main:uint=0;
		private var _sub:uint=0;
		private var _fix:uint=0;
		private var _build:uint=0;
		private var _test:String;
		public function get Main():uint{
			return _main;
		}
		public function get Sub():uint{
			return _sub;
		}
		public function get Fix():uint{
			return _fix;
		}
		public function get Build():uint{
			return _build;
		}
		
		public function Version(main:uint,sub:uint,fix:uint=0,build:uint=0,test:String="")
		{
			_main=main;
			_sub=sub;
			_fix=fix;
			_build=build;
			_test = test;
		}
		
		public function toString():String{
			return _main+"."+_sub+"."+_fix+"."+_build+" "+_test;
		}
	}
}