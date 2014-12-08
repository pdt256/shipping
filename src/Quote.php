<?php namespace pdt256\Shipping;

use DateTime;

class Quote
{
    protected $code;
    protected $name;
    protected $cost;
    protected $transitTime;
    protected $deliveryEstimate;
    protected $carrier;

    public function __construct($carrier = null, $code = null, $name = null, $cost = null)
    {
        $this->setCarrier($carrier);
        $this->setCode($code);
        $this->setName($name);
        $this->setCost($cost);
    }

    /**
     * @return mixed
     */
    public function getCarrier()
    {
        return $this->carrier;
    }

    /**
     * @param mixed $carrier
     * @return $this
     */
    public function setCarrier($carrier)
    {
        $this->carrier = (string) $carrier;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = (string) $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Quoted cost of this service, in pennies
     *
     * @param int $cost
     * @return $this
     */
    public function setCost($cost)
    {
        $this->cost = (int) $cost;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransitTime()
    {
        return $this->transitTime;
    }

    /**
     * @param mixed $transitTime
     * @return $this
     */
    public function setTransitTime($transitTime)
    {
        $this->transitTime = $transitTime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryEstimate()
    {
        return $this->deliveryEstimate;
    }

    /**
     * @param DateTime $deliveryEstimate
     * @return $this
     */
    public function setDeliveryEstimate(DateTime $deliveryEstimate)
    {
        $this->deliveryEstimate = $deliveryEstimate;
        return $this;
    }
}
