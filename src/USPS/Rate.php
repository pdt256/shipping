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

		if (isset($options['username'])) {
			$this->username = $options['username'];
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

		$packages = '';
		$sequence_number = 0;
		foreach ($this->shipment->getPackages() as $p) {
			$sequence_number++;

			/**
			 * RateV4Request / Package / Size
			 required once
			 Defined as follows:

			 REGULAR: Package dimensions are 12’’ or less;
			 LARGE: Any package dimension is larger than 12’’.

			 For example: <Size>REGULAR</Size>
			 string
			 whiteSpace=collapse
			 enumeration=LARGE
			 enumeration=REGULAR

			 */
			if ($p->getWidth() > 12 or $p->getLength() > 12 or $p->getHeight() > 12) {
				$size = 'LARGE';
			} else {
				$size = 'REGULAR';
			}

			$packages .= '<Package ID="' . $sequence_number .'">
					<Service>ALL</Service>
					<ZipOrigination>' . $this->shipment->getFromPostalCode() . '</ZipOrigination>
					<ZipDestination>' . $this->shipment->getToPostalCode() . '</ZipDestination>
					<Pounds>' . $p->getWeight() . '</Pounds>
					<Ounces>0</Ounces>
					<Container>RECTANGULAR</Container>
					<Size>' . $size . '</Size>
					<Width>' . $p->getWidth() . '</Width>
					<Length>' . $p->getLength() . '</Length>
					<Height>' . $p->getHeight() . '</Height>
					<Machinable>' . 'False' . '</Machinable>
				</Package>';
		}

		$this->data =
'<RateV4Request USERID="' . $this->username . '">
	<Revision/>
	' . $packages . '
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

		$rates = [];

		foreach ($postage_list as $postage) {
			$code = @$postage->getAttribute('CLASSID');
			$cost = @$postage->getElementsByTagName('Rate')->item(0)->nodeValue;

			$name = Arr::get($this->shipping_codes['domestic'], $code);

			if ( ! empty($this->approved_codes) AND ! in_array($code, $this->approved_codes)) {
				continue;
			}

			if (array_key_exists($code, $rates)) {
				$cost = $rates[$code]['cost'] + ($cost * 100);
			} else {
				$cost = $cost * 100;
			}

			$rates[$code] = [
				'code' => $code,
				'name' => $name,
				'cost' => (int) $cost,
			];
		}

		$this->rates = array_values($rates);

		return $this;
	}
}
