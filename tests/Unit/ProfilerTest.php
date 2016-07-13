<?php
/**
 * This file is part of RedisClient.
 * git: https://github.com/cheprasov/php-simple-profiler
 *
 * (C) Alexander Cheprasov <cheprasov.84@ya.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleProfiler\Profiler;

class ProfilerTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        Profiler::clear();
    }

    /**
     * @param string $name
     * @param int $count
     * @param float $time
     * @param float $single
     * @param array $actual
     */
    protected function checkTimer($name, $count, $time, $single, $actual) {
        $this->assertSame($name, $actual['name']);
        $this->assertSame($count, $actual['count']);
        $this->assertSame($time, substr($actual['time'], 0, strlen($time)));
        $this->assertSame($single, substr($actual['single'], 0, strlen($single)));
    }

    public function testCommonSimple() {
        Profiler::start('foo');
        sleep(1);
        Profiler::stop();

        $result = Profiler::getTimerStat();

        $this->assertSame(1, count($result));
        $this->checkTimer('foo', 1, '1.00', '1.00', $result['foo']);
    }

    public function testCommonSimple2() {
        Profiler::start('foo');
        usleep(300000);
        Profiler::stop();

        Profiler::start('bar');
        usleep(200000);
        Profiler::stop();

        Profiler::start('bar');
        usleep(100000);
        Profiler::stop();

        $result = Profiler::getTimerStat();

        $this->assertSame(2, count($result));
        $this->checkTimer('foo', 1, '0.30', '0.30', $result['foo']);
        $this->checkTimer('bar', 2, '0.30', '0.15', $result['bar']);
    }

    public function testCommonGroups() {
        for ($i = 0; $i < 10; ++$i) {
            Profiler::start('group.foo');
            usleep(10000);
            Profiler::stop();
            Profiler::start('group.bar');
            usleep(15000);
            Profiler::stop();
        }

        $result = Profiler::getTimerStat();

        $this->assertSame(1, count($result));
        $this->checkTimer('foo', 10, '0.10', '0.010', $result['group']['foo']);
        $this->checkTimer('bar', 10, '0.15', '0.015', $result['group']['bar']);
    }

    public function testCommonGroups2() {
        for ($i = 0; $i < 10; ++$i) {
            Profiler::start('foo.one');
            usleep(10000);
            Profiler::stop();
            Profiler::start('bar.one');
            usleep(15000);
            Profiler::stop();
        }

        $result = Profiler::getTimerStat();

        $this->assertSame(2, count($result));
        $this->checkTimer('one', 10, '0.10', '0.010', $result['foo']['one']);
        $this->checkTimer('one', 10, '0.15', '0.015', $result['bar']['one']);
    }

    public function testTableCommonGroups() {
        for ($i = 0; $i < 10; ++$i) {
            Profiler::start('foo.one');
            usleep(10000);
            Profiler::stop();
            Profiler::start('bar.two');
            usleep(15000);
            Profiler::stop();
        }

        $this->assertSame(
            "+-------+-------+------+\n" .
            "| cost  | count | name |\n" .
            "+-------+-------+------+\n" .
            "| 100 % | 10    | one  |\n" .
            "+-------+-------+------+\n" .
            "| 100 % | 10    | two  |\n" .
            "+-------+-------+------+",
            Profiler::getTimerTableStat(['cost', 'count', 'name'])
        );
    }

    public function testCounter1() {
        Profiler::count('foo');
        Profiler::count('bar');
        Profiler::count('par');
        Profiler::count('foo');
        Profiler::count('foo');
        Profiler::count('bar', 3);

        $this->assertSame([
            ['name' => 'foo', 'count' => 3],
            ['name' => 'bar', 'count' => 4],
            ['name' => 'par', 'count' => 1],
        ], Profiler::getCounterStat());
    }
}
