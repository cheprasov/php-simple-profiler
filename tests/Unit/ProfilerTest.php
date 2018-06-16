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

class ProfilerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Profiler::clear();
    }
}
