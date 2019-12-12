<?php
namespace pdt256\Shipping;

use PHPUnit\Framework\TestCase;
use stdClass;

class ValidatorTest extends TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testNull()
    {
        Validator::checkIfNull(null, 'null');
    }

    public function testNotNull()
    {
        Validator::checkIfNull('XXX', 'notNullValue');
        Validator::checkIfNull([], 'notNullValue');
        Validator::checkIfNull(new stdClass(), 'notNullValue');
        Validator::checkIfNull(function () {
        }, 'notNullValue');
        $this->assertTrue(true);
    }
}
