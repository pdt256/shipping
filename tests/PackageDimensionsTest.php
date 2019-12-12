<?php

namespace pdt256\Shipping;

use pdt256\Shipping\RateRequest\StubFedex;
use pdt256\Shipping\RateRequest\StubUPS;
use pdt256\Shipping\RateRequest\StubUSPS;
use PHPUnit\Framework\TestCase;

class PackageDimensionsValidationTrait extends TestCase
{
    protected function getNormalPackage()
    {
        $normalPackage = new Package();
        $normalPackage
        ->setHeight(10)
        ->setWidth(6)
        ->setLength(10)
        ->setWeight(5)
        ;
        return $normalPackage;
    }
    protected function getNoHeightPackage()
    {
        $noHeightPackage = new Package();
        $noHeightPackage
        ->setWidth(6)
        ->setLength(10)
        ->setWeight(5)
        ;
        return $noHeightPackage;
    }
    protected function getNoWidthPackage()
    {
        $noWidthPackage = new Package();
        $noWidthPackage
        ->setHeight(10)
        ->setLength(10)
        ->setWeight(5)
        ;
        return $noWidthPackage;
    }
    protected function getNoLengthPackage()
    {
        $noLengthPackage = new Package();
        $noLengthPackage
        ->setHeight(10)
        ->setWidth(6)
        ->setWeight(5)
        ;
        return $noLengthPackage;
    }
    protected function getNoWeightPackage()
    {
        $noWeightPackage = new Package();
        $noWeightPackage
        ->setHeight(10)
        ->setWidth(10)
        ->setWidth(6)
        ;
        return $noWeightPackage;
    }
    protected function getUSPSAdapter()
    {
        return  new USPS\Rate([
        'prod' => false,
        'username' => 'XXXX',
        'password' => 'XXXX',
        'requestAdapter' => new StubUSPS(),
        ]);
    }

    protected function getUPSAdapter()
    {
        return new UPS\Rate([
        'accessKey' => 'XXX',
        'userId' => 'XXX',
        'password' => 'XXX',
        'shipperNumber' => 'XXX',
        'prod' => false,
        'requestAdapter' => new StubUPS(),
        ]);
    }

    protected function getFedexAdapter()
    {
        return new Fedex\Rate([
        'prod' => false,
        'key' => 'XXX',
        'password' => 'XXX',
        'accountNumber' => 'XXX',
        'meterNumber' => 'XXX',
        'dropOffType' => 'BUSINESS_SERVICE_CENTER',
        'requestAdapter' => new StubFedex(),
        ]);
    }

    protected function validatePackage(Package $package, RateAdapter $adapter)
    {
        $shipment = new Shipment();
        $shipment->setFromStateProvinceCode('CA')
        ->setFromPostalCode('90401')
        ->setFromCountryCode('US')
        ->setFromIsResidential(true)
        ->setToPostalCode('78703')
        ->setToCountryCode('US')
        ->setToIsResidential(true)
         ->addPackage($package);
        $adapter->setShipment($shipment);
        $adapter->getRates();
    }

    public function testNormalUSPS()
    {
        $this->validatePackage($this->getNormalPackage(), $this->getUSPSAdapter());
    }
    /**
     * @expectedException \LogicException
     */
    public function testNoHeightPackageUSPS()
    {
        $this->validatePackage($this->getNoHeightPackage(), $this->getUSPSAdapter());
    }
    /**
     * @expectedException \LogicException
     */
    public function testNoLengthPackageUSPS()
    {
        $this->validatePackage($this->getNoLengthPackage(), $this->getUSPSAdapter());
    }
    /**
     * @expectedException \LogicException
     */
    public function testNoWidthPackageUSPS()
    {
        $this->validatePackage($this->getNoWidthPackage(), $this->getUSPSAdapter());
    }
    /**
     * @expectedException \LogicException
     */
    public function testNoWeightPackageUSPS()
    {
        $this->validatePackage($this->getNoWeightPackage(), $this->getUSPSAdapter());
    }


    public function testNormalUPS()
    {
        $this->validatePackage($this->getNormalPackage(), $this->getUPSAdapter());
    }
    /**
     * @expectedException \LogicException
     */
    public function testNoHeightPackageUPS()
    {
        $this->validatePackage($this->getNoHeightPackage(), $this->getUPSAdapter());
    }
    /**
     * @expectedException \LogicException
     */
    public function testNoLengthPackageUPS()
    {
        $this->validatePackage($this->getNoLengthPackage(), $this->getUPSAdapter());
    }
    /**
     * @expectedException \LogicException
     */
    public function testNoWidthPackageUPS()
    {
        $this->validatePackage($this->getNoWidthPackage(), $this->getUPSAdapter());
    }
    /**
     * @expectedException \LogicException
     */
    public function testNoWeightPackageUPS()
    {
        $this->validatePackage($this->getNoWeightPackage(), $this->getUPSAdapter());
    }


    public function testNormalFedex()
    {
        $this->validatePackage($this->getNormalPackage(), $this->getFedexAdapter());
    }
    /**
     * @expectedException \LogicException
     */
    public function testNoHeightPackageFedex()
    {
        $this->validatePackage($this->getNoHeightPackage(), $this->getFedexAdapter());
    }
    /**
     * @expectedException \LogicException
     */
    public function testNoLengthPackageFedex()
    {
        $this->validatePackage($this->getNoLengthPackage(), $this->getFedexAdapter());
    }
    /**
     * @expectedException \LogicException
     */
    public function testNoWidthPackageFedex()
    {
        $this->validatePackage($this->getNoWidthPackage(), $this->getFedexAdapter());
    }
    /**
     * @expectedException \LogicException
     */
    public function testNoWeightPackageFedex()
    {
        $this->validatePackage($this->getNoWeightPackage(), $this->getFedexAdapter());
    }
}
