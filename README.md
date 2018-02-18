[![MIT license](http://img.shields.io/badge/license-MIT-brightgreen.svg)](http://opensource.org/licenses/MIT)

Simple Profiler for PHP
=========

It helps to add profiler to your php-project easy.

##### Features:
- Easy to implement.
- Written on PHP.
- Has 'counter' and 'timer' tools.
- Has grouping for compare elements.

### 1. How to add the profiler to you project

All you need is open your 'autoload' function, and use the profiler's function for loading class.

```php
\SimpleProfiler\Profiler::loadFile(string $classPath, bool $inject_profiler = true) : void`
```

Example:
```php
// Path to autoloader class for SimpleProfiler
include ('../php-simple-profiler/src/autoloader.php');

// It is some function for loading your classes
spl_autoload_register(
    function($class) {
        if (0 !== strpos($class, __NAMESPACE__.'\\')) {
            return;
        }
        $classPath = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($classPath)) {
            // Disable old way to include class by classPath
            //include $classPath;

            // Use Profiler function for load a class
            \SimpleProfiler\Profiler::loadFile($classPath);
        }
    },
    false,
    true
);
```

### 2. How to get result

The Profiler has 2 methods that returns semi-raw collected data, that you can use or modify as you want.

`\SimpleProfiler\Profiler::getTimerStat() : array`

`\SimpleProfiler\Profiler::getCounterStat() : array`

You can use function `\SimpleProfiler\Profiler::getLog` for already getting formatted log data.

Example of output:
```
# Group [ default ]

1) ProcessTime
   count: 1, avg_time: 0.003965 sec, full_time: 0.003965 sec
   cost: [----------------------------------------------------------------------------------------------------] 100 %


# Group [ Profiler ]

1) CliArgs\CliArgs::__construct
   count: 1, avg_time: 0.000027 sec, full_time: 0.000027 sec
   cost: [--------------] 14 %

2) CliArgs\CliArgs::setConfig
   count: 1, avg_time: 0.000018 sec, full_time: 0.000018 sec
   cost: [---------] 9.3 %

3) CliArgs\CliArgs::getArg
   count: 6, avg_time: 0.000014 sec, full_time: 0.000085 sec
   cost: [--------------------------------------------] 44.2 %

4) CliArgs\CliArgs::getArgFromConfig
   count: 6, avg_time: 0.000001 sec, full_time: 0.000006 sec
   cost: [---] 3 %

5) CliArgs\CliArgs::getArguments
   count: 18, avg_time: 0.000001 sec, full_time: 0.000022 sec
   cost: [-----------] 11.3 %

6) CliArgs\CliArgs::parseArray
   count: 1, avg_time: 0.000005 sec, full_time: 0.000005 sec
   cost: [---] 2.6 %

7) CliArgs\CliArgs::isFlagExists
   count: 12, avg_time: 0.000003 sec, full_time: 0.000030 sec
   cost: [----------------] 15.6 %


# COUNTERS:
 > Counter_of_some_event : 2
 > Some_event : 3
```

### Usage

1. Using timers tool
```php
\SimpleProfiler\Profiler::startTimer('someName');

// some code

\SimpleProfiler\Profiler::stopTimer('someName');
```

For grouping times use dot `.` as separator for group and name.
Example of grouping calculation please see above.

```php
\SimpleProfiler\Profiler::startTimer('Group1.someName');

// some code

\SimpleProfiler\Profiler::stopTimer('Group1.someName');
```

2. Using counter tool

For collection count of some event just use next code
```php
\SimpleProfiler\Profiler::counter('Counter_of_some_event');

// second paramenet is count of increment
\SimpleProfiler\Profiler::counter('Counter_of_some_event', 3);
```

### Composer

Download composer:

    wget -nc http://getcomposer.org/composer.phar

and add dependency to your project:

    php composer.phar cheprasov/php-simple-profiler

## Something doesn't work

Feel free to fork project, fix bugs and finally request for pull
