<?php
namespace pdt256\Shipping;

class ValidatorTest extends \PHPUnit_Framework_TestCase
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
        Validator::checkIfNull(new \stdClass(), 'notNullValue');
        Validator::checkIfNull(function () {
        }, 'notNullValue');
    }
}
