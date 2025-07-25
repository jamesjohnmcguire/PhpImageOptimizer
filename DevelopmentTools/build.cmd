CD %~dp0
CD ..

CALL composer validate --strict
CALL composer install --prefer-dist

ECHO composer outdated packages:
CALL composer outdated

echo Checking syntax...
CALL vendor/bin/parallel-lint --exclude .git --exclude .phpdoc --exclude Documentation --exclude Support --exclude vendor .

echo PHP code styles
CALL vendor/bin/phpcs -sp --standard=ruleset.xml .

CALL vendor\bin\phpunit --testdox -c Tests\phpunit.xml Tests\UnitTests.php

if "%1" == "release" GOTO release
GOTO end

:release
ECHO Release
REM CD SourceCode
REM CALL VersionUpdate ImageOptimizer.php
REM CD ..\Tests
REM CALL VersionUpdate tests.php
REM CD ..

REM git add SourceCode\ImageOptimizer.php Tests\tests.php
REM git commit -am"Increment version build number" 

REM Currently, not working on windows - phpdocumentor bug
REM CALL phpdocumentor --setting=graphs.enabled=true -d SourceCode -t Documentation

if "%~2"=="" GOTO error1
if "%~3"=="" GOTO error2

git tag %2
git push --tags
git push --all

gh release create v%2 --notes %2

GOTO end

:error1
ECHO No tag specified
GOTO end

:error2
ECHO No message specified

:end
