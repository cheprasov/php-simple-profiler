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
    protected static $callsTree;

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
     * @return array|null
     */
    public static function getRawData()
    {
        return self::$callsTree;
    }

    /**
     * @return string
     */
    public static function getLog(): string
    {
        if (empty(self::$callsTree)) {
            return '';
        }
        self::$callsTree['duration'] += microtime(true) - self::$callsTree['timeBeg'];
        $output = self::formatElement(self::$callsTree);

        return $output;
    }

    protected static function formatData($data)
    {
        return json_encode($data);
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

        $spaces = str_repeat(' ', 2);

        if (!$level) {
            $total = sprintf('%02.6f', $element['duration']);
            $output .= "Profiler, total: {$total} sec \n";
        } else {
            $output .= str_repeat($spaces, $level) . PHP_EOL;
            $output .= str_repeat($spaces, $level - 1);
            $output .= '> ' . $element['name'] . PHP_EOL;

            if (!empty($element['data'])) {
                $data = self::formatData($element['data']);
                $output .= str_repeat($spaces, $level - 1);
                $output .= "  | data: {$data}\n";
            }

            $output .= str_repeat($spaces, $level - 1);
            $count = $element['count'];
            $avg = sprintf('%02.6f', $element['duration'] / $count);
            $total = sprintf('%02.6f', $element['duration']);
            $cost = sprintf('%02.1f', $element['duration'] / ($totalDuration ?: 1) * 100);

            $output .= "  | cost: {$cost} %, count: {$count}, avg: {$avg} sec, total: {$total} sec\n";
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
     * @param string $regExpFilter
     */
    public static function includeFile(string $filename, bool $withArguments = false, bool $withResult = false, string $regExpFilter = '')
    {
        $file = file_get_contents($filename);
        $file = self::injectProfilerToCode($file, $withArguments, $withResult, $regExpFilter);
        $file = trim($file);

        print_r($file);

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
     * @param bool $withArguments
     * @param bool $withResult
     * @param string $regExpFilter
     * @return string
     */
    protected static function injectProfilerToCode(string $source, bool $withArguments, bool $withResult, string $regExpFilter = '')
    {
        $tokens = token_get_all($source);
        unset($source);

        $getNextToken = function () use (&$tokens) {
            static $i = 0;
            $token = $tokens[$i++] ?? null;
            if (is_string($token)) {
                return [null, $token, 0];
            }
            return $token;
        };

        if ($withArguments || $withResult) {
            $unitClass = '\\' . DetailedFuncUnit::class;
        } else {
            $unitClass = '\\' . FuncUnit::class;
        }
        $arguments = $withArguments ? 'func_get_args()' : 'null';

        $functionFound = false;
        $stack = [];

        $code = '';
        while (null !== ($token = $getNextToken())) {
            list($id, $text, $lineNum) = $token;
            $code .= $text;

            if ($id === T_FUNCTION) {
                $functionName = null;
                $functionFound = true;
                $functionLine = $lineNum;
                $functionColumn = strlen($code) - (strrpos($code, "\n") ?: 0);

                // try to find function name
                while (null !== ($token2 = $getNextToken())) {
                    list($id2, $text2, ) = $token2;
                    $code .= $text2;
                    if ($id2 === T_STRING && preg_match('/^\w+$/', $text2)) {
                        $functionName = $text2;
                        break;
                    }
                    if ($text2 === '(') {
                        break;
                    }
                }

                $stack = [];
                continue;
            }

            if (($id === T_RETURN || $id === T_THROW) && $withResult) {
                if (!$regExpFilter || preg_match($regExpFilter, $functionName ?? '{closure}')) {
                    $code .= ' $ProfilerFuncUnit->result =';
                }
                continue;
            }

            if ($functionFound && !isset($id)) {
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
                    if (!$regExpFilter || preg_match($regExpFilter, $functionName ?? '{closure}')) {
                        $code .= "\$ProfilerFuncUnit = {$unitClass}::create(__METHOD__, {$functionLine}, {$functionColumn}, {$arguments});";
                    }
                    continue;
                }
            }

        }
        return $code;
    }
}
