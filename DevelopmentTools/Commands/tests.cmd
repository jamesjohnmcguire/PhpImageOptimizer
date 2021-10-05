CD %~dp0
cd ..\..

vendor\bin\phpunit -c Tests\phpunit.xml Tests\UnitTests.php %1 %2
