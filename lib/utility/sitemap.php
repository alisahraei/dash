<?php
namespace dash\utility;


class sitemap
{
	private static $show_result      = [];
	private static $current_language = null;
	private static $default_language = null;


	public static function create()
	{
		// set log to create new sitemap
		\dash\log::set('sitemapGenerate');

		// make new show_result
		self::$show_result = [];

		self::$current_language = \dash\language::current();
		self::$default_language = \dash\language::primary();

		// create sitemap for each language
		self::static_page();

		self::add_sitemap_item('posts', 		\dash\db\sitemap::posts(), 		'0.8', 'daily', 	'publishdate');
		self::add_sitemap_item('pages', 		\dash\db\sitemap::pages(), 		'0.8', 'daily', 	'publishdate');
		self::add_sitemap_item('mags',  		\dash\db\sitemap::mags(), 		'0.8', 'daily', 	'publishdate');
		self::add_sitemap_item('attachments',  	\dash\db\sitemap::attachments(),'0.2', 'weekly', 	'publishdate');
		self::add_sitemap_item('help_center',  	\dash\db\sitemap::help_center(),'0.3', 'monthly', 	'publishdate');
		self::add_sitemap_item('other',  		\dash\db\sitemap::other(), 		'0.5', 'weekly', 	'publishdate');
		self::add_sitemap_item('mag_tag',  		\dash\db\sitemap::mag_tag(), 	'0.5', 'weekly', 	'datecreated');
		self::add_sitemap_item('help_tag',  	\dash\db\sitemap::help_tag(), 	'0.5', 'weekly', 	'datecreated');
		self::add_sitemap_item('mag_cat',  		\dash\db\sitemap::mag_cat(), 	'0.5', 'weekly', 	'datecreated');
		self::add_sitemap_item('cats',  		\dash\db\sitemap::cats(), 		'0.5', 'weekly', 	'datecreated');
		self::add_sitemap_item('tags',  		\dash\db\sitemap::tags(), 		'0.5', 'weekly', 	'datecreated');

		$site_url = \dash\url::site().'/';
		$sitemap  = new \dash\utility\sitemap_generator($site_url , root.'public_html/', 'sitemap' );
		self::current_project($sitemap);
		$sitemap->createSitemapIndex();

		return self::$show_result;

	}

	// can call from current project to show result
	// \lib\sitemap::create();
	public static function show_result($_key, $_count)
	{
		self::$show_result[$_key] = $_count;
	}


	private static function static_page()
	{
		$site_url = \dash\url::site().'/';
		$sitemap  = new \dash\utility\sitemap_generator($site_url , root.'public_html/', 'sitemap' );
		$sitemap->setFilename('static_page');

		$static_page =
		[
			'about'     =>  ['0.6', 'weekly'],
			'pricing'   =>  ['0.6', 'weekly'],
			'terms'     =>  ['0.4', 'weekly'],
			'privacy'   =>  ['0.4', 'weekly'],
			'changelog' =>  ['0.5', 'daily'],
			'contact'   =>  ['0.6', 'weekly'],
			'logo'      =>  ['0.8', 'monthly'],
		];

		// add list of static pages
		$sitemap->addItem('', '1', 'daily');

		foreach ($static_page as $key => $value)
		{
			$sitemap->addItem($key, $value[0], $value[1]);
		}

		// PERSIAN
		// add all language static page automatically
		// we must detect pages automatically and list static pages here
		$lang_data        = \dash\language::all();

		if(is_array($lang_data))
		{
			foreach ($lang_data as $myLang => $value)
			{
				if($myLang != self::$current_language)
				{
					foreach ($static_page as $key => $value)
					{
						$sitemap->addItem($myLang. '/'. $key, $value[0], $value[1]);
					}
				}
			}
		}

		self::show_result('static_page', count($static_page));

		$sitemap->createSitemapIndex();
	}


	private static function add_sitemap_item($_type, $_array, $_priority, $_changefreq, $_lastmod_field)
	{
		if(!is_array($_array) || !$_array)
		{
			self::show_result($_type, 0);
			return;
		}

		$site_url = \dash\url::site().'/';
		$sitemap  = new \dash\utility\sitemap_generator($site_url , root.'public_html/', 'sitemap' );

		$sitemap->setFilename($_type);

		foreach ($_array as $row)
		{
			$myUrl = $row['url'];
			if($row['language'] && $row['language'] !== self::$default_language)
			{
				$myUrl = $row['language'].'/'. $myUrl;
			}

			$sitemap->addItem($myUrl, $_priority, $_changefreq, $row[$_lastmod_field]);
		}


		$sitemap->createSitemapIndex();

		self::show_result($_type, count($_array));
	}


	private static function current_project(&$sitemap)
	{
		if(is_callable(['\\lib\\sitemap', 'create']))
		{
			\lib\sitemap::create($sitemap);
		}
	}
}
?>