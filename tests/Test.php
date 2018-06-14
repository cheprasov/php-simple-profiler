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

class Test extends PHPUnit_Framework_TestCase {

    public function setUp() {
        //Profiler::clear();
    }

    public function testInjectProfilerToCode()
    {
        $filename = __DIR__ . '/TestClass.php';
        //$file = trim(file_get_contents());
        //$result = Profiler::injectProfilerToCode($file, true);
        //Profiler::profilerFile($filename, false, true);
        //Profiler::profilerFile($filename, false, false);
        Profiler::profilerFile($filename, true, true);

        \SimpleProfiler\Tests\TestClass::anonymous();
        \SimpleProfiler\Tests\TestClass::sleep(300);
        \SimpleProfiler\Tests\TestClass::get_random_int(100, 300);
        \SimpleProfiler\Tests\TestClass::test();


        print_r(Profiler::$callsTree);
        print_r(Profiler::getLog());
    }
}
