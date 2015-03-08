<?php
namespace pdt256\Shipping\Fedex;

use pdt256\Shipping\RateRequest\StubFailingFedex;
use pdt256\Shipping\RateRequest\StubFedex;
use pdt256\Shipping\RateRequest\StubIncorrectResponseAdapter;
use pdt256\Shipping\Ship;
use pdt256\Shipping\Package;
use pdt256\Shipping\Shipment;
use pdt256\Shipping\Quote;
use DateTime;

class RateTest extends \PHPUnit_Framework_TestCase
{
    /** @var Shipment */
    protected $shipment;

    protected $approvedCodes = [];

    public function setUp()
    {
        $ship = Ship::factory([
            'Standard Shipping' => [
                'fedex' => [
                    'FEDEX_EXPRESS_SAVER' => '1-3 business days',
                    'FEDEX_GROUND' => '1-5 business days',
                    'GROUND_HOME_DELIVERY' => '1-5 business days',
                ],
            ],
            'Two-Day Shipping' => [
                'fedex' => [
                    'FEDEX_2_DAY' => '2 business days',
                ],
            ],
            'One-Day Shipping' => [
                'fedex' => [
                    'STANDARD_OVERNIGHT' => 'overnight',
                ],
            ],
        ]);

        $this->approvedCodes = $ship->getApprovedCodes('fedex');

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
            'prod' => false,
            'key' => 'XXX',
            'password' => 'XXX',
            'accountNumber' => 'XXX',
            'meterNumber' => 'XXX',
            'dropOffType' => 'BUSINESS_SERVICE_CENTER',
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubFedex,
        ]);
        $rates = $rateAdapter->getRates();

        $ground = new Quote('fedex', 'GROUND_HOME_DELIVERY', 'Ground Home Delivery', 1655);
        $ground->setTransitTime('THREE_DAYS');

        $express = new Quote('fedex', 'FEDEX_EXPRESS_SAVER', 'Fedex Express Saver', 2989);
        $express->setDeliveryEstimate(new DateTime('2014-09-30T20:00:00'));

        $secondDay = new Quote('fedex', 'FEDEX_2_DAY', 'Fedex 2 Day', 4072);
        $secondDay->setDeliveryEstimate(new DateTime('2014-09-29T20:00:00'));

        $overnight = new Quote('fedex', 'STANDARD_OVERNIGHT', 'Standard Overnight', 7834);
        $overnight->setDeliveryEstimate(new DateTime('2014-09-26T20:00:00'));

        $expected = [$ground, $express, $secondDay, $overnight];

        $this->assertEquals($expected, $rates);
    }

    public function testFail()
    {
        $rateAdapter = new Rate([
            'prod' => false,
            'key' => 'XXX',
            'password' => 'XXX',
            'accountNumber' => 'XXX',
            'meterNumber' => 'XXX',
            'dropOffType' => 'BUSINESS_SERVICE_CENTER',
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubFailingFedex(),
        ]);
        try {
            $rateAdapter->getRates();
            $this->fail('Getting error from fedex should throw an exception');
        } catch (FedexRequestException $ex) {
            $this->assertEquals('556', $ex->getCode());
            $this->assertEquals('There are no valid services available. ', $ex->getMessage());
            $this->assertEquals('WARNING', $ex->getSeverity());

        }
    }

    public function testIncorrectResponse()
    {
        $rateAdapter = new Rate([
            'prod' => false,
            'key' => 'XXX',
            'password' => 'XXX',
            'accountNumber' => 'XXX',
            'meterNumber' => 'XXX',
            'dropOffType' => 'BUSINESS_SERVICE_CENTER',
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubIncorrectResponseAdapter(),
        ]);
        try {
            $rateAdapter->getRates();
            $this->fail('Getting incorrect response should throw an exception');
        } catch (FedexRequestException $ex) {
            $this->assertEquals('Incorrect response received from FedEx: <html/>', $ex->getMessage());
        }
    }

    public function testLiveRates()
    {
        if (getenv('FEDEX_KEY') === false) {
            $this->markTestSkipped('Live Fedex credentials missing.');
        }

        $rateAdapter = new Rate([
            'prod' => false,
            'key' => getenv('FEDEX_KEY'),
            'password' => getenv('FEDEX_PASSWORD'),
            'account_number' => getenv('FEDEX_ACCOUNT_NUMBER'),
            'meter_number' => getenv('FEDEX_METER_NUMBER'),
            'drop_off_type' => 'BUSINESS_SERVICE_CENTER',
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubFedex,
        ]);

        $rates = $rateAdapter->getRates();

        $this->assertTrue(count($rates) > 0);
        $this->assertTrue($rates[0] instanceof Quote);
    }
    /**
     * @expectedException \LogicException
     */
    public function testMissingKey()
    {
        $rateAdapter = new Rate([
            'prod' => false,
            'password' => 'XXX',
            'accountNumber' => 'XXX',
            'meterNumber' => 'XXX',
            'dropOffType' => 'BUSINESS_SERVICE_CENTER',
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubFedex,
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
            'key' => 'XXX',
            'accountNumber' => 'XXX',
            'meterNumber' => 'XXX',
            'dropOffType' => 'BUSINESS_SERVICE_CENTER',
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubFedex,
        ]);

        $rateAdapter->getRates();
    }
    /**
     * @expectedException \LogicException
     */
    public function testMissingAccountNumber()
    {
        $rateAdapter = new Rate([
            'prod' => false,
            'key' => 'XXX',
            'password' => 'XXX',
            'meterNumber' => 'XXX',
            'dropOffType' => 'BUSINESS_SERVICE_CENTER',
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubFedex,
        ]);

        $rateAdapter->getRates();
    }
    /**
     * @expectedException \LogicException
     */
    public function testMissingMeterNumber()
    {

        $rateAdapter = new Rate([
            'prod' => false,
            'key' => 'XXX',
            'password' => 'XXX',
            'accountNumber' => 'XXX',
            'dropOffType' => 'BUSINESS_SERVICE_CENTER',
            'shipment' => $this->shipment,
            'approvedCodes' => $this->approvedCodes,
            'requestAdapter' => new StubFedex,
        ]);
        $rateAdapter->getRates();

    }
}
