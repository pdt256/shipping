<?php
namespace pdt256\Shipping;

use DateTime;
use PHPUnit\Framework\TestCase;

class QuoteTest extends TestCase
{
    public function testCreate()
    {
        $quote = new Quote;
        $quote->setCode('-code-');
        $quote->setName('Test Name');
        $quote->setCost(500);
        $quote->setTransitTime('-transit-time-');
        $quote->setDeliveryEstimate(new DateTime);
        $quote->setCarrier('-carrier-');

        $this->assertEquals('-code-', $quote->getCode());
        $this->assertEquals('Test Name', $quote->getName());
        $this->assertEquals(500, $quote->getCost());
        $this->assertEquals('-transit-time-', $quote->getTransitTime());
        $this->assertTrue($quote->getDeliveryEstimate() instanceof DateTime);
        $this->assertEquals('-carrier-', $quote->getCarrier());
    }
}
