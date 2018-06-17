<?php
/**
 * This file is part of RedisClient.
 * git: https://github.com/cheprasov/php-simple-profiler
 *
 * (C) Alexander Cheprasov <acheprasov84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleProfiler\Tests\Unit\Unit;

use SimpleProfiler\Unit\FunctionUnit;

class FunctionUnitTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        /** @var FunctionUnit $Unit */
        $Unit = FunctionUnit::create('TestMethod', 17, 42);

        $this->assertSame('TestMethod 17:42', $Unit->getName());
        $this->assertSame('TestMethod 17:42', $Unit->getKey());
        $this->assertSame(null, $Unit->getData());
    }

    public function providerPrepareValue()
    {
        return [
            'line ' . __LINE__ => [
                'value' => 42,
                'expect' => 42,
            ],
            'line ' . __LINE__ => [
                'value' => -42.2,
                'expect' => -42.2,
            ],
            'line ' . __LINE__ => [
                'value' => 0,
                'expect' => 0,
            ],
            'line ' . __LINE__ => [
                'value' => true,
                'expect' => true,
            ],
            'line ' . __LINE__ => [
                'value' => false,
                'expect' => false,
            ],
            'line ' . __LINE__ => [
                'value' => null,
                'expect' => null,
            ],
            'line ' . __LINE__ => [
                'value' => 'Foo Bar',
                'expect' => 'Foo Bar',
            ],
            'line ' . __LINE__ => [
                'value' => '',
                'expect' => '',
            ],
            'line ' . __LINE__ => [
                'value' => [1, 2, 3],
                'expect' => 'Array(3)',
            ],
            'line ' . __LINE__ => [
                'value' => new \stdClass(),
                'expect' => 'Object:stdClass',
            ],
            'line ' . __LINE__ => [
                'value' => new \Exception('foo'),
                'expect' => 'Object:Exception',
            ],
            'line ' . __LINE__ => [
                'value' => function($f = 'foo'){return $f;},
                'expect' => 'Object:Closure',
            ],
        ];
    }

    /**
     * @see \SimpleProfiler\Unit\FunctionUnit::prepareValue
     * @dataProvider providerPrepareValue
     */
    public function testPrepareValue($value, $expect)
    {
        $ReflectionMethod = new \ReflectionMethod(FunctionUnit::class, 'prepareValue');
        $ReflectionMethod->setAccessible(true);
        $result = $ReflectionMethod->invoke(null, $value);

        $this->assertSame($expect, $result);
    }

    /**
     * @see \SimpleProfiler\Unit\FunctionUnit::prepareValue
     */
    public function testPrepareValueResource()
    {
        $f = fopen(__FILE__, 'r');
        $ReflectionMethod = new \ReflectionMethod(FunctionUnit::class, 'prepareValue');
        $ReflectionMethod->setAccessible(true);
        $result = $ReflectionMethod->invoke(null, $f);

        $this->assertSame('Resource:stream', $result);
        fclose($f);
    }
}
