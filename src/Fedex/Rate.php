<?php
namespace pdt256\Shipping\Fedex;

use pdt256\Shipping;
use pdt256\Shipping\Arr;
use pdt256\Shipping\RateAdapter;
use pdt256\Shipping\RateRequest;
use DOMDocument;
use Exception;

class Rate extends RateAdapter
{
	private $url_dev  = 'https://gatewaybeta.fedex.com/web-services/';
	private $url_prod = 'https://gateway.fedex.com/web-services/';

	private $key = 'XXX';
	private $password = 'XXX';
	private $account_number = 'XXX';
	private $meter_number = 'XXX';
	private $drop_off_type = 'BUSINESS_SERVICE_CENTER';

	public $approved_codes = [
		'PRIORITY_OVERNIGHT',
		'FEDEX_2_DAY',
		'FEDEX_EXPRESS_SAVER',
		'FEDEX_GROUND',
		'GROUND_HOME_DELIVERY',
	];

	private $shipping_codes = [
		'EUROPE_FIRST_INTERNATIONAL_PRIORITY' => 'Europe First International Priority',
		'FEDEX_1_DAY_FREIGHT'                 => 'Fedex 1 Day Freight',
		'FEDEX_2_DAY'                         => 'Fedex 2 Day',
		'FEDEX_2_DAY_AM'                      => 'Fedex 2 Day AM',
		'FEDEX_2_DAY_FREIGHT'                 => 'Fedex 2 Day Freight',
		'FEDEX_3_DAY_FREIGHT'                 => 'Fedex 3 Day Freight',
		'FEDEX_EXPRESS_SAVER'                 => 'Fedex Express Saver',
		'FEDEX_FIRST_FREIGHT'                 => 'Fedex First Freight',
		'FEDEX_FREIGHT_ECONOMY'               => 'Fedex Freight Economy',
		'FEDEX_FREIGHT_PRIORITY'              => 'Fedex Freight Priority',
		'FEDEX_GROUND'                        => 'Fedex Ground',
		'FIRST_OVERNIGHT'                     => 'First Overnight',
		'GROUND_HOME_DELIVERY'                => 'Ground Home Delivery',
		'INTERNATIONAL_ECONOMY'               => 'International Economy',
		'INTERNATIONAL_ECONOMY_FREIGHT'       => 'International Economy Freight',
		'INTERNATIONAL_FIRST'                 => 'International First',
		'INTERNATIONAL_PRIORITY'              => 'International Priority',
		'INTERNATIONAL_PRIORITY_FREIGHT'      => 'International Priority Freight',
		'PRIORITY_OVERNIGHT'                  => 'Priority Overnight',
		'SMART_POST'                          => 'Smart Post',
		'STANDARD_OVERNIGHT'                  => 'Standard Overnight',
	];

	public function __construct($options = [])
	{
		parent::__construct($options);

		if (isset($options['key'])) {
			$this->key = $options['key'];
		}

		if (isset($options['password'])) {
			$this->password = $options['password'];
		}

		if (isset($options['account_number'])) {
			$this->account_number = $options['account_number'];
		}

		if (isset($options['meter_number'])) {
			$this->meter_number = $options['meter_number'];
		}

		if (isset($options['approved_codes'])) {
			$this->approved_codes = $options['approved_codes'];
		}

		if (isset($options['drop_off_type'])) {
			$this->drop_off_type = $options['drop_off_type'];
		}

		if (isset($options['request_adapter'])) {
			$this->set_request_adapter($options['request_adapter']);
		} else {
			$this->set_request_adapter(new RateRequest\Post());
		}
	}

	protected function prepare()
	{
		$date = time();
		$day_name = date('l', $date);

		if ($day_name == 'Saturday') {
			$date += 172800;
		} elseif ($day_name == 'Sunday') {
			$date += 86400;
		}

		// http://www.fedex.com/templates/components/apps/wpor/secure/downloads/pdf/Aug13/PropDevGuide.pdf
		// http://www.fedex.com/us/developer/product/WebServices/MyWebHelp_August2010/Content/Proprietary_Developer_Guide/Rate_Services_conditionalized.htm


		$packages = '';
		$sequence_number = 0;
		foreach ($this->shipment->getPackages() as $p) {
			$sequence_number++;

			$packages .= '<RequestedPackageLineItems>
						<SequenceNumber>' . $sequence_number . '</SequenceNumber>
						<GroupPackageCount>1</GroupPackageCount>
						<Weight>
							<Units>LB</Units>
							<Value>' . $p->getWeight() . '</Value>
						</Weight>
						<Dimensions>
							<Length>' . $p->getLength() . '</Length>
							<Width>' . $p->getWidth() . '</Width>
							<Height>' . $p->getHeight() . '</Height>
							<Units>IN</Units>
						</Dimensions>
					</RequestedPackageLineItems>';
		}

		$this->data =
'<?xml version="1.0"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns="http://fedex.com/ws/rate/v13">
<SOAP-ENV:Body>
<RateRequest>
	<WebAuthenticationDetail>
		<UserCredential>
			<Key>' . $this->key . '</Key>
			<Password>' . $this->password . '</Password>
		</UserCredential>
	</WebAuthenticationDetail>
	<ClientDetail>
		<AccountNumber>' . $this->account_number . '</AccountNumber>
		<MeterNumber>' . $this->meter_number . '</MeterNumber>
	</ClientDetail>
	<Version>
		<ServiceId>crs</ServiceId>
		<Major>13</Major>
		<Intermediate>0</Intermediate>
		<Minor>0</Minor>
	</Version>
	<ReturnTransitAndCommit>true</ReturnTransitAndCommit>
	<RequestedShipment>
		<ShipTimestamp>' . date('c') . '</ShipTimestamp>
		<DropoffType>' . $this->drop_off_type . '</DropoffType>
		<PackagingType>YOUR_PACKAGING</PackagingType>
		<Shipper>
			<Address>
				<PostalCode>' . $this->shipment->getFromPostalCode() . '</PostalCode>
				<CountryCode>' . $this->shipment->getFromCountryCode() . '</CountryCode>
				' . (($this->shipment->isFromResidential()) ? '<Residential>1</Residential>' : '') . '
			</Address>
		</Shipper>
		<Recipient>
			<Address>
				<PostalCode>' . $this->shipment->getToPostalCode() . '</PostalCode>
				<CountryCode>' . $this->shipment->getToCountryCode() . '</CountryCode>
				' . (($this->shipment->isToResidential()) ? '<Residential>1</Residential>' : '') . '
			</Address>
		</Recipient>
		<RateRequestTypes>LIST</RateRequestTypes>
		<PackageCount>' . $this->shipment->packageCount() . '</PackageCount>
		' . $packages . '
	</RequestedShipment>
</RateRequest>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

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
			$rate_reply = $dom->getElementsByTagName('RateReplyDetails');

			if (empty($rate_reply->length)) {
				throw new Exception('Unable to get FedEx Rates.');
			}
		} catch (Exception $e) {
			// StatsD::increment('error.shipping.get_fedex_rate');
			// Kohana::$log->add(Log::ERROR, $e)->write();
			throw $e;
		}

		foreach ($rate_reply as $rate) {
			$code = $rate->getElementsByTagName('ServiceType')->item(0)->nodeValue;

			if ( ! empty($this->approved_codes) AND ! in_array($code, $this->approved_codes)) {
				continue;
			}

			$name = Arr::get($this->shipping_codes, $code);

			$delivery_ts = @$rate->getElementsByTagName('DeliveryTimestamp')->item(0)->nodeValue;
			$transit_time = @$rate->getElementsByTagName('TransitTime')->item(0)->nodeValue;

			$cost = $rate
				->getElementsByTagName('RatedShipmentDetails')->item(0)
				->getElementsByTagName('ShipmentRateDetail')->item(0)
				->getElementsByTagName('TotalNetCharge')->item(0)
				->getElementsByTagName('Amount')->item(0)->nodeValue;

			$this->rates[] = array(
				'code' => $code,
				'name' => $name,
				'cost' => (int) $cost * 100,
				'delivery_ts' => $delivery_ts,
				'transit_time' => $transit_time,
			);
		}

		return $this;
	}
}
