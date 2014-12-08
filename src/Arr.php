<?php
namespace pdt256\Shipping;

class Arr
{
    public static function get($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}
