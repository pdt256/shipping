<?php
namespace pdt256\Shipping;

class ShipmentTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $shipment = new Shipment;
        $shipment->addPackage(new Package);

        $shipment->setFromIsResidential(false);
        $shipment->setFromPostalCode('90401');
        $shipment->setFromCountryCode('US');
        $shipment->setFromStateProvinceCode('CA');

        $shipment->setToIsResidential(true);
        $shipment->setToPostalCode('90210');
        $shipment->setToCountryCode('US');

        $this->assertTrue($shipment->getPackages()[0] instanceof Package);
        $this->assertEquals(1, $shipment->packageCount());

        $this->assertFalse($shipment->getFromIsResidential());
        $this->assertEquals('90401', $shipment->getFromPostalCode());
        $this->assertEquals('US', $shipment->getFromCountryCode());
        $this->assertEquals('CA', $shipment->getFromStateProvinceCode());

        $this->assertTrue($shipment->getToIsResidential());
        $this->assertEquals('90210', $shipment->getToPostalCode());
        $this->assertEquals('US', $shipment->getToCountryCode());
    }
}
