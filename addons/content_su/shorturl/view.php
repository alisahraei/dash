<?php
namespace content_su\shorturl;


class view
{
	public static function config()
	{
		\dash\log::set('codingView');
		$val = \dash\request::get('val');
		if($val)
		{
			\dash\data::valEncode(\dash\coding::decode($val));
			\dash\data::valDecode(\dash\coding::encode($val));
		}

	}
}
?>