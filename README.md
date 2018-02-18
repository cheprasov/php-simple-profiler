[![MIT license](http://img.shields.io/badge/license-MIT-brightgreen.svg)](http://opensource.org/licenses/MIT)

Simple Profiler for PHP
=========

It helps to add profiler to your php-project easy.

##### Features:
- Easy to implement.
- Written on PHP.
- Has 'counter' and 'timer' tools.
- Has grouping for compare elements.

### How to add the profiler to you project

All you need is open your 'autoload' function, and use the profiler's function for loading class.

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

### Examples

1. Implementation to you project

1. Using timer
```php
Profiler::start('some name');

// some code

Profiler::stop();

echo Profiler::getLog();
```

### Composer

Download composer:

    wget -nc http://getcomposer.org/composer.phar

and add dependency to your project:

    php composer.phar cheprasov/php-simple-profiler

## Something doesn't work

Feel free to fork project, fix bugs and finally request for pull
