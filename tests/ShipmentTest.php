<?php
namespace pdt256\Shipping;

use PHPUnit\Framework\TestCase;

class ShipmentTest extends TestCase
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

    private function getShipment()
    {
        $shipment = new Shipment;
        $shipment
            ->setFromIsResidential(false)
            ->setFromStateProvinceCode('IN')
            ->setFromPostalCode('46205')
            ->setFromCountryCode('US')
            ->setToIsResidential(true)
            ->setToPostalCode('20101')
            ->setToCountryCode('US');

        $package = new Package;
        $package
            ->setLength(12)
            ->setWidth(4)
            ->setHeight(3)
            ->setWeight(3);

        $shipment->addPackage($package);

        return $shipment;
    }

    public function xtestUPSStubForReadme()
    {
        $shipment = $this->getShipment();

        $ups = new UPS\Rate([
            'prod'           => false,
            'accessKey'      => 'XXXX',
            'userId'         => 'XXXX',
            'password'       => 'XXXX',
            'shipperNumber'  => 'XXXX',
            'shipment'       => $shipment,
            'approvedCodes'  => [
                '03', // 1-5 business days
                '02', // 2 business days
                '01', // next business day 10:30am
                '13', // next business day by 3pm
                '14', // next business day by 8am
            ],
            'requestAdapter' => new RateRequest\StubUPS(),
        ]);

        $rates = $ups->getRates();
        var_export($rates);
    }

    public function xtestUSPSStubForReadme()
    {
        $shipment = $this->getShipment();

        $usps = new USPS\Rate([
            'prod'     => false,
            'username' => 'XXXX',
            'password' => 'XXXX',
            'shipment' => $shipment,
            'approvedCodes'  => [
                '1', // 1-3 business days
                '4', // 2-8 business days
            ],
            'requestAdapter' => new RateRequest\StubUSPS(),
        ]);

        $rates = $usps->getRates();
        var_export($rates);
    }

    public function xtestFedexStubForReadme()
    {
        $shipment = $this->getShipment();

        $fedex = new Fedex\Rate([
            'prod'           => false,
            'key'            => 'XXXX',
            'password'       => 'XXXX',
            'accountNumber' => 'XXXX',
            'meterNumber'   => 'XXXX',
            'dropOffType'  => 'BUSINESS_SERVICE_CENTER',
            'shipment'       => $shipment,
            'approvedCodes'  => [
                'FEDEX_EXPRESS_SAVER',  // 1-3 business days
                'FEDEX_GROUND',         // 1-5 business days
                'GROUND_HOME_DELIVERY', // 1-5 business days
                'FEDEX_2_DAY',          // 2 business days
                'STANDARD_OVERNIGHT',   // overnight
            ],
            'requestAdapter' => new RateRequest\StubFedex(),
        ]);

        $rates = $fedex->getRates();
        var_export($rates);
    }
}
