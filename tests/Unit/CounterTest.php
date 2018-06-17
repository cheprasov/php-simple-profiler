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

namespace SimpleProfiler\Tests\Unit;

use SimpleProfiler\Counter;

class CounterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Counter::clear();
    }

    public function providerIncrement()
    {
        return [
            'line ' . __LINE__ => [
                'increments' => [1, 1, 1],
                'expect' => 3,
            ],
            'line ' . __LINE__ => [
                'increments' => [2, 2, 2],
                'expect' => 6,
            ],
            'line ' . __LINE__ => [
                'increments' => [0, 1, 2],
                'expect' => 3,
            ],
            'line ' . __LINE__ => [
                'increments' => [null, null],
                'expect' => 2,
            ],
            'line ' . __LINE__ => [
                'increments' => [10, -5, 2],
                'expect' => 7,
            ],
            'line ' . __LINE__ => [
                'increments' => [-1, -2, -3],
                'expect' => -6,
            ],
        ];
    }

    /**
     * @see \SimpleProfiler\Counter::increment
     * @dataProvider providerIncrement
     */
    public function testIncrement(array $increments, int $expect)
    {
        $result = 0;
        $name = 'counterName';
        foreach ($increments as $count) {
            if (is_null($count)) {
                $result = Counter::increment($name);
            } else {
                $result = Counter::increment($name, $count);
            }
        }

        $this->assertSame($expect, $result);
    }

    public function providerDecrement()
    {
        return [
            'line ' . __LINE__ => [
                'increments' => [1, 1, 1],
                'expect' => -3,
            ],
            'line ' . __LINE__ => [
                'increments' => [2, 2, 2],
                'expect' => -6,
            ],
            'line ' . __LINE__ => [
                'increments' => [0, 1, 2],
                'expect' => -3,
            ],
            'line ' . __LINE__ => [
                'increments' => [null, null],
                'expect' => -2,
            ],
            'line ' . __LINE__ => [
                'increments' => [10, -5, 2],
                'expect' => -7,
            ],
            'line ' . __LINE__ => [
                'increments' => [-1, -2, -3],
                'expect' => 6,
            ],
        ];
    }

    /**
     * @see \SimpleProfiler\Counter::decrement
     * @dataProvider providerDecrement
     */
    public function testDecrement(array $decrements, int $expect)
    {
        $result = 0;
        $name = 'counterName';
        foreach ($decrements as $count) {
            if (is_null($count)) {
                $result = Counter::decrement($name);
            } else {
                $result = Counter::decrement($name, $count);
            }
        }

        $this->assertSame($expect, $result);
    }

    /**
     * @see \SimpleProfiler\Counter::get
     */
    public function testGet()
    {
        $this->assertSame(0, Counter::get('name_not_exists'));

        Counter::increment('foo');
        $this->assertSame(1, Counter::get('foo'));

        Counter::increment('foo', 8);
        $this->assertSame(9, Counter::get('foo'));

        $this->assertSame(0, Counter::get('bar'));

        Counter::increment('bar');
        $this->assertSame(1, Counter::get('bar'));

        Counter::decrement('bar');
        $this->assertSame(0, Counter::get('bar'));

        Counter::decrement('bar', 10);
        $this->assertSame(-10, Counter::get('bar'));

        Counter::increment('bar', 10);
        $this->assertSame(0, Counter::get('bar'));

        Counter::increment('bar', 5);
        $this->assertSame(5, Counter::get('bar'));
    }

    /**
     * @see \SimpleProfiler\Counter::getAll
     */
    public function testGetAll()
    {
        $this->assertSame([], Counter::getAll());

        $this->assertSame(0, Counter::get('foo'));
        $this->assertSame(0, Counter::get('bar'));

        $this->assertSame([], Counter::getAll());

        Counter::increment('foo', 10);
        $this->assertSame(['foo' => 10], Counter::getAll());

        Counter::increment('bar', 42);
        $this->assertSame(['foo' => 10, 'bar' => 42], Counter::getAll());

        Counter::increment('lol', -17);
        $this->assertSame(['foo' => 10, 'bar' => 42, 'lol' => -17], Counter::getAll());

        Counter::increment('lol', 17);
        $this->assertSame(['foo' => 10, 'bar' => 42, 'lol' => 0], Counter::getAll());
    }

    /**
     * @see \SimpleProfiler\Counter::clear
     */
    public function testClear()
    {
        $this->assertSame([], Counter::getAll());

        Counter::increment('foo', 10);
        Counter::increment('bar', 42);
        Counter::increment('lol', -17);
        $this->assertSame(['foo' => 10, 'bar' => 42, 'lol' => -17], Counter::getAll());

        Counter::clear();
        $this->assertSame([], Counter::getAll());
    }
}
