<?php
namespace dash\engine;

class view
{

	public static function variable()
	{
		// default display value
		\dash\data::display_mvc("includes/html/display-mvc.html");
		\dash\data::display_dash("includes/html/display-dash.html");
		\dash\data::display_enter("includes/html/display-enter.html");
		// add special pages to display array to use without name
		\dash\data::display_main("content/main/layout.html");
		\dash\data::display_account("content_account/home/layout.html");
		\dash\data::display_cpMain("content_cp/layout.html");

		\dash\data::display_pagination("includes/html/inc_pagination.html");
		\dash\data::display_hive("includes/html/inc_hive.html");
		\dash\data::display_spay("includes/html/inc_spay.html");

		// return all url detail
		\dash\data::url(\dash\url::all());

		// return all parameters and clean it
		\dash\data::requestGET(\dash\request::get());

		// ----- language variable
		\dash\data::lang_list(\dash\language::all(true));
		\dash\data::lang_current(\dash\language::current());
		\dash\data::lang_default(\dash\language::primary());

		// save all options to use in display
		\dash\data::options(\dash\option::config());

		\dash\data::page_title(null);
		\dash\data::page_desc(null);
		\dash\data::page_special(null);

		\dash\data::bodyclass(null);

		$user_detail = \dash\user::detail();
		\dash\data::user($user_detail);
		\dash\data::login($user_detail);

		// set detail of browser
		\dash\data::browser(\dash\utility\browserDetection::browser_detection('full_assoc'));
		\dash\data::visitor('not ready!');

		// define default value for global
		\dash\data::global_title(null);
		\dash\data::global_login(\dash\user::login());
		\dash\data::global_lang(\dash\language::current());
		\dash\data::global_direction(\dash\language::current('direction'));
		\dash\data::global_id(implode('_', \dash\url::dir()));
		if(!\dash\data::global_id() && \dash\url::module() === null)
		{
			\dash\data::global_id('home');
		}
		\dash\data::global_content(\dash\url::content());
		if(\dash\data::global_content() === null)
		{
			\dash\data::global_content('site');
		}

		\dash\data::dev(\dash\option::config('dev'));

		\dash\data::site_title(T_(\dash\option::config('site', 'title')));
		\dash\data::site_desc(T_(\dash\option::config('site', 'desc')));
		\dash\data::site_slogan(T_(\dash\option::config('site', 'slogan')));
		\dash\data::site_logo(\dash\url::site(). '/static/images/logo.png');
		// set custom logo
		if(\dash\option::config('site', 'logo'))
		{
			\dash\data::site_logo(\dash\url::site(). \dash\option::config('site', 'logo'));
		}

		// add service detail
		\dash\data::service_title(T_('Ermile'));
		\dash\data::service_desc(T_('As easy as ABC is our slogan!'). '<br>'. T_('If you are not finded a solution for your problem, call us.'));
		\dash\data::service_slogan(T_('Software Solution Designer'));
		\dash\data::service_logo(\dash\url::site(). '/static/siftal/images/logo/ermile.png');
		\dash\data::service_url('https://ermile.com');

		// toggle side bar
		if(\dash\user::sidebar() === null || \dash\user::sidebar() === true)
		{
			\dash\data::userToggleSidebar(true);
		}

		// if allow to use social then get social network account list
		if(\dash\option::social('status'))
		{
			\dash\data::social(\dash\option::social('list'));
			// create data of share url
			\dash\data::share_title(\dash\data::get('site', 'title'));
			\dash\data::share_desc(\dash\data::get('site', 'desc'));
			\dash\data::share_image(\dash\url::site(). '/static/images/logo.png');
			\dash\data::share_twitterCard('summary');
		}

		// define default value for include
		\dash\data::include_siftal(true);
		\dash\data::include_css(true);
		\dash\data::include_js(true);

		self::deadbrowserDetection();
	}


	/**
	 * set title for pages depending on condition
	 */
	public static function set_title()
	{
		if($page_title = \dash\data::page_title())
		{
			// set title of locations if exist in breadcrumb
			if(\dash\data::get('breadcrumb', $page_title))
			{
				$page_title = \dash\data::get('breadcrumb', $page_title);
			}
			// replace title of page
			if(!\dash\data::page_special())
			{
				$page_title = ucwords(str_replace('-', ' ', $page_title));
			}
			// for child page set the
			if(\dash\url::child() && \dash\url::subdomain() === 'cp')
			{
				$myModule = \dash\url::module();
				if(substr($myModule, -3) === 'ies')
				{
					$moduleName = substr($myModule, 0, -3).'y';
				}
				elseif(substr($myModule, -1) === 's')
				{
					$moduleName = substr($myModule, 0, -1);
				}
				else
				{
					$moduleName = $myModule;
				}

				$childName = \dash\url::child();
				if($childName)
				{
					$page_title = T_($childName).' '.T_($moduleName);
				}
			}

			// translate all title at last step
			$page_title = T_($page_title);
			$page_title_strip = strip_tags($page_title);

			\dash\data::page_title($page_title);

			if(\dash\data::page_special())
			{
				\dash\data::global_title($page_title_strip);
			}
			else
			{
				\dash\data::global_title($page_title_strip.' | '.T_(\dash\data::site_title()));
			}
		}
		else
		{
			\dash\data::global_title(T_(\dash\data::site_title()));
		}

		\dash\data::global_short_title(substr(\dash\data::global_title(), 0, strrpos(substr(\dash\data::global_title(), 0, 120), ' ')). '...');

		if(!\dash\data::page_desc())
		{
			\dash\data::page_desc(\dash\data::site_desc());
		}
	}


	public static function set_cms_titles()
	{
		if(!\dash\data::get('datarow'))
		{
			if(\dash\url::module() === 'blog')
			{
				\dash\data::page_title(T_("Latest news"));
			}
			self::set_title();
			return false;
		}

		// set title
		if(\dash\data::datarow_title())
		{
			\dash\data::page_title(\dash\data::datarow_title());
		}

		// set desc
		if(\dash\data::datarow_excerpt())
		{
			\dash\data::page_desc(\dash\data::datarow_excerpt());
		}
		elseif(\dash\data::datarow_content())
		{
			\dash\data::page_desc(\dash\utility\excerpt::extractRelevant(\dash\data::datarow_content()));
		}
		elseif(\dash\data::datarow_desc())
		{
			\dash\data::page_desc(\dash\utility\excerpt::extractRelevant(\dash\data::datarow_desc()));
		}

		// set new title
		self::set_title();
	}


	public static function deadbrowserDetection()
	{
		$currentBrowser = \dash\data::browser();
		$browsers = array(
			"chrome"  => 64.0,
			"firefox" => 60.0,
			"gecko"   => 60.0,
			"crios"   => 67.0,
			"msie"    => 11.0,
			"edge"    => 13,
			"opera"	  => 50.0,
			"safari"  => 534.57
		);

		if (isset($browsers[$currentBrowser['browser_name']]))
		{
			if($currentBrowser['browser_name'] == 'msie')
			{
				\dash\data::youAreDead(T_("You are using Internet Explorer."). ' '. T_('Really!!!'). ' '. T_('IE is DIE!'));
			}
			elseif ($currentBrowser['browser_math_number'] < $browsers[$currentBrowser['browser_name']])
			{
				$msg = T_("You need to update your :browser to new version.", ['browser' => $currentBrowser['browser_name']]). ' '. T_('The world is changing rapidly!');

				\dash\data::youAreDead($msg);
			}
			else
			{
				// $myMsg2 = T_("Hooray! You are using the latest version");
			}
		}
		else
		{
			\dash\data::youAreDead(T_("Please use famous browser to have better experience!"));
		}
	}


	public static function lastChanges()
	{
		if(\dash\data::include_adminPanel())
		{
			\dash\data::global_siftal(true);

			$txtDesc   = '';
			if(\dash\data::user_displayname())
			{
				$txtDesc .= "<b>". \dash\data::user_displayname(). "</b><br>";
			}
			if(\dash\data::user_mobile())
			{
				$txtDesc .= \dash\utility\human::fitNumber(\dash\data::user_mobile(), 'mobile12');
			}

			$txtFooter = '';
			$txtFooter .= '<div class="align-center">';
			// notification
			$txtFooter .= ' <a class="btn outline lg" href="'. \dash\url::kingdom(). '/account/notification" title="'. T_("Notifications"). '">'. '<i class="sf-bell"></i>'. '</a>';
			// profile
			$txtFooter .= ' <a class="btn outline lg" href="'. \dash\url::kingdom(). '/account/profile" title="'. T_("Profile"). '">'. '<i class="sf-user"></i>'. '</a>';
			// billing
			$txtFooter .= ' <a class="btn outline lg" href="'. \dash\url::kingdom(). '/account/billing" title="'. T_("Billing"). '">'. '<i class="sf-credit-card"></i>'. '</a>';
			// support
			$txtFooter .= ' <a class="btn outline lg" href="'. \dash\url::kingdom(). '/support" title="'. T_("Support center"). '">'. '<i class="sf-life-ring"></i>'. '</a>';
			// ticket
			$txtFooter .= ' <a class="btn outline lg" href="'. \dash\url::kingdom(). '/support/ticket" title="'. T_("Tickets"). '">'. '<i class="sf-question-circle"></i>'. '</a>';
			$txtFooter .= '</div>';

			\dash\data::userBadge_desc($txtDesc);
			\dash\data::userBadge_footer($txtFooter);
		}
	}

}
?>
