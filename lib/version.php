<?php
namespace lib;
/**
 * dash main configure
 */
class version
{
	// @var dash core current version
	const version = '13.0.1';


	/**
	 * return current version
	 *
	 * @return     string  The current version of dash
	 */
	public static function get()
	{
		return self::version;
	}
}
?>
