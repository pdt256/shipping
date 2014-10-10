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
$shipment = [
	'weight' => 3, // lbs
	'dimensions' => [
		'width' => 9, // inches
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
array (
  0 => 
  array (
    'code' => '03',
    'name' => 'UPS Ground',
    'cost' => 1900,
  ),
  1 => 
  array (
    'code' => '02',
    'name' => 'UPS 2nd Day Air',
    'cost' => 4900,
  ),
  2 => 
  array (
    'code' => '13',
    'name' => 'UPS Next Day Air Saver',
    'cost' => 8900,
  ),
  3 => 
  array (
    'code' => '01',
    'name' => 'UPS Next Day Air',
    'cost' => 9300,
  ),
)
```

## USPS (Stub) Example

```php
use pdt256\Shipping\USPS;
use pdt256\Shipping\RateRequest;

$usps = new USPS\Rate([
	'prod'     => FALSE,
	'username' => 'XXXX',
	'password' => 'XXXX',
	'shipment' => array_merge($shipment, [
		'size' => 'LARGE',
		'container' => 'RECTANGULAR',
	]),
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
array (
  1 => 
  array (
    'code' => '4',
    'name' => 'Parcel Post',
    'cost' => 1000,
  ),
  0 => 
  array (
    'code' => '1',
    'name' => 'Priority Mail',
    'cost' => 1200,
  ),
)
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
	'shipment'       => array_merge($shipment, [
		'packaging_type' => 'YOUR_PACKAGING',
	]),
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
array (
  3 => 
  array (
    'code' => 'GROUND_HOME_DELIVERY',
    'name' => 'Ground Home Delivery',
    'cost' => 1600,
    'delivery_ts' => NULL,
    'transit_time' => 'THREE_DAYS',
  ),
  2 => 
  array (
    'code' => 'FEDEX_EXPRESS_SAVER',
    'name' => 'Fedex Express Saver',
    'cost' => 2900,
    'delivery_ts' => '2014-09-30T20:00:00',
    'transit_time' => NULL,
  ),
  1 => 
  array (
    'code' => 'FEDEX_2_DAY',
    'name' => 'Fedex 2 Day',
    'cost' => 4000,
    'delivery_ts' => '2014-09-29T20:00:00',
    'transit_time' => NULL,
  ),
  0 => 
  array (
    'code' => 'STANDARD_OVERNIGHT',
    'name' => 'Standard Overnight',
    'cost' => 7800,
    'delivery_ts' => '2014-09-26T20:00:00',
    'transit_time' => NULL,
  ),
)
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
