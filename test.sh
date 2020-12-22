# Test script

composer update
RC=./vendor/phpunit/phpunit/phpunit tests
rm -rf vendor composer.lock
exit $RC

