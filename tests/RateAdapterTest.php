<?php
namespace pdt256\Shipping;

use PHPUnit\Framework\TestCase;

class RateAdapterTest extends TestCase
{
    public function testCreate()
    {
        /* @var RateAdapter $mock */
        $mock = $this->getMockForAbstractClass('pdt256\Shipping\RateAdapter');
        $mockRequestAdapter = $this->getMockForAbstractClass('pdt256\Shipping\RateRequest\Adapter');
        $mock->setRequestAdapter($mockRequestAdapter);
    }
}
