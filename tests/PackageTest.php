<?php
namespace pdt256\Shipping;

class PackageTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $package = new Package;
        $package->setWeight(5);
        $package->setWidth(6);
        $package->setLength(7);
        $package->setHeight(8);

        $this->assertEquals(5, $package->getWeight());
        $this->assertEquals(6, $package->getWidth());
        $this->assertEquals(7, $package->getLength());
        $this->assertEquals(8, $package->getHeight());
    }
}
