<?php
namespace pdt256\Shipping\UPS;

use pdt256\Shipping\RateRequest\StubUPS;
use pdt256\Shipping\Ship;
use pdt256\Shipping\Package;
use pdt256\Shipping\Shipment;
use pdt256\Shipping\Quote;
use PHPUnit\Framework\TestCase;

class RateTest extends TestCase
{
    /** @var Shipment */
    protected $shipment;

    protected $approvedCodes = [];

    public function setUp()
    {
        $ship = Ship::factory([
            'Standard Shipping' => [
                'ups' => [
                    '03' => '1-5 business days',
                ],
            ],
            'Two-Day Shipping' => [
                'ups' => [
                    '02' => '2 business days',
                ],
            ],
            'One-Day Shipping' => [
                'ups' => [
                    '01' => 'next business day 10:30am',
                    '13' => 'next business day by 3pm',
                    '14' => 'next business day by 8am',
                ],
            ],
        ]);

        $this->approvedCodes = $ship->getApprovedCodes('ups');

        $package = new Package;
        $package->setWeight(3)
            ->setWidth(9)
            ->setLength(9)
            ->setHeight(9);

        $this->shipment = new Shipment;
        $this->shipment->setFromStateProvinceCode('CA')
            ->setFromPostalCode('90401')
            ->setFromCountryCode('US')
            ->setFromIsResidential(true)
            ->setToPostalCode('78703')
            ->setToCountryCode('US')
            ->setToIsResidential(true)
            ->addPackage($package);
    }

    public function testMockRates()
    {
        $rateAdapter = new Rate([
            'accessKey' => 'XXX',
            'userId' => 'XXX',
            'password' => 'XXX',
            'shipperNumber' => 'XXX',
            'prod' => false,
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubUPS,
        ]);

        $rates = $rateAdapter->getRates();

        $expected = [
            new Quote('ups', '03', 'UPS Ground', 1910),
            new Quote('ups', '02', 'UPS 2nd Day Air', 4923),
            new Quote('ups', '13', 'UPS Next Day Air Saver', 8954),
            new Quote('ups', '01', 'UPS Next Day Air', 9328),
        ];

        $this->assertEquals($expected, $rates);
    }

    public function testLiveRates()
    {
        if (getenv('UPS_ACCESS_KEY') === false) {
            $this->markTestSkipped('Live UPS credentials missing.');
        }

        $rateAdapter = new Rate([
            'prod' => false,
            'accessKey' => getenv('UPS_ACCESS_KEY'),
            'userId' => getenv('UPS_USER_ID'),
            'password' => getenv('UPS_PASSWORD'),
            'shipperNumber' => getenv('UPS_SHIPPER_NUMBER'),
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
    public function testMissingAccessKey()
    {
        $rateAdapter = new Rate([
            'userId' => 'XXX',
            'password' => 'XXX',
            'shipperNumber' => 'XXX',
            'prod' => false,
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubUPS,
        ]);

        $rateAdapter->getRates();
    }
    /**
     * @expectedException \LogicException
     */
    public function testMissingPassword()
    {
        $rateAdapter = new Rate([
            'accessKey' => 'XXX',
            'userId' => 'XXX',
            'shipperNumber' => 'XXX',
            'prod' => false,
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubUPS,
        ]);

        $rateAdapter->getRates();
    }
    /**
     * @expectedException \LogicException
     */
    public function testMissingShipperNumber()
    {
        $rateAdapter = new Rate([
            'accessKey' => 'XXX',
            'userId' => 'XXX',
            'password' => 'XXX',
            'prod' => false,
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubUPS,
        ]);

        $rateAdapter->getRates();
    }
}
