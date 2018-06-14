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

namespace SimpleProfiler\Tests;

$foo = function($f = 'foo') {
    return $f;
};
$foo();
$foo();

$bar = function() use ($foo) {
    return $foo('bar');
};
$bar();
$bar();

class TestClass {

    /**
     * @return string
     */
    public static function getSomeData()
    {
        //Some comment
        return 'some data';
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function get_random_int ($min = 0, $max = 100)
    {
        // Some comment
        return mt_rand($min, $max);
    }

// Test commented method
//    public static function CommentedMethod() {
//        return null;
//    }

/*
    Test commented method
    public static function CommentedMethod() {
        return null;
    }
*/
    /**
     * @param string $_a
     * @return string
     */
    public static function   __strange__name__  ($_a = 'Alexander')
    {
        return $_a;
    }

    /**
     * @param $a
     * @param $b
     * @param $c
     * @return string
     */
    public
    static
    function
    multi_line_name
    (
        $a,
        $b,
        $c
    )
    {
        return $a . $b . $c;
    }

    /**
     *
     */
    public static function anonymous()
    {
        $get42 = function() {
            return 42;
        };
        return $get42();
    }

    /**
     *
     */
    public static function sleep($t = 100)
    {
        usleep(100);
    }

    /**
     *
     */
    public static function test()
    {
        $Test1 = new TestClass();
        $Test1->anonymous();
        $Test2 = new TestClass();
        $Test2->anonymous();
    }

    /**
     * @param null $function
     * @return null
     */
    public static function withParams($function = null) {
        return $function;
    }

    /**
     * @param string $a
     * @param string $b
     * @param string $c
     * @return string
     */
    public static function withParams2($a = 'function', $b = '){', $c = '{}') {
        return $a;
    }
}
