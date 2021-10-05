CD %~dp0
CD ..\..\SourceCode

ECHO PHP code styles
CALL ..\vendor\bin\phpcs -sp --standard=ruleset.xml .

CD ..
CALL phpdocumentor --setting=graphs.enabled=true -d SourceCode -t Documentation
