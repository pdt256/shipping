<?php
namespace pdt256\Shipping\RateRequest;

abstract class Adapter
{
    protected $curlConnectTimeoutInMilliseconds = 1500;
    protected $curlDownloadTimeoutInSeconds = 50;

    abstract public function execute($url, $data = null);
}
