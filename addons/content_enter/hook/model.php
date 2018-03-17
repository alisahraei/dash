<?php
namespace addons\content_enter\hook;


class model extends \mvc\model
{

	/**
	 * the user id
	 *
	 * @var        integer
	 */
	public $user_id = null;

	public function delete_chat_id()
	{
		if($this->check_api_key())
		{
			$telegram_id = \lib\utility::request("telegramid");


			if(!$telegram_id)
			{
				\lib\notif::error(T_("Telegram id not found"), 'telegram_id', 'post');
				return false;
			}

			if(!is_numeric($telegram_id))
			{
				\lib\notif::error(T_("Invalid telegram id"), 'telegram_id', 'post');
				return false;
			}

			$where =
			[
				'chatid' => $telegram_id,
				'limit'        => 1
			];

			$exist_chart_id = \lib\db\config::public_get('users', $where);

			$log_meta =
			[
				'meta' =>
					[
						'request'        => \lib\utility::request(),
						'record_chat_id' => $exist_chart_id,
					],
			];
			if(isset($exist_chart_id['id']))
			{
				$remove_all_this_chat_id = "UPDATE users SET chatid = NULL WHERE chatid = '$telegram_id' ";
				\lib\db::query($remove_all_this_chat_id);
				\lib\db\logs::set('enter:hook:remove:all:chat:id:by:delete:request', $exist_chart_id['id'], $log_meta);
			}
		}

		if(\lib\notif::$status)
		{
			\lib\notif::title(T_("Operation complete"));
		}
		else
		{
			\lib\notif::title(T_("Operation faild"));
		}
	}

	/**
	 * get user data
	 */
	public function post_user()
	{
		if($this->check_api_key())
		{
			$this->config_user_data();
		}

		if(\lib\notif::$status)
		{
			\lib\notif::title(T_("Operation complete"));
		}
		else
		{
			\lib\notif::title(T_("Operation faild"));
		}
	}


	/**
	 * check api key and set the user id
	 */
	public function check_api_key()
	{
		$authorization = \lib\utility::header("authorization");

		if(!$authorization)
		{
			$authorization = \lib\utility::header("Authorization");
		}

		if(!$authorization)
		{
			\lib\notif::error(T_('Authorization not found'), 'authorization', 'access');
			return false;
		}

		if($authorization === \lib\option::config('enter', 'telegram_hook'))
		{
			$this->authorization = $authorization;
			return true;
		}
		else
		{
			\lib\notif::error(T_('Invalid Authorization'), 'authorization', 'access');
			return false;
		}

	}


	/**
	 * the api telegram token
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function config_user_data()
	{
		$telegram_id = \lib\utility::request("tg_id");
		$first_name  = \lib\utility::request('tg_first_name');
		$last_name   = \lib\utility::request('tg_last_name');
		$username    = \lib\utility::request('tg_username');
		$started     = \lib\utility::request('tg_start');
		$ref         = \lib\utility::request('tg_ref');
		$mobile      = \lib\utility::request('tg_mobile');
		$mobile      = \lib\utility\filter::mobile($mobile);

		if(!$mobile)
		{
			\lib\notif::error(T_("Mobile is not set"), 'tg_mobile', 'post');
			return false;
		}

		if(!$telegram_id)
		{
			\lib\notif::error(T_("Telegram id not found"), 'telegram_id', 'post');
			return false;
		}

		if(!is_numeric($telegram_id))
		{
			\lib\notif::error(T_("Invalid telegram id"), 'telegram_id', 'post');
			return false;
		}

		$where =
		[
			'chatid' => $telegram_id,
			'limit'        => 1
		];

		$exist_chart_id = \lib\db\config::public_get('users', $where);

		$exist_mobile = \lib\db\users::get_by_mobile($mobile);


		$log_meta =
		[
			'meta' =>
				[
					'request'        => \lib\utility::request(),
					'record_mobile'  => $exist_mobile,
					'record_chat_id' => $exist_chart_id,
				],
		];

		if(!$exist_chart_id && !$exist_mobile)
		{
			// calc full_name of user
			$fullName = trim($first_name. ' '. $last_name);
			$fullName = \lib\utility\safe::safe($fullName, 'sqlinjection');

			if(mb_strlen($fullName) > 50)
			{
				$fullName = null;
			}

			$insert_user                = [];
			$insert_user['mobile']      = $mobile;
			$insert_user['displayname'] = $fullName;
			$insert_user['chatid']      = $telegram_id;
			$insert_user['datecreated'] = date("Y-m-d H:i:s");
			\lib\db\users::insert($insert_user);
			$this->user_id = \lib\db::insert_id();
			\lib\db\logs::set('enter:hook:signup:new', $exist_mobile['id'], $log_meta);

		}
		elseif($exist_chart_id && $exist_mobile)
		{
			if(isset($exist_chart_id['id']) && isset($exist_mobile['id']))
			{
				if(intval($exist_chart_id['id']) === intval($exist_mobile['id']))
				{
					$this->user_id = (int) $exist_mobile['id'];
				}
				else
				{
					$remove_all_this_chat_id = "UPDATE users SET chatid = NULL WHERE chatid = '$telegram_id' ";
					\lib\db::query($remove_all_this_chat_id);
					\lib\db\logs::set('enter:hook:remove:all:chat:id', $exist_mobile['id'], $log_meta);
					\lib\db\users::update(['chatid' => $telegram_id], $exist_mobile['id']);
					$this->user_id = (int) $exist_mobile['id'];
				}
			}
			else
			{
				\lib\notif::error(T_("System error 1"));
				return false;
			}
		}
		elseif($exist_chart_id && !$exist_mobile)
		{
			if(isset($exist_chart_id['id']))
			{
				if($mobile)
				{
					$remove_all_this_chat_id = "UPDATE users SET chatid = NULL WHERE chatid = '$telegram_id' ";

					\lib\db::query($remove_all_this_chat_id);

					\lib\db\logs::set('enter:hook:remove:all:chat:id', $exist_chart_id['id'], $log_meta);

					\lib\db\users::update(['mobile' => $mobile, 'chatid' => $telegram_id], $exist_chart_id['id']);

					\lib\db\logs::set('enter:hook:change:mobile', $exist_chart_id['id'], $log_meta);
				}
				$this->user_id = (int) $exist_chart_id['id'];
			}
			else
			{
				\lib\notif::error(T_("System error 2"));
				return false;
			}
		}
		elseif(!$exist_chart_id && $exist_mobile)
		{
			if(isset($exist_mobile['id']))
			{
				if($telegram_id)
				{
					\lib\db\users::update(['chatid' => $telegram_id], $exist_mobile['id']);
					\lib\db\logs::set('enter:hook:change:chat_id', $exist_mobile['id'], $log_meta);
				}
				$this->user_id = (int) $exist_mobile['id'];
			}
			else
			{
				\lib\notif::error(T_("System error 3"));
				return false;
			}
		}

	}


	/**
	 * save api log
	 *
	 * @param      boolean  $options  The options
	 */
	public function _processor($options = false)
	{
		// $log = [];

		// if(isset($_SERVER['REQUEST_URI']))
		// {
		// 	$log['url'] = $_SERVER['REQUEST_URI'];
		// }

		// if(isset($_SERVER['REQUEST_METHOD']))
		// {
		// 	$log['method'] = $_SERVER['REQUEST_METHOD'];
		// }

		// if(isset($_SERVER['REDIRECT_STATUS']))
		// {
		// 	$log['pagestatus'] = $_SERVER['REDIRECT_STATUS'];
		// }

		// $log['request']        = json_encode(\lib\utility::request(), JSON_UNESCAPED_UNICODE);
		// $log['\lib\notif']          = json_encode(\lib\notif::compile(), JSON_UNESCAPED_UNICODE);
		// $log['response']       = json_encode(\lib\notif::get_result(), JSON_UNESCAPED_UNICODE);
		// $log['requestheader']  = json_encode(\lib\utility::header(), JSON_UNESCAPED_UNICODE);
		// $log['responseheader'] = json_encode(apache_response_headers(), JSON_UNESCAPED_UNICODE);
		// $log['status']         = \lib\notif::$status;
		// $log['token']          = $this->authorization;
		// $log['user_id']        = $this->user_id;
		// $log['apikeyuserid']   = $this->parent_api_key_user_id;
		// $log['apikey']         = $this->parent_api_key;
		// $log['clientip']       = \lib\server::ip(true);
		// $log['visit_id']       = null;

		// $log                   = \lib\utility\safe::safe($log);

		// \lib\db\apilogs::insert($log);

		parent::_processor($options);
	}
}
?>