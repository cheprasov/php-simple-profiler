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

spl_autoload_register(
    function($class) {
        if (0 !== strpos($class, __NAMESPACE__ . '\\')) {
            return;
        }
        $classPath = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen(__NAMESPACE__) + 1)) . '.php';
        if (file_exists($classPath)) {
            include $classPath;
        }
    },
    false,
    true
);
