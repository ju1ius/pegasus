.PHONY: test coverage

test:
	tools/phpunit.phar

coverage:
	php -e -d zend_extension=xdebug.so tools/phpunit.phar --coverage-html=./tests/coverage
