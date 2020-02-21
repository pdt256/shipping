<?php
namespace pdt256\Shipping\Fedex;

use pdt256\Shipping\RateRequest\RequestException;

class FedexRequestException extends RequestException
{
    /**
     * @var string
     */
    protected $severity;

    /**
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @param string $severity
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
    }
}
