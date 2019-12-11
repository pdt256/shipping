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
     * Make sure all necessary fields are set
     */
    abstract protected function validate();
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

    /**
     * @throws \LogicException
     * To be called from validate() when packages have to have 3 dimensions and weight
     */
    protected function validatePackages()
    {
        foreach ($this->shipment->getPackages() as $package) {
            Validator::checkIfNull($package->getWeight(), 'weight');
            Validator::checkIfNull($package->getLength(), 'length');
            Validator::checkIfNull($package->getHeight(), 'height');
            Validator::checkIfNull($package->getWidth(), 'width');
        }
    }
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

    public function setShipment($shipment)
    {
        $this->shipment = $shipment;
    }

    public function getShipment()
    {
        return $this->shipment;
    }

    public function setIsProduction($isProduction)
    {
        $this->isProduction = $isProduction;
    }

    public function getIsProduction()
    {
        return $this->isProduction;
    }

    public function getRates()
    {
        $this
            ->validate()
            ->prepare()
            ->execute()
            ->process()
            ->sortByCost();

        return array_values($this->rates);
    }

    protected function sortByCost()
    {
        uasort($this->rates, function ($a, $b) { return ($a->getCost() > $b->getCost());});
    }
}
