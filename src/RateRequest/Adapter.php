<?php
namespace pdt256\Shipping\RateRequest;

abstract class Adapter
{
    protected $curlConnectTimeoutInMilliseconds = 1000;
    protected $curlDownloadTimeoutInSeconds = 11;

    abstract public function execute($url, $data = null);
}
