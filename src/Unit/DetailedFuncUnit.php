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

class DetailedFuncUnit extends FuncUnit
{
    /**
     * @var array
     */
    protected $args;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @var bool
     */
    protected $resultIsDefined = false;

    /**
     * @param string $methodName
     * @param array $args
     */
    protected function __construct(string $methodName, array $args = null)
    {
        if (isset($args)) {
            $this->args = array_map(['self', 'prepareValue'], $args);
        }
        parent::__construct($methodName);
    }

    public function getKey()
    {
        return null;
    }

    public function getData()
    {
        $data = [];
        if ($this->args) {
            $data['arguments'] = $this->args;
        }
        if ($this->resultIsDefined) {
            $data['result'] = $this->result;
        }
        return $data ?: null;
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
