#!/bin/bash
cd ../../SourceCode

echo PHP code styles
../vendor/bin/phpcs -sp --standard=ruleset.xml .

cd ..
# vendor/bin/phpunit -c Tests/phpunit.xml Tests/UnitTests.php %1 %2

if [[ $1 == "release" ]] ; then
	echo "release Is set!"

	rm -rf Documentation
	php /mnt/c/Users/JamesMc/Data/External/Util/Bin/phpDocumentor.phar --setting="graphs.enabled=true" -d SourceCode -t Documentation
fi
