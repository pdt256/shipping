<?php
namespace pdt256\Shipping\RateRequest;

class StubFedex extends Adapter
{
    private $artificialDelay = 0;

    public function __construct($artificial_delay = 0)
    {
        $this->artificialDelay = $artificial_delay;
    }

    public function execute($url, $data = null)
    {
        if ($this->artificialDelay > 0) {
            sleep($this->artificialDelay);
        }

        return file_get_contents(__DIR__ . '/FedexResponse.xml');
    }
}
