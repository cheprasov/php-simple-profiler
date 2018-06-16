<?php
/**
 * This file is part of SimpleProfiler.
 * git: https://github.com/cheprasov/php-simple-profiler
 *
 * (C) Alexander Cheprasov <acheprasov84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SimpleProfiler;

class Counter
{
    /**
     * @var array
     */
    protected static $counter = [];

    public static function clear()
    {
        self::$counter = [];
    }

    /**
     * @param string $name
     * @param int $count
     */
    public static function increment($name, int $count = 1): int
    {
        if (isset(self::$counter[$name])) {
            self::$counter[$name] += $count;
        } else {
            self::$counter[$name] = $count;
        }
        return self::$counter[$name];
    }

    /**
     * @param string|null $name
     * @param int $count
     */
    public static function decrement($name, int $count = 1): int
    {
        return self::increment($name, -$count);
    }

    /**
     * @param string $name
     * @return int
     */
    public static function get(string $name): int
    {
        return self::$counter[$name] ?? 0;
    }

    /**
     * @return array
     */
    public static function getAll(): array
    {
        return self::$counter;
    }
}
