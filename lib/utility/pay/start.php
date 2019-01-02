<?php
namespace dash\utility\pay;


class start
{
	public static function bank($_args)
	{
		$default =
		[
			'bank'  => null,
			'token' => null,
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$token = $_args['token'];
		if(!$token)
		{
			\dash\notif::error(T_("Invalid token"));
			return self::theend();
		}

        \dash\utility\pay\setting::load_token($token);

        $bank = \dash\utility\pay\setting::get_bank();
        if(!$bank)
		{
			\dash\notif::error(T_("Invalid bank"));
			return self::theend();
		}

		$_args = array_merge($default, $_args);

        if(is_callable(["\\dash\\utility\\pay\\api\\$bank\\go", "bank"]))
        {
            ("\\dash\\utility\\pay\\api\\$bank\\go")::bank();
        }
        else
        {
            \dash\notif::error(T_("This payment is not supported in this system"));
        }

        return self::theend();
	}


	public static function site($_args)
	{
		if(!is_array($_args))
		{
			$_args = [];
		}

		if(!array_key_exists('get_token', $_args))
		{
			$_args['get_token'] = false;
		}

		return self::token($_args);
	}

	public static function token($_args)
	{
		$default =
		[
			'amount'          => 0,
			'bank'            => null,
			'title'           => null,
			'unit'            => null,
			'type'            => null,
			'fromurl'         => null, // from url
			'turn_back'       => null, // back to this url
			'msg_go'          => null,
			'msg_back_ok'     => null,
			'msg_back_failed' => null,
			'auto_go'         => false,
			'auto_back'       => false,
			'user_id'         => null,
			'get_token'       => false,
			'other_field'     => [],
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default, $_args);

		$user_id = $_args['user_id'];

        if(!$user_id || !is_numeric($user_id))
        {
            if($user_id === 'unverify')
            {
                // pay as undefined user in some project
            }
            else
            {
            	\dash\notif::error(T_("Invalid user"));
                return self::theend($_args);
            }
        }

        if(is_numeric($_args['amount']) && $_args['amount'] > 0 && $_args['amount'] == round($_args['amount'], 0))
        {
            // no problem to continue!
        }
        else
        {
            \dash\notif::error(T_("Invalid amount"));
            return self::theend($_args);
        }

        $bank = mb_strtolower($_args['bank']);

        if(!$bank)
        {
        	if(!$_args['get_token'] || $_args['auto_go'])
        	{
	            \dash\notif::error(T_("Please select a bank port"), 'payment');
	            return self::theend($_args);
        	}
        }

        if($bank)
        {
	        if(is_callable(["\\dash\\utility\\payment\\pay\\$bank", $bank]))
	        {
	            return self::generate_token($_args);
	        }
	        else
	        {
	            \dash\notif::error(T_("This payment is not supported in this system"));
	            return self::theend($_args);
	        }
        }

	}

	private static function theend($_args = [])
	{
		if(isset($_args['get_token']) && $_args['get_token'])
		{
			\dash\code::jsonBoom(\dash\notif::get());
		}
		else
		{
			return false;
		}
	}


	private static function generate_token($_args)
	{
		$payment_response =
		[
			'fromurl'         => $_args['fromurl'],
			'turn_back'       => $_args['turn_back'],
			'msg_go'          => $_args['msg_go'],
			'msg_back_ok'     => $_args['msg_back_ok'],
			'msg_back_failed' => $_args['msg_back_failed'],
			'auto_back'       => $_args['auto_back'],
			'auto_go'         => $_args['auto_go'],
			'get_token'       => $_args['get_token'],
		];

		$payment_response = json_encode($payment_response, JSON_UNESCAPED_UNICODE);

		$insert_transaction =
		[
			'caller'           => 'payment',
			'title'            => $_args['title'] ? $_args['title'] : T_("Pay whith :bank",['bank' => T_(ucfirst($_args['bank']))]),
			'unit'             => $_args['unit'] ? $_args['unit'] : 'toman',
			'type'             => $_args['type'] ? $_args['type'] : 'money',
			'plus'             => $_args['amount'],
			'amount_request'   => $_args['amount'],
			'payment'          => mb_strtolower($_args['bank']),
			'user_id'          => $_args['user_id'],
			'other_field'      => $_args['other_field'],
			'payment_response' => $payment_response,
		];

		$token = json_encode($insert_transaction);
		$token .= (string) time();
		$token .= (string) rand(1,9999);
		$token .= (string) rand(1,9999);
		$token .= (string) rand(1,9999);
		$token = md5($token);

		$insert_transaction['condition'] = 'request';
		$insert_transaction['token']     = $token;

		$result = \dash\utility\pay\transactions::start($insert_transaction);

		if(!$result)
		{
			return self::theend($_args);
		}

		$url = \dash\url::kingdom(). '/hook/pay/'. $token;

		if($_args['get_token'])
		{
			$detail =
			[
				'token' => $token,
				'url'   => $url,
			];

			\dash\notif::result($detail);
			\dash\code::jsonBoom(\dash\notif::get());
		}
		else
		{
			\dash\redirect::to($url);
		}

	}

}
?>