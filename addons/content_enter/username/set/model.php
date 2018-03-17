<?php
namespace addons\content_enter\username\set;


class model extends \addons\content_enter\main\model
{

	/**
	 * Posts an enter.
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public function post_username($_args)
	{
		$username = \lib\request::post('username');
		$username = trim($username);
		if($username)
		{
			if(mb_strlen($username) < 5)
			{
				\lib\notif::error(T_("You must set large than 5 character in username"));
				return false;
			}

			if(mb_strlen($username) > 50)
			{
				\lib\notif::error(T_("You must set less than 50 character in username"));
				return false;
			}

			// check username exist
			$check_exist_name = \lib\db\users::get_by_username($username);

			if(!empty($check_exist_name))
			{
				\lib\notif::error(T_("This username alreay taked!"));
				return false;
			}

			\lib\db\users::update(['username' => $username], $this->login('id'));
			// set the alert message
			self::set_alert(T_("Your username was set"));
			// open lock of alert page
			self::next_step('alert');
			// go to alert page
			self::go_to('alert');

		}
		else
		{
			\lib\notif::error(T_("Please enter the username"));
			return false;
		}
	}
}
?>