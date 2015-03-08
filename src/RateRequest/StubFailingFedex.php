<?php
namespace pdt256\Shipping\RateRequest;

class StubFailingFedex extends StubFedex
{
    public function execute($url, $data = null)
    {
        if ($this->artificialDelay > 0) {
            sleep($this->artificialDelay);
        }

        return file_get_contents(__DIR__ . '/FedexErrorResponse.xml');
    }
}
