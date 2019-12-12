<?php
namespace pdt256\Shipping;

use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
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
