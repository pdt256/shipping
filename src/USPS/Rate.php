<?php
namespace pdt256\Shipping\USPS;

use pdt256\Shipping;
use pdt256\Shipping\Arr;
use pdt256\Shipping\Quote;
use pdt256\Shipping\RateAdapter;
use pdt256\Shipping\RateRequest;
use DOMDocument;
use Exception;

class Rate extends RateAdapter
{
    private $urlDev = 'http://production.shippingapis.com/ShippingAPI.dll';
    private $urlProd = 'http://production.shippingapis.com/ShippingAPI.dll';

    private $username = 'XXX';
    private $password = 'XXX';

    public $approvedCodes = [
        '1',
        '4',
    ];

    private $shipping_codes = [
        'domestic' => [
            '00' => 'First-Class Mail Parcel',
            '01' => 'First-Class Mail Large Envelope',
            '02' => 'First-Class Mail Letter',
            '03' => 'First-Class Mail Postcards',
            '1' => 'Priority Mail',
            '2' => 'Express Mail Hold for Pickup',
            '3' => 'Express Mail',
            '4' => 'Parcel Post', // Standard Post
            '5' => 'Bound Printed Matter',
            '6' => 'Media Mail',
            '7' => 'Library',
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
            '1' => 'Express Mail International',
            '2' => 'Priority Mail International',
            '4' => 'Global Express Guaranteed (Document and Non-document)',
            '5' => 'Global Express Guaranteed Document used',
            '6' => 'Global Express Guaranteed Non-Document Rectangular shape',
            '7' => 'Global Express Guaranteed Non-Document Non-Rectangular',
            '8' => 'Priority Mail Flat Rate Envelope',
            '9' => 'Priority Mail Flat Rate Box',
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

        $this->username = Arr::get($options, 'username');
        $this->password = Arr::get($options, 'password');
        $this->approvedCodes = Arr::get($options, 'approvedCodes');
        $this->setRequestAdapter(Arr::get($options, 'requestAdapter', new RateRequest\Get()));
    }

    protected function prepare()
    {
        $packages = '';
        $sequence_number = 0;
        foreach ($this->shipment->getPackages() as $p) {
            $sequence_number++;

            /**
             * RateV4Request / Package / Size
             * required once
             * Defined as follows:
             *
             * REGULAR: Package dimensions are 12’’ or less;
             * LARGE: Any package dimension is larger than 12’’.
             *
             * For example: <Size>REGULAR</Size>
             * string
             * whiteSpace=collapse
             * enumeration=LARGE
             * enumeration=REGULAR

             */
            if ($p->getWidth() > 12 or $p->getLength() > 12 or $p->getHeight() > 12) {
                $size = 'LARGE';
                $container = 'RECTANGULAR';
            } else {
                $size = 'REGULAR';
                $container = 'VARIABLE';
            }

            $packages .=
                '<Package ID="' . $sequence_number . '">' .
                    '<Service>ALL</Service>' .
                    '<ZipOrigination>' . $this->shipment->getFromPostalCode() . '</ZipOrigination>' .
                    '<ZipDestination>' . $this->shipment->getToPostalCode() . '</ZipDestination>' .
                    '<Pounds>' . $p->getWeight() . '</Pounds>' .
                    '<Ounces>0</Ounces>' .
                    '<Container>' . $container . '</Container>' .
                    '<Size>' . $size . '</Size>' .
                    '<Width>' . $p->getWidth() . '</Width>' .
                    '<Length>' . $p->getLength() . '</Length>' .
                    '<Height>' . $p->getHeight() . '</Height>' .
                    '<Machinable>' . 'False' . '</Machinable>' .
                '</Package>';
        }

        $this->data =
            '<RateV4Request USERID="' . $this->username . '">' .
                '<Revision/>' .
                $packages .
            '</RateV4Request>';

        return $this;
    }

    protected function execute()
    {
        if ($this->isProduction) {
            $url = $this->urlProd;
        } else {
            $url = $this->urlDev;
        }

        $url_request = $url . '?API=RateV4&XML=' . rawurlencode($this->data);

        $this->response = $this->rateRequest->execute($url_request);

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
            throw $e;
        }

        /** @var Quote[] $rates */
        $rates = [];

        foreach ($postage_list as $postage) {
            $code = @$postage->getAttribute('CLASSID');
            $cost = @$postage->getElementsByTagName('Rate')->item(0)->nodeValue;

            $name = Arr::get($this->shipping_codes['domestic'], $code);

            if (!empty($this->approvedCodes) && !in_array($code, $this->approvedCodes)) {
                continue;
            }

            if (array_key_exists($code, $rates)) {
                $cost = $rates[$code]->getCost() + ($cost * 100);
            } else {
                $cost = $cost * 100;
            }

            $quote = new Quote;
            $quote
                ->setCarrier('usps')
                ->setCode($code)
                ->setName($name)
                ->setCost((int) $cost);

            $rates[$quote->getCode()] = $quote;
        }

        $this->rates = array_values($rates);

        return $this;
    }
}
