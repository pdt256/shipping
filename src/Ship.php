<?php
namespace pdt256\Shipping;

class Ship
{
	protected $shipping_options = [
		'Standard Shipping' => [
			'ups' => [
				'03' => '1-5 business days',
			],
			'fedex' => [
				'FEDEX_EXPRESS_SAVER' => '1-3 business days',
				'FEDEX_GROUND' => '1-5 business days',
				'GROUND_HOME_DELIVERY' => '1-5 business days',
			],
			'usps' => [
				'1' => '1-3 business days',
				'4' => '2-8 business days',
			],
		],
		'Two-Day Shipping' => [
			'ups' => [
				'02' => '2 business days',
			],
			'fedex' => [
				'FEDEX_2_DAY' => '2 business days',
			],
		],
		'One-Day Shipping' => [
			'ups' => [
				'01' => 'next business day 10:30am',
				'13' => 'next business day by 3pm',
				'14' => 'next business day by 8am',
			],
			'fedex' => [
				'STANDARD_OVERNIGHT' => 'overnight',
			],
		],
	];

	public static function factory($shipping_options = [])
	{
		$object = new self();

		if ( ! empty($shipping_options)) {
			$object->shipping_options = $shipping_options;
		}

		return $object;
	}

	public function get_approved_codes($carrier = NULL) {
		$approved_codes = [];

		// Build approved_codes
		foreach ($this->shipping_options as $shipping_group => $row) {

			foreach ($row as $_carrier => $row2) {
				if ( ! isset($approved_codes[$_carrier])) {
					$approved_codes[$_carrier] = [];
				}

				foreach ($row2 as $code => $display) {
					$approved_codes[$_carrier][] = $code;
				}
			}
		}

		if ($carrier !== NULL AND isset($approved_codes[$carrier])) {
			return $approved_codes[$carrier];
		}

		return $approved_codes;
	}

	public function get_display_rates($rates)
	{
		// Build output array with cheapest shipping option for each group
		$display_rates = [];
		foreach ($this->shipping_options as $shipping_group => $row) {
			$display_rates[$shipping_group] = [];
			$cheapest_row = NULL;

			foreach ($row as $carrier => $row2) {
				$group_codes = array_keys($row2);

				if ( ! empty($rates[$carrier])) {

					foreach ($rates[$carrier] as $row3) {

						if (in_array($row3['code'], $group_codes)) {
							$row3['carrier'] = $carrier;

							if ($cheapest_row === NULL) {
								$cheapest_row = $row3;
							} else {
								if ($row3['cost'] < $cheapest_row['cost']) {
									$cheapest_row = $row3;
								}
							}
						}
					}
				}
			}

			// Add row if it exists
			if ( ! empty($cheapest_row)) {
				$display_rates[$shipping_group][] = $cheapest_row;
			}
		}

		return $display_rates;
	}

	public function get_all_display_rates($rates)
	{
		// Build output array listing all group options
		$display_rates = [];
		foreach ($this->shipping_options as $shipping_group => $row) {
			$display_rates[$shipping_group] = [];

			foreach ($row as $carrier => $row2) {
				$group_codes = array_keys($row2);

				if ( ! empty($rates[$carrier])) {

					foreach ($rates[$carrier] as $row3) {

						if (in_array($row3['code'], $group_codes)) {
							$row3['carrier'] = $carrier;
							$display_rates[$shipping_group][] = $row3;
						}
					}
				}
			}

			$this->sort_by_cost($display_rates[$shipping_group]);
		}

		return $display_rates;
	}

	protected function sort_by_cost( & $rates)
	{
		uasort($rates, create_function('$a, $b', 'return ($a["cost"] > $b["cost"]);'));
	}
}
