.PHONY: test coverage

test:
	vendor/bin/phpunit

coverage:
	php -e -d zend_extension=xdebug.so vendor/bin/phpunit --coverage-html=./tests/coverage
