<?php
namespace pdt256\Shipping;

use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{
    public function providerGet()
    {
        return array(
            array(array('uno', 'dos', 'tress'), 1, null, 'dos'),
            array(array('we' => 'can', 'make' => 'change'), 'we', null, 'can'),
            array(array('uno', 'dos', 'tress'), 10, null, null),
            array(array('we' => 'can', 'make' => 'change'), 'he', null, null),
            array(array('we' => 'can', 'make' => 'change'), 'he', 'who', 'who'),
            array(array('we' => 'can', 'make' => 'change'), 'he', array('arrays'), array('arrays')),
        );
    }

    /**
     * @dataProvider providerGet()
     */
    public function testGet(array $array, $key, $default, $expected)
    {
        $this->assertSame(
            $expected,
            Arr::get($array, $key, $default)
        );
    }
}
