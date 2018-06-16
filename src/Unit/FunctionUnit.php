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
namespace SimpleProfiler\Unit;

use SimpleProfiler\Profiler;

class FunctionUnit implements UnitInterface
{
    /**
     * @var string
     */
    protected $methodName;

    /**
     * @param string $methodName
     */
    protected function __construct(string $methodName)
    {
        $this->methodName = $methodName;
        Profiler::addUnit($this);
    }

    public static function create(string $methodName, int $line, int $column): UnitInterface
    {
        return new static("{$methodName} {$line}:{$column}");
    }

    public function __destruct()
    {
        Profiler::closeUnit($this);
    }

    public function getName(): string
    {
        return $this->methodName;
    }

    public function getKey()
    {
        return $this->methodName;
    }

    public function getData()
    {
        return null;
    }

    /**
     * @param $value
     * @return mixed
     */
    protected static function prepareValue($value)
    {
        if (is_scalar($value) || is_null($value)) {
            return $value;
        }
        if (is_array($value)) {
            return 'Array('. count($value) .')';
        }
        if (is_object($value)) {
            return 'Object:' . get_class($value);// . ',id:' . spl_object_hash($value);
        }
        if ($value instanceof \Closure) {
            return 'Closure:' . get_class($value);// . ',id:' . spl_object_hash($value);
        }
        if (is_resource($value)) {
            return 'Resource:' . get_resource_type($value);
        }
        return 'Undefined';
    }
}
