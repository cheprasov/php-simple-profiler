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

class FuncUnit implements UnitInterface
{
    /**
     * @var string
     */
    protected $methodName;

    /**
     * @param string $methodName
     */
    public function __construct(string $methodName)
    {
        $this->methodName = $methodName;
        Profiler::addUnit($this);
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
            return 'Object:' . get_class($value);
        }
        if (is_callable($value)) {
            return 'Closure:' . get_class($value);
        }
        if (is_resource($value)) {
            return 'Resource:' . get_resource_type($value);
        }
        return 'Undefined';
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function setResult($value)
    {
        $this->result = self::prepareValue($value);
        $this->resultIsDefined = true;
    }

    public function __set($name, $value)
    {
        if ($name === 'result') {
            return $this->setResult($value);
        }
    }
}
