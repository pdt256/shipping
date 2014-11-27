<?php
use Carbon\Carbon;
use pdt256\Shipping\Package;
use pdt256\Shipping\Quote;
use pdt256\Shipping\Ship;
use pdt256\Shipping\Shipment;
use pdt256\Shipping\USPS;
use pdt256\Shipping\UPS;
use pdt256\Shipping\Fedex;
use pdt256\Shipping\RateRequest;

class ShipTest extends PHPUnit_Framework_TestCase
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
			->setToResidential(true);

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
		$approved_codes = $ship->get_approved_codes('usps');

		return [
			'prod'     => FALSE,
			'username' => 'XXXX',
			'password' => 'XXXX',
			'shipment' => $this->shipment,
			'approved_codes' => $approved_codes,
			'request_adapter' => new RateRequest\StubUSPS(),
		];
	}

	private function getUPSOptions()
	{
		$ship = Ship::factory($this->shipping_options);
		$approved_codes = $ship->get_approved_codes('ups');

		return [
			'prod'            => FALSE,
			'access_key'      => 'XXXX',
			'user_id'         => 'XXXX',
			'password'        => 'XXXX',
			'shipper_number'  => 'XXXX',
			'shipment'        => $this->shipment,
			'approved_codes'  => $approved_codes,
			'request_adapter' => new RateRequest\StubUPS(),
		];
	}

	private function getFedexOptions()
	{
		$ship = Ship::factory($this->shipping_options);
		$approved_codes = $ship->get_approved_codes('fedex');

		return [
			'prod'           => FALSE,
			'key'            => 'XXXX',
			'password'       => 'XXXX',
			'account_number' => 'XXXX',
			'meter_number'   => 'XXXX',
			'drop_off_type'  => 'BUSINESS_SERVICE_CENTER',
			'shipment'       => $this->shipment,
			'approved_codes'  => $approved_codes,
			'request_adapter' => new RateRequest\StubFedex(),
		];
	}

	public function testUSPSRate()
	{
		$usps = new USPS\Rate($this->getUSPSOptions());
		$usps_rates = $usps->get_rates();

		$post = new Quote;
		$post
			->setCarrier('usps')
			->setCode(4)
			->setName('Parcel Post')
			->setCost(1001);

		$priority = new Quote;
		$priority
			->setCarrier('usps')
			->setCode(1)
			->setName('Priority Mail')
			->setCost(1220);

		$expected_return = [$post, $priority];

		$this->assertEquals($expected_return, $usps_rates);
	}

	public function testUPSRate()
	{
		$ups = new UPS\Rate($this->getUPSOptions());
		$ups_rates = $ups->get_rates();

		$ground = new Quote;
		$ground
			->setCarrier('ups')
			->setCode('03')
			->setName('UPS Ground')
			->setCost(1900);

		$twodayair = new Quote;
		$twodayair
			->setCarrier('ups')
			->setCode('02')
			->setName('UPS 2nd Day Air')
			->setCost(4900);

		$nextdaysaver = new Quote;
		$nextdaysaver
			->setCarrier('ups')
			->setCode('13')
			->setName('UPS Next Day Air Saver')
			->setCost(8900);

		$nextdayair = new Quote;
		$nextdayair
			->setCarrier('ups')
			->setCode('01')
			->setName('UPS Next Day Air')
			->setCost(9300);

		$expected_return = [$ground, $twodayair, $nextdaysaver, $nextdayair];

		$this->assertEquals($expected_return, $ups_rates);
	}

	public function testFedexRate()
	{
		$fedex = new Fedex\Rate($this->getFedexOptions());
		$fedex_rates = $fedex->get_rates();

		$ground = new Quote;
		$ground
			->setCarrier('fedex')
			->setCode('GROUND_HOME_DELIVERY')
			->setName('Ground Home Delivery')
			->setCost(1600)
			->setTransitTime('THREE_DAYS');

		$express = new Quote;
		$express
			->setCarrier('fedex')
			->setCode('FEDEX_EXPRESS_SAVER')
			->setName('Fedex Express Saver')
			->setCost(2900)
			->setDeliveryEstimate(new Carbon('2014-09-30T20:00:00'))
			->setTransitTime(null);

		$secondday = new Quote;
		$secondday
			->setCarrier('fedex')
			->setCode('FEDEX_2_DAY')
			->setName('Fedex 2 Day')
			->setCost(4000)
			->setDeliveryEstimate(new Carbon('2014-09-29T20:00:00'))
			->setTransitTime(null);

		$overnight = new Quote;
		$overnight
			->setCarrier('fedex')
			->setCode('STANDARD_OVERNIGHT')
			->setName('Standard Overnight')
			->setCost(7800)
			->setDeliveryEstimate(new Carbon('2014-09-26T20:00:00'))
			->setTransitTime(null);

		$expected_result = [$ground, $express, $secondday, $overnight];

		$this->assertEquals($expected_result, $fedex_rates);
	}

	public function testDisplayOptions()
	{
		$rates = [];

		$usps = new USPS\Rate($this->getUSPSOptions());
		$rates['usps'] = $usps->get_rates();

		$ups = new UPS\Rate($this->getUPSOptions());
		$rates['ups'] = $ups->get_rates();

		$fedex = new Fedex\Rate($this->getFedexOptions());
		$rates['fedex'] = $fedex->get_rates();

		$ship = Ship::factory($this->shipping_options);
		$display_rates = $ship->get_display_rates($rates);

		$post = new Quote;
		$post->setCode(4)
			->setName('Parcel Post')
			->setCost(1001)
			->setCarrier('usps');

		$fedex_two_day = new Quote;
		$fedex_two_day->setCode('FEDEX_2_DAY')
			->setName('Fedex 2 Day')
			->setCost(4000)
			->setDeliveryEstimate(new Carbon('2014-09-29T20:00:00'))
			->setCarrier('fedex');

		$overnight = new Quote;
		$overnight->setCode('STANDARD_OVERNIGHT')
			->setName('Standard Overnight')
			->setCost(7800)
			->setDeliveryEstimate(new Carbon('2014-09-26T20:00:00'))
			->setCarrier('fedex');

		$this->assertEquals([
			'Standard Shipping' => [
				$post,
			],
			'Two-Day Shipping' => [
				$fedex_two_day,
			],
			'One-Day Shipping' => [
				$overnight,
			],
		], $display_rates);
	}
}
