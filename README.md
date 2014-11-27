## PHP Shipping API

[![Total Downloads](https://poser.pugx.org/pdt256/shipping/downloads.svg)](https://packagist.org/packages/pdt256/shipping) [![Latest Stable Version](https://poser.pugx.org/pdt256/shipping/v/stable.svg)](https://packagist.org/packages/pdt256/shipping) [![Latest Unstable Version](https://poser.pugx.org/pdt256/shipping/v/unstable.svg)](https://packagist.org/packages/pdt256/shipping) [![License](https://poser.pugx.org/pdt256/shipping/license.svg)](https://packagist.org/packages/pdt256/shipping)

A shipping rate wrapper for USPS, UPS, and Fedex.

## Installation

Add the following lines to your ``composer.json`` file.

```JSON
{
	"require": {
		"pdt256/shipping": "dev-master"
	}
}
```

## Example

Create a shipment object:

```php
$shipment = new Shipment;
$shipment
    ->setFromStateProvinceCode('IN')
    ->setFromPostalCode('46205')
    ->setFromCountryCode('US')
    ->setToPostalCode('20101')
    ->setToCountryCode('US')
    ->setToResidential(true);

$package = new Package;
$package->setLength(12)->setWidth(4)->setHeight(3)->setWeight(3);

$shipment->addPackage($package);
```

## UPS (Stub) Example

Below is an example request to get shipping rates from the UPS API. 

Notice: The below line uses a stub class to fake a response from the UPS API.
You can immediately use this method in your code until you get an account with UPS.

```php
'request_adapter' => new RateRequest\StubUPS(),
```

```php
use pdt256\Shipping\UPS;
use pdt256\Shipping\RateRequest;

$ups = new UPS\Rate([
	'prod'            => FALSE,
	'access_key'      => 'XXXX',
	'user_id'         => 'XXXX',
	'password'        => 'XXXX',
	'shipper_number'  => 'XXXX',
	'shipment'        => $shipment,
	'approved_codes'  => [
		'03', // 1-5 business days
		'02', // 2 business days
		'01', // next business day 10:30am
		'13', // next business day by 3pm
		'14', // next business day by 8am
	],
	'request_adapter' => new RateRequest\StubUPS(),
]);

$ups_rates = $ups->get_rates();
```

Output array sorted by cost: (in cents)

```php
array(4) {
  [0] =>
  class pdt256\Shipping\Quote#56 (6) {
    protected $code =>
    string(2) "03"
    protected $name =>
    string(10) "UPS Ground"
    protected $cost =>
    int(1900)
    protected $transit_time =>
    NULL
    protected $delivery_ts =>
    NULL
    protected $carrier =>
    string(3) "ups"
  }
  [1] =>
  class pdt256\Shipping\Quote#58 (6) {
    protected $code =>
    string(2) "02"
    protected $name =>
    string(15) "UPS 2nd Day Air"
    protected $cost =>
    int(4900)
    protected $transit_time =>
    NULL
    protected $delivery_ts =>
    NULL
    protected $carrier =>
    string(3) "ups"
  }
  [2] =>
  class pdt256\Shipping\Quote#57 (6) {
    protected $code =>
    string(2) "13"
    protected $name =>
    string(22) "UPS Next Day Air Saver"
    protected $cost =>
    int(8900)
    protected $transit_time =>
    NULL
    protected $delivery_ts =>
    NULL
    protected $carrier =>
    string(3) "ups"
  }
  [3] =>
  class pdt256\Shipping\Quote#55 (6) {
    protected $code =>
    string(2) "01"
    protected $name =>
    string(16) "UPS Next Day Air"
    protected $cost =>
    int(9300)
    protected $transit_time =>
    NULL
    protected $delivery_ts =>
    NULL
    protected $carrier =>
    string(3) "ups"
  }
}
```

## USPS (Stub) Example

```php
use pdt256\Shipping\USPS;
use pdt256\Shipping\RateRequest;

$usps = new USPS\Rate([
	'prod'     => FALSE,
	'username' => 'XXXX',
	'password' => 'XXXX',
	'shipment' => $shipment,
	'approved_codes'  => [
		'1', // 1-3 business days
		'4', // 2-8 business days
	],
	'request_adapter' => new RateRequest\StubUSPS(),
]);

$usps_rates = $usps->get_rates();
```

Output array sorted by cost: (in cents)

```php
array(2) {
  [0] =>
  class pdt256\Shipping\Quote#30 (6) {
    protected $code =>
    string(1) "4"
    protected $name =>
    string(11) "Parcel Post"
    protected $cost =>
    int(1001)
    protected $transit_time =>
    NULL
    protected $delivery_ts =>
    NULL
    protected $carrier =>
    string(4) "usps"
  }
  [1] =>
  class pdt256\Shipping\Quote#26 (6) {
    protected $code =>
    string(1) "1"
    protected $name =>
    string(13) "Priority Mail"
    protected $cost =>
    int(1220)
    protected $transit_time =>
    NULL
    protected $delivery_ts =>
    NULL
    protected $carrier =>
    string(4) "usps"
  }
}
```

## Fedex (Stub) Example

```php
use pdt256\Shipping\Fedex;
use pdt256\Shipping\RateRequest;

$fedex = new Fedex\Rate([
	'prod'           => FALSE,
	'key'            => 'XXXX',
	'password'       => 'XXXX',
	'account_number' => 'XXXX',
	'meter_number'   => 'XXXX',
	'drop_off_type'  => 'BUSINESS_SERVICE_CENTER',
	'shipment'       => $shipment,
	'approved_codes'  => [
		'FEDEX_EXPRESS_SAVER',  // 1-3 business days
		'FEDEX_GROUND',         // 1-5 business days
		'GROUND_HOME_DELIVERY', // 1-5 business days
		'FEDEX_2_DAY',          // 2 business days
		'STANDARD_OVERNIGHT',   // overnight
	],
	'request_adapter' => new RateRequest\StubFedex(),
]);

$fedex_rates = $fedex->get_rates();
```

Output array sorted by cost: (in cents)

```php
array(4) {
  [0] =>
  class pdt256\Shipping\Quote#65 (6) {
    protected $code =>
    string(20) "GROUND_HOME_DELIVERY"
    protected $name =>
    string(20) "Ground Home Delivery"
    protected $cost =>
    int(1600)
    protected $transit_time =>
    string(10) "THREE_DAYS"
    protected $delivery_ts =>
    NULL
    protected $carrier =>
    string(5) "fedex"
  }
  [1] =>
  class pdt256\Shipping\Quote#63 (6) {
    protected $code =>
    string(19) "FEDEX_EXPRESS_SAVER"
    protected $name =>
    string(19) "Fedex Express Saver"
    protected $cost =>
    int(2900)
    protected $transit_time =>
    NULL
    protected $delivery_ts =>
    class Carbon\Carbon#23 (3) {
      public $date =>
      string(26) "2014-09-30 20:00:00.000000"
      public $timezone_type =>
      int(3)
      public $timezone =>
      string(16) "America/New_York"
    }
    protected $carrier =>
    string(5) "fedex"
  }
  [2] =>
  class pdt256\Shipping\Quote#61 (6) {
    protected $code =>
    string(11) "FEDEX_2_DAY"
    protected $name =>
    string(11) "Fedex 2 Day"
    protected $cost =>
    int(4000)
    protected $transit_time =>
    NULL
    protected $delivery_ts =>
    class Carbon\Carbon#26 (3) {
      public $date =>
      string(26) "2014-09-29 20:00:00.000000"
      public $timezone_type =>
      int(3)
      public $timezone =>
      string(16) "America/New_York"
    }
    protected $carrier =>
    string(5) "fedex"
  }
  [3] =>
  class pdt256\Shipping\Quote#60 (6) {
    protected $code =>
    string(18) "STANDARD_OVERNIGHT"
    protected $name =>
    string(18) "Standard Overnight"
    protected $cost =>
    int(7800)
    protected $transit_time =>
    NULL
    protected $delivery_ts =>
    class Carbon\Carbon#58 (3) {
      public $date =>
      string(26) "2014-09-26 20:00:00.000000"
      public $timezone_type =>
      int(3)
      public $timezone =>
      string(16) "America/New_York"
    }
    protected $carrier =>
    string(5) "fedex"
  }
}
```

### License

The MIT License (MIT)

Copyright (c) 2014 Jamie Isaacs <pdt256@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
