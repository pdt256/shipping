<?php
namespace pdt256\Shipping;

use DateTime;

class ShipTest extends \PHPUnit_Framework_TestCase
{
    /** @var Shipment */
    public $shipment;

    public $shipping_options = [
        'Standard Shipping' => [
            'ups' => [
                '03' => '1-5 business days',
            ],
            'fedex' => [
                'FEDEX_EXPRESS_SAVER' => '1-3 business days',
                'FEDEX_GROUND' => '1-5 business days',
                'GROUND_HOME_DELIVERY' => '1-5 business days',
            ],
            'usps' => [
                '1' => '1-3 business days',
                '4' => '2-8 business days',
            ],
        ],
        'Two-Day Shipping' => [
            'ups' => [
                '02' => '2 business days',
            ],
            'fedex' => [
                'FEDEX_2_DAY' => '2 business days',
            ],
        ],
        'One-Day Shipping' => [
            'ups' => [
                '01' => 'next business day 10:30am',
                '13' => 'next business day by 3pm',
                '14' => 'next business day by 8am',
            ],
            'fedex' => [
                'STANDARD_OVERNIGHT' => 'overnight',
            ],
        ],
    ];

    public function setUp()
    {
        $s = new Shipment;
        $s->setFromStateProvinceCode('CA')
            ->setFromPostalCode('90401')
            ->setFromCountryCode('US')
            ->setToPostalCode('78703')
            ->setToCountryCode('US')
            ->setToIsResidential(true);

        $p = new Package;
        $p->setWeight(3)
            ->setWidth(9)
            ->setLength(9)
            ->setHeight(9);

        $s->addPackage($p);

        $this->shipment = $s;
    }

    private function getUSPSOptions()
    {
        $ship = Ship::factory($this->shipping_options);
        $approvedCodes = $ship->getApprovedCodes('usps');

        return [
            'prod' => false,
            'username' => 'XXXX',
            'password' => 'XXXX',
            'shipment' => $this->shipment,
            'approvedCodes' => $approvedCodes,
            'requestAdapter' => new RateRequest\StubUSPS(),
        ];
    }

    private function getUPSOptions()
    {
        $ship = Ship::factory($this->shipping_options);
        $approvedCodes = $ship->getApprovedCodes('ups');

        return [
            'prod' => false,
            'accessKey' => 'XXXX',
            'userId' => 'XXXX',
            'password' => 'XXXX',
            'shipperNumber' => 'XXXX',
            'shipment' => $this->shipment,
            'approvedCodes' => $approvedCodes,
            'requestAdapter' => new RateRequest\StubUPS(),
        ];
    }

    private function getFedexOptions()
    {
        $ship = Ship::factory($this->shipping_options);
        $approvedCodes = $ship->getApprovedCodes('fedex');

        return [
            'prod' => false,
            'key' => 'XXXX',
            'password' => 'XXXX',
            'account_number' => 'XXXX',
            'meter_number' => 'XXXX',
            'drop_off_type' => 'BUSINESS_SERVICE_CENTER',
            'shipment' => $this->shipment,
            'approvedCodes' => $approvedCodes,
            'requestAdapter' => new RateRequest\StubFedex(),
        ];
    }

    public function testDisplayOptions()
    {
        $rates = [];

        $usps = new USPS\Rate($this->getUSPSOptions());
        $rates['usps'] = $usps->getRates();

        $ups = new UPS\Rate($this->getUPSOptions());
        $rates['ups'] = $ups->getRates();

        $fedex = new Fedex\Rate($this->getFedexOptions());
        $rates['fedex'] = $fedex->getRates();

        $ship = Ship::factory($this->shipping_options);
        $rates = $ship->getDisplayRates($rates);

        $post = new Quote('usps', '4', 'Parcel Post', 1001);

        $fedexTwoDay = new Quote('fedex', 'FEDEX_2_DAY', 'Fedex 2 Day', 4072);
        $fedexTwoDay->setDeliveryEstimate(new DateTime('2014-09-29T20:00:00'));

        $overnight = new Quote('fedex', 'STANDARD_OVERNIGHT', 'Standard Overnight', 7834);
        $overnight->setDeliveryEstimate(new DateTime('2014-09-26T20:00:00'));

        $expected = [
            'Standard Shipping' => [
                $post,
            ],
            'Two-Day Shipping' => [
                $fedexTwoDay,
            ],
            'One-Day Shipping' => [
                $overnight,
            ],
        ];

        $this->assertEquals($expected, $rates);
    }
}
