<?php
namespace dash\db;


class sitemap
{


	public static function posts()
	{
		$query  = "SELECT posts.url, posts.language, posts.publishdate FROM posts WHERE posts.type = 'post' AND posts.status = 'publish' ORDER BY posts.id DESC ";
		$result = \dash\db::get($query);
		return $result;
	}


	public static function pages()
	{
		$query  = "SELECT posts.url, posts.language, posts.publishdate FROM posts WHERE posts.type = 'page' AND posts.status = 'publish' ORDER BY posts.id DESC ";
		$result = \dash\db::get($query);
		return $result;
	}




	public static function help_center()
	{
		$query  = "SELECT posts.url, posts.language, posts.publishdate FROM posts WHERE posts.type = 'help' AND posts.status = 'publish' ORDER BY posts.id DESC ";
		$result = \dash\db::get($query);
		return $result;
	}


	public static function attachments()
	{
		$query  = "SELECT posts.url, posts.language, posts.publishdate FROM posts WHERE posts.type = 'attachment' AND posts.status = 'publish' ORDER BY posts.id DESC ";
		$result = \dash\db::get($query);
		return $result;
	}


	public static function cats()
	{
		$query  = "SELECT terms.url, terms.language, terms.datecreated FROM terms WHERE terms.type = 'cat' AND terms.status = 'enable' ORDER BY terms.id DESC ";
		$result = \dash\db::get($query);
		return $result;
	}


	public static function tags()
	{
		$query  = "SELECT terms.url, terms.language, terms.datecreated FROM terms WHERE terms.type = 'tag' AND terms.status = 'enable' ORDER BY terms.id DESC ";
		$result = \dash\db::get($query);
		return $result;
	}


	public static function other()
	{
		$query  = "SELECT posts.url, posts.language, posts.publishdate FROM posts WHERE posts.type NOT IN ('attachment','page','post', 'help') AND posts.status = 'publish' ORDER BY posts.id DESC ";
		$result = \dash\db::get($query);
		return $result;
	}




}
?>
