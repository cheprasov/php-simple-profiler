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

use SimpleProfiler\Profiler;

class ProfilerTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        Profiler::clear();
    }

    /**
     * @param string $name
     * @param int $count
     * @param float $full_time
     * @param float $average_time
     * @param array $actual
     */
    protected function checkTimer($name, $count, $full_time, $average_time, $actual) {
        $this->assertSame($name, $actual['name']);
        $this->assertSame($count, $actual['count']);
        $this->assertSame($full_time, substr($actual['full_time'], 0, strlen($full_time)));
        $this->assertSame($average_time, substr($actual['average_time'], 0, strlen($average_time)));
    }

    public function testCommonSimple() {
        Profiler::startTimer('foo');
        sleep(1);
        Profiler::stopTimer();

        $result = Profiler::getTimerStat();

        $this->assertSame(1, count($result));
        $this->checkTimer('foo', 1, '1.00', '1.00', $result['default']['foo']);
    }

    public function testCommonSimple2() {
        Profiler::startTimer('foo');
        usleep(300000);
        Profiler::stopTimer();

        Profiler::startTimer('bar');
        usleep(200000);
        Profiler::stopTimer();

        Profiler::startTimer('bar');
        usleep(100000);
        Profiler::stopTimer();

        $result = Profiler::getTimerStat();

        $this->assertSame(2, count($result['default']));
        $this->checkTimer('foo', 1, '0.30', '0.30', $result['default']['foo']);
        $this->checkTimer('bar', 2, '0.30', '0.15', $result['default']['bar']);
    }

    public function testCommonGroups() {
        for ($i = 0; $i < 10; ++$i) {
            Profiler::startTimer('group.foo');
            usleep(10000);
            Profiler::stopTimer();
            Profiler::startTimer('group.bar');
            usleep(15000);
            Profiler::stopTimer();
        }

        $result = Profiler::getTimerStat();

        $this->assertSame(1, count($result));
        $this->checkTimer('foo', 10, '0.10', '0.010', $result['group']['foo']);
        $this->checkTimer('bar', 10, '0.15', '0.015', $result['group']['bar']);
    }

    public function testCommonGroups2() {
        for ($i = 0; $i < 10; ++$i) {
            Profiler::startTimer('foo.one');
            usleep(10000);
            Profiler::stopTimer();
            Profiler::startTimer('bar.one');
            usleep(15000);
            Profiler::stopTimer();
        }

        $result = Profiler::getTimerStat();

        $this->assertSame(2, count($result));
        $this->checkTimer('one', 10, '0.10', '0.010', $result['foo']['one']);
        $this->checkTimer('one', 10, '0.15', '0.015', $result['bar']['one']);
    }

    public function testCounter1() {
        Profiler::counter('foo');
        Profiler::counter('bar');
        Profiler::counter('par');
        Profiler::counter('foo');
        Profiler::counter('foo');
        Profiler::counter('bar', 3);

        $this->assertSame([
            ['name' => 'foo', 'count' => 3],
            ['name' => 'bar', 'count' => 4],
            ['name' => 'par', 'count' => 1],
        ], Profiler::getCounterStat());
    }

    public function testInjectProfilerToCode() {
        $file = trim(php_strip_whitespace(__DIR__ . '/../TestClass.php'));
        $result = Profiler::injectProfilerToCode($file);

        $this->assertSame(11, substr_count($result, '$SimpleProfilerTimer = new \SimpleProfiler\Timer'));

        $expect =<<<'EXPECT'
<?php
 namespace SimpleProfiler\Tests; $foo = function() {$SimpleProfilerTimer = new \SimpleProfiler\Timer('Profiler.' . __METHOD__ . '_h'); return 'foo'; }; $foo(); function bar() {$SimpleProfilerTimer = new \SimpleProfiler\Timer('Profiler.' . __METHOD__); return 'bar'; } bar(); class TestClass { public static function getSomeData() {$SimpleProfilerTimer = new \SimpleProfiler\Timer('Profiler.' . __METHOD__); return 'some data'; } public static function get_random_int ($min = 0, $max = 100) {$SimpleProfilerTimer = new \SimpleProfiler\Timer('Profiler.' . __METHOD__); return mt_rand($min, $max); } public static function __strange__name__ ($_a = 'Alexander') {$SimpleProfilerTimer = new \SimpleProfiler\Timer('Profiler.' . __METHOD__); return $_a; } public static function multi_line_name ( $a, $b, $c ) {$SimpleProfilerTimer = new \SimpleProfiler\Timer('Profiler.' . __METHOD__); return $a . $b . $c; } public static function anonymous() {$SimpleProfilerTimer = new \SimpleProfiler\Timer('Profiler.' . __METHOD__); $get42 = function() {$SimpleProfilerTimer = new \SimpleProfiler\Timer('Profiler.' . __METHOD__ . '_5h'); return 42; }; return $get42(); } public static function sleep() {$SimpleProfilerTimer = new \SimpleProfiler\Timer('Profiler.' . __METHOD__); usleep(100); } public static function withParams($function = null) {$SimpleProfilerTimer = new \SimpleProfiler\Timer('Profiler.' . __METHOD__); return $function; } public static function withParams2($a = 'function', $b = '){', $c = '{}') {$SimpleProfilerTimer = new \SimpleProfiler\Timer('Profiler.' . __METHOD__); return $a; } }
EXPECT;

        $this->assertSame($expect, $result);
    }

    public function testProfilerCode() {
        Profiler::loadFile(__DIR__ . '/../TestClass.php');

        for ($i = 0 ; $i < 10; $i++) {
            \SimpleProfiler\Tests\TestClass::anonymous();
        }
        \SimpleProfiler\Tests\TestClass::sleep();
        for ($i = 0 ; $i < 2; $i++) {
            \SimpleProfiler\Tests\TestClass::get_random_int();
        }

        $data = Profiler::getTimerStat();

        $this->assertSame(6, count($data['Profiler']));

        $this->assertSame(
            [
                'SimpleProfiler\Tests\{closure}_h',
                'SimpleProfiler\Tests\bar',
                'SimpleProfiler\Tests\TestClass::anonymous',
                'SimpleProfiler\Tests\TestClass::SimpleProfiler\Tests\{closure}_5h',
                'SimpleProfiler\Tests\TestClass::sleep',
                'SimpleProfiler\Tests\TestClass::get_random_int',
            ],
            array_keys($data['Profiler'])
        );

        $this->assertSame(1, $data['Profiler']['SimpleProfiler\Tests\{closure}_h']['count']);
        $this->assertSame(1, $data['Profiler']['SimpleProfiler\Tests\bar']['count']);
        $this->assertSame(10, $data['Profiler']['SimpleProfiler\Tests\TestClass::anonymous']['count']);
        $this->assertSame(10, $data['Profiler']['SimpleProfiler\Tests\TestClass::SimpleProfiler\Tests\{closure}_5h']['count']);
        $this->assertSame(1, $data['Profiler']['SimpleProfiler\Tests\TestClass::sleep']['count']);
        $this->assertSame(2, $data['Profiler']['SimpleProfiler\Tests\TestClass::get_random_int']['count']);
    }
}
