#!/bin/bash

cd "$(dirname "${BASH_SOURCE[0]}")"
cd ..

composer validate --strict
composer install --prefer-dist
echo composer outdated packages:
composer outdated --direct

echo Checking syntax...
vendor/bin/parallel-lint --exclude .git --exclude .phpdoc --exclude Documentation --exclude Support --exclude vendor .

echo PHP code styles
vendor/bin/phpcs -sp --standard=ruleset.xml .

vendor\bin\phpunit --testdox -c Tests\phpunit.xml Tests\UnitTests.php

# some problem with phpunit on linux? this installation?
# vendor/bin/phpunit -c Tests/phpunit.xml Tests/UnitTests.php %1 %2

if [[ $1 == "release" ]] ; then
	echo "release Is set!"

	rm -rf Documentation
	phpDocumentor.phar --setting="graphs.enabled=true" -d SourceCode -t Documentation

	if [ -z "$2" ]
	then
		echo "No tag specified"
		exit 1
	fi

	if [ -z "$3" ]
	then
		echo "No message specified"
		exit 1
	fi

	git checkout main
	git merge --no-ff development

	git tag $2
	git push --tags
	git push --all

	gh release create $2 --notes $3
fi
