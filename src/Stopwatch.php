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

class Stopwatch
{
    /**
     * @var array
     */
    protected static $timers = [];

    public static function clear()
    {
        self::$timers = [];
    }

    /**
     * @param string $name
     */
    public static function start(string $name)
    {
        if (!isset(self::$timers[$name])) {
            self::$timers[$name] = [
                'duration' => 0,
            ];
        }
        $timer = &self::$timers[$name];
        $timer['timeBeg'] = microtime(true);
    }

    /**
     * @param string $name
     * @return float
     */
    public static function stop(string $name): float
    {
        $microtime = microtime(true);
        if (!isset(self::$timers[$name])) {
            return 0;
        }
        $timer = &self::$timers[$name];
        if (isset($timer['timeBeg'])) {
            $timer['duration'] += $microtime - $timer['timeBeg'];
            unset($timer['timeBeg']);
        }
        return $timer['duration'];
    }

    /**
     * @param string $name
     * @return float
     */
    public static function get(string $name): float
    {
        return self::$timers[$name]['duration'] ?? 0;
    }

    /**
     * @param string $name
     * @return array
     */
    public static function getAll(): array
    {
        $result = [];
        foreach (self::$timers as $name => $data) {
            $result[$name] = $data['duration'] ?? 0;
        }
        return $result;
    }
}
