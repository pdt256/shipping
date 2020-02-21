<?php
namespace pdt256\Shipping\RateRequest;

class StubIncorrectResponseAdapter extends Adapter
{
    protected $artificialDelay = 0;

    public function __construct($artificial_delay = 0)
    {
        $this->artificialDelay = $artificial_delay;
    }

    public function execute($url, $data = null)
    {
        if ($this->artificialDelay > 0) {
            sleep($this->artificialDelay);
        }

        return '<html/>';
    }
}
