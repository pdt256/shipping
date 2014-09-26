<?php
namespace pdt256\Shipping\RateRequest;

class Get extends Adapter
{
	public function execute($url, $data = NULL)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->curl_connect_timeout_ms);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_dl_timeout);
		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}
}
