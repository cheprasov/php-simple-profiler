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
namespace SimpleProfiler;

class Timer {

    /**
     * @var string
     */
    protected $method;

    /**
     * @param string $method
     */
    public function __construct($method) {
        Profiler::start($this->method = $method);
    }

    public function __destruct() {
        Profiler::stop($this->method);
    }
}
