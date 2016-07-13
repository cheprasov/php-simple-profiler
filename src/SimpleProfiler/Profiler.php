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

    const VERSION = '1.1.0';

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
        $link = &self::$workTimers[$name];
        $link = microtime(true);
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
            ++self::$timerCounters[$name];
            self::$timers[$name] += $time - self::$workTimers[$name];
        }
        unset(self::$workTimers[$name]);
    }

    /**
     * @param string $name
     * @param int $count
     */
    public static function count($name, $count = 1) {
        if (isset(self::$counters[$name])) {
            self::$counters[$name] += $count;
        } else {
            self::$counters[$name] = $count;
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
            if (strpos($name, self::GROUP_DELIMITER)) { // pos > 0
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
     * @param string[] $fields
     * @return string
     */
    public static function getTimerTableStat($fields = []) {
        if (!$fields) {
            $fields = ['group', 'name', 'count', 'time', 'single', 'cost'];
        }
        $stats = self::getTimerStat();
        $Table = new Console_Table();
        $Table->setHeaders($fields);
        $first = true;
        foreach ($stats as $item) {
            if ($first) {
                $first = false;
            } else {
                $Table->addSeparator();
            }
            if (isset($item['name'])) {
                self::addRowToTable($Table, $item, $fields);
            } else {
                foreach ($item as $elem) {
                    self::addRowToTable($Table, $elem, $fields);
                }
            }
        }
        return trim($Table->getTable());
    }

    /**
     * @param array $fields
     */
    public static function echoTimerStat($fields = []) {
        echo "\n", self::getTimerTableStat($fields), "\n";
    }

    /**
     * @return array
     */
    public static function getCounterStat() {
        $result = [];
        foreach (self::$counters as $name => $count) {
            $result[] = [
                'name'  => $name,
                'count' => $count,
            ];
        }
        return $result;
    }

    /**
     * @return string
     */
    public static function getCounterTableStat() {
        $stats = self::getCounterStat();
        $Table = new Console_Table();
        $Table->setHeaders(['name', 'count']);
        foreach ($stats as $item) {
            self::addRowToTable($Table, $item, ['name', 'count']);
        }
        return trim($Table->getTable());
    }

    /**
     *
     */
    public static function echoCounterStat() {
        echo "\n", self::getCounterTableStat(), "\n";
    }

    /**
     * @param Console_Table $Table
     * @param array $item
     * @param string[] $fields
     */
    protected static function addRowToTable(Console_Table $Table, array $item, array $fields) {
        $row = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $item)) {
                $row[] = $item[$field];
            }
        }
        $Table->addRow($row);
    }
}
