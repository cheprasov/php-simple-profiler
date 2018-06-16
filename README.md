[![MIT license](http://img.shields.io/badge/license-MIT-brightgreen.svg)](http://opensource.org/licenses/MIT)

SimpleProfiler v3.0.0 for PHP >= 7.0
=========

The SimpleProfiler is a tool for automatic analysis of code.
Or, you just using simple tools like Stopwatch and Counter.

##### Features:
- Easy to connect with a project if you want of analysis of your code.
- It has 'Stopwatch' and 'Counter' tools.
- Support profiling for anonymous function.
- Support collecting arguments, result and exceptions of functions.
- Written on PHP, you do not need install any extensions.
- Easy to enable/disable it only for some classes based on your logic.
- The profiler works with tree structure of function calls.

### 1. How to add the profiler to you project for automatic analysis of code
Note. You can use profiler tools like 'Stopwatch' and 'Counter' without this step.

All you need is open your 'autoload' function, and use the profiler's function for loading class.

```php
\SimpleProfiler\Profiler::includeFile($classPath);
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
            \SimpleProfiler\Profiler::includeFile($classPath);
        }
    },
    false,
    true
);
```

### 2. How to get result

The Profiler has 2 methods that return collected data:

1. `\SimpleProfiler\Profiler::getRawData() : array|null`

2. `\SimpleProfiler\Profiler::getLog() : string`

You can use function `\SimpleProfiler\Profiler::getLog()` for getting already formatted log data as string.

Example of output:
```
Profiler, total: 1.001041 sec

1 | SimpleProfiler\Tests\{closure} 14:16
  | data: {"result":"foo"}
  | cost: 0.0 %, count: 1, avg: 0.000008 sec, total: 0.000008 sec

2 | SimpleProfiler\Tests\{closure} 14:16
  | data: {"result":"foo"}
  | cost: 0.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

3 | SimpleProfiler\Tests\{closure} 20:16
  | data: {"result":"bar"}
  | cost: 0.0 %, count: 1, avg: 0.000006 sec, total: 0.000006 sec

  4 | SimpleProfiler\Tests\{closure} 14:16
    | data: {"arguments":["bar"],"result":"bar"}
    | cost: 20.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

5 | SimpleProfiler\Tests\{closure} 20:16
  | data: {"result":"bar"}
  | cost: 0.0 %, count: 1, avg: 0.000005 sec, total: 0.000005 sec

  6 | SimpleProfiler\Tests\{closure} 14:16
    | data: {"arguments":["bar"],"result":"bar"}
    | cost: 19.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

7 | SimpleProfiler\Tests\TestClass::anonymous 90:27
  | cost: 0.0 %, count: 1, avg: 0.000006 sec, total: 0.000006 sec

  8 | SimpleProfiler\Tests\{closure} 92:26
    | data: {"result":42}
    | cost: 20.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

9 | SimpleProfiler\Tests\TestClass::sleep 101:27
  | data: {"arguments":[300]}
  | cost: 0.0 %, count: 1, avg: 0.000277 sec, total: 0.000277 sec

10 | SimpleProfiler\Tests\TestClass::get_random_int 42:27
   | data: {"arguments":[100,300],"result":271}
   | cost: 0.0 %, count: 1, avg: 0.000013 sec, total: 0.000013 sec

11 | SimpleProfiler\Tests\TestClass::exception 135:27
   | data: {"arguments":[true],"result":"Object:Exception"}
   | cost: 0.0 %, count: 1, avg: 0.000013 sec, total: 0.000013 sec

12 | SimpleProfiler\Tests\TestClass::test 109:27
   | cost: 100.0 %, count: 1, avg: 1.000647 sec, total: 1.000647 sec

  13 | SimpleProfiler\Tests\TestClass::anonymous 90:27
     | cost: 0.0 %, count: 1, avg: 0.000006 sec, total: 0.000006 sec

    14 | SimpleProfiler\Tests\{closure} 92:26
       | data: {"result":42}
       | cost: 36.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

  15 | SimpleProfiler\Tests\TestClass::withParams 121:27
     | data: {"arguments":["Object:Closure"],"result":1529169307}
     | cost: 100.0 %, count: 1, avg: 1.000630 sec, total: 1.000630 sec

    16 | SimpleProfiler\Tests\{closure} 114:36
       | data: {"result":1529169307}
       | cost: 100.0 %, count: 1, avg: 1.000591 sec, total: 1.000591 sec
```

Lets see what we have in the output
```
...
    10 | SimpleProfiler\Tests\TestClass::get_random_int 42:27
       | data: {"arguments":[100,300],"result":271}
       | cost: 0.0 %, count: 1, avg: 0.000013 sec, total: 0.000013 sec
...
```
- `SimpleProfiler\Tests\TestClass::get_random_int 42:27` - `function name` and `line:column` in code
- `data: {"arguments":[100,300],"result":271}` - data of the function: arguments and result
- `cost: 0.0 %, count: 1, avg: 0.000013 sec, total: 0.000013 sec`
- - `cost: 0.0 %` - How much time it took out of the parent function total time.
- - `count: 1` - Count of call the function. Note, functions with data are calculated without grouping.
- - `avg: 0.000013 sec` - Average time for 1 call of the function.
- - `total: 0.000013 sec` - Total time for all calls of the function.

### 3. Usage of Profiler tool

1. Add profiler to a file.

```
Profiler::includeFile(string $classPath, string regExpFilter = null) : void
```
Arguments:
- `string` **$classPath** - path to file of a class
- `string|null` **regExpFilter** - RegExp for adding profiler by function name. Use `/^\{closure\}$/` for profiling only anonymous functions.

2. Get result:

```
Profiler::getRawData() : array|null
```
or
```
Profiler::getLog() : string
```

3. Configure profile.

By default, the Profiler uses `\SimpleProfiler\Unit\FunctionUnit::class` for collecting statistic.
You can set another unit
```
Profiler::setProfilerUnitClass(\SimpleProfiler\Unit\DetailedFunctionUnit::class);
```
- `\SimpleProfiler\Unit\FunctionUnit::class` - the unit collects base stats without arguments and result.
- `\SimpleProfiler\Unit\DetailedFunctionUnit::class` - the unit collects detailed stats with arguments and result.


Another function `setProfilerUnitVarName`, it changes var's name that will injected in code.
```
Profiler::setProfilerUnitVarName('$ProfilerUnit');
```

### 4. Usage Counter tool

1. `Counter::clear() : void` - clear all counters

2. `Counter::increment(string $name, int $count = 1): int` - increment the counter, it returns new value

3. `Counter::decrement(string $name, int $count = 1): int` - decrement the counter, it returns new value

4. `Counter::get(string $name): int` - get the counter's value

5. `Counter::getAll(): array` - get values of all counters


### 5. Usage Stopwatch tool

1. `Stopwatch::clear() : void` - clear all timers

2. `Stopwatch::start(string $name): void` - start timer, or continue it after stop.

3. `Stopwatch::stop(string $name): float` - stop timer, and get duration in sec.

4. `Stopwatch::get(string $name): int` - get the timer's duration

5. `Stopwatch::getAll(): array` - get durations of all timers


### Example with phpMyAdmin-4.6.0

1. I changed the file `phpMyAdmin-4.6.0/libraries/Psr4Autoloader.php`
```php
...
    protected function requireFile($file)
    {
        if (file_exists($file)) {
            //include $file;
            \SimpleProfiler\Profiler::includeFile($file);
            return true;
        }
        return false;
    }
...
```

2. I added echo of the Profiler's log in file `phpMyAdmin-4.6.0/server_sql.php`
```php
$response->addHTML('<pre>' . \SimpleProfiler\Profiler::getLog() . '</pre>');
```

3. I opened phpMyAdmin in a browser and got this, and we can see that function `PMA\libraries\Language::activate` took 70.9% of working time.
```
48 | PMA\libraries\Language::activate 169:20
   | cost: 70.9 %, count: 1, avg: 0.319907 sec, total: 0.319907 sec

```
Full log:
```
Profiler, total: 0.451348 sec

1 | PMA\libraries\ErrorHandler::__construct 30:20
  | cost: 0.0 %, count: 1, avg: 0.000005 sec, total: 0.000005 sec

2 | PMA\libraries\Config::__construct 83:20
  | cost: 0.2 %, count: 1, avg: 0.000805 sec, total: 0.000805 sec

  3 | PMA\libraries\Config::load 793:20
    | cost: 90.1 %, count: 1, avg: 0.000725 sec, total: 0.000725 sec

    4 | PMA\libraries\Config::loadDefaults 763:20
      | cost: 77.7 %, count: 1, avg: 0.000563 sec, total: 0.000563 sec

    5 | PMA\libraries\Config::setSource 1099:20
      | cost: 0.1 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    6 | PMA\libraries\Config::checkFontsize 1273:20
      | cost: 2.8 %, count: 1, avg: 0.000020 sec, total: 0.000020 sec

      7 | PMA\libraries\Config::get 1182:20
        | cost: 6.0 %, count: 2, avg: 0.000001 sec, total: 0.000001 sec

      8 | PMA\libraries\Config::set 1198:20
        | cost: 9.5 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

      9 | PMA\libraries\Config::setCookie 1595:20
        | cost: 25.0 %, count: 1, avg: 0.000005 sec, total: 0.000005 sec

    10 | PMA\libraries\Config::checkConfigSource 1109:20
       | cost: 15.0 %, count: 1, avg: 0.000109 sec, total: 0.000109 sec

      11 | PMA\libraries\Config::getSource 1213:20
         | cost: 0.0 %, count: 2, avg: 0.000000 sec, total: 0.000000 sec

    12 | PMA\libraries\Config::checkCollationConnection 1256:20
       | cost: 1.0 %, count: 1, avg: 0.000007 sec, total: 0.000007 sec

      13 | PMA\libraries\Config::set 1198:20
         | cost: 13.8 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

  14 | PMA\libraries\Config::checkSystem 102:20
     | cost: 9.2 %, count: 1, avg: 0.000074 sec, total: 0.000074 sec

    15 | PMA\libraries\Config::set 1198:20
       | cost: 2.6 %, count: 3, avg: 0.000001 sec, total: 0.000002 sec

    16 | PMA\libraries\Config::checkWebServerOs 338:20
       | cost: 6.8 %, count: 1, avg: 0.000005 sec, total: 0.000005 sec

      17 | PMA\libraries\Config::set 1198:20
         | cost: 19.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    18 | PMA\libraries\Config::checkWebServer 319:20
       | cost: 6.8 %, count: 1, avg: 0.000005 sec, total: 0.000005 sec

      19 | PMA\libraries\Config::set 1198:20
         | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

    20 | PMA\libraries\Config::checkGd2 285:20
       | cost: 8.4 %, count: 1, avg: 0.000006 sec, total: 0.000006 sec

      21 | PMA\libraries\Config::get 1182:20
         | cost: 0.0 %, count: 2, avg: 0.000000 sec, total: 0.000000 sec

      22 | PMA\libraries\Config::set 1198:20
         | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

    23 | PMA\libraries\Config::checkClient 181:20
       | cost: 28.4 %, count: 1, avg: 0.000021 sec, total: 0.000021 sec

      24 | PMA\libraries\Config::_setClientPlatform 156:21
         | cost: 29.5 %, count: 1, avg: 0.000006 sec, total: 0.000006 sec

        25 | PMA\libraries\Config::set 1198:20
           | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

      26 | PMA\libraries\Config::set 1198:20
         | cost: 4.5 %, count: 2, avg: 0.000000 sec, total: 0.000001 sec

    27 | PMA\libraries\Config::checkUpload 1301:20
       | cost: 5.5 %, count: 1, avg: 0.000004 sec, total: 0.000004 sec

      28 | PMA\libraries\Config::set 1198:20
         | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

    29 | PMA\libraries\Config::checkUploadSize 1324:20
       | cost: 11.0 %, count: 1, avg: 0.000008 sec, total: 0.000008 sec

      30 | PMA\libraries\Config::set 1198:20
         | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

    31 | PMA\libraries\Config::checkOutputCompression 128:20
       | cost: 9.7 %, count: 1, avg: 0.000007 sec, total: 0.000007 sec

      32 | PMA\libraries\Config::get 1182:20
         | cost: 13.3 %, count: 2, avg: 0.000000 sec, total: 0.000001 sec

      33 | PMA\libraries\Config::set 1198:20
         | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

34 | PMA\libraries\Config::enableBc 1420:20
   | cost: 0.0 %, count: 1, avg: 0.000025 sec, total: 0.000025 sec

  35 | PMA\libraries\Config::get 1182:20
     | cost: 23.8 %, count: 13, avg: 0.000000 sec, total: 0.000006 sec

36 | PMA\libraries\Config::getCookiePath 1382:20
   | cost: 0.0 %, count: 1, avg: 0.000004 sec, total: 0.000004 sec

37 | PMA\libraries\Config::isHttps 1347:20
   | cost: 0.0 %, count: 1, avg: 0.000012 sec, total: 0.000012 sec

  38 | PMA\libraries\Config::get 1182:20
     | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

  39 | PMA\libraries\Config::set 1198:20
     | cost: 18.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

40 | PMA\libraries\Config::get 1182:20
   | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

41 | PMA\libraries\ErrorHandler::handleError 115:20
   | cost: 0.0 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

42 | PMA\libraries\LanguageManager::getInstance 543:27
   | cost: 0.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

43 | PMA\libraries\LanguageManager::selectLanguage 695:20
   | cost: 2.1 %, count: 1, avg: 0.009429 sec, total: 0.009429 sec

  44 | PMA\libraries\LanguageManager::availableLanguages 616:20
     | cost: 99.9 %, count: 1, avg: 0.009424 sec, total: 0.009424 sec

    45 | PMA\libraries\LanguageManager::availableLocales 595:20
       | cost: 89.0 %, count: 1, avg: 0.008392 sec, total: 0.008392 sec

      46 | PMA\libraries\LanguageManager::listLocaleDir 556:20
         | cost: 99.9 %, count: 1, avg: 0.008385 sec, total: 0.008385 sec

    47 | PMA\libraries\Language::__construct 36:20
       | cost: 0.1 %, count: 41, avg: 0.000000 sec, total: 0.000014 sec

48 | PMA\libraries\Language::activate 169:20
   | cost: 70.9 %, count: 1, avg: 0.319907 sec, total: 0.319907 sec

  49 | PMA\libraries\Language::isRTL 159:20
     | cost: 0.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

  50 | PMA\libraries\LanguageManager::getInstance 543:27
     | cost: 0.0 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

  51 | PMA\libraries\LanguageManager::showWarnings 770:20
     | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

52 | PMA\libraries\Config::checkPermissions 1154:20
   | cost: 0.0 %, count: 1, avg: 0.000105 sec, total: 0.000105 sec

  53 | PMA\libraries\Config::get 1182:20
     | cost: 0.9 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

  54 | PMA\libraries\Config::getSource 1213:20
     | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

  55 | PMA\libraries\ErrorHandler::handleError 115:20
     | cost: 1.8 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

56 | PMA\libraries\ThemeManager::initializeTheme 464:27
   | cost: 0.7 %, count: 1, avg: 0.003033 sec, total: 0.003033 sec

  57 | PMA\libraries\ThemeManager::checkConfig 165:20
     | cost: 94.7 %, count: 1, avg: 0.002872 sec, total: 0.002872 sec

    58 | PMA\libraries\ThemeManager::loadThemes 289:20
       | cost: 99.7 %, count: 1, avg: 0.002863 sec, total: 0.002863 sec

      59 | PMA\libraries\ThemeManager::getThemesPath 92:20
         | cost: 0.2 %, count: 8, avg: 0.000001 sec, total: 0.000006 sec

      60 | PMA\libraries\Theme::load 127:27
         | cost: 35.5 %, count: 2, avg: 0.000508 sec, total: 0.001016 sec

        61 | PMA\libraries\Theme::setPath 206:20
           | cost: 0.2 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

        62 | PMA\libraries\Theme::loadInfo 87:13
           | cost: 65.4 %, count: 2, avg: 0.000332 sec, total: 0.000664 sec

          63 | PMA\libraries\Theme::getPath 182:20
             | cost: 0.8 %, count: 10, avg: 0.000001 sec, total: 0.000005 sec

          64 | PMA\libraries\Theme::setVersion 219:20
             | cost: 0.4 %, count: 2, avg: 0.000001 sec, total: 0.000003 sec

          65 | PMA\libraries\Theme::setName 257:20
             | cost: 0.1 %, count: 2, avg: 0.000000 sec, total: 0.000001 sec

        66 | PMA\libraries\Theme::checkImgPath 148:20
           | cost: 32.9 %, count: 2, avg: 0.000167 sec, total: 0.000334 sec

          67 | PMA\libraries\Theme::getPath 182:20
             | cost: 0.6 %, count: 4, avg: 0.000001 sec, total: 0.000002 sec

          68 | PMA\libraries\Theme::setImgPath 305:20
             | cost: 0.0 %, count: 2, avg: 0.000000 sec, total: 0.000000 sec

      69 | PMA\libraries\Theme::setId 281:20
         | cost: 0.0 %, count: 2, avg: 0.000000 sec, total: 0.000001 sec

  70 | PMA\libraries\Theme::getName 268:20
     | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

  71 | PMA\libraries\Theme::getPath 182:20
     | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

  72 | PMA\libraries\Theme::getImgPath 320:20
     | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

  73 | PMA\libraries\Theme::getLayoutFile 193:20
     | cost: 0.2 %, count: 2, avg: 0.000003 sec, total: 0.000006 sec

    74 | PMA\libraries\Theme::getPath 182:20
       | cost: 16.0 %, count: 2, avg: 0.000000 sec, total: 0.000001 sec

75 | PMA\libraries\Config::setCookie 1595:20
   | cost: 0.0 %, count: 2, avg: 0.000005 sec, total: 0.000009 sec

76 | PMA\libraries\ThemeManager::setThemeCookie 246:20
   | cost: 0.0 %, count: 1, avg: 0.000012 sec, total: 0.000012 sec

  77 | PMA\libraries\ThemeManager::getThemeCookieName 215:20
     | cost: 8.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

  78 | PMA\libraries\Config::setCookie 1595:20
     | cost: 8.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

  79 | PMA\libraries\Config::set 1198:20
     | cost: 16.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

80 | PMA\libraries\DatabaseInterface::checkDbExtension 68:27
   | cost: 0.0 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

81 | PMA\libraries\DatabaseInterface::__construct 55:20
   | cost: 0.0 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

82 | PMA\libraries\di\Container::getDefaultContainer 160:27
   | cost: 0.6 %, count: 1, avg: 0.002499 sec, total: 0.002499 sec

  83 | PMA\libraries\di\Container::__construct 33:20
     | cost: 99.8 %, count: 1, avg: 0.002495 sec, total: 0.002495 sec

    84 | PMA\libraries\di\Container::alias 149:20
       | cost: 67.1 %, count: 1, avg: 0.001675 sec, total: 0.001675 sec

      85 | PMA\libraries\di\AliasItem::__construct 30:20
         | cost: 0.1 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    86 | PMA\libraries\di\Container::set 98:20
       | cost: 32.7 %, count: 1, avg: 0.000816 sec, total: 0.000816 sec

      87 | PMA\libraries\di\ValueItem::__construct 26:20
         | cost: 0.1 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

88 | PMA\libraries\di\Container::set 98:20
   | cost: 0.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

  89 | PMA\libraries\di\ValueItem::__construct 26:20
     | cost: 44.4 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

90 | PMA\libraries\di\Container::alias 149:20
   | cost: 0.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

  91 | PMA\libraries\di\AliasItem::__construct 30:20
     | cost: 55.6 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

92 | PMA\libraries\plugins\auth\AuthenticationCookie::authCheck 278:20
   | cost: 0.0 %, count: 1, avg: 0.000073 sec, total: 0.000073 sec

  93 | PMA\libraries\plugins\auth\AuthenticationCookie::_getEncryptionSecret 676:21
     | cost: 9.5 %, count: 1, avg: 0.000007 sec, total: 0.000007 sec

    94 | PMA\libraries\plugins\auth\AuthenticationCookie::_getSessionEncryptionSecret 691:21
       | cost: 13.8 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

  95 | PMA\libraries\plugins\auth\AuthenticationCookie::cookieDecrypt 753:20
     | cost: 57.8 %, count: 2, avg: 0.000021 sec, total: 0.000042 sec

    96 | PMA\libraries\plugins\auth\AuthenticationCookie::getIVSize 786:20
       | cost: 33.3 %, count: 2, avg: 0.000007 sec, total: 0.000014 sec

      97 | PMA\libraries\plugins\auth\AuthenticationCookie::useOpenSSL 708:27
         | cost: 15.3 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

    98 | PMA\libraries\plugins\auth\AuthenticationCookie::useOpenSSL 708:27
       | cost: 5.1 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

  99 | PMA\libraries\plugins\auth\AuthenticationCookie::_getSessionEncryptionSecret 691:21
     | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

100 | PMA\libraries\plugins\auth\AuthenticationCookie::authSetUser 470:20
    | cost: 0.0 %, count: 1, avg: 0.000004 sec, total: 0.000004 sec

101 | PMA\libraries\DatabaseInterface::connect 2350:20
    | cost: 5.4 %, count: 1, avg: 0.024523 sec, total: 0.024523 sec

  102 | PMA\libraries\dbi\DBIMysqli::connect 120:20
      | cost: 1.4 %, count: 1, avg: 0.000342 sec, total: 0.000342 sec

    103 | PMA\libraries\DatabaseInterface::getServerPort 2698:20
        | cost: 0.6 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

    104 | PMA\libraries\DatabaseInterface::getServerSocket 2718:20
        | cost: 0.3 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    105 | PMA\libraries\dbi\DBIMysqli::_realConnect 73:21
        | cost: 91.5 %, count: 1, avg: 0.000313 sec, total: 0.000313 sec

  106 | PMA\libraries\DatabaseInterface::postConnect 1417:20
      | cost: 98.6 %, count: 1, avg: 0.024171 sec, total: 0.024171 sec

    107 | PMA\libraries\Util::cacheExists 3010:27
        | cost: 0.0 %, count: 1, avg: 0.000004 sec, total: 0.000004 sec

    108 | PMA\libraries\Util::cacheGet 3023:27
        | cost: 0.0 %, count: 5, avg: 0.000002 sec, total: 0.000010 sec

      109 | PMA\libraries\Util::cacheExists 3010:27
          | cost: 19.0 %, count: 5, avg: 0.000000 sec, total: 0.000002 sec

    110 | PMA\libraries\DatabaseInterface::query 86:20
        | cost: 28.5 %, count: 2, avg: 0.003440 sec, total: 0.006881 sec

      111 | PMA\libraries\DatabaseInterface::tryQuery 228:20
          | cost: 99.9 %, count: 2, avg: 0.003438 sec, total: 0.006876 sec

        112 | PMA\libraries\DatabaseInterface::getLink 2738:20
            | cost: 0.0 %, count: 2, avg: 0.000001 sec, total: 0.000003 sec

        113 | PMA\libraries\dbi\DBIMysqli::realQuery 242:20
            | cost: 25.4 %, count: 2, avg: 0.000874 sec, total: 0.001749 sec

        114 | PMA\libraries\DatabaseInterface::affectedRows 2614:20
            | cost: 0.2 %, count: 2, avg: 0.000008 sec, total: 0.000016 sec

          115 | PMA\libraries\DatabaseInterface::getLink 2738:20
              | cost: 7.5 %, count: 2, avg: 0.000001 sec, total: 0.000001 sec

          116 | PMA\libraries\dbi\DBIMysqli::affectedRows 454:20
              | cost: 17.9 %, count: 2, avg: 0.000001 sec, total: 0.000003 sec

        117 | PMA\libraries\Tracker::isActive 46:27
            | cost: 0.0 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

    118 | PMA\libraries\Util::sqlAddSlashes 302:27
        | cost: 0.0 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

    119 | PMA\libraries\DatabaseInterface::tryQuery 228:20
        | cost: 0.8 %, count: 1, avg: 0.000190 sec, total: 0.000190 sec

      120 | PMA\libraries\DatabaseInterface::getLink 2738:20
          | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

      121 | PMA\libraries\dbi\DBIMysqli::realQuery 242:20
          | cost: 89.1 %, count: 1, avg: 0.000169 sec, total: 0.000169 sec

      122 | PMA\libraries\DatabaseInterface::affectedRows 2614:20
          | cost: 3.1 %, count: 1, avg: 0.000006 sec, total: 0.000006 sec

        123 | PMA\libraries\DatabaseInterface::getLink 2738:20
            | cost: 16.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

        124 | PMA\libraries\dbi\DBIMysqli::affectedRows 454:20
            | cost: 20.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

      125 | PMA\libraries\Tracker::isActive 46:27
          | cost: 0.5 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    126 | PMA\libraries\LanguageManager::getInstance 543:27
        | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    127 | PMA\libraries\LanguageManager::getCurrentLanguage 684:20
        | cost: 0.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

    128 | PMA\libraries\Language::getMySQLLocale 97:20
        | cost: 0.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

129 | PMA\libraries\plugins\auth\AuthenticationCookie::storeUserCredentials 530:20
    | cost: 0.0 %, count: 1, avg: 0.000122 sec, total: 0.000122 sec

  130 | PMA\libraries\plugins\auth\AuthenticationCookie::createIV 803:20
      | cost: 51.6 %, count: 1, avg: 0.000063 sec, total: 0.000063 sec

    131 | PMA\libraries\plugins\auth\AuthenticationCookie::useOpenSSL 708:27
        | cost: 3.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

    132 | PMA\libraries\plugins\auth\AuthenticationCookie::getIVSize 786:20
        | cost: 11.0 %, count: 1, avg: 0.000007 sec, total: 0.000007 sec

      133 | PMA\libraries\plugins\auth\AuthenticationCookie::useOpenSSL 708:27
          | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

    134 | PMA\libraries\Config::setCookie 1595:20
        | cost: 50.8 %, count: 1, avg: 0.000032 sec, total: 0.000032 sec

      135 | PMA\libraries\Config::getCookiePath 1382:20
          | cost: 3.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

      136 | PMA\libraries\Config::isHttps 1347:20
          | cost: 15.7 %, count: 1, avg: 0.000005 sec, total: 0.000005 sec

        137 | PMA\libraries\Config::get 1182:20
            | cost: 19.0 %, count: 2, avg: 0.000000 sec, total: 0.000001 sec

  138 | PMA\libraries\plugins\auth\AuthenticationCookie::storeUsernameCookie 608:20
      | cost: 25.4 %, count: 1, avg: 0.000031 sec, total: 0.000031 sec

    139 | PMA\libraries\plugins\auth\AuthenticationCookie::_getEncryptionSecret 676:21
        | cost: 16.2 %, count: 1, avg: 0.000005 sec, total: 0.000005 sec

      140 | PMA\libraries\plugins\auth\AuthenticationCookie::_getSessionEncryptionSecret 691:21
          | cost: 42.9 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

    141 | PMA\libraries\plugins\auth\AuthenticationCookie::cookieEncrypt 726:20
        | cost: 29.2 %, count: 1, avg: 0.000009 sec, total: 0.000009 sec

      142 | PMA\libraries\plugins\auth\AuthenticationCookie::useOpenSSL 708:27
          | cost: 13.2 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    143 | PMA\libraries\Config::setCookie 1595:20
        | cost: 32.3 %, count: 1, avg: 0.000010 sec, total: 0.000010 sec

      144 | PMA\libraries\Config::getCookiePath 1382:20
          | cost: 9.5 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

      145 | PMA\libraries\Config::isHttps 1347:20
          | cost: 31.0 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

        146 | PMA\libraries\Config::get 1182:20
            | cost: 30.8 %, count: 2, avg: 0.000000 sec, total: 0.000001 sec

  147 | PMA\libraries\plugins\auth\AuthenticationCookie::storePasswordCookie 628:20
      | cost: 15.6 %, count: 1, avg: 0.000019 sec, total: 0.000019 sec

    148 | PMA\libraries\plugins\auth\AuthenticationCookie::_getSessionEncryptionSecret 691:21
        | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

    149 | PMA\libraries\plugins\auth\AuthenticationCookie::cookieEncrypt 726:20
        | cost: 21.2 %, count: 1, avg: 0.000004 sec, total: 0.000004 sec

      150 | PMA\libraries\plugins\auth\AuthenticationCookie::useOpenSSL 708:27
          | cost: 23.5 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    151 | PMA\libraries\Config::setCookie 1595:20
        | cost: 47.5 %, count: 1, avg: 0.000009 sec, total: 0.000009 sec

      152 | PMA\libraries\Config::getCookiePath 1382:20
          | cost: 13.2 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

      153 | PMA\libraries\Config::isHttps 1347:20
          | cost: 31.6 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

        154 | PMA\libraries\Config::get 1182:20
            | cost: 33.3 %, count: 2, avg: 0.000000 sec, total: 0.000001 sec

155 | PMA\libraries\Util::cacheExists 3010:27
    | cost: 0.0 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

156 | PMA\libraries\DatabaseInterface::query 86:20
    | cost: 0.2 %, count: 2, avg: 0.000480 sec, total: 0.000960 sec

  157 | PMA\libraries\DatabaseInterface::tryQuery 228:20
      | cost: 99.5 %, count: 2, avg: 0.000477 sec, total: 0.000955 sec

    158 | PMA\libraries\DatabaseInterface::getLink 2738:20
        | cost: 0.4 %, count: 2, avg: 0.000002 sec, total: 0.000004 sec

    159 | PMA\libraries\dbi\DBIMysqli::realQuery 242:20
        | cost: 92.9 %, count: 2, avg: 0.000443 sec, total: 0.000887 sec

    160 | PMA\libraries\DatabaseInterface::affectedRows 2614:20
        | cost: 1.2 %, count: 2, avg: 0.000006 sec, total: 0.000012 sec

      161 | PMA\libraries\DatabaseInterface::getLink 2738:20
          | cost: 0.0 %, count: 2, avg: 0.000000 sec, total: 0.000000 sec

      162 | PMA\libraries\dbi\DBIMysqli::affectedRows 454:20
          | cost: 18.0 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

    163 | PMA\libraries\Tracker::isActive 46:27
        | cost: 0.1 %, count: 2, avg: 0.000000 sec, total: 0.000001 sec

164 | PMA\libraries\DatabaseInterface::fetchAssoc 2424:20
    | cost: 0.1 %, count: 265, avg: 0.000002 sec, total: 0.000661 sec

  165 | PMA\libraries\dbi\DBIMysqli::fetchAssoc 287:20
      | cost: 23.1 %, count: 265, avg: 0.000001 sec, total: 0.000152 sec

166 | PMA\libraries\DatabaseInterface::freeResult 2461:20
    | cost: 0.0 %, count: 2, avg: 0.000003 sec, total: 0.000005 sec

  167 | PMA\libraries\dbi\DBIMysqli::freeResult 324:20
      | cost: 38.1 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

168 | PMA\libraries\Util::cacheSet 3045:27
    | cost: 0.0 %, count: 7, avg: 0.000001 sec, total: 0.000006 sec

169 | PMA\libraries\DbList::__set 73:20
    | cost: 0.0 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

170 | PMA\libraries\Config::loadUserPreferences 916:20
    | cost: 0.2 %, count: 1, avg: 0.000986 sec, total: 0.000986 sec

  171 | PMA\libraries\Config::set 1198:20
      | cost: 0.2 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

  172 | PMA\libraries\ThemeManager::getThemeCookie 231:20
      | cost: 0.4 %, count: 1, avg: 0.000004 sec, total: 0.000004 sec

    173 | PMA\libraries\ThemeManager::getThemeCookieName 215:20
        | cost: 25.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

  174 | PMA\libraries\Theme::getId 292:20
      | cost: 0.1 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

  175 | PMA\libraries\Config::setUserValue 1045:20
      | cost: 1.7 %, count: 1, avg: 0.000017 sec, total: 0.000017 sec

    176 | PMA\libraries\Config::get 1182:20
        | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

  177 | PMA\libraries\Config::_saveConnectionCollation 877:21
      | cost: 1.0 %, count: 1, avg: 0.000010 sec, total: 0.000010 sec

    178 | PMA\libraries\Config::setUserValue 1045:20
        | cost: 69.0 %, count: 1, avg: 0.000007 sec, total: 0.000007 sec

      179 | PMA\libraries\Config::get 1182:20
          | cost: 13.8 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

180 | PMA\libraries\Response::getInstance 123:27
    | cost: 2.4 %, count: 2, avg: 0.005478 sec, total: 0.010956 sec

  181 | PMA\libraries\Response::__construct 90:21
      | cost: 99.9 %, count: 1, avg: 0.010950 sec, total: 0.010950 sec

    182 | PMA\libraries\OutputBuffering::getInstance 65:27
        | cost: 0.1 %, count: 1, avg: 0.000014 sec, total: 0.000014 sec

      183 | PMA\libraries\OutputBuffering::__construct 24:21
          | cost: 79.7 %, count: 1, avg: 0.000011 sec, total: 0.000011 sec

        184 | PMA\libraries\OutputBuffering::_getMode 35:21
            | cost: 53.2 %, count: 1, avg: 0.000006 sec, total: 0.000006 sec

    185 | PMA\libraries\OutputBuffering::start 80:20
        | cost: 0.1 %, count: 1, avg: 0.000010 sec, total: 0.000010 sec

    186 | PMA\libraries\Header::__construct 113:20
        | cost: 52.6 %, count: 1, avg: 0.005756 sec, total: 0.005756 sec

      187 | PMA\libraries\Console::__construct 34:20
          | cost: 0.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

      188 | PMA\libraries\Menu::__construct 46:20
          | cost: 0.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

      189 | PMA\libraries\Scripts::__construct 108:20
          | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

      190 | PMA\libraries\Header::_addDefaultScripts 149:21
          | cost: 3.9 %, count: 1, avg: 0.000222 sec, total: 0.000222 sec

        191 | PMA\libraries\Scripts::addFile 127:20
            | cost: 26.7 %, count: 26, avg: 0.000002 sec, total: 0.000059 sec

          192 | PMA\libraries\Scripts::_eventBlacklist 170:21
              | cost: 14.1 %, count: 26, avg: 0.000000 sec, total: 0.000008 sec

        193 | PMA\libraries\Theme::getId 292:20
            | cost: 0.4 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

        194 | PMA\libraries\Header::getJsParamsCode 271:20
            | cost: 44.1 %, count: 1, avg: 0.000098 sec, total: 0.000098 sec

          195 | PMA\libraries\Header::getJsParams 214:20
              | cost: 82.7 %, count: 1, avg: 0.000081 sec, total: 0.000081 sec

            196 | PMA\libraries\Util::getScriptNameForOption 3446:27
                | cost: 1.2 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

            197 | PMA\libraries\Util::getTitleForTarget 3406:27
                | cost: 73.8 %, count: 3, avg: 0.000020 sec, total: 0.000060 sec

        198 | PMA\libraries\Scripts::addCode 192:20
            | cost: 0.4 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

      199 | PMA\libraries\Config::get 1182:20
          | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    200 | PMA\libraries\Footer::__construct 54:20
        | cost: 0.1 %, count: 1, avg: 0.000006 sec, total: 0.000006 sec

      201 | PMA\libraries\Scripts::__construct 108:20
          | cost: 16.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    202 | PMA\libraries\Header::setAjax 298:20
        | cost: 0.0 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

      203 | PMA\libraries\Console::setAjax 57:20
          | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

    204 | PMA\libraries\Footer::setAjax 272:20
        | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

205 | PMA\libraries\Response::isAjax 150:20
    | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

206 | PMA\libraries\Config::set 1198:20
    | cost: 0.0 %, count: 3, avg: 0.000001 sec, total: 0.000004 sec

207 | PMA\libraries\Tracker::enable 34:27
    | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

208 | PMA\libraries\DatabaseInterface::isSuperuser 2193:20
    | cost: 0.1 %, count: 2, avg: 0.000199 sec, total: 0.000397 sec

  209 | PMA\libraries\DatabaseInterface::isUserType 2208:20
      | cost: 98.1 %, count: 2, avg: 0.000195 sec, total: 0.000390 sec

    210 | PMA\libraries\Util::cacheExists 3010:27
        | cost: 3.6 %, count: 2, avg: 0.000007 sec, total: 0.000014 sec

    211 | PMA\libraries\DatabaseInterface::tryQuery 228:20
        | cost: 85.4 %, count: 1, avg: 0.000333 sec, total: 0.000333 sec

      212 | PMA\libraries\DatabaseInterface::getLink 2738:20
          | cost: 0.3 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

      213 | PMA\libraries\dbi\DBIMysqli::realQuery 242:20
          | cost: 91.3 %, count: 1, avg: 0.000304 sec, total: 0.000304 sec

      214 | PMA\libraries\DatabaseInterface::affectedRows 2614:20
          | cost: 2.1 %, count: 1, avg: 0.000007 sec, total: 0.000007 sec

        215 | PMA\libraries\DatabaseInterface::getLink 2738:20
            | cost: 13.8 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

        216 | PMA\libraries\dbi\DBIMysqli::affectedRows 454:20
            | cost: 13.8 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

      217 | PMA\libraries\Tracker::isActive 46:27
          | cost: 1.8 %, count: 1, avg: 0.000006 sec, total: 0.000006 sec

    218 | PMA\libraries\DatabaseInterface::numRows 2576:20
        | cost: 1.3 %, count: 1, avg: 0.000005 sec, total: 0.000005 sec

      219 | PMA\libraries\dbi\DBIMysqli::numRows 437:20
          | cost: 42.9 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

    220 | PMA\libraries\DatabaseInterface::freeResult 2461:20
        | cost: 0.8 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

      221 | PMA\libraries\dbi\DBIMysqli::freeResult 324:20
          | cost: 30.8 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    222 | PMA\libraries\Util::cacheSet 3045:27
        | cost: 0.6 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

    223 | PMA\libraries\Util::cacheGet 3023:27
        | cost: 2.1 %, count: 2, avg: 0.000004 sec, total: 0.000008 sec

      224 | PMA\libraries\Util::cacheExists 3010:27
          | cost: 14.7 %, count: 2, avg: 0.000001 sec, total: 0.000001 sec

225 | PMA\libraries\DbList::__get 50:20
    | cost: 0.7 %, count: 1, avg: 0.002938 sec, total: 0.002938 sec

  226 | PMA\libraries\DbList::getDatabaseList 90:20
      | cost: 99.9 %, count: 1, avg: 0.002935 sec, total: 0.002935 sec

    227 | PMA\libraries\DatabaseInterface::isSuperuser 2193:20
        | cost: 0.5 %, count: 1, avg: 0.000014 sec, total: 0.000014 sec

      228 | PMA\libraries\DatabaseInterface::isUserType 2208:20
          | cost: 86.4 %, count: 1, avg: 0.000012 sec, total: 0.000012 sec

        229 | PMA\libraries\Util::cacheExists 3010:27
            | cost: 15.7 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

        230 | PMA\libraries\Util::cacheGet 3023:27
            | cost: 23.5 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

          231 | PMA\libraries\Util::cacheExists 3010:27
              | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

    232 | PMA\libraries\DatabaseInterface::fetchValue 1580:20
        | cost: 5.0 %, count: 1, avg: 0.000146 sec, total: 0.000146 sec

      233 | PMA\libraries\DatabaseInterface::tryQuery 228:20
          | cost: 83.4 %, count: 1, avg: 0.000122 sec, total: 0.000122 sec

        234 | PMA\libraries\DatabaseInterface::getLink 2738:20
            | cost: 1.6 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

        235 | PMA\libraries\dbi\DBIMysqli::realQuery 242:20
            | cost: 86.9 %, count: 1, avg: 0.000106 sec, total: 0.000106 sec

        236 | PMA\libraries\Tracker::isActive 46:27
            | cost: 1.6 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

      237 | PMA\libraries\DatabaseInterface::numRows 2576:20
          | cost: 2.0 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

        238 | PMA\libraries\dbi\DBIMysqli::numRows 437:20
            | cost: 66.7 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

      239 | PMA\libraries\DatabaseInterface::fetchRow 2436:20
          | cost: 4.9 %, count: 1, avg: 0.000007 sec, total: 0.000007 sec

        240 | PMA\libraries\dbi\DBIMysqli::fetchRow 299:20
            | cost: 70.0 %, count: 1, avg: 0.000005 sec, total: 0.000005 sec

      241 | PMA\libraries\DatabaseInterface::freeResult 2461:20
          | cost: 1.3 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

        242 | PMA\libraries\dbi\DBIMysqli::freeResult 324:20
            | cost: 50.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    243 | PMA\libraries\Util::cacheExists 3010:27
        | cost: 0.1 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

    244 | PMA\libraries\DatabaseInterface::getSystemSchemas 2306:20
        | cost: 0.3 %, count: 1, avg: 0.000010 sec, total: 0.000010 sec

      245 | PMA\libraries\DatabaseInterface::isSystemSchema 2329:20
          | cost: 31.0 %, count: 4, avg: 0.000001 sec, total: 0.000003 sec

    246 | PMA\libraries\DatabaseInterface::tryQuery 228:20
        | cost: 3.7 %, count: 1, avg: 0.000109 sec, total: 0.000109 sec

      247 | PMA\libraries\DatabaseInterface::getLink 2738:20
          | cost: 0.9 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

      248 | PMA\libraries\dbi\DBIMysqli::realQuery 242:20
          | cost: 72.6 %, count: 1, avg: 0.000079 sec, total: 0.000079 sec

      249 | PMA\libraries\DatabaseInterface::affectedRows 2614:20
          | cost: 5.5 %, count: 1, avg: 0.000006 sec, total: 0.000006 sec

        250 | PMA\libraries\DatabaseInterface::getLink 2738:20
            | cost: 20.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

        251 | PMA\libraries\dbi\DBIMysqli::affectedRows 454:20
            | cost: 16.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

      252 | PMA\libraries\Tracker::isActive 46:27
          | cost: 1.8 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

    253 | PMA\libraries\DatabaseInterface::fetchRow 2436:20
        | cost: 0.1 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

      254 | PMA\libraries\dbi\DBIMysqli::fetchRow 299:20
          | cost: 66.7 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

    255 | PMA\libraries\Util::unQuote 371:27
        | cost: 0.1 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

    256 | PMA\libraries\DatabaseInterface::freeResult 2461:20
        | cost: 0.1 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

      257 | PMA\libraries\dbi\DBIMysqli::freeResult 324:20
          | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

    258 | PMA\libraries\Util::cacheSet 3045:27
        | cost: 0.1 %, count: 9, avg: 0.000000 sec, total: 0.000003 sec

    259 | PMA\libraries\ListDatabase::__construct 43:20
        | cost: 14.8 %, count: 1, avg: 0.000435 sec, total: 0.000435 sec

      260 | PMA\libraries\ListAbstract::__construct 38:20
          | cost: 0.5 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

      261 | PMA\libraries\ListDatabase::build 123:20
          | cost: 98.0 %, count: 1, avg: 0.000426 sec, total: 0.000426 sec

        262 | PMA\libraries\ListDatabase::checkOnlyDatabase 138:23
            | cost: 0.9 %, count: 1, avg: 0.000004 sec, total: 0.000004 sec

        263 | PMA\libraries\ListDatabase::retrieve 77:23
            | cost: 97.1 %, count: 1, avg: 0.000414 sec, total: 0.000414 sec

          264 | PMA\libraries\DatabaseInterface::fetchResult 1744:20
              | cost: 97.6 %, count: 1, avg: 0.000404 sec, total: 0.000404 sec

            265 | PMA\libraries\DatabaseInterface::tryQuery 228:20
                | cost: 87.9 %, count: 1, avg: 0.000355 sec, total: 0.000355 sec

              266 | PMA\libraries\DatabaseInterface::getLink 2738:20
                  | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

              267 | PMA\libraries\dbi\DBIMysqli::realQuery 242:20
                  | cost: 80.3 %, count: 1, avg: 0.000285 sec, total: 0.000285 sec

              268 | PMA\libraries\Tracker::isActive 46:27
                  | cost: 1.1 %, count: 1, avg: 0.000004 sec, total: 0.000004 sec

            269 | PMA\libraries\DatabaseInterface::numFields 2647:20
                | cost: 1.2 %, count: 1, avg: 0.000005 sec, total: 0.000005 sec

              270 | PMA\libraries\dbi\DBIMysqli::numFields 549:20
                  | cost: 42.9 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

            271 | PMA\libraries\DatabaseInterface::fetchRow 2436:20
                | cost: 3.1 %, count: 6, avg: 0.000002 sec, total: 0.000012 sec

              272 | PMA\libraries\dbi\DBIMysqli::fetchRow 299:20
                  | cost: 50.0 %, count: 6, avg: 0.000001 sec, total: 0.000006 sec

            273 | PMA\libraries\DatabaseInterface::_fetchValue 1682:21
                | cost: 0.7 %, count: 5, avg: 0.000001 sec, total: 0.000003 sec

            274 | PMA\libraries\DatabaseInterface::freeResult 2461:20
                | cost: 0.5 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

              275 | PMA\libraries\dbi\DBIMysqli::freeResult 324:20
                  | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

        276 | PMA\libraries\ListDatabase::checkHideDatabase 57:23
            | cost: 0.2 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

277 | PMA\libraries\config\PageSettings::showGroup 208:27
    | cost: 5.1 %, count: 1, avg: 0.023153 sec, total: 0.023153 sec

  278 | PMA\libraries\config\PageSettings::__construct 57:20
      | cost: 99.9 %, count: 1, avg: 0.023120 sec, total: 0.023120 sec

    279 | PMA\libraries\config\ConfigFile::__construct 83:20
        | cost: 2.5 %, count: 1, avg: 0.000587 sec, total: 0.000587 sec

    280 | PMA\libraries\config\ConfigFile::resetConfigData 173:20
        | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    281 | PMA\libraries\config\ConfigFile::setAllowedKeys 142:20
        | cost: 0.0 %, count: 1, avg: 0.000009 sec, total: 0.000009 sec

    282 | PMA\libraries\config\ConfigFile::setCfgUpdateReadMapping 163:20
        | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

    283 | PMA\libraries\config\ConfigFile::updateWithGlobalConfig 287:20
        | cost: 12.2 %, count: 1, avg: 0.002816 sec, total: 0.002816 sec

      284 | PMA\libraries\config\ConfigFile::_flattenArray 254:21
          | cost: 21.9 %, count: 204, avg: 0.000003 sec, total: 0.000616 sec

        285 | PMA\libraries\config\ConfigFile::_flattenArray 254:21
            | cost: 30.7 %, count: 236, avg: 0.000001 sec, total: 0.000189 sec

          286 | PMA\libraries\config\ConfigFile::_flattenArray 254:21
              | cost: 13.9 %, count: 62, avg: 0.000000 sec, total: 0.000026 sec

            287 | PMA\libraries\config\ConfigFile::_flattenArray 254:21
                | cost: 8.2 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

      288 | PMA\libraries\config\ConfigFile::set 199:20
          | cost: 46.2 %, count: 488, avg: 0.000003 sec, total: 0.001300 sec

        289 | PMA\libraries\config\ConfigFile::getDefault 329:20
            | cost: 31.0 %, count: 210, avg: 0.000002 sec, total: 0.000403 sec

    290 | PMA\libraries\config\FormDisplay::__construct 95:20
        | cost: 13.7 %, count: 1, avg: 0.003172 sec, total: 0.003172 sec

      291 | PMA\libraries\config\Validator::getValidators 35:27
          | cost: 1.0 %, count: 1, avg: 0.000033 sec, total: 0.000033 sec

        292 | PMA\libraries\config\ConfigFile::getDbEntry 373:20
            | cost: 18.0 %, count: 2, avg: 0.000003 sec, total: 0.000006 sec

    293 | PMA\libraries\config\FormDisplay::registerForm 127:20
        | cost: 6.3 %, count: 2, avg: 0.000731 sec, total: 0.001462 sec

      294 | PMA\libraries\config\Form::__construct 62:20
          | cost: 8.8 %, count: 2, avg: 0.000064 sec, total: 0.000129 sec

        295 | PMA\libraries\config\Form::loadForm 225:20
            | cost: 96.1 %, count: 2, avg: 0.000062 sec, total: 0.000124 sec

          296 | PMA\libraries\config\Form::readFormPaths 173:23
              | cost: 37.1 %, count: 2, avg: 0.000023 sec, total: 0.000046 sec

            297 | PMA\libraries\config\Form::_readFormPathsCallback 145:21
                | cost: 19.7 %, count: 14, avg: 0.000001 sec, total: 0.000009 sec

          298 | PMA\libraries\config\Form::readTypes 198:23
              | cost: 57.1 %, count: 2, avg: 0.000035 sec, total: 0.000071 sec

            299 | PMA\libraries\config\ConfigFile::getDbEntry 373:20
                | cost: 11.4 %, count: 14, avg: 0.000001 sec, total: 0.000008 sec

            300 | PMA\libraries\config\ConfigFile::getDefault 329:20
                | cost: 28.6 %, count: 13, avg: 0.000002 sec, total: 0.000020 sec

    301 | PMA\libraries\config\PageSettings::_getPageSettingsDisplay 158:21
        | cost: 37.6 %, count: 1, avg: 0.008696 sec, total: 0.008696 sec

      302 | PMA\libraries\Response::getInstance 123:27
          | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

      303 | PMA\libraries\config\PageSettings::_storeError 131:21
          | cost: 0.0 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

        304 | PMA\libraries\config\FormDisplay::hasErrors 734:20
            | cost: 33.3 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

      305 | PMA\libraries\Response::getFooter 194:20
          | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

      306 | PMA\libraries\Footer::getSelfUrl 141:20
          | cost: 0.2 %, count: 1, avg: 0.000016 sec, total: 0.000016 sec

      307 | PMA\libraries\config\FormDisplay::getDisplay 283:20
          | cost: 99.6 %, count: 1, avg: 0.008659 sec, total: 0.008659 sec

        308 | PMA\libraries\Util::getImage 181:27
            | cost: 8.0 %, count: 2, avg: 0.000348 sec, total: 0.000696 sec

          309 | PMA\libraries\Theme::getPath 182:20
              | cost: 0.2 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

        310 | PMA\libraries\Template::get 59:27
            | cost: 0.1 %, count: 2, avg: 0.000004 sec, total: 0.000008 sec

          311 | PMA\libraries\Template::__construct 43:23
              | cost: 12.1 %, count: 2, avg: 0.000000 sec, total: 0.000001 sec

        312 | PMA\libraries\Template::render 155:20
            | cost: 39.2 %, count: 2, avg: 0.001697 sec, total: 0.003394 sec

          313 | PMA\libraries\Template::set 84:20
              | cost: 0.1 %, count: 2, avg: 0.000001 sec, total: 0.000003 sec

          314 | PMA\libraries\Template::get 59:27
              | cost: 0.5 %, count: 2, avg: 0.000008 sec, total: 0.000017 sec

            315 | PMA\libraries\Template::__construct 43:23
                | cost: 11.4 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

          316 | PMA\libraries\Template::render 155:20
              | cost: 38.6 %, count: 2, avg: 0.000655 sec, total: 0.001310 sec

            317 | PMA\libraries\Template::set 84:20
                | cost: 0.2 %, count: 2, avg: 0.000001 sec, total: 0.000003 sec

            318 | PMA\libraries\Template::trim 71:27
                | cost: 0.6 %, count: 2, avg: 0.000004 sec, total: 0.000008 sec

          319 | PMA\libraries\Template::trim 71:27
              | cost: 0.2 %, count: 2, avg: 0.000004 sec, total: 0.000008 sec

        320 | PMA\libraries\config\FormDisplay::_validate 169:21
            | cost: 2.8 %, count: 1, avg: 0.000241 sec, total: 0.000241 sec

          321 | PMA\libraries\config\ConfigFile::getValue 343:20
              | cost: 51.5 %, count: 14, avg: 0.000009 sec, total: 0.000124 sec

            322 | PMA\libraries\config\ConfigFile::getCanonicalPath 360:20
                | cost: 3.1 %, count: 14, avg: 0.000000 sec, total: 0.000004 sec

            323 | PMA\libraries\config\ConfigFile::getDefault 329:20
                | cost: 23.2 %, count: 14, avg: 0.000002 sec, total: 0.000029 sec

          324 | PMA\libraries\config\Validator::validate 92:27
              | cost: 34.0 %, count: 1, avg: 0.000082 sec, total: 0.000082 sec

            325 | PMA\libraries\config\Validator::getValidators 35:27
                | cost: 1.2 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

            326 | PMA\libraries\config\ConfigFile::getCanonicalPath 360:20
                | cost: 4.9 %, count: 20, avg: 0.000000 sec, total: 0.000004 sec

            327 | PMA\libraries\config\Validator::validatePositiveNumber 531:27
                | cost: 24.4 %, count: 2, avg: 0.000010 sec, total: 0.000020 sec

              328 | PMA\libraries\config\Validator::validateNumber 478:27
                  | cost: 25.0 %, count: 2, avg: 0.000003 sec, total: 0.000005 sec

            329 | PMA\libraries\config\Validator::validateUpperBound 592:27
                | cost: 1.5 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

        330 | PMA\libraries\config\FormDisplay::_loadUserprefsInfo 776:21
            | cost: 0.2 %, count: 1, avg: 0.000014 sec, total: 0.000014 sec

        331 | PMA\libraries\config\FormDisplay::_displayForms 221:21
            | cost: 31.0 %, count: 1, avg: 0.002681 sec, total: 0.002681 sec

          332 | PMA\libraries\config\Validator::getValidators 35:27
              | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

          333 | PMA\libraries\Util::getImage 181:27
              | cost: 0.6 %, count: 4, avg: 0.000004 sec, total: 0.000017 sec

          334 | PMA\libraries\config\FormDisplay::_displayFieldInput 370:21
              | cost: 95.0 %, count: 14, avg: 0.000182 sec, total: 0.002548 sec

            335 | PMA\libraries\Util::getImage 181:27
                | cost: 1.6 %, count: 14, avg: 0.000003 sec, total: 0.000040 sec

            336 | PMA\libraries\config\ConfigFile::get 314:20
                | cost: 0.8 %, count: 14, avg: 0.000001 sec, total: 0.000021 sec

            337 | PMA\libraries\config\ConfigFile::getDefault 329:20
                | cost: 1.4 %, count: 14, avg: 0.000003 sec, total: 0.000035 sec

            338 | PMA\libraries\config\FormDisplay::getDocLink 747:20
                | cost: 75.7 %, count: 14, avg: 0.000138 sec, total: 0.001928 sec

              339 | PMA\libraries\config\FormDisplay::_getOptName 766:21
                  | cost: 0.3 %, count: 14, avg: 0.000000 sec, total: 0.000006 sec

              340 | PMA\libraries\Util::getDocuLink 530:27
                  | cost: 94.8 %, count: 14, avg: 0.000131 sec, total: 0.001828 sec

            341 | PMA\libraries\config\Form::getOptionType 77:20
                | cost: 1.5 %, count: 14, avg: 0.000003 sec, total: 0.000039 sec

            342 | PMA\libraries\config\FormDisplay::_setComments 798:21
                | cost: 0.6 %, count: 14, avg: 0.000001 sec, total: 0.000016 sec

            343 | PMA\libraries\config\Form::getOptionValueList 98:20
                | cost: 0.3 %, count: 1, avg: 0.000008 sec, total: 0.000008 sec

              344 | PMA\libraries\config\ConfigFile::getDbEntry 373:20
                  | cost: 39.4 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

  345 | PMA\libraries\Response::getInstance 123:27
      | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

  346 | PMA\libraries\config\PageSettings::getErrorHTML 198:20
      | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

  347 | PMA\libraries\Response::addHTML 207:20
      | cost: 0.0 %, count: 2, avg: 0.000001 sec, total: 0.000003 sec

  348 | PMA\libraries\config\PageSettings::getHTML 188:20
      | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

349 | PMA\libraries\Response::getHeader 184:20
    | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

350 | PMA\libraries\Header::getScripts 309:20
    | cost: 0.0 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

351 | PMA\libraries\Scripts::addFile 127:20
    | cost: 0.0 %, count: 3, avg: 0.000004 sec, total: 0.000011 sec

  352 | PMA\libraries\Scripts::_eventBlacklist 170:21
      | cost: 27.7 %, count: 3, avg: 0.000001 sec, total: 0.000003 sec

353 | PMA\libraries\DatabaseInterface::isUserType 2208:20
    | cost: 0.3 %, count: 2, avg: 0.000769 sec, total: 0.001539 sec

  354 | PMA\libraries\Util::cacheExists 3010:27
      | cost: 0.0 %, count: 2, avg: 0.000000 sec, total: 0.000000 sec

  355 | PMA\libraries\DatabaseInterface::_getCurrentUserAndHost 2295:21
      | cost: 25.3 %, count: 2, avg: 0.000195 sec, total: 0.000390 sec

    356 | PMA\libraries\DatabaseInterface::fetchValue 1580:20
        | cost: 97.1 %, count: 2, avg: 0.000189 sec, total: 0.000379 sec

      357 | PMA\libraries\DatabaseInterface::tryQuery 228:20
          | cost: 89.2 %, count: 2, avg: 0.000169 sec, total: 0.000338 sec

        358 | PMA\libraries\DatabaseInterface::getLink 2738:20
            | cost: 1.2 %, count: 2, avg: 0.000002 sec, total: 0.000004 sec

        359 | PMA\libraries\dbi\DBIMysqli::realQuery 242:20
            | cost: 90.0 %, count: 2, avg: 0.000152 sec, total: 0.000304 sec

        360 | PMA\libraries\Tracker::isActive 46:27
            | cost: 2.4 %, count: 2, avg: 0.000004 sec, total: 0.000008 sec

      361 | PMA\libraries\DatabaseInterface::numRows 2576:20
          | cost: 1.9 %, count: 2, avg: 0.000004 sec, total: 0.000007 sec

        362 | PMA\libraries\dbi\DBIMysqli::numRows 437:20
            | cost: 40.0 %, count: 2, avg: 0.000001 sec, total: 0.000003 sec

      363 | PMA\libraries\DatabaseInterface::fetchRow 2436:20
          | cost: 2.3 %, count: 2, avg: 0.000004 sec, total: 0.000009 sec

        364 | PMA\libraries\dbi\DBIMysqli::fetchRow 299:20
            | cost: 56.8 %, count: 2, avg: 0.000003 sec, total: 0.000005 sec

      365 | PMA\libraries\DatabaseInterface::freeResult 2461:20
          | cost: 1.3 %, count: 2, avg: 0.000003 sec, total: 0.000005 sec

        366 | PMA\libraries\dbi\DBIMysqli::freeResult 324:20
            | cost: 19.0 %, count: 2, avg: 0.000000 sec, total: 0.000001 sec

  367 | PMA\libraries\DatabaseInterface::tryQuery 228:20
      | cost: 68.0 %, count: 2, avg: 0.000523 sec, total: 0.001047 sec

    368 | PMA\libraries\DatabaseInterface::getLink 2738:20
        | cost: 0.1 %, count: 2, avg: 0.000001 sec, total: 0.000001 sec

    369 | PMA\libraries\dbi\DBIMysqli::realQuery 242:20
        | cost: 95.1 %, count: 2, avg: 0.000498 sec, total: 0.000995 sec

    370 | PMA\libraries\DatabaseInterface::affectedRows 2614:20
        | cost: 1.3 %, count: 2, avg: 0.000007 sec, total: 0.000014 sec

      371 | PMA\libraries\DatabaseInterface::getLink 2738:20
          | cost: 6.9 %, count: 2, avg: 0.000000 sec, total: 0.000001 sec

      372 | PMA\libraries\dbi\DBIMysqli::affectedRows 454:20
          | cost: 13.8 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

    373 | PMA\libraries\Tracker::isActive 46:27
        | cost: 0.7 %, count: 2, avg: 0.000004 sec, total: 0.000007 sec

  374 | PMA\libraries\DatabaseInterface::numRows 2576:20
      | cost: 2.7 %, count: 2, avg: 0.000021 sec, total: 0.000042 sec

    375 | PMA\libraries\dbi\DBIMysqli::numRows 437:20
        | cost: 5.1 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

  376 | PMA\libraries\DatabaseInterface::freeResult 2461:20
      | cost: 0.4 %, count: 2, avg: 0.000003 sec, total: 0.000006 sec

    377 | PMA\libraries\dbi\DBIMysqli::freeResult 324:20
        | cost: 36.0 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

  378 | PMA\libraries\Util::cacheSet 3045:27
      | cost: 0.1 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

  379 | PMA\libraries\Util::cacheGet 3023:27
      | cost: 0.5 %, count: 2, avg: 0.000004 sec, total: 0.000008 sec

    380 | PMA\libraries\Util::cacheExists 3010:27
        | cost: 23.5 %, count: 2, avg: 0.000001 sec, total: 0.000002 sec

381 | PMA\libraries\DatabaseInterface::selectDb 2396:20
    | cost: 0.0 %, count: 1, avg: 0.000068 sec, total: 0.000068 sec

  382 | PMA\libraries\DatabaseInterface::getLink 2738:20
      | cost: 1.8 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

  383 | PMA\libraries\dbi\DBIMysqli::selectDb 228:20
      | cost: 73.7 %, count: 1, avg: 0.000050 sec, total: 0.000050 sec

384 | PMA\libraries\Util::checkParameters 2169:27
    | cost: 0.0 %, count: 1, avg: 0.000006 sec, total: 0.000006 sec

385 | PMA\libraries\Util::showMySQLDocu 504:27
    | cost: 0.0 %, count: 1, avg: 0.000053 sec, total: 0.000053 sec

  386 | PMA\libraries\Util::getMySQLDocuURL 464:27
      | cost: 60.1 %, count: 1, avg: 0.000032 sec, total: 0.000032 sec

  387 | PMA\libraries\Util::showDocLink 443:27
      | cost: 22.4 %, count: 1, avg: 0.000012 sec, total: 0.000012 sec

    388 | PMA\libraries\Util::getImage 181:27
        | cost: 42.0 %, count: 1, avg: 0.000005 sec, total: 0.000005 sec

389 | PMA\libraries\Util::showDocu 565:27
    | cost: 0.0 %, count: 1, avg: 0.000150 sec, total: 0.000150 sec

  390 | PMA\libraries\Util::getDocuLink 530:27
      | cost: 86.5 %, count: 1, avg: 0.000130 sec, total: 0.000130 sec

  391 | PMA\libraries\Util::showDocLink 443:27
      | cost: 7.9 %, count: 1, avg: 0.000012 sec, total: 0.000012 sec

    392 | PMA\libraries\Util::getImage 181:27
        | cost: 34.0 %, count: 1, avg: 0.000004 sec, total: 0.000004 sec

393 | PMA\libraries\Util::getFKCheckbox 3284:27
    | cost: 0.3 %, count: 1, avg: 0.001174 sec, total: 0.001174 sec

  394 | PMA\libraries\Util::isForeignKeyCheck 3269:27
      | cost: 99.1 %, count: 1, avg: 0.001164 sec, total: 0.001164 sec

    395 | PMA\libraries\DatabaseInterface::getVariable 1360:20
        | cost: 99.4 %, count: 1, avg: 0.001157 sec, total: 0.001157 sec

      396 | PMA\libraries\DatabaseInterface::getLink 2738:20
          | cost: 0.2 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

      397 | PMA\libraries\DatabaseInterface::fetchValue 1580:20
          | cost: 99.3 %, count: 1, avg: 0.001149 sec, total: 0.001149 sec

        398 | PMA\libraries\DatabaseInterface::tryQuery 228:20
            | cost: 98.2 %, count: 1, avg: 0.001129 sec, total: 0.001129 sec

          399 | PMA\libraries\DatabaseInterface::getLink 2738:20
              | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

          400 | PMA\libraries\dbi\DBIMysqli::realQuery 242:20
              | cost: 98.8 %, count: 1, avg: 0.001115 sec, total: 0.001115 sec

          401 | PMA\libraries\Tracker::isActive 46:27
              | cost: 0.2 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

        402 | PMA\libraries\DatabaseInterface::numRows 2576:20
            | cost: 0.2 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

          403 | PMA\libraries\dbi\DBIMysqli::numRows 437:20
              | cost: 33.3 %, count: 1, avg: 0.000001 sec, total: 0.000001 sec

        404 | PMA\libraries\DatabaseInterface::fetchRow 2436:20
            | cost: 0.4 %, count: 1, avg: 0.000005 sec, total: 0.000005 sec

          405 | PMA\libraries\dbi\DBIMysqli::fetchRow 299:20
              | cost: 38.1 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec

        406 | PMA\libraries\DatabaseInterface::freeResult 2461:20
            | cost: 0.2 %, count: 1, avg: 0.000003 sec, total: 0.000003 sec

          407 | PMA\libraries\dbi\DBIMysqli::freeResult 324:20
              | cost: 0.0 %, count: 1, avg: 0.000000 sec, total: 0.000000 sec

408 | PMA\libraries\Response::addHTML 207:20
    | cost: 0.0 %, count: 1, avg: 0.000002 sec, total: 0.000002 sec
```

### Composer

Download composer:

    wget -nc http://getcomposer.org/composer.phar

and add dependency to your project:

    php composer.phar cheprasov/php-simple-profiler

## Something doesn't work

Feel free to fork project, fix bugs and finally request for pull
