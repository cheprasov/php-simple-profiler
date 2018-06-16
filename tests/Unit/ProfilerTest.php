<?php
/**
 * This file is part of RedisClient.
 * git: https://github.com/cheprasov/php-simple-profiler
 *
 * (C) Alexander Cheprasov <acheprasov84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SimpleProfiler\Tests\Unit;

use SimpleProfiler\Profiler;
use SimpleProfiler\Unit\DetailedFunctionUnit;
use SimpleProfiler\Unit\FunctionUnit;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Profiler::clear();
        Profiler::setProfilerUnitClass(\SimpleProfiler\Unit\FunctionUnit::class);
        Profiler::setProfilerUnitVarName('$ProfilerUnit');
    }

    public function providerSetProfilerUnitVarName()
    {
        return [
            'line ' . __LINE__ => [
                'name' => 'foo',
                'expect' => false,
            ],
            'line ' . __LINE__ => [
                'name' => '$$foo',
                'expect' => false,
            ],
            'line ' . __LINE__ => [
                'name' => '$ foo',
                'expect' => false,
            ],
            'line ' . __LINE__ => [
                'name' => '_foo',
                'expect' => false,
            ],
            'line ' . __LINE__ => [
                'name' => '$_foo',
                'expect' => true,
            ],
            'line ' . __LINE__ => [
                'name' => '$ProfilerUnit',
                'expect' => true,
            ],
        ];
    }

    /**
     * @see \SimpleProfiler\Profiler::setProfilerUnitVarName
     * @dataProvider providerSetProfilerUnitVarName
     */
    public function testSetProfilerUnitVarName($name, $expect)
    {
        $this->assertSame($expect, Profiler::setProfilerUnitVarName($name));
    }

    public function providerSetProfilerUnitClass()
    {
        return [
            'line ' . __LINE__ => [
                'name' => FunctionUnit::class,
                'expect' => true,
            ],
            'line ' . __LINE__ => [
                'name' => DetailedFunctionUnit::class,
                'expect' => true,
            ],
            'line ' . __LINE__ => [
                'name' => 'SomeWrong',
                'expect' => false,
            ],
            'line ' . __LINE__ => [
                'name' => Profiler::class,
                'expect' => false,
            ],
        ];
    }

    /**
     * @see \SimpleProfiler\Profiler::setProfilerUnitClass
     * @dataProvider providerSetProfilerUnitClass
     */
    public function testSetProfilerUnitClass($name, $expect)
    {
        $this->assertSame($expect, Profiler::setProfilerUnitClass($name));
    }

    public function providerInjectProfilerUnitToCode()
    {
        return [
            'line' . __LINE__ => [
                'unit' => FunctionUnit::class,
                'filter' => null,
                'code' => '<?php
                    class TestClass
                    {
                        public static function someFunction(int $a = 10): int
                        {
                            return $a * 2;
                        }
                    }
                ',
                'expect' => '<?php
                    class TestClass
                    {
                        public static function someFunction(int $a = 10): int
                        {$ProfilerUnit = \SimpleProfiler\Unit\FunctionUnit::create(__METHOD__, 4, 47);
                            return $a * 2;
                        }
                    }
                ',
            ],
            'line' . __LINE__ => [
                'unit' => DetailedFunctionUnit::class,
                'filter' => null,
                'code' => '<?php
                    class TestClass
                    {
                        public static function someFunction(int $a = 10): int
                        {
                            return $a * 2;
                        }
                    }
                ',
                'expect' => '<?php
                    class TestClass
                    {
                        public static function someFunction(int $a = 10): int
                        {$ProfilerUnit = \SimpleProfiler\Unit\DetailedFunctionUnit::create(__METHOD__, 4, 47);$ProfilerUnit->setArguments(func_get_args());
                            return $ProfilerUnit->result = $a * 2;
                        }
                    }
                ',
            ],
            'line' . __LINE__ => [
                'unit' => FunctionUnit::class,
                'filter' => '/^\{closure\}$/',
                'code' => '<?php
                    $get42 = function() {return 42};
                ',
                'expect' => '<?php
                    $get42 = function() {$ProfilerUnit = \SimpleProfiler\Unit\FunctionUnit::create(__METHOD__, 2, 38);return 42};
                ',
            ],
            'line' . __LINE__ => [
                'unit' => DetailedFunctionUnit::class,
                'filter' => null,
                'code' => '<?php
                    namespace Test\Some;
                    abstract class TestClass
                    {
                        abstract public function getName();

                        public function /*oops*/ test /*oops*/ (   )
                        {
                            return self::withParams(function(){return 42;});
                        }

                        /**
                         * @param null $function
                         * @return null
                         */
                        static protected function withParams(\Closure $function = null) {
                            if (!$result = $function()) {
                                throw new \Exception("Some message");
                            }
                            return $result;
                        }
                    }
                ',
                'expect' => '<?php
                    namespace Test\Some;
                    abstract class TestClass
                    {
                        abstract public function getName();

                        public function /*oops*/ test /*oops*/ (   )
                        {$ProfilerUnit = \SimpleProfiler\Unit\DetailedFunctionUnit::create(__METHOD__, 7, 40);$ProfilerUnit->setArguments(func_get_args());
                            return $ProfilerUnit->result = self::withParams(function(){$ProfilerUnit = \SimpleProfiler\Unit\DetailedFunctionUnit::create(__METHOD__, 9, 85);$ProfilerUnit->setArguments(func_get_args());return $ProfilerUnit->result = 42;});
                        }

                        /**
                         * @param null $function
                         * @return null
                         */
                        static protected function withParams(\Closure $function = null) {$ProfilerUnit = \SimpleProfiler\Unit\DetailedFunctionUnit::create(__METHOD__, 16, 50);$ProfilerUnit->setArguments(func_get_args());
                            if (!$result = $function()) {
                                throw $ProfilerUnit->result = new \Exception("Some message");
                            }
                            return $ProfilerUnit->result = $result;
                        }
                    }
                ',
            ],
            'line' . __LINE__ => [
                'unit' => FunctionUnit::class,
                'filter' => '/^get4(3|4)$/',
                'code' => '<?php
                    function get42(): int
                    {
                        return 42;
                    };
                    function get43(): int
                    {
                        return 43;
                    };
                    function get44(): int
                    {
                        return 44;
                    };
                    return get42() + get44();
                ',
                'expect' => '<?php
                    function get42(): int
                    {
                        return 42;
                    };
                    function get43(): int
                    {$ProfilerUnit = \SimpleProfiler\Unit\FunctionUnit::create(__METHOD__, 6, 29);
                        return 43;
                    };
                    function get44(): int
                    {$ProfilerUnit = \SimpleProfiler\Unit\FunctionUnit::create(__METHOD__, 10, 29);
                        return 44;
                    };
                    return get42() + get44();
                ',
            ],
        ];
    }

    /**
     * @see \SimpleProfiler\Profiler::injectProfilerUnitToCode
     * @dataProvider providerInjectProfilerUnitToCode
     */
    public function testInjectProfilerUnitToCode($unit, $filter, $code, $expect)
    {
        $MethodReflection = new \ReflectionMethod(Profiler::class, 'injectProfilerUnitToCode');
        $MethodReflection->setAccessible(true);
        $result = $MethodReflection->invoke(null, $code, $unit, $filter);

        $this->assertSame($expect, $result);
    }

}
