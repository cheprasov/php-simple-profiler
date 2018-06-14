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

use SimpleProfiler\Unit\DetailedFuncUnit;
use SimpleProfiler\Unit\FuncUnit;
use SimpleProfiler\Unit\UnitInterface;

class Profiler {

    const VERSION = '3.0.0';

    /**
     * @var array
     */
    public static $callsTree;

    /**
     * @var &array|null
     */
    protected static $lastElement;

    /**
     * @return array
     */
    protected function &getLastElement()
    {
        if (!self::$callsTree) {
            self::$callsTree = [
                'parent' => null,
                'data' => null,
                'timeBeg' => microtime(true),
                'duration' => 0,
                'items' => [],
            ];
        }
        if (is_null(self::$lastElement)) {
            self::$lastElement = &self::$callsTree;
        }
        return self::$lastElement;
    }

    /**
     * @param UnitInterface $Unit
     */
    public static function addUnit(UnitInterface $Unit)
    {
        $lastElement = &self::getLastElement();
        if (!$key = $Unit->getKey()) {
            $key = count($lastElement['items']);
        }
        if (isset($lastElement['items'][$key])) {
            $lastElement['items'][$key]['count'] += 1;
        } else {
            $lastElement['items'][$key] = [
                'parent' => &$lastElement,
                'name' => $Unit->getName(),
                'count' => 1,
                'duration' => 0,
                'data' => null,
                'items' => [],
            ];
        }
        self::$lastElement = &$lastElement['items'][$key];
        self::$lastElement['timeBeg'] = microtime(true);
    }

    /**
     * @param UnitInterface $Unit
     */
    public static function closeUnit(UnitInterface $Unit)
    {
        $lastElement = &self::getLastElement();
        if (empty($lastElement['name']) || $lastElement['name'] !== $Unit->getName()) {
            // Error
            return;
        }
        $lastElement['duration'] += microtime(true) - $lastElement['timeBeg'];
        $lastElement['data'] = $Unit->getData();
        unset($lastElement['timeBeg']);
        if (empty($lastElement['items'])) {
            unset($lastElement['items']);
        }
        self::$lastElement = &$lastElement['parent'];
    }

    public static function clear()
    {
        self::$callsTree = null;
        self::$lastElement = null;
    }

    /**
     * @return array
     */
    public static function getRawData()
    {
        return self::$callsTree;
    }

    public static function getLog(): string
    {
        if (empty(self::$callsTree)) {
            return '<Empty profiler>';
        }
        self::$callsTree['duration'] += microtime(true) - self::$callsTree['timeBeg'];
        $output = self::formatElement(self::$callsTree);

        return $output;
    }

    protected static function formatData($data)
    {
        $output = [];
        if (!empty($data['arguments'])) {
            $output[] = 'arguments: [ ' . implode(', ', $data['arguments']) . ' ]';
        }
        if (!empty($data['result'])) {
            $output[] = 'result: ' . $data['result'];
        }
        return implode(', ', $output);
    }

    /**
     * @param array $element
     * @param int $level
     * @param int $totalDuration
     * @return string
     */
    protected static function formatElement($element, $level = 0, $totalDuration = 0)
    {
        $output = '';
        $hasItems = !empty($element['items']);

        $spaces = str_repeat(' ', 4);

        if (!$level) {
            $total = sprintf('%02.6f', $element['duration']);
            $output .= "Profiler, total: {$total} sec \n";
        } else {
            $output .= str_repeat($spaces, $level) . PHP_EOL;
            $output .= str_repeat($spaces, $level - 1);
            $output .= '>  ' . $element['name'] . PHP_EOL;

            if (!empty($element['data'])) {
                $data = self::formatData($element['data']);
                $output .= str_repeat($spaces, $level - 1);
                $output .= "   {$data}\n";
            }

            $output .= str_repeat($spaces, $level - 1);
            $count = $element['count'];
            $avg = sprintf('%02.6f', $element['duration'] / $count);
            $total = sprintf('%02.6f', $element['duration']);
            $cost = sprintf('%02.1f', $element['duration'] / ($totalDuration ?: 1) * 100);

            $output .= "   cost: {$cost} %, count: {$count}, avg: {$avg} sec, total: {$total} sec\n";
        }
        if ($hasItems) {
            foreach ($element['items'] as $el) {
                $output .= self::formatElement($el, $level + 1, $element['duration']);
            }
        }
        return $output;
    }

    /**
     * @param string $filename
     * @param bool $withArguments
     * @param bool $withResult
     */
    public static function profilerFile(string $filename, bool $withArguments = false, bool $withResult = false)
    {
        $file = trim(file_get_contents($filename));
        $file = self::injectProfilerToCode($file, $withArguments, $withResult);

        if (substr($file, 0, 5) === '<?php') {
            $file = substr($file, 5);
        }

        if (substr($file, -2) === '?>') {
            $file = substr($file, 0, -2);
        }

        eval($file);
    }

    /**
     * @param string $source
     * @param bool $withResult
     * @return string
     */
    public static function injectProfilerToCode(string $source, bool $withArguments, bool $withResult) {
        $tokens = token_get_all($source);
        $code = '';

        if ($withArguments || $withResult) {
            $unitClass = '\\' . DetailedFuncUnit::class;
        } else {
            $unitClass = '\\' . FuncUnit::class;
        }

        $withArguments = $withArguments ? 'func_get_args()' : 'null';

        $functionFound = false;
        $functionName = null;
        $returnPosition = 0;
        $functionColumn = 0;
        $stack = [];

        foreach ($tokens as $i => $token) {
            if (is_string($token)) {
                $id = null;
                $text = $token;
            } else {
                list($id, $text) = $token;
            }
            $code .= $text;

            if (false !== ($pos = strpos($text, "\n"))) {
                $returnPosition = strlen($code) + $pos - strlen($text) + 8; // strlen('function')
            }

            if ($id === T_FUNCTION) {
                $functionFound = true;
                $functionColumn = strlen($code) - $returnPosition;
                $stack = [];
                continue;
            }

            if ($id === T_RETURN && $withResult) {
                $code .= ' $ProfilerFuncUnit->result =';
                continue;
            }

            if ($functionFound) {
                if ($id === T_STRING && !$stack) {
                    $functionName = $text;
                    continue;
                }

                if (!isset($id)) {
                    if ($text === '(') {
                        $stack[] = '(';
                        continue;
                    }
                    if ($text === ')') {
                        array_pop($stack);
                        continue;
                    }
                    if ($text === '{' && !$stack) {
                        $functionFound = false;
                        $name =  "__METHOD__ . ' ' . __LINE__ . ':{$functionColumn}'";
                        $code .= "\$ProfilerFuncUnit = new {$unitClass}({$name}, {$withArguments});";
                        continue;
                    }
                }
            }

        }
        return $code;
    }
}
