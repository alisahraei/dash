<?php
namespace content_su\update;

class controller
{
	public static function routing()
	{
		$name         = \dash\request::get('git');
		if(!$name)
		{
			return;
		}

		$result = self::gitUpdate($name);
		if(is_array($result))
		{
			foreach ($result as $key => $value)
			{
				echo $value;
			}
		}
		\dash\code::boom();
	}


	public static function gitUpdate($_name, $_password = null)
	{
		$result   = [];
		// switch by name of repository
		switch ($_name)
		{
			case 'dash':
				$result[] = self::updateDash();
				break;

			case 'all':
				// pull dash
				$result[] = self::updateDash();

				// pull current project
				$_name = \dash\url::root();
				$result[] = "<h1>$_name <small>Current Project</small></h1>";
				$result[] = "<p>Project location is <b>". root. "</b></p>";
				$result[] =  \dash\utility\git::pull(root, false, $_password);
				break;

			case '':
				break;

			default:
				$result[] =  \dash\utility\git::pull(root, false, $_password);

				// return;
				break;
		}
		return $result;
	}



	public static function updateDash()
	{
		$dashLocation = null;
		// check dash location
		if(is_dir(root. 'dash'))
		{
			$dashLocation = '../dash';
		}
		elseif(is_dir(root. '../dash'))
		{
			$dashLocation = '../../dash';
		}

		return "<h1>Dash</h1>". \dash\utility\git::pull($dashLocation);
	}

}
?>