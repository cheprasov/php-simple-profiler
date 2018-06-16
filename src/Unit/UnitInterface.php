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

interface UnitInterface
{
    /**
     * @param string $methodName
     * @param int $line
     * @param int $column
     * @return UnitInterface
     */
    public static function create(string $methodName, int $line, int $column): UnitInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string|null
     */
    public function getKey();

    /**
     * @return mixed
     */
    public function getData();
}
