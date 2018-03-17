<?php
namespace lib;

class user
{
	/**
	 * The user working by system
	 *
	 * @var        <type>
	 */
	private static $USER_ID = null;


	/**
	 * Initial user id
	 *
	 * @param      <type>  $_user_id  The user identifier
	 */
	public static function init($_user_id, $_detail = [])
	{
		self::$USER_ID = $_user_id;
		$_SESSION['auth']['id'] = $_user_id;

		if(is_array($_detail))
		{
			foreach ($_detail as $key => $value)
			{
				$_SESSION['auth'][$key] = $value;
			}
		}
	}


	public static function refresh()
	{
		$user_id = self::id();
		$detail = \lib\db\users::get_by_id($user_id);
		self::destroy();
		self::init($user_id, $detail);
	}


	public static function login($_key = null)
	{
		if($_key === null)
		{
			if(self::id())
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return self::detail($_key);
		}
	}


	/**
	 * destroy user id
	 */
	public static function destroy()
	{
		self::$USER_ID = null;
		unset($_SESSION['auth']);
	}

	/**
	 * return current version
	 *
	 * @return     string  The current version of dash
	 */
	public static function id()
	{
		if(!isset(self::$USER_ID))
		{
			if(isset($_SESSION['auth']['id']))
			{
				self::$USER_ID = $_SESSION['auth']['id'];
			}
		}

		if(is_numeric(self::$USER_ID))
		{
			return intval(self::$USER_ID);
		}

		return self::$USER_ID;
	}


	/**
	 * get detail of user
	 *
	 * @param      <type>  $_key   The key
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function detail($_key = null)
	{
		if($_key)
		{
			if(isset($_SESSION['auth'][$_key]))
			{
				return $_SESSION['auth'][$_key];
			}
			return null;
		}
		else
		{
			if(isset($_SESSION['auth']))
			{
				return $_SESSION['auth'];
			}
			return null;
		}
	}



	/**
	* check is set remember of this user and login by this
	*
	*/
	public static function check_remeber_login()
	{
		// check if have cookie set login by remember
		if(!\lib\user::login())
		{
			\addons\content_enter\main\tools\login::login_by_remember();
		}
	}


	/**
	* if the user use 'en' language of site
	* and her country is "IR"
	* and no referer to this page
	* and no cookie set from this site
	* redirect to 'fa' page
	* WARNING:
	* this function work when the default lanuage of site is 'en'
	* if the default language if 'fa'
	* and the user work by 'en' site
	* this function redirect to tj.com/fa/en
	* and then redirect to tj.com/en
	* so no change to user interface ;)
	*/
	public static function user_country_redirect()
	{
		if(\lib\url::isLocal())
		{
			return;
		}

		if(\lib\agent::isBot())
		{
			return ;
		}

		$referer = (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) ? true : false;

		if($referer)
		{
			return;
		}

		$key = 'language';

		$cookie = \lib\utility\cookie::read($key);

		if(!$_SESSION && !$cookie && !\lib\url::lang())
		{
			$default_site_language = \lib\language::default();
			$country_is_ir         = (isset($_SERVER['HTTP_CF_IPCOUNTRY']) && mb_strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']) === 'IR') ? true : false;
			$redirect_lang         = null;

			$access_lang = \lib\option::language('list');

			if($default_site_language === 'fa' && !$country_is_ir)
			{
				$redirect_lang = 'en';
			}
			elseif($default_site_language === 'en' && $country_is_ir)
			{
				$redirect_lang = 'fa';
			}
			$cookie_lang = $redirect_lang ? $redirect_lang : $default_site_language;
			$domain = '.'. \lib\url::domain();

			\lib\utility\cookie::write($key, $cookie_lang, (60*60*24*30), $domain);
			$_SESSION[$key] = $cookie_lang;

			if($redirect_lang && array_key_exists($redirect_lang, $access_lang))
			{
				$root    = \lib\url::base();
				$full    = \lib\url::pwd();
				$new_url = str_replace($root, $root. '/'. $redirect_lang, $full);
				\lib\redirect::to($new_url);
			}
		}
	}
}
?>
