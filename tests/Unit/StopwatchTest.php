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

use SimpleProfiler\Stopwatch;

class StopwatchTest extends \PHPUnit_Framework_TestCase
{
    const MICRO = 1000000;

    public function setUp()
    {
        Stopwatch::clear();
    }

    /**
     * @see \SimpleProfiler\Stopwatch::start
     * @see \SimpleProfiler\Stopwatch::stop
     */
    public function testStartStop()
    {
        $name = 'timer1';
        $this->assertSame(0.0, Stopwatch::get($name));

        Stopwatch::start($name);
        usleep(1.5 * self::MICRO);
        $result = Stopwatch::stop($name);

        $this->assertTrue($result > 1.3 && $result < 1.6);
        $this->assertTrue(Stopwatch::get($name) > 1.3 && Stopwatch::get($name) < 1.6);

        Stopwatch::start($name);
        usleep(1 * self::MICRO);
        $result = Stopwatch::stop($name);

        $this->assertTrue($result > 2.3 && $result < 2.6);
        $this->assertTrue(Stopwatch::get($name) > 2.3 && Stopwatch::get($name) < 2.6);
    }

    /**
     * @see \SimpleProfiler\Stopwatch::get
     * @see \SimpleProfiler\Stopwatch::stop
     */
    public function testGetAll()
    {
        $this->assertSame(0.0, Stopwatch::get('timer1'));
        $this->assertSame(0.0, Stopwatch::get('timer2'));

        Stopwatch::start('timer1');
        usleep(0.1 * self::MICRO);
        Stopwatch::stop('timer1');

        Stopwatch::start('timer2');
        usleep(0.2 * self::MICRO);
        Stopwatch::stop('timer2');

        $this->assertSame(['timer1', 'timer2'], array_keys(Stopwatch::getAll()));
    }
}
