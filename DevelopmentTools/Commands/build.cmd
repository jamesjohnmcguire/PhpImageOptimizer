CD %~dp0
CD ..\..\SourceCode

ECHO PHP code styles
CALL ..\vendor\bin\phpcs -sp --standard=ruleset.xml .

CD ..
CALL vendor\bin\phpunit -c Tests\phpunit.xml Tests\UnitTests.php %1 %2

if "%1" == "release" GOTO release
GOTO end

:release
ECHO Release
CD SourceCode
CALL VersionUpdate ImageOptimizer.php
cd ..\Tests
CALL VersionUpdate tests.php

REM Currently, not working on windows - phpdocumentor bug
REM CALL phpdocumentor --setting=graphs.enabled=true -d SourceCode -t Documentation

:end
CD ..
