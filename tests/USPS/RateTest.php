<?php
namespace pdt256\Shipping\USPS;

use pdt256\Shipping\RateRequest\StubUSPS;
use pdt256\Shipping\Ship;
use pdt256\Shipping\Package;
use pdt256\Shipping\Shipment;
use pdt256\Shipping\Quote;

class RateTest extends \PHPUnit_Framework_TestCase
{
    /** @var Shipment */
    protected $shipment;

    protected $approvedCodes = [];

    public function setUp()
    {
        $ship = Ship::factory([
            'Standard Shipping' => [
                'usps' => [
                    '1' => '1-3 business days',
                    '4' => '2-8 business days',
                ],
            ],
        ]);

        $this->approvedCodes = $ship->getApprovedCodes('usps');

        $package = new Package;
        $package->setWeight(3)
            ->setWidth(9)
            ->setLength(9)
            ->setHeight(9);

        $this->shipment = new Shipment;
        $this->shipment->setFromStateProvinceCode('CA')
            ->setFromPostalCode('90401')
            ->setFromCountryCode('US')
            ->setToPostalCode('78703')
            ->setToCountryCode('US')
            ->setToIsResidential(true)
            ->addPackage($package);
    }

    public function testMockRates()
    {
        $rateAdapter = new Rate([
            'prod' => false,
            'username' => 'XXXX',
            'password' => 'XXXX',
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubUSPS,
        ]);

        $rates = $rateAdapter->getRates();

        $expected = [
            new Quote('usps', '4', 'Parcel Post', 1001),
            new Quote('usps', '1', 'Priority Mail', 1220),
        ];

        $this->assertEquals($expected, $rates);
    }

    public function testLiveRates()
    {
        if (getenv('USPS_USERNAME') === false) {
            $this->markTestSkipped('Live USPS credentials missing.');
        }

        $rateAdapter = new Rate([
            'prod' => false,
            'username' => getenv('USPS_USERNAME'),
            'password' => getenv('USPS_PASSWORD'),
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
        ]);

        $rates = $rateAdapter->getRates();

        $this->assertTrue(count($rates) > 0);
        $this->assertTrue($rates[0] instanceof Quote);
    }

    /**
     * @expectedException \LogicException
     */
    public function testMissingUserName()
    {
        $rateAdapter = new Rate([
            'prod' => false,
            'password' => 'XXXX',
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubUSPS,
        ]);

        $rateAdapter->getRates();
    }
    /**
     * @expectedException \LogicException
     */
    public function testMissingPassword()
    {
        $rateAdapter = new Rate([
            'prod' => false,
            'username' => 'XXX',
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubUSPS,
        ]);

        $rateAdapter->getRates();
    }
}
