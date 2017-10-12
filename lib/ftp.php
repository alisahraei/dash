<?php
namespace lib;
/**
 * Class for ftp.
 * every ftp function
 * for @example
 *  \lib\ftp::pwd();
 *  \lib\ftp::size('remotefile');
 *  \lib\ftp::get(...);
 */
class ftp
{
	public static $link  = null;
	public static $login = false;

	/**
	 * connect fo fpt server
	 */
	public static function connect()
	{
		if(!self::$link)
		{
			$ftp_host = \lib\option::config('ftp', 'host');
			$ftp_port = \lib\option::config('ftp', 'port');

			$link = @ftp_connect($ftp_host, $ftp_port);
			if($link)
			{
				self::$link = $link;
				self::login();
			}
		}
	}


	/**
	 * login in connected ftp server
	 */
	public static function login()
	{
		$user  = \lib\option::config('ftp', 'user');
		$pass  = \lib\option::config('ftp', 'pass');
		$login = @ftp_login(self::$link, $user, $pass);

		if($login)
		{
			self::$login = $login;
		}

	}


	/**
	 * call every ftp function if exist
	 *
	 * @param      <type>  $_func  The function
	 * @param      <type>  $_args  The arguments
	 */
	public static function __callStatic($_func, $_args)
	{
		$func_name = 'ftp_'. $_func;
		if(function_exists($func_name))
		{
			self::connect();
			if(self::$login)
			{
				$result = $func_name(self::$link, ...$_args);
				return $result;
			}
		}
		return false;
	}
}
?>