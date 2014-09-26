<?php
use pdt256\Shipping\Ship;
use pdt256\Shipping\USPS;
use pdt256\Shipping\UPS;
use pdt256\Shipping\Fedex;
use pdt256\Shipping\RateRequest;

class ShipTest extends PHPUnit_Framework_TestCase
{
	public $shipment = [
		'weight' => 3, // lbs
		'dimensions' => [
			'width' => 9,
			'length' => 9,
			'height' => 9,
		],
		'from' => [
			'postal_code' => '90401',
			'country_code' => 'US',
		],
		'to' => [
			'postal_code' => '78703',
			'country_code' => 'US',
			'is_residential' => TRUE,
		],
	];

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

	private function getUSPSOptions()
	{
		$ship = Ship::factory($this->shipping_options);
		$approved_codes = $ship->get_approved_codes('usps');

		return [
			'prod'     => FALSE,
			'username' => 'XXXX',
			'password' => 'XXXX',
			'shipment' => array_merge($this->shipment, [
				'size' => 'LARGE',
				'container' => 'RECTANGULAR',
			]),
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
			'shipment'       => array_merge($this->shipment, [
				'packaging_type' => 'YOUR_PACKAGING',
			]),
			'approved_codes'  => $approved_codes,
			'request_adapter' => new RateRequest\StubFedex(),
		];
	}

	public function testUSPSRate()
	{
		$usps = new USPS\Rate($this->getUSPSOptions());
		$usps_rates = $usps->get_rates();

		$this->assertEquals(json_encode([
			1 => [
				'code' => '4',
				'name' => 'Parcel Post',
				'cost' => 1000,
			],
			0 => [
				'code' => '1',
				'name' => 'Priority Mail',
				'cost' => 1200,
			],
		]), json_encode($usps_rates));
	}

	public function testUPSRate()
	{
		$ups = new UPS\Rate($this->getUPSOptions());
		$ups_rates = $ups->get_rates();

		$this->assertEquals(json_encode([
			0 => [
				'code' => '03',
				'name' => 'UPS Ground',
				'cost' => 1900,
			],
			1 => [
				'code' => '02',
				'name' => 'UPS 2nd Day Air',
				'cost' => 4900,
			],
			2 => [
				'code' => '13',
				'name' => 'UPS Next Day Air Saver',
				'cost' => 8900,
			],
			3 => [
				'code' => '01',
				'name' => 'UPS Next Day Air',
				'cost' => 9300,
			],
		]), json_encode($ups_rates));
	}

	public function testFedexRate()
	{
		$fedex = new Fedex\Rate($this->getFedexOptions());
		$fedex_rates = $fedex->get_rates();

		$this->assertEquals(json_encode([
			3 => [
				'code' => 'GROUND_HOME_DELIVERY',
				'name' => 'Ground Home Delivery',
				'cost' => 1600,
				'delivery_ts' => NULL,
				'transit_time' => 'THREE_DAYS',
			],
			2 => [
				'code' => 'FEDEX_EXPRESS_SAVER',
				'name' => 'Fedex Express Saver',
				'cost' => 2900,
				'delivery_ts' => '2014-09-30T20:00:00',
				'transit_time' => NULL,
			],
			1 => [
				'code' => 'FEDEX_2_DAY',
				'name' => 'Fedex 2 Day',
				'cost' => 4000,
				'delivery_ts' => '2014-09-29T20:00:00',
				'transit_time' => NULL,
			],
			0 => [
			    'code' => 'STANDARD_OVERNIGHT',
			    'name' => 'Standard Overnight',
			    'cost' => 7800,
			    'delivery_ts' => '2014-09-26T20:00:00',
			    'transit_time' => NULL,
			],
		]), json_encode($fedex_rates));
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

		$this->assertEquals(json_encode([
			'Standard Shipping' => [
				0 => [
					'code' => '4',
					'name' => 'Parcel Post',
					'cost' => 1000,
					'carrier' => 'usps',
				],
			],
			'Two-Day Shipping' => [
				0 => [
					'code' => 'FEDEX_2_DAY',
					'name' => 'Fedex 2 Day',
					'cost' => 4000,
					'delivery_ts' => '2014-09-29T20:00:00',
					'transit_time' => NULL,
					'carrier' => 'fedex',
				],
			],
			'One-Day Shipping' => [
				0 => [
					'code' => 'STANDARD_OVERNIGHT',
					'name' => 'Standard Overnight',
					'cost' => 7800,
					'delivery_ts' => '2014-09-26T20:00:00',
					'transit_time' => NULL,
					'carrier' => 'fedex',
				],
			],
		]), json_encode($display_rates));
	}

	/**
	* @expectedException Exception
	*/
	public function testUSPSRateMissingTo()
	{
		$usps_options = $this->getUSPSOptions();
		unset($usps_options['shipment']['to']);

		$usps = new USPS\Rate($usps_options);
		$usps_rates = $usps->get_rates();
	}

	/**
	* @expectedException Exception
	*/
	public function testUSPSRateMissingFrom()
	{
		$usps_options = $this->getUSPSOptions();
		unset($usps_options['shipment']['from']);

		$usps = new USPS\Rate($usps_options);
		$usps_rates = $usps->get_rates();
	}

	/**
	* @expectedException Exception
	*/
	public function testUSPSRateMissingDimensions()
	{
		$usps_options = $this->getUSPSOptions();
		unset($usps_options['shipment']['dimensions']);

		$usps = new USPS\Rate($usps_options);
		$usps_rates = $usps->get_rates();
	}

	/**
	* @expectedException Exception
	*/
	public function testUPSRateMissingTo()
	{
		$ups_options = $this->getUPSOptions();
		unset($ups_options['shipment']['to']);

		$ups = new UPS\Rate($ups_options);
		$ups_rates = $ups->get_rates();
	}

	/**
	* @expectedException Exception
	*/
	public function testUPSRateMissingFrom()
	{
		$ups_options = $this->getUPSOptions();
		unset($ups_options['shipment']['from']);

		$ups = new UPS\Rate($ups_options);
		$ups_rates = $ups->get_rates();
	}

	/**
	* @expectedException Exception
	*/
	public function testUPSRateMissingDimensions()
	{
		$ups_options = $this->getUPSOptions();
		unset($ups_options['shipment']['dimensions']);

		$ups = new UPS\Rate($ups_options);
		$ups_rates = $ups->get_rates();
	}

	/**
	* @expectedException Exception
	*/
	public function testFedexRateMissingTo()
	{
		$fedex_options = $this->getFedexOptions();
		unset($fedex_options['shipment']['to']);

		$fedex = new Fedex\Rate($fedex_options);
		$fedex_rates = $fedex->get_rates();
	}

	/**
	* @expectedException Exception
	*/
	public function testFedexRateMissingFrom()
	{
		$fedex_options = $this->getFedexOptions();
		unset($fedex_options['shipment']['from']);

		$fedex = new Fedex\Rate($fedex_options);
		$fedex_rates = $fedex->get_rates();
	}

	/**
	* @expectedException Exception
	*/
	public function testFedexRateMissingDimensions()
	{
		$fedex_options = $this->getFedexOptions();
		unset($fedex_options['shipment']['dimensions']);

		$fedex = new Fedex\Rate($fedex_options);
		$fedex_rates = $fedex->get_rates();
	}

	// // Readme Examples:
	// public function testUSPSReadmeExample()
	// {
	// 	$usps = new USPS\Rate([
	// 		'prod'     => FALSE,
	// 		'username' => 'XXXX',
	// 		'password' => 'XXXX',
	// 		'shipment' => array_merge($this->shipment, [
	// 			'size' => 'LARGE',
	// 			'container' => 'RECTANGULAR',
	// 		]),
	// 		'approved_codes'  => [
	// 			'1', // 1-3 business days
	// 			'4', // 2-8 business days
	// 		],
	// 		'request_adapter' => new RateRequest\StubUSPS(),
	// 	]);
	//
	// 	$usps_rates = $usps->get_rates();
	// 	var_export($usps_rates);
	// }
	//
	// public function testUPSReadmeExample()
	// {
	// 	$ups = new UPS\Rate([
	// 		'prod'            => FALSE,
	// 		'shipment'        => $this->shipment,
	// 		'approved_codes'  => [
	// 			'03', // 1-5 business days
	// 			'02', // 2 business days
	// 			'01', // next business day 10:30am
	// 			'13', // next business day by 3pm
	// 			'14', // next business day by 8am
	// 		],
	// 		'request_adapter' => new RateRequest\StubUPS(),
	// 	]);
	//
	// 	$ups_rates = $ups->get_rates();
	// 	var_export($ups_rates);
	// }
	//
	// public function testFedexReadmeExample()
	// {
	// 	$fedex = new Fedex\Rate([
	// 		'prod'           => FALSE,
	// 		'key'            => 'XXXX',
	// 		'password'       => 'XXXX',
	// 		'account_number' => 'XXXX',
	// 		'meter_number'   => 'XXXX',
	// 		'drop_off_type'  => 'BUSINESS_SERVICE_CENTER',
	// 		'shipment'       => array_merge($this->shipment, [
	// 			'packaging_type' => 'YOUR_PACKAGING',
	// 		]),
	// 		'approved_codes'  => [
	// 			'FEDEX_EXPRESS_SAVER',  // 1-3 business days
	// 			'FEDEX_GROUND',         // 1-5 business days
	// 			'GROUND_HOME_DELIVERY', // 1-5 business days
	// 			'FEDEX_2_DAY',          // 2 business days
	// 			'STANDARD_OVERNIGHT',   // overnight
	// 		],
	// 		'request_adapter' => new RateRequest\StubFedex(),
	// 	]);
	//
	// 	$fedex_rates = $fedex->get_rates();
	// 	var_export($fedex_rates);
	// }
}
