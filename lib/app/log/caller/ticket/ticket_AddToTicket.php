<?php
namespace dash\app\log\caller\ticket;

class ticket_AddToTicket
{
	public static function site($_args = [])
	{
		$code = \dash\app\log\support_tools::code($_args);

		$result              = [];
		$result['title']     = T_("Add message to ticket");
		$result['icon']      = 'life-ring';
		$result['cat']       = T_("Support");
		$result['iconClass'] = 'fc-blue';

		$is_me = \dash\app\log\msg::is_me($_args);

		if(!$is_me)
		{
			$excerpt  = '<span class="fc-green">'.\dash\app\log\msg::displayname($_args). '</span> ';
			$excerpt .= T_("added new message to ticket");
		}
		else
		{
			$excerpt = T_("You added new message to ticket");
		}

		$excerpt .= ' ';
		$excerpt .=	'<a href="'.\dash\url::kingdom(). '/!'. $code. '">';
		$excerpt .= T_("Show ticket");
		$excerpt .= ' ';
		$excerpt .= \dash\utility\human::fitNumber($code, false);
		$excerpt .= '</a>';

		$result['txt'] = $excerpt;

		return $result;
	}


	public static function send_to()
	{
		return ['supportTicketAnswer'];
	}

	public static function expire()
	{
		return date("Y-m-d H:i:s", strtotime("+100 days"));
	}


	public static function is_notif()
	{
		return true;
	}

	public static function telegram()
	{
		return true;
	}


	public static function telegram_text($_args, $_chat_id)
	{
		$load = \dash\app\log\support_tools::load($_args);
		$plus = \dash\app\log\support_tools::plus($_args);

		$code = isset($_args['code']) ? $_args['code'] : null;

		$tg_msg = '';
		$tg_msg .= "🆔#Ticket".$code;
		$tg_msg .= " ⚔". $plus;
		$tg_msg .= "\n🗣 ". \dash\log::from_name(). " #user". \dash\log::from_id();
		$tg_msg .= "\n—————\n📬 ";

		$title   = isset($load['title']) ? $load['title'] : null;
		$content = isset($load['content']) ? $load['content'] : null;
		$file    = isset($load['file']) ? $load['file'] : null;

		if($title)
		{
			$tg_msg .= $title . "\n";
		}

		if($content)
		{
			$content = \dash\app\log\msg::myStripTags($content);
			$tg_msg .= $content . "\n";
		}

		if($file)
		{
			$tg_msg .= $file . "\n";
		}

		$tg_msg .= "\n⏳ ". \dash\datetime::fit(date("Y-m-d H:i:s"), true);

		$tg                 = [];
		$tg['chat_id']      = $_chat_id;
		$tg['text']         = $tg_msg;
		$tg['reply_markup'] = \dash\app\log\support_tools::tg_btn($code);

		// $tg = json_encode($tg, JSON_UNESCAPED_UNICODE);

		return $tg;
	}
}
?>