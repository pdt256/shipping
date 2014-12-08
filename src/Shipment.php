<?php namespace pdt256\Shipping;

class Shipment
{
    /** @var Package[] */
    protected $packages = [];

    /** @var bool */
    protected $fromIsResidential;
    protected $fromPostalCode;
    protected $fromCountryCode;
    protected $fromStateProvince;

    /** @var bool */
    protected $toIsResidential;
    protected $toPostalCode;
    protected $toCountryCode;

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
        return $this->fromPostalCode;
    }

    /**
     * @param mixed $fromPostalCode
     * @return $this
     */
    public function setFromPostalCode($fromPostalCode)
    {
        $this->fromPostalCode = $fromPostalCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromCountryCode()
    {
        return $this->fromCountryCode;
    }

    /**
     * @param mixed $fromCountryCode
     * @return $this
     */
    public function setFromCountryCode($fromCountryCode)
    {
        $this->fromCountryCode = $fromCountryCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToPostalCode()
    {
        return $this->toPostalCode;
    }

    /**
     * @param mixed $toPostalCode
     * @return $this
     */
    public function setToPostalCode($toPostalCode)
    {
        $this->toPostalCode = $toPostalCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToCountryCode()
    {
        return $this->toCountryCode;
    }

    /**
     * @param mixed $toCountryCode
     * @return $this
     */
    public function setToCountryCode($toCountryCode)
    {
        $this->toCountryCode = $toCountryCode;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getToIsResidential()
    {
        return $this->toIsResidential;
    }

    /**
     * @param boolean $toIsResidential
     * @return $this
     */
    public function setToIsResidential($toIsResidential)
    {
        $this->toIsResidential = $toIsResidential;
        return $this;
    }

    /**
     * @return bool
     */
    public function getFromIsResidential()
    {
        return $this->fromIsResidential;
    }

    /**
     * @param $fromIsResidential
     * @return $this
     */
    public function setFromIsResidential($fromIsResidential)
    {
        $this->fromIsResidential = $fromIsResidential;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromStateProvinceCode()
    {
        return $this->fromStateProvince;
    }

    /**
     * @param $fromStateProvince
     * @return $this
     */
    public function setFromStateProvinceCode($fromStateProvince)
    {
        $this->fromStateProvince = $fromStateProvince;
        return $this;
    }
}
