<?php
namespace content_api\v6\smile;


class controller
{
	public static function routing()
	{
		if(\dash\url::subchild())
		{
			\content_api\v6::no(404);
		}

		$smile = self::smile();

		\content_api\v6::bye($smile);
	}


	private static function smile()
	{
		$smile     = [];

		$usercode = \dash\header::get('usercode');


		if(!$usercode)
		{
			return false;
		}

		$id = \dash\coding::decode($usercode);

		if(!$id)
		{
			return false;
		}

		$notif_count = \dash\app\log::my_notif_count($id);

		$smile =
		[
			'notif_new'   => $notif_count ? true : false,
			'notif_count' => $notif_count,
		];

		return $smile;
	}
}
?>