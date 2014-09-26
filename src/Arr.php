<?php
namespace pdt256\Shipping;

class Arr
{
	public static function get($array, $key, $default = NULL)
	{
		return isset($array[$key]) ? $array[$key] : $default;
	}
}
