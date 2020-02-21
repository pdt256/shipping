<?php
namespace pdt256\Shipping\Fedex;

use DateTime;
use pdt256\Shipping;
use pdt256\Shipping\Arr;
use pdt256\Shipping\Quote;
use pdt256\Shipping\RateAdapter;
use pdt256\Shipping\RateRequest;
use pdt256\Shipping\Validator;
use DOMDocument;
use Exception;

class Rate extends RateAdapter
{
    private $urlDev = 'https://gatewaybeta.fedex.com/web-services/';
    private $urlProd = 'https://gateway.fedex.com/web-services/';

    private $key;
    private $password;
    private $accountNumber;
    private $meterNumber;
    /**
     * Type of Drop off, default value "BUSINESS_SERVICE_CENTER" is defined in __construct if not specified.
     */
    private $dropOffType;
    /**
     * Codes of appropriate shipping types. Default value is specified in __construct.
     */
    public $approvedCodes;

    private $shippingCodes = [
        'EUROPE_FIRST_INTERNATIONAL_PRIORITY' => 'Europe First International Priority',
        'FEDEX_1_DAY_FREIGHT' => 'Fedex 1 Day Freight',
        'FEDEX_2_DAY' => 'Fedex 2 Day',
        'FEDEX_2_DAY_AM' => 'Fedex 2 Day AM',
        'FEDEX_2_DAY_FREIGHT' => 'Fedex 2 Day Freight',
        'FEDEX_3_DAY_FREIGHT' => 'Fedex 3 Day Freight',
        'FEDEX_EXPRESS_SAVER' => 'Fedex Express Saver',
        'FEDEX_FIRST_FREIGHT' => 'Fedex First Freight',
        'FEDEX_FREIGHT_ECONOMY' => 'Fedex Freight Economy',
        'FEDEX_FREIGHT_PRIORITY' => 'Fedex Freight Priority',
        'FEDEX_GROUND' => 'Fedex Ground',
        'FIRST_OVERNIGHT' => 'First Overnight',
        'GROUND_HOME_DELIVERY' => 'Ground Home Delivery',
        'INTERNATIONAL_ECONOMY' => 'International Economy',
        'INTERNATIONAL_ECONOMY_FREIGHT' => 'International Economy Freight',
        'INTERNATIONAL_FIRST' => 'International First',
        'INTERNATIONAL_PRIORITY' => 'International Priority',
        'INTERNATIONAL_PRIORITY_FREIGHT' => 'International Priority Freight',
        'PRIORITY_OVERNIGHT' => 'Priority Overnight',
        'SMART_POST' => 'Smart Post',
        'STANDARD_OVERNIGHT' => 'Standard Overnight',
    ];

    public function __construct($options = [])
    {
        parent::__construct($options);

        $this->key           = Arr::get($options, 'key');
        $this->password      = Arr::get($options, 'password');
        $this->accountNumber = Arr::get($options, 'accountNumber');
        $this->meterNumber   = Arr::get($options, 'meterNumber');
        $this->approvedCodes = Arr::get($options, 'approvedCodes', [
            'PRIORITY_OVERNIGHT',
            'FEDEX_2_DAY',
            'FEDEX_EXPRESS_SAVER',
            'FEDEX_GROUND',
            'GROUND_HOME_DELIVERY',
        ]);
        $this->dropOffType   = Arr::get($options, 'dropOffType', 'BUSINESS_SERVICE_CENTER');

        $this->setRequestAdapter(Arr::get($options, 'requestAdapter', new RateRequest\Post()));
    }
    protected function validate()
    {
        $this->validatePackages();
        Validator::checkIfNull($this->key, 'key');
        Validator::checkIfNull($this->password, 'password');
        Validator::checkIfNull($this->accountNumber, 'accountNumber');
        Validator::checkIfNull($this->meterNumber, 'meterNumber');
        Validator::checkIfNull($this->shipment->getFromPostalCode(), 'fromPostalCode');
        Validator::checkIfNull($this->shipment->getFromCountryCode(), 'fromCountryCode');
        Validator::checkIfNull($this->shipment->getFromIsResidential(), 'fromIsResidential');
        Validator::checkIfNull($this->shipment->getToPostalCode(), 'toPostalCode');
        Validator::checkIfNull($this->shipment->getToCountryCode(), 'toCountryCode');
        Validator::checkIfNull($this->shipment->getToIsResidential(), 'toIsResidential');

        return $this;
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
        // http://www.fedex.com/us/developer/product/WebServices/MyWebHelp_August2010/Content/
        // Proprietary_Developer_Guide/Rate_Services_conditionalized.htm

        $packages = '';
        $sequence_number = 0;
        foreach ($this->shipment->getPackages() as $package) {
            $sequence_number++;

            $packages .=
                '<RequestedPackageLineItems>' .
                    '<SequenceNumber>' . $sequence_number . '</SequenceNumber>' .
                    '<GroupPackageCount>1</GroupPackageCount>' .
                    '<Weight>' .
                        '<Units>LB</Units>' .
                        '<Value>' . $package->getWeight() . '</Value>' .
                    '</Weight>' .
                    '<Dimensions>' .
                        '<Length>' . $package->getLength() . '</Length>' .
                        '<Width>' . $package->getWidth() . '</Width>' .
                        '<Height>' . $package->getHeight() . '</Height>' .
                        '<Units>IN</Units>' .
                    '</Dimensions>' .
                '</RequestedPackageLineItems>';
        }
        $this->data = '<?xml version="1.0"?>' .
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" ' .
                'xmlns="http://fedex.com/ws/rate/v13">' .
                '<SOAP-ENV:Body>' .
                    '<RateRequest>' .
                        '<WebAuthenticationDetail>' .
                            '<UserCredential>' .
                                '<Key>' . $this->key . '</Key>' .
                                '<Password>' . $this->password . '</Password>' .
                            '</UserCredential>' .
                        '</WebAuthenticationDetail>' .
                        '<ClientDetail>' .
                            '<AccountNumber>' . $this->accountNumber . '</AccountNumber>' .
                            '<MeterNumber>' . $this->meterNumber . '</MeterNumber>' .
                        '</ClientDetail>' .
                        '<Version>' .
                            '<ServiceId>crs</ServiceId>' .
                            '<Major>13</Major>' .
                            '<Intermediate>0</Intermediate>' .
                            '<Minor>0</Minor>' .
                        '</Version>' .
                        '<ReturnTransitAndCommit>true</ReturnTransitAndCommit>' .
                        '<RequestedShipment>' .
                            '<ShipTimestamp>' . date('c') . '</ShipTimestamp>' .
                            '<DropoffType>' . $this->dropOffType . '</DropoffType>' .
                            '<PackagingType>YOUR_PACKAGING</PackagingType>' .
                            '<Shipper>' .
                                '<Address>' .
                                    '<PostalCode>' . $this->shipment->getFromPostalCode() . '</PostalCode>' .
                                    '<CountryCode>' . $this->shipment->getFromCountryCode() . '</CountryCode>' .
                                    (
                                        $this->shipment->getFromIsResidential() ?
                                            '<Residential>1</Residential>' :
                                            ''
                                    ) .
                                '</Address>' .
                            '</Shipper>' .
                            '<Recipient>' .
                                '<Address>' .
                                    '<PostalCode>' . $this->shipment->getToPostalCode() . '</PostalCode>' .
                                    '<CountryCode>' . $this->shipment->getToCountryCode() . '</CountryCode>' .
                                    (
                                        $this->shipment->getToIsResidential() ?
                                            '<Residential>1</Residential>' :
                                            ''
                                    ) .
                                '</Address>' .
                            '</Recipient>' .
                            '<RateRequestTypes>LIST</RateRequestTypes>' .
                            '<PackageCount>' . $this->shipment->packageCount() . '</PackageCount>' .
                            $packages .
                        '</RequestedShipment>' .
                    '</RateRequest>' .
                '</SOAP-ENV:Body>' .
            '</SOAP-ENV:Envelope>';

        return $this;
    }

    protected function execute()
    {
        if ($this->isProduction) {
            $url = $this->urlProd;
        } else {
            $url = $this->urlDev;
        }

        $this->response = $this->rateRequest->execute($url, $this->data);

        return $this;
    }

    /**
     * @param $response
     * @return FedexRequestException
     */
    protected function getIncorrectResponseException($response)
    {
        return new FedexRequestException('Incorrect response received from FedEx: ' . $response);
    }

    /**
     * @param string $response
     * @return FedexRequestException
     */
    protected function getExceptionFromResponse($response)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXml($response);
        $notifications = $dom->getElementsByTagName('Notifications');
        foreach ($notifications as $notification) {
            $messages = $notification->getElementsByTagName('Message');
            if ($messages->length === 0) {
                break;
            }
            foreach ($messages as $message) {
                $exceptionMessage = $message->textContent;
                break;
            }
            $codes = $notification->getElementsByTagName('Code');
            if ($codes->length === 0) {
                break;
            }
            foreach ($codes as $code) {
                $exceptionCode = $code->textContent;
                break;
            }
            $severities = $notification->getElementsByTagName('Severity');
            if ($severities->length === 0) {
                break;
            }
            foreach ($severities as $severity) {
                $exceptionSeverity = $severity->textContent;
                break;
            }
            if (!isset($exceptionMessage) || !isset($exceptionCode) || !isset($exceptionSeverity)) {
                return $this->getIncorrectResponseException($response);
            }
            $exception = new FedexRequestException($exceptionMessage, $exceptionCode);
            $exception->setSeverity($exceptionSeverity);
            return $exception;

        }
        return $this->getIncorrectResponseException($response);
    }

    protected function process()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXml($this->response);
        $rate_reply = $dom->getElementsByTagName('RateReplyDetails');

        if (empty($rate_reply->length)) {
            throw $this->getExceptionFromResponse($this->response);
        }

        foreach ($rate_reply as $rate) {
            $code = $rate->getElementsByTagName('ServiceType')->item(0)->nodeValue;

            if (! empty($this->approvedCodes) && ! in_array($code, $this->approvedCodes)) {
                continue;
            }

            $name = Arr::get($this->shippingCodes, $code);

            $delivery_ts = @$rate->getElementsByTagName('DeliveryTimestamp')->item(0)->nodeValue;
            $transit_time = @$rate->getElementsByTagName('TransitTime')->item(0)->nodeValue;

            $cost = $rate
                ->getElementsByTagName('RatedShipmentDetails')->item(0)
                ->getElementsByTagName('ShipmentRateDetail')->item(0)
                ->getElementsByTagName('TotalNetCharge')->item(0)
                ->getElementsByTagName('Amount')->item(0)->nodeValue;

            $quote = new Quote;
            $quote
                ->setCarrier('fedex')
                ->setCode($code)
                ->setName($name)
                ->setCost((int) ($cost * 100))
                ->setTransitTime($transit_time);
            if ($delivery_ts) {
                $quote->setDeliveryEstimate(new DateTime($delivery_ts));
            }

            $this->rates[] = $quote;
        }

        return $this;
    }
}
