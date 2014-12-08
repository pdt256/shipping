<?php
namespace pdt256\Shipping;

class RateAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        /* @var RateAdapter $mock */
        $mock = $this->getMockForAbstractClass('pdt256\Shipping\RateAdapter');
        $mockRequestAdapter = $this->getMockForAbstractClass('pdt256\Shipping\RateRequest\Adapter');
        $mock->setRequestAdapter($mockRequestAdapter);
    }
}
