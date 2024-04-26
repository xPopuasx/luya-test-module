<?php
namespace sitis\tests\tests\api;

use ApiTester;

class MyApiTestCest
{
    public function _before(ApiTester $I): void
    {
        $I->wantToTest('MyApiTest');
    }

    // tests
    public function tryToTest(ApiTester $I): void
    {
        $I->wantToTest('MyApiTest');
    }
}
