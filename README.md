[![MIT license](http://img.shields.io/badge/license-MIT-brightgreen.svg)](http://opensource.org/licenses/MIT)

SimpleProfiler v2.0.0 for PHP >= 5.5
=========

The SimpleProfiler is a tool for automatic analysis of code.
Or, you just using simple tools like Timer and Counters.

##### Features:
- Easy to connect to a project if you want of automatic analysis of your code.
- Has 'counter' and 'timer' tools.
- Has grouping for compare elements.
- Support anonymous function.
- Written on PHP.

### 1. How to add the profiler to you project for automatic analysis of code
Note. You can use profiler tools like 'counter' and 'timer' without this step.

All you need is open your 'autoload' function, and use the profiler's function for loading class.

```php
\SimpleProfiler\Profiler::loadFile(string $classPath, bool $inject_profiler = true) : void
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

You can use function `\SimpleProfiler\Profiler::getLog` for getting already formatted log data.

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

Note. Calculation of `cost` does not accounting nested function, it just uses sum time of elements in a group.

### 3. Usage of Tools

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

### Example with phpMyAdmin-4.6.0

1. I changed the file `phpMyAdmin-4.6.0/libraries/Psr4Autoloader.php`
```php
...
    protected function requireFile($file)
    {
        if (file_exists($file)) {
            //include $file;
            \SimpleProfiler\Profiler::loadFile($file);
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

3. I opened phpMyAdmin in a browser in executed the next query `SELECT * FROM test.A;` and I got the next output:
```
# Group [ Profiler ]

1) PMA\libraries\ErrorHandler::__construct
   count: 1, avg_time: 0.000009 sec, full_time: 0.000009 sec
   cost: [] 0 %

2) PMA\libraries\Config::__construct
   count: 1, avg_time: 0.000288 sec, full_time: 0.000288 sec
   cost: [] 0.1 %

3) PMA\libraries\Config::load
   count: 1, avg_time: 0.000172 sec, full_time: 0.000172 sec
   cost: [] 0.1 %

4) PMA\libraries\Config::loadDefaults
   count: 1, avg_time: 0.000113 sec, full_time: 0.000113 sec
   cost: [] 0.1 %

5) PMA\libraries\Config::setSource
   count: 1, avg_time: 0.000003 sec, full_time: 0.000003 sec
   cost: [] 0 %

6) PMA\libraries\Config::checkFontsize
   count: 1, avg_time: 0.000028 sec, full_time: 0.000028 sec
   cost: [] 0 %

7) PMA\libraries\Config::get
   count: 29, avg_time: 0.000001 sec, full_time: 0.000029 sec
   cost: [] 0 %

8) PMA\libraries\Config::set
   count: 21, avg_time: 0.000002 sec, full_time: 0.000034 sec
   cost: [] 0 %

9) PMA\libraries\Config::setCookie
   count: 7, avg_time: 0.000013 sec, full_time: 0.000089 sec
   cost: [] 0 %

10) PMA\libraries\Config::checkConfigSource
   count: 1, avg_time: 0.000007 sec, full_time: 0.000007 sec
   cost: [] 0 %

11) PMA\libraries\Config::getSource
   count: 3, avg_time: 0.000001 sec, full_time: 0.000003 sec
   cost: [] 0 %

12) PMA\libraries\Config::checkCollationConnection
   count: 1, avg_time: 0.000007 sec, full_time: 0.000007 sec
   cost: [] 0 %

13) PMA\libraries\Config::checkSystem
   count: 1, avg_time: 0.000109 sec, full_time: 0.000109 sec
   cost: [] 0.1 %

14) PMA\libraries\Config::checkWebServerOs
   count: 1, avg_time: 0.000007 sec, full_time: 0.000007 sec
   cost: [] 0 %

15) PMA\libraries\Config::checkWebServer
   count: 1, avg_time: 0.000006 sec, full_time: 0.000006 sec
   cost: [] 0 %

16) PMA\libraries\Config::checkGd2
   count: 1, avg_time: 0.000018 sec, full_time: 0.000018 sec
   cost: [] 0 %

17) PMA\libraries\Config::checkClient
   count: 1, avg_time: 0.000031 sec, full_time: 0.000031 sec
   cost: [] 0 %

18) PMA\libraries\Config::_setClientPlatform
   count: 1, avg_time: 0.000005 sec, full_time: 0.000005 sec
   cost: [] 0 %

19) PMA\libraries\Config::checkUpload
   count: 1, avg_time: 0.000006 sec, full_time: 0.000006 sec
   cost: [] 0 %

20) PMA\libraries\Config::checkUploadSize
   count: 1, avg_time: 0.000016 sec, full_time: 0.000016 sec
   cost: [] 0 %

21) PMA\libraries\Config::checkOutputCompression
   count: 1, avg_time: 0.000008 sec, full_time: 0.000008 sec
   cost: [] 0 %

22) PMA\libraries\Config::enableBc
   count: 1, avg_time: 0.000038 sec, full_time: 0.000038 sec
   cost: [] 0 %

23) PMA\libraries\Config::getCookiePath
   count: 4, avg_time: 0.000004 sec, full_time: 0.000014 sec
   cost: [] 0 %

24) PMA\libraries\Config::isHttps
   count: 4, avg_time: 0.000010 sec, full_time: 0.000039 sec
   cost: [] 0 %

25) PMA\libraries\LanguageManager::getInstance
   count: 3, avg_time: 0.000005 sec, full_time: 0.000015 sec
   cost: [] 0 %

26) PMA\libraries\LanguageManager::selectLanguage
   count: 1, avg_time: 0.000821 sec, full_time: 0.000821 sec
   cost: [] 0.4 %

27) PMA\libraries\LanguageManager::availableLanguages
   count: 1, avg_time: 0.000817 sec, full_time: 0.000817 sec
   cost: [] 0.4 %

28) PMA\libraries\LanguageManager::availableLocales
   count: 1, avg_time: 0.000128 sec, full_time: 0.000128 sec
   cost: [] 0.1 %

29) PMA\libraries\LanguageManager::listLocaleDir
   count: 1, avg_time: 0.000124 sec, full_time: 0.000124 sec
   cost: [] 0.1 %

30) PMA\libraries\Language::__construct
   count: 41, avg_time: 0.000001 sec, full_time: 0.000051 sec
   cost: [] 0 %

31) PMA\libraries\Language::activate
   count: 1, avg_time: 0.017082 sec, full_time: 0.017082 sec
   cost: [--------] 8.2 %

32) PMA\libraries\Language::isRTL
   count: 1, avg_time: 0.000002 sec, full_time: 0.000002 sec
   cost: [] 0 %

33) PMA\libraries\LanguageManager::showWarnings
   count: 1, avg_time: 0.000007 sec, full_time: 0.000007 sec
   cost: [] 0 %

34) PMA\libraries\Config::checkPermissions
   count: 1, avg_time: 0.000036 sec, full_time: 0.000036 sec
   cost: [] 0 %

35) PMA\libraries\ErrorHandler::handleError
   count: 1, avg_time: 0.000003 sec, full_time: 0.000003 sec
   cost: [] 0 %

36) PMA\libraries\ThemeManager::initializeTheme
   count: 1, avg_time: 0.000260 sec, full_time: 0.000260 sec
   cost: [] 0.1 %

37) PMA\libraries\ThemeManager::checkConfig
   count: 1, avg_time: 0.000220 sec, full_time: 0.000220 sec
   cost: [] 0.1 %

38) PMA\libraries\ThemeManager::loadThemes
   count: 1, avg_time: 0.000212 sec, full_time: 0.000212 sec
   cost: [] 0.1 %

39) PMA\libraries\ThemeManager::getThemesPath
   count: 8, avg_time: 0.000001 sec, full_time: 0.000007 sec
   cost: [] 0 %

40) PMA\libraries\Theme::load
   count: 2, avg_time: 0.000062 sec, full_time: 0.000123 sec
   cost: [] 0.1 %

41) PMA\libraries\Theme::setPath
   count: 2, avg_time: 0.000001 sec, full_time: 0.000003 sec
   cost: [] 0 %

42) PMA\libraries\Theme::loadInfo
   count: 2, avg_time: 0.000044 sec, full_time: 0.000089 sec
   cost: [] 0 %

43) PMA\libraries\Theme::getPath
   count: 18, avg_time: 0.000001 sec, full_time: 0.000017 sec
   cost: [] 0 %

44) PMA\libraries\Theme::setVersion
   count: 2, avg_time: 0.000001 sec, full_time: 0.000003 sec
   cost: [] 0 %

45) PMA\libraries\Theme::setName
   count: 2, avg_time: 0.000002 sec, full_time: 0.000003 sec
   cost: [] 0 %

46) PMA\libraries\Theme::checkImgPath
   count: 2, avg_time: 0.000009 sec, full_time: 0.000018 sec
   cost: [] 0 %

47) PMA\libraries\Theme::setImgPath
   count: 2, avg_time: 0.000001 sec, full_time: 0.000002 sec
   cost: [] 0 %

48) PMA\libraries\Theme::setId
   count: 2, avg_time: 0.000001 sec, full_time: 0.000002 sec
   cost: [] 0 %

49) PMA\libraries\Theme::getName
   count: 1, avg_time: 0.000001 sec, full_time: 0.000001 sec
   cost: [] 0 %

50) PMA\libraries\Theme::getImgPath
   count: 1, avg_time: 0.000003 sec, full_time: 0.000003 sec
   cost: [] 0 %

51) PMA\libraries\Theme::getLayoutFile
   count: 2, avg_time: 0.000004 sec, full_time: 0.000007 sec
   cost: [] 0 %

52) PMA\libraries\ThemeManager::setThemeCookie
   count: 1, avg_time: 0.000015 sec, full_time: 0.000015 sec
   cost: [] 0 %

53) PMA\libraries\ThemeManager::getThemeCookieName
   count: 2, avg_time: 0.000002 sec, full_time: 0.000004 sec
   cost: [] 0 %

54) PMA\libraries\DatabaseInterface::checkDbExtension
   count: 1, avg_time: 0.000009 sec, full_time: 0.000009 sec
   cost: [] 0 %

55) PMA\libraries\DatabaseInterface::__construct
   count: 1, avg_time: 0.000003 sec, full_time: 0.000003 sec
   cost: [] 0 %

56) PMA\libraries\di\Container::getDefaultContainer
   count: 1, avg_time: 0.000293 sec, full_time: 0.000293 sec
   cost: [] 0.1 %

57) PMA\libraries\di\Container::__construct
   count: 1, avg_time: 0.000289 sec, full_time: 0.000289 sec
   cost: [] 0.1 %

58) PMA\libraries\di\Container::alias
   count: 2, avg_time: 0.000094 sec, full_time: 0.000188 sec
   cost: [] 0.1 %

59) PMA\libraries\di\AliasItem::__construct
   count: 2, avg_time: 0.000002 sec, full_time: 0.000004 sec
   cost: [] 0 %

60) PMA\libraries\di\Container::set
   count: 2, avg_time: 0.000051 sec, full_time: 0.000103 sec
   cost: [] 0 %

61) PMA\libraries\di\ValueItem::__construct
   count: 2, avg_time: 0.000001 sec, full_time: 0.000003 sec
   cost: [] 0 %

62) PMA\libraries\plugins\auth\AuthenticationCookie::authCheck
   count: 1, avg_time: 0.000079 sec, full_time: 0.000079 sec
   cost: [] 0 %

63) PMA\libraries\plugins\auth\AuthenticationCookie::_getEncryptionSecret
   count: 2, avg_time: 0.000007 sec, full_time: 0.000013 sec
   cost: [] 0 %

64) PMA\libraries\plugins\auth\AuthenticationCookie::_getSessionEncryptionSecret
   count: 4, avg_time: 0.000002 sec, full_time: 0.000007 sec
   cost: [] 0 %

65) PMA\libraries\plugins\auth\AuthenticationCookie::cookieDecrypt
   count: 2, avg_time: 0.000023 sec, full_time: 0.000046 sec
   cost: [] 0 %

66) PMA\libraries\plugins\auth\AuthenticationCookie::getIVSize
   count: 3, avg_time: 0.000007 sec, full_time: 0.000021 sec
   cost: [] 0 %

67) PMA\libraries\plugins\auth\AuthenticationCookie::useOpenSSL
   count: 8, avg_time: 0.000002 sec, full_time: 0.000016 sec
   cost: [] 0 %

68) PMA\libraries\plugins\auth\AuthenticationCookie::authSetUser
   count: 1, avg_time: 0.000007 sec, full_time: 0.000007 sec
   cost: [] 0 %

69) PMA\libraries\DatabaseInterface::connect
   count: 1, avg_time: 0.030487 sec, full_time: 0.030487 sec
   cost: [---------------] 14.6 %

70) PMA\libraries\dbi\DBIMysqli::connect
   count: 1, avg_time: 0.000183 sec, full_time: 0.000183 sec
   cost: [] 0.1 %

71) PMA\libraries\DatabaseInterface::getServerPort
   count: 1, avg_time: 0.000003 sec, full_time: 0.000003 sec
   cost: [] 0 %

72) PMA\libraries\DatabaseInterface::getServerSocket
   count: 1, avg_time: 0.000002 sec, full_time: 0.000002 sec
   cost: [] 0 %

73) PMA\libraries\dbi\DBIMysqli::_realConnect
   count: 1, avg_time: 0.000158 sec, full_time: 0.000158 sec
   cost: [] 0.1 %

74) PMA\libraries\DatabaseInterface::postConnect
   count: 1, avg_time: 0.030297 sec, full_time: 0.030297 sec
   cost: [---------------] 14.5 %

75) PMA\libraries\Util::cacheExists
   count: 34, avg_time: 0.000002 sec, full_time: 0.000065 sec
   cost: [] 0 %

76) PMA\libraries\Util::cacheGet
   count: 26, avg_time: 0.000003 sec, full_time: 0.000079 sec
   cost: [] 0 %

77) PMA\libraries\DatabaseInterface::query
   count: 2, avg_time: 0.002287 sec, full_time: 0.004574 sec
   cost: [--] 2.2 %

78) PMA\libraries\DatabaseInterface::tryQuery
   count: 6, avg_time: 0.000909 sec, full_time: 0.005456 sec
   cost: [---] 2.6 %

79) PMA\libraries\DatabaseInterface::getLink
   count: 11, avg_time: 0.000002 sec, full_time: 0.000019 sec
   cost: [] 0 %

80) PMA\libraries\dbi\DBIMysqli::realQuery
   count: 6, avg_time: 0.000156 sec, full_time: 0.000936 sec
   cost: [] 0.4 %

81) PMA\libraries\DatabaseInterface::affectedRows
   count: 3, avg_time: 0.000008 sec, full_time: 0.000024 sec
   cost: [] 0 %

82) PMA\libraries\dbi\DBIMysqli::affectedRows
   count: 3, avg_time: 0.000002 sec, full_time: 0.000005 sec
   cost: [] 0 %

83) PMA\libraries\Tracker::isActive
   count: 6, avg_time: 0.000004 sec, full_time: 0.000024 sec
   cost: [] 0 %

84) PMA\libraries\Util::sqlAddSlashes
   count: 1, avg_time: 0.000004 sec, full_time: 0.000004 sec
   cost: [] 0 %

85) PMA\libraries\LanguageManager::getCurrentLanguage
   count: 1, avg_time: 0.000002 sec, full_time: 0.000002 sec
   cost: [] 0 %

86) PMA\libraries\Language::getMySQLLocale
   count: 1, avg_time: 0.000002 sec, full_time: 0.000002 sec
   cost: [] 0 %

87) PMA\libraries\plugins\auth\AuthenticationCookie::storeUserCredentials
   count: 1, avg_time: 0.000273 sec, full_time: 0.000273 sec
   cost: [] 0.1 %

88) PMA\libraries\plugins\auth\AuthenticationCookie::createIV
   count: 1, avg_time: 0.000085 sec, full_time: 0.000085 sec
   cost: [] 0 %

89) PMA\libraries\plugins\auth\AuthenticationCookie::storeUsernameCookie
   count: 1, avg_time: 0.000048 sec, full_time: 0.000048 sec
   cost: [] 0 %

90) PMA\libraries\plugins\auth\AuthenticationCookie::cookieEncrypt
   count: 2, avg_time: 0.000010 sec, full_time: 0.000019 sec
   cost: [] 0 %

91) PMA\libraries\plugins\auth\AuthenticationCookie::storePasswordCookie
   count: 1, avg_time: 0.000029 sec, full_time: 0.000029 sec
   cost: [] 0 %

92) PMA\libraries\DbList::__set
   count: 2, avg_time: 0.000003 sec, full_time: 0.000005 sec
   cost: [] 0 %

93) PMA\libraries\Config::loadUserPreferences
   count: 1, avg_time: 0.000073 sec, full_time: 0.000073 sec
   cost: [] 0 %

94) PMA\libraries\ThemeManager::getThemeCookie
   count: 1, avg_time: 0.000008 sec, full_time: 0.000008 sec
   cost: [] 0 %

95) PMA\libraries\Theme::getId
   count: 2, avg_time: 0.000002 sec, full_time: 0.000004 sec
   cost: [] 0 %

96) PMA\libraries\Config::_saveConnectionCollation
   count: 1, avg_time: 0.000004 sec, full_time: 0.000004 sec
   cost: [] 0 %

97) PMA\libraries\Response::getInstance
   count: 4, avg_time: 0.002725 sec, full_time: 0.010898 sec
   cost: [-----] 5.2 %

98) PMA\libraries\Response::__construct
   count: 1, avg_time: 0.010887 sec, full_time: 0.010887 sec
   cost: [-----] 5.2 %

99) PMA\libraries\OutputBuffering::getInstance
   count: 1, avg_time: 0.000019 sec, full_time: 0.000019 sec
   cost: [] 0 %

100) PMA\libraries\OutputBuffering::__construct
   count: 1, avg_time: 0.000015 sec, full_time: 0.000015 sec
   cost: [] 0 %

101) PMA\libraries\OutputBuffering::_getMode
   count: 1, avg_time: 0.000009 sec, full_time: 0.000009 sec
   cost: [] 0 %

102) PMA\libraries\OutputBuffering::start
   count: 1, avg_time: 0.000012 sec, full_time: 0.000012 sec
   cost: [] 0 %

103) PMA\libraries\Header::__construct
   count: 1, avg_time: 0.006119 sec, full_time: 0.006119 sec
   cost: [---] 2.9 %

104) PMA\libraries\Console::__construct
   count: 1, avg_time: 0.000006 sec, full_time: 0.000006 sec
   cost: [] 0 %

105) PMA\libraries\Menu::__construct
   count: 1, avg_time: 0.000004 sec, full_time: 0.000004 sec
   cost: [] 0 %

106) PMA\libraries\Scripts::__construct
   count: 2, avg_time: 0.000003 sec, full_time: 0.000005 sec
   cost: [] 0 %

107) PMA\libraries\Header::_addDefaultScripts
   count: 1, avg_time: 0.000464 sec, full_time: 0.000464 sec
   cost: [] 0.2 %

108) PMA\libraries\Scripts::addFile
   count: 29, avg_time: 0.000004 sec, full_time: 0.000124 sec
   cost: [] 0.1 %

109) PMA\libraries\Scripts::_eventBlacklist
   count: 29, avg_time: 0.000002 sec, full_time: 0.000046 sec
   cost: [] 0 %

110) PMA\libraries\Header::getJsParamsCode
   count: 1, avg_time: 0.000287 sec, full_time: 0.000287 sec
   cost: [] 0.1 %

111) PMA\libraries\Header::getJsParams
   count: 1, avg_time: 0.000147 sec, full_time: 0.000147 sec
   cost: [] 0.1 %

112) PMA\libraries\Util::getScriptNameForOption
   count: 1, avg_time: 0.000003 sec, full_time: 0.000003 sec
   cost: [] 0 %

113) PMA\libraries\Util::getTitleForTarget
   count: 3, avg_time: 0.000040 sec, full_time: 0.000121 sec
   cost: [] 0.1 %

114) PMA\libraries\Scripts::addCode
   count: 1, avg_time: 0.000003 sec, full_time: 0.000003 sec
   cost: [] 0 %

115) PMA\libraries\Footer::__construct
   count: 1, avg_time: 0.000007 sec, full_time: 0.000007 sec
   cost: [] 0 %

116) PMA\libraries\Header::setAjax
   count: 1, avg_time: 0.000005 sec, full_time: 0.000005 sec
   cost: [] 0 %

117) PMA\libraries\Console::setAjax
   count: 1, avg_time: 0.000001 sec, full_time: 0.000001 sec
   cost: [] 0 %

118) PMA\libraries\Footer::setAjax
   count: 1, avg_time: 0.000001 sec, full_time: 0.000001 sec
   cost: [] 0 %

119) PMA\libraries\Response::isAjax
   count: 1, avg_time: 0.000001 sec, full_time: 0.000001 sec
   cost: [] 0 %

120) PMA\libraries\Tracker::enable
   count: 1, avg_time: 0.000002 sec, full_time: 0.000002 sec
   cost: [] 0 %

121) PMA\libraries\DatabaseInterface::isSuperuser
   count: 3, avg_time: 0.000021 sec, full_time: 0.000064 sec
   cost: [] 0 %

122) PMA\libraries\DatabaseInterface::isUserType
   count: 5, avg_time: 0.000014 sec, full_time: 0.000069 sec
   cost: [] 0 %

123) PMA\libraries\DbList::__get
   count: 1, avg_time: 0.001409 sec, full_time: 0.001409 sec
   cost: [-] 0.7 %

124) PMA\libraries\DbList::getDatabaseList
   count: 1, avg_time: 0.001405 sec, full_time: 0.001405 sec
   cost: [-] 0.7 %

125) PMA\libraries\DatabaseInterface::fetchValue
   count: 2, avg_time: 0.000327 sec, full_time: 0.000654 sec
   cost: [] 0.3 %

126) PMA\libraries\DatabaseInterface::numRows
   count: 2, avg_time: 0.000008 sec, full_time: 0.000016 sec
   cost: [] 0 %

127) PMA\libraries\dbi\DBIMysqli::numRows
   count: 2, avg_time: 0.000004 sec, full_time: 0.000008 sec
   cost: [] 0 %

128) PMA\libraries\DatabaseInterface::fetchRow
   count: 9, avg_time: 0.000005 sec, full_time: 0.000048 sec
   cost: [] 0 %

129) PMA\libraries\dbi\DBIMysqli::fetchRow
   count: 9, avg_time: 0.000003 sec, full_time: 0.000027 sec
   cost: [] 0 %

130) PMA\libraries\DatabaseInterface::freeResult
   count: 3, avg_time: 0.000004 sec, full_time: 0.000011 sec
   cost: [] 0 %

131) PMA\libraries\dbi\DBIMysqli::freeResult
   count: 3, avg_time: 0.000001 sec, full_time: 0.000003 sec
   cost: [] 0 %

132) PMA\libraries\ListDatabase::__construct
   count: 1, avg_time: 0.000286 sec, full_time: 0.000286 sec
   cost: [] 0.1 %

133) PMA\libraries\ListAbstract::__construct
   count: 1, avg_time: 0.000004 sec, full_time: 0.000004 sec
   cost: [] 0 %

134) PMA\libraries\ListDatabase::build
   count: 1, avg_time: 0.000276 sec, full_time: 0.000276 sec
   cost: [] 0.1 %

135) PMA\libraries\ListDatabase::checkOnlyDatabase
   count: 1, avg_time: 0.000005 sec, full_time: 0.000005 sec
   cost: [] 0 %

136) PMA\libraries\ListDatabase::retrieve
   count: 1, avg_time: 0.000263 sec, full_time: 0.000263 sec
   cost: [] 0.1 %

137) PMA\libraries\DatabaseInterface::fetchResult
   count: 1, avg_time: 0.000254 sec, full_time: 0.000254 sec
   cost: [] 0.1 %

138) PMA\libraries\DatabaseInterface::numFields
   count: 1, avg_time: 0.000005 sec, full_time: 0.000005 sec
   cost: [] 0 %

139) PMA\libraries\dbi\DBIMysqli::numFields
   count: 1, avg_time: 0.000003 sec, full_time: 0.000003 sec
   cost: [] 0 %

140) PMA\libraries\DatabaseInterface::_fetchValue
   count: 6, avg_time: 0.000001 sec, full_time: 0.000006 sec
   cost: [] 0 %

141) PMA\libraries\ListDatabase::checkHideDatabase
   count: 1, avg_time: 0.000001 sec, full_time: 0.000001 sec
   cost: [] 0 %

142) PMA\libraries\config\PageSettings::showGroup
   count: 1, avg_time: 0.022433 sec, full_time: 0.022433 sec
   cost: [-----------] 10.7 %

143) PMA\libraries\config\PageSettings::__construct
   count: 1, avg_time: 0.022316 sec, full_time: 0.022316 sec
   cost: [-----------] 10.7 %

144) PMA\libraries\config\ConfigFile::__construct
   count: 1, avg_time: 0.000221 sec, full_time: 0.000221 sec
   cost: [] 0.1 %

145) PMA\libraries\config\ConfigFile::resetConfigData
   count: 1, avg_time: 0.000004 sec, full_time: 0.000004 sec
   cost: [] 0 %

146) PMA\libraries\config\ConfigFile::setAllowedKeys
   count: 1, avg_time: 0.000023 sec, full_time: 0.000023 sec
   cost: [] 0 %

147) PMA\libraries\config\ConfigFile::setCfgUpdateReadMapping
   count: 1, avg_time: 0.000001 sec, full_time: 0.000001 sec
   cost: [] 0 %

148) PMA\libraries\config\ConfigFile::updateWithGlobalConfig
   count: 1, avg_time: 0.007185 sec, full_time: 0.007185 sec
   cost: [---] 3.4 %

149) PMA\libraries\config\ConfigFile::_flattenArray
   count: 504, avg_time: 0.000001 sec, full_time: 0.000419 sec
   cost: [] 0.2 %

150) PMA\libraries\config\ConfigFile::set
   count: 488, avg_time: 0.000011 sec, full_time: 0.005176 sec
   cost: [---] 2.5 %

151) PMA\libraries\config\ConfigFile::getDefault
   count: 251, avg_time: 0.000010 sec, full_time: 0.002461 sec
   cost: [-] 1.2 %

152) PMA\libraries\config\FormDisplay::__construct
   count: 1, avg_time: 0.002945 sec, full_time: 0.002945 sec
   cost: [-] 1.4 %

153) PMA\libraries\config\Validator::getValidators
   count: 3, avg_time: 0.000031 sec, full_time: 0.000094 sec
   cost: [] 0 %

154) PMA\libraries\config\ConfigFile::getDbEntry
   count: 17, avg_time: 0.000004 sec, full_time: 0.000072 sec
   cost: [] 0 %

155) PMA\libraries\config\FormDisplay::registerForm
   count: 2, avg_time: 0.000672 sec, full_time: 0.001343 sec
   cost: [-] 0.6 %

156) PMA\libraries\config\Form::__construct
   count: 2, avg_time: 0.000160 sec, full_time: 0.000319 sec
   cost: [] 0.2 %

157) PMA\libraries\config\Form::loadForm
   count: 2, avg_time: 0.000155 sec, full_time: 0.000311 sec
   cost: [] 0.1 %

158) PMA\libraries\config\Form::readFormPaths
   count: 2, avg_time: 0.000040 sec, full_time: 0.000081 sec
   cost: [] 0 %

159) PMA\libraries\config\Form::_readFormPathsCallback
   count: 14, avg_time: 0.000002 sec, full_time: 0.000023 sec
   cost: [] 0 %

160) PMA\libraries\config\Form::readTypes
   count: 2, avg_time: 0.000109 sec, full_time: 0.000218 sec
   cost: [] 0.1 %

161) PMA\libraries\config\PageSettings::_getPageSettingsDisplay
   count: 1, avg_time: 0.003560 sec, full_time: 0.003560 sec
   cost: [--] 1.7 %

162) PMA\libraries\config\PageSettings::_storeError
   count: 1, avg_time: 0.000007 sec, full_time: 0.000007 sec
   cost: [] 0 %

163) PMA\libraries\config\FormDisplay::hasErrors
   count: 1, avg_time: 0.000002 sec, full_time: 0.000002 sec
   cost: [] 0 %

164) PMA\libraries\Response::getFooter
   count: 1, avg_time: 0.000002 sec, full_time: 0.000002 sec
   cost: [] 0 %

165) PMA\libraries\Footer::getSelfUrl
   count: 1, avg_time: 0.000026 sec, full_time: 0.000026 sec
   cost: [] 0 %

166) PMA\libraries\config\FormDisplay::getDisplay
   count: 1, avg_time: 0.003499 sec, full_time: 0.003499 sec
   cost: [--] 1.7 %

167) PMA\libraries\Util::getImage
   count: 22, avg_time: 0.000020 sec, full_time: 0.000437 sec
   cost: [] 0.2 %

168) PMA\libraries\Template::get
   count: 4, avg_time: 0.000006 sec, full_time: 0.000024 sec
   cost: [] 0 %

169) PMA\libraries\Template::__construct
   count: 4, avg_time: 0.000002 sec, full_time: 0.000007 sec
   cost: [] 0 %

170) PMA\libraries\Template::render
   count: 4, avg_time: 0.000045 sec, full_time: 0.000182 sec
   cost: [] 0.1 %

171) PMA\libraries\Template::set
   count: 4, avg_time: 0.000003 sec, full_time: 0.000012 sec
   cost: [] 0 %

172) PMA\libraries\Template::trim
   count: 4, avg_time: 0.000031 sec, full_time: 0.000126 sec
   cost: [] 0.1 %

173) PMA\libraries\config\FormDisplay::_validate
   count: 1, avg_time: 0.000406 sec, full_time: 0.000406 sec
   cost: [] 0.2 %

174) PMA\libraries\config\ConfigFile::getValue
   count: 14, avg_time: 0.000015 sec, full_time: 0.000215 sec
   cost: [] 0.1 %

175) PMA\libraries\config\ConfigFile::getCanonicalPath
   count: 34, avg_time: 0.000001 sec, full_time: 0.000046 sec
   cost: [] 0 %

176) PMA\libraries\config\Validator::validate
   count: 1, avg_time: 0.000139 sec, full_time: 0.000139 sec
   cost: [] 0.1 %

177) PMA\libraries\config\Validator::validatePositiveNumber
   count: 2, avg_time: 0.000013 sec, full_time: 0.000026 sec
   cost: [] 0 %

178) PMA\libraries\config\Validator::validateNumber
   count: 2, avg_time: 0.000003 sec, full_time: 0.000006 sec
   cost: [] 0 %

179) PMA\libraries\config\Validator::validateUpperBound
   count: 1, avg_time: 0.000003 sec, full_time: 0.000003 sec
   cost: [] 0 %

180) PMA\libraries\config\FormDisplay::_loadUserprefsInfo
   count: 1, avg_time: 0.000053 sec, full_time: 0.000053 sec
   cost: [] 0 %

181) PMA\libraries\config\FormDisplay::_displayForms
   count: 1, avg_time: 0.001722 sec, full_time: 0.001722 sec
   cost: [-] 0.8 %

182) PMA\libraries\config\FormDisplay::_displayFieldInput
   count: 14, avg_time: 0.000102 sec, full_time: 0.001433 sec
   cost: [-] 0.7 %

183) PMA\libraries\config\ConfigFile::get
   count: 14, avg_time: 0.000003 sec, full_time: 0.000042 sec
   cost: [] 0 %

184) PMA\libraries\config\FormDisplay::getDocLink
   count: 14, avg_time: 0.000012 sec, full_time: 0.000163 sec
   cost: [] 0.1 %

185) PMA\libraries\config\FormDisplay::_getOptName
   count: 14, avg_time: 0.000002 sec, full_time: 0.000026 sec
   cost: [] 0 %

186) PMA\libraries\Util::getDocuLink
   count: 15, avg_time: 0.000005 sec, full_time: 0.000077 sec
   cost: [] 0 %

187) PMA\libraries\config\Form::getOptionType
   count: 14, avg_time: 0.000003 sec, full_time: 0.000039 sec
   cost: [] 0 %

188) PMA\libraries\config\FormDisplay::_setComments
   count: 14, avg_time: 0.000002 sec, full_time: 0.000033 sec
   cost: [] 0 %

189) PMA\libraries\config\Form::getOptionValueList
   count: 1, avg_time: 0.000012 sec, full_time: 0.000012 sec
   cost: [] 0 %

190) PMA\libraries\config\PageSettings::getErrorHTML
   count: 1, avg_time: 0.000002 sec, full_time: 0.000002 sec
   cost: [] 0 %

191) PMA\libraries\Response::addHTML
   count: 3, avg_time: 0.000003 sec, full_time: 0.000008 sec
   cost: [] 0 %

192) PMA\libraries\config\PageSettings::getHTML
   count: 1, avg_time: 0.000001 sec, full_time: 0.000001 sec
   cost: [] 0 %

193) PMA\libraries\Response::getHeader
   count: 1, avg_time: 0.000002 sec, full_time: 0.000002 sec
   cost: [] 0 %

194) PMA\libraries\Header::getScripts
   count: 1, avg_time: 0.000001 sec, full_time: 0.000001 sec
   cost: [] 0 %

195) PMA\libraries\DatabaseInterface::selectDb
   count: 1, avg_time: 0.000095 sec, full_time: 0.000095 sec
   cost: [] 0 %

196) PMA\libraries\dbi\DBIMysqli::selectDb
   count: 1, avg_time: 0.000082 sec, full_time: 0.000082 sec
   cost: [] 0 %

197) PMA\libraries\Util::checkParameters
   count: 1, avg_time: 0.000009 sec, full_time: 0.000009 sec
   cost: [] 0 %

198) PMA\libraries\Util::showMySQLDocu
   count: 1, avg_time: 0.000071 sec, full_time: 0.000071 sec
   cost: [] 0 %

199) PMA\libraries\Util::getMySQLDocuURL
   count: 1, avg_time: 0.000036 sec, full_time: 0.000036 sec
   cost: [] 0 %

200) PMA\libraries\Util::showDocLink
   count: 2, avg_time: 0.000023 sec, full_time: 0.000045 sec
   cost: [] 0 %

201) PMA\libraries\Util::showDocu
   count: 1, avg_time: 0.000030 sec, full_time: 0.000030 sec
   cost: [] 0 %

202) PMA\libraries\Util::getFKCheckbox
   count: 1, avg_time: 0.000508 sec, full_time: 0.000508 sec
   cost: [] 0.2 %

203) PMA\libraries\Util::isForeignKeyCheck
   count: 1, avg_time: 0.000494 sec, full_time: 0.000494 sec
   cost: [] 0.2 %

204) PMA\libraries\DatabaseInterface::getVariable
   count: 1, avg_time: 0.000488 sec, full_time: 0.000488 sec
   cost: [] 0.2 %
```

### TODO

I want to add accounting of nested functions for calculation correct cost percentage.

### Composer

Download composer:

    wget -nc http://getcomposer.org/composer.phar

and add dependency to your project:

    php composer.phar cheprasov/php-simple-profiler

## Something doesn't work

Feel free to fork project, fix bugs and finally request for pull
