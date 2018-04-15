<?php
namespace content_enter\payment;


class controller
{
	public static function routing()
	{
		$url             = \dash\url::directory();

		$url_type        = \dash\url::module();
		$payment         = \dash\url::child();

		$args            = [];
		$args['get']     = \dash\request::get();
		$args['post']    = \dash\request::post();
		$args['request'] = \dash\safe::safe($_REQUEST);

		switch ($url_type)
		{
			case 'verify':
				if(method_exists("\\dash\\utility\\payment\\verify", $payment))
				{
					\dash\utility\payment\verify::$payment($args);
					return;
				}
				break;

			default:
				\dash\header::status(404, T_("Invalid payment type"));
				break;
		}
	}
}
?>