<?php
namespace hitwpupdateserver\app\config;
class AppVersion
{
	const MAJOR = 0;
	const MINOR = 0;
	const PATCH = 1;
    const POINT = 0;

	public static function get()
	{
		return sprintf('%s.%s.%s.%s', self::MAJOR, self::MINOR, self::PATCH,self::POINT);
	}


}

?>