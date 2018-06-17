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

use SimpleProfiler\Unit\DetailedFunctionUnit;

class DetailedFunctionUnitTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        /** @var DetailedFunctionUnit $Unit */
        $Unit = DetailedFunctionUnit::create('TestMethod', 10, 15);
        $Unit->setArguments([1, 2, 3, 'foo']);
        $Unit->result = 42;

        $this->assertSame('TestMethod 10:15', $Unit->getName());
        $this->assertSame(null, $Unit->getKey());
        $this->assertSame(['arguments' => [1, 2, 3, 'foo'], 'result' => 42], $Unit->getData());
    }
}
