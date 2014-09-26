<?php
namespace pdt256\Shipping\RateRequest;

abstract class Adapter
{
	protected $curl_connect_timeout_ms = 1000; // milliseconds
	protected $curl_dl_timeout = 11; // seconds

	abstract public function execute($url, $data = NULL);
}
