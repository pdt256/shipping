<?php
namespace pdt256\Shipping;

use Exception;

abstract class RateAdapter
{
	protected $is_prod = FALSE;

	/** @var Shipment */
	protected $shipment;
	protected $data;
	protected $response;
	protected $rates = [];

	protected $rate_request;

	abstract protected function prepare(); // Prepare XML
	abstract protected function execute(); // Curl Request
	abstract protected function process(); // Convert to shipping rates array

	public function __construct($options = [])
	{
		if (isset($options['prod'])) {
			$this->is_prod = (bool) $options['prod'];
		}

		if (isset($options['shipment'])) {
			$this->shipment = $options['shipment'];
		}
	}

	public function set_request_adapter(RateRequest\Adapter $rate_request)
	{
		$this->rate_request = $rate_request;
	}

	public function get_rates()
	{
		$this
			->prepare()
			->execute()
			->process()
			->sort_by_cost();

		return $this->rates;
	}

	protected function sort_by_cost()
	{
		uasort($this->rates, create_function('$a, $b', 'return ($a["cost"] > $b["cost"]);'));
	}
}
