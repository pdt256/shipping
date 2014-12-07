<?php
namespace pdt256\Shipping\USPS;

use pdt256\Shipping;
use pdt256\Shipping\Arr;
use pdt256\Shipping\RateAdapter;
use pdt256\Shipping\RateRequest;
use DOMDocument;
use Exception;

class Rate extends RateAdapter
{
	private $url_dev  = 'http://production.shippingapis.com/ShippingAPI.dll';
	private $url_prod = 'http://production.shippingapis.com/ShippingAPI.dll';

	private $username = 'XXX';
	private $password = 'XXX';

	public $approved_codes = [
		'1',
		'4',
	];

	private $shipping_codes = [
		'domestic' => [
			'00' => 'First-Class Mail Parcel',
			'01' => 'First-Class Mail Large Envelope',
			'02' => 'First-Class Mail Letter',
			'03' => 'First-Class Mail Postcards',
			'1'  => 'Priority Mail',
			'2'  => 'Express Mail Hold for Pickup',
			'3'  => 'Express Mail',
			'4'  => 'Parcel Post', // Standard Post
			'5'  => 'Bound Printed Matter',
			'6'  => 'Media Mail',
			'7'  => 'Library',
			'12' => 'First-Class Postcard Stamped',
			'13' => 'Express Mail Flat-Rate Envelope',
			'16' => 'Priority Mail Flat-Rate Envelope',
			'17' => 'Priority Mail Regular Flat-Rate Box',
			'18' => 'Priority Mail Keys and IDs',
			'19' => 'First-Class Keys and IDs',
			'22' => 'Priority Mail Flat-Rate Large Box',
			'23' => 'Express Mail Sunday/Holiday',
			'25' => 'Express Mail Flat-Rate Envelope Sunday/Holiday',
			'27' => 'Express Mail Flat-Rate Envelope Hold For Pickup',
			'28' => 'Priority Mail Small Flat-Rate Box',
		],
		'international' => [
			'1'  => 'Express Mail International',
			'2'  => 'Priority Mail International',
			'4'  => 'Global Express Guaranteed (Document and Non-document)',
			'5'  => 'Global Express Guaranteed Document used',
			'6'  => 'Global Express Guaranteed Non-Document Rectangular shape',
			'7'  => 'Global Express Guaranteed Non-Document Non-Rectangular',
			'8'  => 'Priority Mail Flat Rate Envelope',
			'9'  => 'Priority Mail Flat Rate Box',
			'10' => 'Express Mail International Flat Rate Envelope',
			'11' => 'Priority Mail Flat Rate Large Box',
			'12' => 'Global Express Guaranteed Envelope',
			'13' => 'First Class Mail International Letters',
			'14' => 'First Class Mail International Flats',
			'15' => 'First Class Mail International Parcels',
			'16' => 'Priority Mail Flat Rate Small Box',
			'21' => 'Postcards',
		],
	];

	public function __construct($options = [])
	{
		parent::__construct($options);

		if (isset($options['username'])) {
			$this->username = $options['username'];
		}

		if (isset($options['password'])) {
			$this->password = $options['password'];
		}

		if (isset($options['approved_codes'])) {
			$this->approved_codes = $options['approved_codes'];
		}

		if (isset($options['request_adapter'])) {
			$this->set_request_adapter($options['request_adapter']);
		} else {
			$this->set_request_adapter(new RateRequest\Get());
		}
	}

	protected function prepare()
	{
		$to = Arr::get($this->shipment, 'to');
		$shipper = Arr::get($this->shipment, 'from');
		$dimensions = Arr::get($this->shipment, 'dimensions');

		// https://www.usps.com/business/web-tools-apis/rate-calculators-v1-7a.htm
		$pounds = (int) Arr::get($this->shipment, 'weight');
		$ounces = 0;

		if ($pounds < 1) {
			throw new Exception('Weight missing');
		}

		$size = Arr::get($this->shipment, 'size');

		// If user has not specified size, determine it automatically
		// https://www.usps.com/business/web-tools-apis/rate-calculator-api.htm#_Toc378922331
		if ($size === null) {
			// Size is considered large if any dimension is larger than 12 inches
			foreach ($dimensions as $dimension) {
				if ($dimension > 12) {
					$size = 'LARGE';
					break;
				}
			}
			if (!isset($size)) {
				$size = 'REGULAR';
			}
		}
		$this->data =
'<RateV4Request USERID="' . $this->username . '">
	<Revision/>
	<Package ID="1">
		<Service>ALL</Service>
		<ZipOrigination>' . Arr::get($shipper, 'postal_code') . '</ZipOrigination>
		<ZipDestination>' . Arr::get($to, 'postal_code') . '</ZipDestination>
		<Pounds>' . $pounds . '</Pounds>
		<Ounces>' . $ounces . '</Ounces>
		<Container>' . Arr::get($this->shipment, 'container') . '</Container>
		<Size>' . $size . '</Size>
		<Width>' . Arr::get($dimensions, 'width') . '</Width>
		<Length>' . Arr::get($dimensions, 'length') . '</Length>
		<Height>' . Arr::get($dimensions, 'height') . '</Height>
		<Machinable>' . 'False' . '</Machinable>
	</Package>
</RateV4Request>';

		return $this;
	}

	protected function execute()
	{
		if ($this->is_prod) {
			$url = $this->url_prod;
		} else {
			$url = $this->url_dev;
		}

		$url_request = $url . '?API=RateV4&XML=' . rawurlencode($this->data);

		$this->response = $this->rate_request->execute($url_request);

		return $this;
	}

	protected function process()
	{
		try {
			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->loadXml($this->response);

			$postage_list = @$dom->getElementsByTagName('Postage');

			if (empty($postage_list)) {
				throw new Exception('Unable to get USPS Rates.');
			}
		} catch (Exception $e) {
			// StatsD::increment('error.shipping.get_usps_rate');
			// Kohana::$log->add(Log::ERROR, $e)->write();
			throw $e;
		}

		foreach ($postage_list as $postage) {
			$code = @$postage->getAttribute('CLASSID');
			$cost = @$postage->getElementsByTagName('Rate')->item(0)->nodeValue;

			$name = Arr::get($this->shipping_codes['domestic'], $code);

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
