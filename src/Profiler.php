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

use SimpleProfiler\Unit\CollectArgumentsInterface;
use SimpleProfiler\Unit\CollectResultInterface;
use SimpleProfiler\Unit\DetailedFunctionUnit;
use SimpleProfiler\Unit\FunctionUnit;
use SimpleProfiler\Unit\UnitInterface;

class Profiler {

    const VERSION = '3.0.0';

    /**
     * @see http://php.net/manual/en/language.variables.basics.php
     */
    const REGEXP_PHP_VAR = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    protected static $profilerUnitVarName = '$ProfilerUnit';

    protected static $profilerUnitClass = FunctionUnit::class;

    /**
     * @var array
     */
    protected static $callsTree;

    /**
     * @var &array|null
     */
    protected static $lastElement;

    /**
     * @param string $profilerUnitVarName
     * @returns bool
     */
    public static function setProfilerUnitVarName(string $profilerUnitVarName): bool
    {
        if (!$profilerUnitVarName || $profilerUnitVarName[0] !== '$') {
            return false;
        }
        if (!preg_match(self::REGEXP_PHP_VAR, substr($profilerUnitVarName, 1))) {
            return false;
        }
        self::$profilerUnitVarName = $profilerUnitVarName;
        return true;
    }

    /**
     * @param mixed $profilerUnitClass
     * @returns bool
     */
    public static function setProfilerUnitClass(string $profilerUnitClass)
    {
        if (!is_subclass_of($profilerUnitClass, UnitInterface::class)) {
            return false;
        }
        self::$profilerUnitClass = $profilerUnitClass;
        return true;
    }

    /**
     * @return array
     */
    protected static function &getLastElement()
    {
        if (!self::$callsTree) {
            self::$callsTree = [
                'parent' => null,
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
        $microtime = microtime(true);
        $lastElement = &self::getLastElement();
        if (empty($lastElement['name']) || $lastElement['name'] !== $Unit->getName()) {
            // Error
            return;
        }
        $lastElement['duration'] += $microtime - $lastElement['timeBeg'];
        $lastElement['data'] = $Unit->getData();
        unset($lastElement['timeBeg']);
        if (empty($lastElement['items'])) {
            unset($lastElement['items']);
        }
        self::$lastElement = &$lastElement['parent'];
        self::$callsTree['duration'] = $microtime - self::$callsTree['timeBeg'];
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
        $output = self::formatElement(self::$callsTree);

        return $output;
    }

    /**
     * @param mixed $data
     * @return string
     */
    protected static function formatData($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array $element
     * @param int $level
     * @param int $totalDuration
     * @return string
     */
    protected static function formatElement($element, $level = 0, $totalDuration = 0, &$num = 0)
    {
        $output = '';
        $hasItems = !empty($element['items']);

        $spaces = str_repeat(' ', 2);

        if (!$level) {
            $total = sprintf('%02.6f', $element['duration']);
            $output .= "Profiler, total: {$total} sec \n";
        } else {
            $num++;
            $sp = str_repeat(' ', strlen($num));

            $output .= str_repeat($spaces, $level) . PHP_EOL;
            $output .= str_repeat($spaces, $level - 1);
            $output .=  $num . ' | ' . $element['name'] . PHP_EOL;

            if (!empty($element['data'])) {
                $data = self::formatData($element['data']);
                $output .= str_repeat($spaces, $level - 1);
                $output .= "{$sp} | data: {$data}\n";
            }

            $output .= str_repeat($spaces, $level - 1);
            $count = $element['count'];
            $avg = sprintf('%02.6f', $element['duration'] / $count);
            $total = sprintf('%02.6f', $element['duration']);
            $cost = sprintf('%02.1f', $element['duration'] / ($totalDuration ?: 1) * 100);

            $output .= "{$sp} | cost: {$cost} %, count: {$count}, avg: {$avg} sec, total: {$total} sec\n";
        }
        if ($hasItems) {
            foreach ($element['items'] as $el) {
                $output .= self::formatElement($el, $level + 1, $element['duration'], $num);
            }
        }
        return $output;
    }

    /**
     * @param string $filename
     * @param bool $withArguments
     * @param bool $withResult
     * @param string|null $regExpFilter
     */
    public static function includeFile(string $filename, string $regExpFilter = null)
    {
        $file = file_get_contents($filename);
        $file = self::injectProfilerUnitToCode($file, self::$profilerUnitClass, $regExpFilter);
        $file = trim($file);

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
     * @param string $unitClassName
     * @param string $regExpFilter
     * @return string
     */
    protected static function injectProfilerUnitToCode(string $source, string $unitClassName, string $regExpFilter = null)
    {
        $tokens = token_get_all($source);
        unset($source);

        $unitVarName = self::$profilerUnitVarName;
        $withArguments = is_subclass_of($unitClassName, CollectArgumentsInterface::class);
        $withResult = is_subclass_of($unitClassName, CollectResultInterface::class);
        $unitClass = '\\' . ltrim($unitClassName, '\\');

        $getNextToken = function () use (&$tokens) {
            static $i = 0;
            $token = $tokens[$i++] ?? null;
            if (is_string($token)) {
                return [null, $token, 0];
            }
            return $token;
        };

        $functionFound = false;
        $functionName = null;
        $functionLine = 0;
        $functionColumn = 0;

        $stack = [];

        $code = '';
        while (null !== ($token = $getNextToken())) {
            list($id, $text, $lineNum) = $token;
            $code .= $text;

            if ($id === T_FUNCTION) {
                $functionFound = true;
                $functionLine = $lineNum;
                $functionColumn = strlen($code) - (strrpos($code, "\n") ?: 0);
                $functionName = null;

                // try to find function name
                while (null !== ($token2 = $getNextToken())) {
                    list($id2, $text2, ) = $token2;
                    $code .= $text2;
                    if ($id2 === T_STRING && preg_match(self::REGEXP_PHP_VAR, $text2)) {
                        $functionName = $text2;
                        break;
                    }
                    if ($text2 === '(') {
                        if (!$functionName) {
                            $functionName = '{closure}';
                        }
                        break;
                    }
                }

                $stack = [];
                continue;
            }

            if ($withResult && $functionFound && ($id === T_RETURN || $id === T_THROW) && $functionName) {
                if (!$regExpFilter || preg_match($regExpFilter, $functionName)) {
                    $code .= " {$unitVarName}->result =";
                }
                continue;
            }

            if ($functionFound && !isset($id)) {
                if ($text === '{') {
                    if (!$stack) {
                        if (!$regExpFilter || preg_match($regExpFilter, $functionName)) {
                            $code .= "{$unitVarName} = {$unitClass}::create(__METHOD__, {$functionLine}, {$functionColumn});";
                            if ($withArguments) {
                                $code .= "{$unitVarName}->setArguments(func_get_args());";
                            }
                        }
                    }
                    $stack[] = $text;
                    continue;
                }
                if ($text === '}' && $stack[count($stack) - 1] === '{') {
                    array_pop($stack);
                    if (!$stack) {
                        $functionFound = false;
                    }
                    continue;
                }
                if ($text === ';' && !$stack) {
                    $functionFound = false;
                    continue;
                }
            }

        }
        return $code;
    }
}
