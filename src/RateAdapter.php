<?php
namespace pdt256\Shipping;

use Exception;

abstract class RateAdapter
{
    protected $isProduction;

    /** @var Shipment */
    protected $shipment;
    protected $data;
    protected $response;
    protected $rates;

    /** @var  @var RateRequest\Adapter */
    protected $rateRequest;

    /**
     * Prepare XML
     */
    abstract protected function prepare();

    /**
     * Curl Request
     */
    abstract protected function execute();

    /**
     * Convert to shipping rates array
     */
    abstract protected function process();

    public function __construct($options = [])
    {
        $this->rates = [];

        $this->isProduction = (bool) Arr::get($options, 'prod', false);
        $this->shipment = Arr::get($options, 'shipment');
    }

    public function setRequestAdapter(RateRequest\Adapter $rateRequest)
    {
        $this->rateRequest = $rateRequest;
    }

    public function getRates()
    {
        $this
            ->prepare()
            ->execute()
            ->process()
            ->sortByCost();

        return array_values($this->rates);
    }

    protected function sortByCost()
    {
        uasort($this->rates, create_function('$a, $b', 'return ($a->getCost() > $b->getCost());'));
    }
}
