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
namespace SimpleProfiler;

use Console_Table;

class Profiler {

    const VERSION = '1.0.0';

    const GROUP_DELIMITER = '.';

    /**
     * @var array
     */
    protected static $timers = [];

    /**
     * @var array
     */
    protected static $timerCounters = [];

    /**
     * @var string[]
     */
    protected static $timerNames = [];

    /**
     * @var array
     */
    protected static $counters = [];

    /**
     * @var array
     */
    protected static $workTimers = [];

    /**
     *
     */
    public static function clear() {
        self::$timers = [];
        self::$timerCounters = [];
        self::$timerNames = [];
        self::$counters = [];
        self::$workTimers = [];
    }

    /**
     * @param string $name
     */
    public static function start($name) {
        self::$timerNames[] = $name;
        self::$workTimers[$name] = microtime(true);
    }

    /**
     * @param string|null $name
     */
    public static function stop($name = null) {
        $time = microtime(true);
        if (!$name) {
            $name = array_pop(self::$timerNames);
        }
        if (!isset(self::$workTimers[$name])) {
            return;
        }
        if (!isset(self::$timerCounters[$name])) {
            self::$timerCounters[$name] = 1;
            self::$timers[$name] = $time - self::$workTimers[$name];
        } else {
            self::$timerCounters[$name]++;
            self::$timers[$name] += $time - self::$workTimers[$name];
        }
        unset(self::$workTimers[$name]);
    }

    /**
     * @param string $name
     * @param int $incr
     */
    public static function count($name, $incr = 1) {
        if (isset(self::$counters[$name])) {
            self::$counters[$name] += $incr;
        } else {
            self::$counters[$name] = $incr;
        }
    }

    /**
     * @return array
     */
    public static function getTimerStat() {
        $result = [];
        $groups = [];
        foreach (self::$timers as $name => $time) {
            $group = null;
            if (strpos($name, self::GROUP_DELIMITER)) { // point pos > 0
                list($group, $shortName) = explode(self::GROUP_DELIMITER, $name, 2);
                if (!isset($result[$group])) {
                    $result[$group] = [];
                }
                $link = &$result[$group];
                $groups[] = &$result[$group];
            } else {
                $shortName = $name;
                $link = &$result;
            }

            $link[$shortName] = [
                'group'  => $group,
                'name'   => $shortName,
                'count'  => self::$timerCounters[$name],
                'time'   => $time,
                'single' => $time / self::$timerCounters[$name],
                'cost'   => null,
            ];
        }
        if ($groups) {
            self::calculateGroupData($groups);
        }
        return $result;
    }

    /**
     * @param array $groups
     */
    protected static function calculateGroupData(array &$groups) {
        foreach ($groups as $groupName => $items) {
            $minTime = PHP_INT_MAX;
            foreach ($items as $item) {
                $minTime = min($minTime, $item['single']);
            }
            foreach ($items as $itemName => $item) {
                $groups[$groupName][$itemName]['cost'] = round($item['single'] / $minTime * 100) . ' %';
            }
        }
    }

    /**
     *
     */
    public static function echoTimerStat() {
        $stats = self::getTimerStat();
        $Table = new Console_Table();
        $Table->setHeaders(['GROUP', 'NAME', 'COUNT', 'TIME', 'SINGLE', 'COST']);
        $first = true;
        foreach ($stats as $item) {
            if ($first) {
                $first = false;
            } else {
                $Table->addSeparator();
            }
            if (isset($item['name'])) {
                self::addRowToTable($Table, $item);
            } else {
                foreach ($item as $elem) {
                    self::addRowToTable($Table, $elem);
                }
            }
        }
        echo "\n", $Table->getTable();
    }

    /**
     * @param Console_Table $Table
     * @param array $item
     */
    protected static function addRowToTable(Console_Table $Table, array $item) {
        $Table->addRow($item);
    }
}
