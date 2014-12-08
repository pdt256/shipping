<?php namespace pdt256\Shipping;

class Shipment
{
    /** @var Package[] */
    protected $packages = [];

    protected $from_postal_code;
    protected $from_country_code;
    protected $to_postal_code;
    protected $to_country_code;
    /** @var bool */
    protected $to_is_residential;
    /** @var bool */
    protected $from_is_residential;
    protected $from_state_province;

    /**
     * @param Package $package
     * @return $this
     */
    public function addPackage(Package $package)
    {
        $this->packages[] = $package;
        return $this;
    }

    /**
     * @return Package[]
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @return int
     */
    public function packageCount()
    {
        return count($this->getPackages());
    }

    /**
     * @return mixed
     */
    public function getFromPostalCode()
    {
        return $this->from_postal_code;
    }

    /**
     * @param mixed $from_postal_code
     * @return $this
     */
    public function setFromPostalCode($from_postal_code)
    {
        $this->from_postal_code = $from_postal_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromCountryCode()
    {
        return $this->from_country_code;
    }

    /**
     * @param mixed $from_country_code
     * @return $this
     */
    public function setFromCountryCode($from_country_code)
    {
        $this->from_country_code = $from_country_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToPostalCode()
    {
        return $this->to_postal_code;
    }

    /**
     * @param mixed $to_postal_code
     * @return $this
     */
    public function setToPostalCode($to_postal_code)
    {
        $this->to_postal_code = $to_postal_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToCountryCode()
    {
        return $this->to_country_code;
    }

    /**
     * @param mixed $to_country_code
     * @return $this
     */
    public function setToCountryCode($to_country_code)
    {
        $this->to_country_code = $to_country_code;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isToResidential()
    {
        return $this->to_is_residential;
    }

    /**
     * @param boolean $to_is_residential
     * @return $this
     */
    public function setToResidential($to_is_residential)
    {
        $this->to_is_residential = $to_is_residential;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFromResidential()
    {
        return $this->from_is_residential;
    }

    /**
     * @param $from_is_residential
     * @return $this
     */
    public function setFromResidential($from_is_residential)
    {
        $this->from_is_residential = $from_is_residential;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromStateProvinceCode()
    {
        return $this->from_state_province;
    }

    /**
     * @param $from_state_province
     * @return $this
     */
    public function setFromStateProvinceCode($from_state_province)
    {
        $this->from_state_province = $from_state_province;
        return $this;
    }
}
