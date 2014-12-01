<?php namespace pdt256\Shipping;

use DateTime;

class Quote
{
    protected $code;
    protected $name;
    protected $cost;
    protected $transit_time;
    protected $delivery_ts;
    protected $carrier;

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
        $this->carrier = $carrier;
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
        $this->code = $code;
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
        $this->name = $name;
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
        $this->cost = $cost;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransitTime()
    {
        return $this->transit_time;
    }

    /**
     * @param mixed $transit_time
     * @return $this
     */
    public function setTransitTime($transit_time)
    {
        $this->transit_time = $transit_time;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryEstimate()
    {
        return $this->delivery_ts;
    }

    /**
     * @param DateTime $estimate
     * @return $this
     */
    public function setDeliveryEstimate(DateTime $estimate)
    {
        $this->delivery_ts = $estimate;
        return $this;
    }
}
