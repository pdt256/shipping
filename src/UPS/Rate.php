<?php
namespace pdt256\Shipping\UPS;

use pdt256\Ship;
use pdt256\Shipping\Arr;
use pdt256\Shipping\RateAdapter;
use pdt256\Shipping\RateRequest;
use DOMDocument;
use Exception;

class Rate extends RateAdapter
{
	private $url_dev  = 'https://wwwcie.ups.com/ups.app/xml/Rate';
	private $url_prod = 'https://www.ups.com/ups.app/xml/Rate';

	private $access_key = 'XXX';
	private $user_id = 'XXX';
	private $password = 'XXX';
	private $shipper_number = 'XXX';

	public $approved_codes = [
		'03',
		'12',
	];

	private $shipping_codes = [
		'US' => [ // United States
			'01' => 'UPS Next Day Air',
			'02' => 'UPS 2nd Day Air',
			'03' => 'UPS Ground',
			'07' => 'UPS Worldwide Express',
			'08' => 'UPS Worldwide Expedited',
			'11' => 'UPS Standard',
			'12' => 'UPS 3 Day Select',
			'13' => 'UPS Next Day Air Saver',
			'14' => 'UPS Next Day Air Early A.M.',
			'54' => 'UPS Worldwide Express Plus',
			'59' => 'UPS 2nd Day Air A.M.',
			'65' => 'UPS Saver',
		],
		'CA' => [ // Canada
			'01' => 'UPS Express',
			'02' => 'UPS Expedited',
			'07' => 'UPS Worldwide Express',
			'08' => 'UPS Worldwide Expedited',
			'11' => 'UPS Standard',
			'12' => 'UPS 3 Day Select',
			'13' => 'UPS Saver',
			'14' => 'UPS Express Early A.M.',
			'54' => 'UPS Worldwide Express Plus',
			'65' => 'UPS Saver',
		],
		'EU' => [ // European Union
			'07' => 'UPS Express',
			'08' => 'UPS Expedited',
			'11' => 'UPS Standard',
			'54' => 'UPS Worldwide Express Plus',
			'65' => 'UPS Saver',
			'82' => 'UPS Today Standard',
			'83' => 'UPS Today Dedicated Courier',
			'84' => 'UPS Today Intercity',
			'85' => 'UPS Today Express',
			'86' => 'UPS Today Express Saver',
			'01' => 'UPS Next Day Air',
			'02' => 'UPS 2nd Day Air',
			'03' => 'UPS Ground',
			'14' => 'UPS Next Day Air Early A.M.',
		],
		'MX' => [ // Mexico
			'07' => 'UPS Express',
			'08' => 'UPS Expedited',
			'54' => 'UPS Express Plus',
			'65' => 'UPS Saver',
		],
		'other' => [ // Other
			'07' => 'UPS Express',
			'08' => 'UPS Worldwide Expedited',
			'11' => 'UPS Standard',
			'54' => 'UPS Worldwide Express Plus',
			'65' => 'UPS Saver',
		],
	];

	public function __construct($options = [])
	{
		parent::__construct($options);

		if (isset($options['access_key'])) {
			$this->access_key = $options['access_key'];
		}

		if (isset($options['user_id'])) {
			$this->user_id = $options['user_id'];
		}

		if (isset($options['password'])) {
			$this->password = $options['password'];
		}

		if (isset($options['shipper_number'])) {
			$this->shipper_number = $options['shipper_number'];
		}

		if (isset($options['approved_codes'])) {
			$this->approved_codes = $options['approved_codes'];
		}

		if (isset($options['request_adapter'])) {
			$this->set_request_adapter($options['request_adapter']);
		} else {
			$this->set_request_adapter(new RateRequest\Post());
		}
	}

	protected function prepare()
	{
		$to = Arr::get($this->shipment, 'to');
		$shipper = Arr::get($this->shipment, 'from');
		$dimensions = Arr::get($this->shipment, 'dimensions');

		$pounds = (int) Arr::get($this->shipment, 'weight');
		$ounces = 0;

		if ($pounds < 1) {
			throw new Exception('Weight missing');
		}

		$service_code = '03';

		$this->data =
'<?xml version="1.0"?>
<AccessRequest xml:lang="en-US">
	<AccessLicenseNumber>' . $this->access_key . '</AccessLicenseNumber>
	<UserId>' . $this->user_id . '</UserId>
	<Password>' . $this->password . '</Password>
</AccessRequest>
<RatingServiceSelectionRequest xml:lang="en-US">
	<Request>
		<RequestAction>Rate</RequestAction>
		<RequestOption>shop</RequestOption>
	</Request>
	<Shipment>
		<Shipper>
			<Address>
				<PostalCode>' . Arr::get($shipper, 'postal_code') . '</PostalCode>
				<CountryCode>' . Arr::get($shipper, 'country_code') . '</CountryCode>
				' . ((Arr::get($shipper, 'is_residential')) ? '<ResidentialAddressIndicator>1</ResidentialAddressIndicator>' : '') . '
			</Address>
			<ShipperNumber>' . $this->shipper_number . '</ShipperNumber>
		</Shipper>
		<ShipTo>
			<Address>
				<PostalCode>' . Arr::get($to, 'postal_code') . '</PostalCode>
				<CountryCode>' . Arr::get($to, 'country_code') . '</CountryCode>
				' . ((Arr::get($to, 'is_residential')) ? '<ResidentialAddressIndicator>1</ResidentialAddressIndicator>' : '') . '
			</Address>
		</ShipTo>
		<ShipFrom>
			<Address>
				<PostalCode>' . Arr::get($shipper, 'postal_code') . '</PostalCode>
				<CountryCode>' . Arr::get($shipper, 'country_code') . '</CountryCode>
				' . ((Arr::get($shipper, 'is_residential')) ? '<ResidentialAddressIndicator>1</ResidentialAddressIndicator>' : '') . '
			</Address>
		</ShipFrom>
		<Service>
			<Code>' . $service_code . '</Code>
		</Service>
		<Package>
			<PackagingType>
				<Code>02</Code>
			</PackagingType>
			<Dimensions>
				<UnitOfMeasurement>
					<Code>IN</Code>
				</UnitOfMeasurement>
				<Length>' . Arr::get($dimensions, 'length') . '</Length>
				<Width>' . Arr::get($dimensions, 'width') . '</Width>
				<Height>' . Arr::get($dimensions, 'height') . '</Height>
			</Dimensions>
			<PackageWeight>
				<UnitOfMeasurement>
					<Code>LBS</Code>
				</UnitOfMeasurement>
				<Weight>' . $pounds . '</Weight>
			</PackageWeight>
		</Package>
	</Shipment>
</RatingServiceSelectionRequest>';

		return $this;
	}

	protected function execute()
	{
		if ($this->is_prod) {
			$url = $this->url_prod;
		} else {
			$url = $this->url_dev;
		}

		$this->response = $this->rate_request->execute($url, $this->data);

		return $this;
	}

	protected function process()
	{
		try {
			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->loadXml($this->response);

			$rate_list = @$dom->getElementsByTagName('RatedShipment');

			if (empty($rate_list->length)) {
				throw new Exception('Unable to get UPS Rates.');
			}
		} catch (Exception $e) {
			// StatsD::increment('error.shipping.get_ups_rate');
			// Kohana::$log->add(Log::ERROR, $e)->write();
			throw $e;
		}

		foreach ($rate_list as $rate) {
			$code = @$rate
				->getElementsByTagName('Service')->item(0)
				->getElementsByTagName('Code')->item(0)->nodeValue;

			$name = Arr::get($this->shipping_codes['US'], $code);

			$cost = @$rate
				->getElementsByTagName('TotalCharges')->item(0)
				->getElementsByTagName('MonetaryValue')->item(0)->nodeValue;

			if ( ! empty($this->approved_codes) AND ! in_array($code, $this->approved_codes)) {
				continue;
			}

			$this->rates[] = array(
				'code' => $code,
				'name' => $name,
				'cost' => (int) ($cost * 100),
			);
		}

		return $this;
	}
}
