<?php
namespace lib;

trait mvc
{
	public $Methods = [];


	/**
	 * inject
	 *
	 * @param      <type>  $_name  The name
	 * @param      <type>  $_args  The arguments
	 */
	public function inject($_name, $_args)
	{
		preg_match("/^((before|after)_)?(.+)$/", $_name, $event);
		$name    = $event[3];
		$event   = empty($event[2]) ? 'edit' : $event[2];
		$closure = $_args[0];

		if(!array_key_exists($name, $this->Methods))
		{
			$this->Methods[$name] = [];
		}

		if(!array_key_exists($event, $this->Methods[$name]))
		{
			$this->Methods[$name][$event] = [];
		}
		$bound = @$closure->bindTo($this);

		if($bound)
		{
			array_push($this->Methods[$name][$event], $bound);
		}
	}



	/**
	 * __call
	 *
	 * @param      <type>  $_name  The name
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function __call($_name, $_args)
	{
		$black = array("_construct", "corridor", "config");

		if(method_exists($this, '_call_corridor') && method_exists($this, '_call') && $value = $this->_call_corridor($_name, $_args))
		{
			return $this->_call($_name, $_args, $value);
		}

		elseif(isset($this->Methods[$_name]))
		{
			return $this->mvc_inject_finder($_name, $_args, $_name);
		}
		elseif(preg_match("#^inject_((after_|before_)?.+)$#Ui", $_name, $inject))
		{
			return $this->inject($inject[1], $_args);
		}
		elseif(preg_match("#^i(.*)$#Ui", $_name, $icall))
		{
			return $this->mvc_inject_finder($_name, $_args, $icall[1]);
		}
		elseif(method_exists($this->controller, $_name) && !preg_grep("/^{$_name}$/", $black))
		{
			return call_user_func_array(array($this->controller, $_name), $_args);
		}

		\lib\header::status(500, get_called_class()."()->$_name()");
	}


	/**
	 * mvc finder inject
	 *
	 * @param      <type>  $_name  The name
	 * @param      <type>  $_args  The arguments
	 * @param      <type>  $_call  The call
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function mvc_inject_finder($_name, $_args, $_call)
	{
		$return = false;
		$method_exists = array_key_exists($_call, $this->Methods);
		$call_method_exists = method_exists($this, $_call);
		if(!$method_exists && !$call_method_exists)
		{
			\lib\header::status(500, get_called_class()."()->$_name()");
		}
		if($method_exists && array_key_exists('before', $this->Methods[$_call]))
		{
			foreach ($this->Methods[$_call]['before'] as $key => $before_method)
			{
				if($before_method)
				{
					$before_method(...$_args);
				}
			}
		}

		if($method_exists && array_key_exists('edit', $this->Methods[$_call]))
		{
			$edit_method = end($this->Methods[$_call]['edit']);
			$return = $edit_method(...$_args);
		}
		else
		{
			$return = call_user_func_array(array($this, $_call), $_args);
		}

		if($method_exists && array_key_exists('after', $this->Methods[$_call]))
		{
			foreach ($this->Methods[$_call]['after'] as $key => $after_method)
			{
				if($after_method)
				{
					$after_method(...$_args);
				}
			}
		}
		return $return;
	}
}
?>