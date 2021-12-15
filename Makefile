.PHONY: test coverage

test:
	XDEBUG_MODE=off php8.1 tools/phpunit.phar

coverage:
	XDEBUG_MODE=coverage php8.1 tools/phpunit.phar --coverage-html=./tmp/coverage
