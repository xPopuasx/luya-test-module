<?php

namespace api;
use ApiTester;

class MyApiTestCest
{
    public function _before(ApiTester $I)
    {
    }

    // tests
    public function tryToTest(ApiTester $I): void
    {
        foreach ($this->getFileResource()['requests'] as $key => $value) {
            if ($value['method'] === 'GET') {
                $I->sendGET($value['action'], $value['params'] ?? []);
            }

            if ($value['method'] === 'POST') {
                $I->sendPOST($value['action'], $value['params'] ?? []);
            }

            if ($value['method'] === 'PUT') {
                $I->sendPUT($value['action'], $value['params'] ?? []);
            }

            if ($value['method'] === 'DELETE') {
                $I->sendDELETE($value['action'], $value['params'] ?? []);
            }

            if ($value['method'] === 'PATCH') {
                $I->sendPATCH($value['action'], $value['params'] ?? []);
            }

            if (isset($value['responseJson']) && $value['responseJson'] === true) {
                $I->seeResponseIsJson();
            }

            if (isset($value['status'])) {
                $I->seeResponseCodeIs($value['status']);
            }
        }
    }


    private function getFileResource(): array
    {
        if (!file_exists('./tests_resources/data.json')) {
            return [];
        }

        $content = file_get_contents('./tests_resources/data.json');
        return json_decode($content, true);
    }


}
