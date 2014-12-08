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

        if (!empty($shipping_options)) {
            $object->shipping_options = $shipping_options;
        }

        return $object;
    }

    /**
     * @return array
     */
    public function getApprovedCodes($carrier = null)
    {
        $approvedCodes = [];

        // Build approvedCodes
        foreach ($this->shipping_options as $shipping_group => $row) {

            foreach ($row as $_carrier => $row2) {
                if (!isset($approvedCodes[$_carrier])) {
                    $approvedCodes[$_carrier] = [];
                }

                foreach ($row2 as $code => $display) {
                    $approvedCodes[$_carrier][] = $code;
                }
            }
        }

        if ($carrier !== null && isset($approvedCodes[$carrier])) {
            return $approvedCodes[$carrier];
        }

        return $approvedCodes;
    }

    public function getDisplayRates($rates)
    {
        // Build output array with cheapest shipping option for each group
        $display_rates = [];
        foreach ($this->shipping_options as $shipping_group => $row) {
            $display_rates[$shipping_group] = [];
            $cheapest_row = null;

            foreach ($row as $carrier => $row2) {
                $group_codes = array_keys($row2);

                if (! empty($rates[$carrier])) {

                    foreach ($rates[$carrier] as $row3) {

                        if (in_array($row3->getCode(), $group_codes)) {
                            $row3->setCarrier($carrier);

                            if ($cheapest_row === null) {
                                $cheapest_row = $row3;
                            } else {
                                if ($row3->getCost() < $cheapest_row->getCost()) {
                                    $cheapest_row = $row3;
                                }
                            }
                        }
                    }
                }
            }

            // Add row if it exists
            if (! empty($cheapest_row)) {
                $display_rates[$shipping_group][] = $cheapest_row;
            }
        }

        return $display_rates;
    }

    public function getAllDisplayRates($rates)
    {
        // Build output array listing all group options
        $display_rates = [];
        foreach ($this->shipping_options as $shipping_group => $row) {
            $display_rates[$shipping_group] = [];

            foreach ($row as $carrier => $row2) {
                $group_codes = array_keys($row2);

                if (!empty($rates[$carrier])) {

                    foreach ($rates[$carrier] as $row3) {

                        if (in_array($row3->getCode(), $group_codes)) {
                            $row3->setCarrier($carrier);
                            $display_rates[$shipping_group][] = $row3;
                        }
                    }
                }
            }

            $this->sortByCost($display_rates[$shipping_group]);
        }

        return $display_rates;
    }

    protected function sortByCost(& $rates)
    {
        uasort($rates, create_function('$a, $b', 'return ($a->getCost() > $b->getCost());'));
    }
}
