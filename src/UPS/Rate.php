<?php
namespace pdt256\Shipping\UPS;

use pdt256\Shipping\Arr;
use pdt256\Shipping\Quote;
use pdt256\Shipping\RateAdapter;
use pdt256\Shipping\RateRequest;
use pdt256\Shipping\Validator;
use DOMDocument;
use Exception;

class Rate extends RateAdapter
{
    private $urlDev = 'https://wwwcie.ups.com/ups.app/xml/Rate';
    private $urlProd = 'https://www.ups.com/ups.app/xml/Rate';

    private $accessKey;
    private $userId;
    private $password;
    private $shipperNumber;
    /**
     * Codes of appropriate shipping types. Default value is specified in __construct.
     */
    public $approvedCodes;

    private $shippingCodes = [
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

        $this->accessKey     = Arr::get($options, 'accessKey');
        $this->userId        = Arr::get($options, 'userId');
        $this->password      = Arr::get($options, 'password');
        $this->shipperNumber = Arr::get($options, 'shipperNumber');
        $this->approvedCodes = Arr::get($options, 'approvedCodes', [
            '03',
            '12',
        ]);

        $this->setRequestAdapter(Arr::get($options, 'requestAdapter', new RateRequest\Post()));

    }
    protected function validate()
    {
        $this->validatePackages();
        Validator::checkIfNull($this->accessKey, 'accessKey');
        Validator::checkIfNull($this->userId, 'userId');
        Validator::checkIfNull($this->password, 'password');
        Validator::checkIfNull($this->shipperNumber, 'shipperNumber');
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
        $service_code = '03';

        $this->data = '<?xml version="1.0"?>' . "\n" .
            '<AccessRequest xml:lang="en-US">' .
                '<AccessLicenseNumber>' . $this->accessKey . '</AccessLicenseNumber>'.
                '<UserId>' . $this->userId . '</UserId>' .
                '<Password>' . $this->password . '</Password>' .
            '</AccessRequest>' .
            '<RatingServiceSelectionRequest xml:lang="en-US">' .
                '<Request>' .
                    '<RequestAction>Rate</RequestAction>' .
                    '<RequestOption>shop</RequestOption>' .
                '</Request>' .
                '<Shipment>' .
                    '<Shipper>' .
                        '<Address>' .
                            '<PostalCode>' . $this->shipment->getFromPostalCode() . '</PostalCode>' .
                            '<CountryCode>' . $this->shipment->getFromCountryCode() . '</CountryCode>' .
                            (
                                $this->shipment->getFromIsResidential() ?
                                    '<ResidentialAddressIndicator>1</ResidentialAddressIndicator>' :
                                    ''
                            ) .
                        '</Address>' .
                        '<ShipperNumber>' . $this->shipperNumber . '</ShipperNumber>' .
                    '</Shipper>' .
                    '<ShipTo>' .
                        '<Address>' .
                            '<PostalCode>' . $this->shipment->getToPostalCode() . '</PostalCode>' .
                            '<CountryCode>' . $this->shipment->getToCountryCode() . '</CountryCode>' .
                            (
                                $this->shipment->getToIsResidential() ?
                                    '<ResidentialAddressIndicator>1</ResidentialAddressIndicator>' :
                                    ''
                            ) .
                        '</Address>' .
                    '</ShipTo>' .
                    '<ShipFrom>' .
                        '<Address>' .
                            '<StateProvinceCode>' .
                                $this->shipment->getFromStateProvinceCode() .
                            '</StateProvinceCode>' .
                            '<PostalCode>' . $this->shipment->getFromPostalCode() . '</PostalCode>' .
                            '<CountryCode>' . $this->shipment->getFromCountryCode() . '</CountryCode>' .
                            (
                                $this->shipment->getFromIsResidential() ?
                                    '<ResidentialAddressIndicator>1</ResidentialAddressIndicator>' :
                                    ''
                            ) .
                        '</Address>' .
                    '</ShipFrom>' .
                    '<Service>' .
                        '<Code>' . $service_code . '</Code>' .
                    '</Service>' .
                    $this->getPackagesXmlString() .
                    '<RateInformation>' .
                        '<NegotiatedRatesIndicator/>' .
                    '</RateInformation>' .
                '</Shipment>' .
        '</RatingServiceSelectionRequest>';

        return $this;
    }

    private function getPackagesXmlString()
    {
        $packages = '';
        foreach ($this->shipment->getPackages() as $p) {
            $packages .=
                '<Package>' .
                    '<PackagingType>' .
                        '<Code>02</Code>' .
                    '</PackagingType>' .
                    '<Dimensions>' .
                        '<UnitOfMeasurement>' .
                            '<Code>IN</Code>' .
                        '</UnitOfMeasurement>' .
                        '<Length>' . $p->getLength() . '</Length>' .
                        '<Width>' . $p->getWidth() . '</Width>' .
                        '<Height>' . $p->getHeight() . '</Height>' .
                    '</Dimensions>' .
                    '<PackageWeight>' .
                        '<UnitOfMeasurement>' .
                            '<Code>LBS</Code>' .
                        '</UnitOfMeasurement>' .
                    '<Weight>' . $p->getWeight() . '</Weight>' .
                    '</PackageWeight>' .
                '</Package>';
        }

        return $packages;
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

    protected function process()
    {
        try {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->loadXml($this->response);

            $rate_list = $dom->getElementsByTagName('RatedShipment');

            if (empty($rate_list->length)) {
                throw new Exception('Unable to get UPS Rates.');
            }
        } catch (Exception $e) {
            echo $this->response;
            throw $e;
        }

        foreach ($rate_list as $rate) {
            $code = @$rate
                ->getElementsByTagName('Service')->item(0)
                ->getElementsByTagName('Code')->item(0)->nodeValue;

            $name = Arr::get($this->shippingCodes['US'], $code);

            $cost = @$rate
                ->getElementsByTagName('TotalCharges')->item(0)
                ->getElementsByTagName('MonetaryValue')->item(0)->nodeValue;

            if (! empty($this->approvedCodes) && ! in_array($code, $this->approvedCodes)) {
                continue;
            }

            $quote = new Quote;
            $quote
                ->setCarrier('ups')
                ->setCode($code)
                ->setName($name)
                ->setCost($cost * 100);
            $this->rates[] = $quote;
        }

        return $this;
    }
}
