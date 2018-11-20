<?php
namespace content_account\notification;

class view
{

	public static function config()
	{
		\dash\data::page_title(T_("Notifications"));
		\dash\data::page_desc(T_("Check your last messages."). ' '. T_("Maybe some messages need your action!"));
		\dash\data::page_pictogram('bell');

		$args =
		[
			'sort'  => \dash\request::get('sort'),
			'order' => \dash\request::get('order'),
		];

		if(!$args['order'])
		{
			$args['order'] = 'desc';
		}

		$args['notif'] = 1;
		$args['to']    = \dash\user::id();

		$search_string   = \dash\request::get('q');

		$sortLink  = \dash\app\sort::make_sortLink(\dash\app\log::$sort_field, \dash\url::this());
		$dataTable = \dash\app\log::list($search_string, $args);

		\dash\app\log::set_readdate($dataTable);

		\dash\data::sortLink($sortLink);
		\dash\data::dataTable($dataTable);

		$check_empty_datatable = $args;
		unset($check_empty_datatable['sort']);
		unset($check_empty_datatable['order']);
		unset($check_empty_datatable['notif']);
		unset($check_empty_datatable['to']);


		// set dataFilter
		$dataFilter = \dash\app\sort::createFilterMsg($search_string, $check_empty_datatable);
		\dash\data::dataFilter($dataFilter);
	}
}
?>