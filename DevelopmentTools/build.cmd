CD %~dp0
CD ..

CALL composer install --prefer-dist

ECHO composer outdated packages:
CALL composer outdated

CD SourceCode

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
cd ..

git add SourceCode\ImageOptimizer.php Tests\tests.php
git commit -am"Increment version build number" 

REM Currently, not working on windows - phpdocumentor bug
REM CALL phpdocumentor --setting=graphs.enabled=true -d SourceCode -t Documentation

if "%~2"=="" GOTO error1
if "%~3"=="" GOTO error2

git checkout main
git merge --no-ff development

git tag %2
git push --tags
git push --all

gh release create %2 --notes %3

GOTO end

:error1
ECHO No tag specified
GOTO end

:error2
ECHO No message specified

:end
