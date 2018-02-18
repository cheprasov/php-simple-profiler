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

class Profiler {

    const VERSION = '2.0.0';

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
    public static function startTimer($name) {
        self::$timerNames[] = $name;
        $time = microtime(true);

        if (!isset(self::$timerCounters[$name])) {
            self::$timerCounters[$name] = 1;
            self::$timers[$name] = 0;
        } else {
            ++self::$timerCounters[$name];
        }

        self::$workTimers[$name] = $time;
    }

    /**
     * @param string|null $name
     */
    public static function stopTimer($name = null) {
        $time = microtime(true);
        if (!$name) {
            $name = array_pop(self::$timerNames);
        }
        if (!isset(self::$workTimers[$name])) {
            return;
        }

        self::$timers[$name] += $time - self::$workTimers[$name];
        unset(self::$workTimers[$name]);
    }

    /**
     * @param string $name
     * @param int $count
     */
    public static function counter($name, $count = 1) {
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
            if (strpos($name, self::GROUP_DELIMITER)) { // pos > 0
                list($group, $shortName) = explode(self::GROUP_DELIMITER, $name, 2);
            } else {
                $group = 'default';
                $shortName = $name;
            }

            if (!isset($result[$group])) {
                $result[$group] = [];
            }
            $link = &$result[$group];
            $groups[] = &$result[$group];

            $link[$shortName] = [
                'group' => $group,
                'name' => $shortName,
                'count' => self::$timerCounters[$name],
                'full_time' => $time,
                'average_time' => $time / self::$timerCounters[$name],
                'cost' => null,
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
            $time = 0;
            foreach ($items as $item) {
                $time += $item['full_time'];
            }
            foreach ($items as $itemName => $item) {
                $groups[$groupName][$itemName]['cost'] = round($item['full_time'] / $time * 100, 1) . ' %';
            }
        }
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
    public static function getLog() {
        $log = [];
        foreach (self::getTimerStat() as $group_name => $group) {
            $log[] = "# Group [ {$group_name} ]";
            $log[] = '';
            $i = 0;
            foreach ($group as $item) {
                $i++;
                $log[] = "{$i}) {$item['name']}";
                $avg_time = sprintf('%02.6f', $item['average_time']);
                $full_time = sprintf('%02.6f', $item['full_time']);
                $log[] = "   count: {$item['count']}, avg_time: {$avg_time} sec, full_time: {$full_time} sec";
                $cost = (int)round(trim($item['cost'], '%'));
                $log[] = '   cost: [' . str_repeat('-', $cost) . '] ' . $item['cost'];
                $log[] = '';
            }
            $log[] = '';
        }

        if ($counters = self::getCounterStat()) {
            $log[] = '# COUNTERS:';
            foreach ($counters as $counter) {
                $log[] = " > {$counter['name']} : {$counter['count']}";
            }
            $log[] = '';
        }

        return implode(PHP_EOL, $log);
    }

    /**
     * @param string $filename
     * @param bool $inject_profiler
     */
    public static function loadFile($filename, $inject_profiler = true) {
        if (!$inject_profiler) {
            include($filename);
            return;
        }

        $file = trim(php_strip_whitespace($filename));
        $file = self::injectProfilerToCode($file);

        if (substr($file, 0, 5) === '<?php') {
            $file = trim(substr($file, 5));
        }

        if (substr($file, -2) === '?>') {
            $file = trim(substr($file, 0, -2));
        }

        eval($file);
    }

    /**
     * @param $source
     * @return string
     */
    public static function injectProfilerToCode($source) {
        $function_positions = self::getFunctionPositions($source);
        foreach ($function_positions as $pos) {
            $source = self::injectProfilerByFunctionPosition($source, $pos);
        }
        return $source;
    }

    /**
     * @param string $source
     * @param int $pos
     * @return string
     */
    protected static function injectProfilerByFunctionPosition($source, $pos) {
        $info = self::getFunctionInfo($source, $pos);

        if ($info['name']) {
            $name = '__METHOD__';
        } else {
            $name =  '__METHOD__ . \'#' . base_convert($pos, 10, 36) . '\'';
        }

        $offset = $info['body_pos'];
        $source = substr($source, 0, $offset + 1)
            . '$SimpleProfilerTimer = new \SimpleProfiler\Timer(\'Profiler.\' . ' . $name . ');'
            . substr($source, $offset + 1);

        return $source;
    }

    /**
     * @param string $source
     * @param int $pos
     * @return array
     */
    protected static function getFunctionInfo($source, $pos) {
        $params_pos = strpos($source, '(', $pos);
        $name = substr($source, $pos + 8, $params_pos - $pos - 8);

        $stack = ['('];
        $i = $params_pos;
        while (isset($source[++$i])) {

            $chr = $source[$i];
            $last = $stack[count($stack) - 1];

            if ($chr === '\'' && $last === '\'' && $source[$i - 1] !== '\\') {
                array_pop($stack);
                continue;
            }

            if ($chr === '"' && $last === '"' && $source[$i - 1] !== '\\') {
                array_pop($stack);
                continue;
            }

            if ($last === '\'' || $last === '"') {
                continue;
            }

            if ($chr === '\'' || $chr === '"') {
                array_push($stack, $chr);
                continue;
            }

            if ($last === '(' && $chr === ')') {
                array_pop($stack);
                if (!$stack) {
                    $i++;
                    break;
                }
            }

            if ($chr === '(') {
                array_push($stack, $chr);
            }
        }

        $func_body = strpos($source, '{', $i);

        return [
            'name' => trim($name),
            'body_pos' => $func_body,
        ];
    }

    /**
     * @param string $source
     * @return int[]
     */
    protected static function getFunctionPositions($source) {
        $function_positions = [];
        $code = '';
        $tokens = token_get_all($source);

        foreach ($tokens as $token) {
            if (is_string($token)) {
                $code .= $token;
                continue;
            }
            list($id, $text) = $token;
            if ($id === T_FUNCTION) {
                $function_positions[] = strlen($code);
            }
            $code .= $text;
        }

        if (count($function_positions) > 1) {
            krsort($function_positions);
        }

        return $function_positions;
    }
}
