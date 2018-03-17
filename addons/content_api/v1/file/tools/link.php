<?php
namespace addons\content_api\v1\file\tools;


trait link
{
	use check;

	public function upload_file($_options = [])
	{
		\lib\notif::title(T_("Can not upload file"));

		$default_options =
		[
			'upload_name' => \lib\utility::request('upload_name'),
			'url'         => null,
			'debug'       => true,
		];

		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_options, $_options);

		if(\lib\utility::request('url') && !$_options['url'])
		{
			$_options['url'] = \lib\utility::request('url');
		}

		$file_path = false;

		if($_options['url'])
		{
			$file_path = true;
		}
		elseif(!\lib\utility::files($_options['upload_name']))
		{
			return \lib\notif::error(T_("Unable to upload, because of selected upload name"), 'upload_name', 'arguments');
		}

		$ready_upload            = [];
		$ready_upload['user_id'] = $this->user_id;
		$ready_upload['debug']   = $_options['debug'];

		if($file_path)
		{
			$ready_upload['file_path'] = $_options['url'];
		}
		else
		{
			$ready_upload['upload_name'] = $_options['upload_name'];
		}

		// if(\lib\permission::access('admin:admin:view', null, $this->user_id))
		// {
		// 	$ready_upload['status'] = 'publish';
		// }
		// else
		// {
		// 	$ready_upload['status'] = 'draft';
		// }

		$ready_upload['status'] = 'publish';

		$ready_upload['user_size_remaining'] = self::remaining($this->user_id);

		// upload::$extentions = ['png', 'jpeg', 'jpg'];

		$upload      = \lib\utility\upload::upload($ready_upload);

		if(!\lib\notif::$status)
		{
			return false;
		}

		$file_detail = \lib\temp::get('upload');
		$file_id     = null;

		if(isset($file_detail['size']))
		{
			self::user_size_plus($this->user_id, $file_detail['size']);
		}

		if(isset($file_detail['id']) && is_numeric($file_detail['id']))
		{
			$file_id = $file_detail['id'];
		}
		else
		{
			return \lib\notif::error(T_("Can not upload file. undefined error"));
		}

		$file_id_code = null;

		if($file_id)
		{
			$file_id_code = utility\shortURL::encode($file_id);
		}

		$url = null;

		if(isset($file_detail['url']))
		{
			$url = \lib\url::site(). '/'. $file_detail['url'];
		}

		\lib\notif::title(T_("File upload completed"));
		return ['code' => $file_id_code, 'url' => $url];
	}
}

?>