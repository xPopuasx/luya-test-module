<?php

namespace sitis\tests\admin\commands;

use luya\console\Command;
use yii\helpers\FileHelper;
use yii\base\Exception;

class TestsController extends Command
{
    private string $testsFolder = './vendor/sitis/feature-test-module/tests';
    private string $testsCaseFolder = './vendor/sitis/feature-test-module/tests_resources';
    private string $ymlFolder = './vendor/sitis/feature-test-module/codeception.yml';

    private array $testExecFiles = [
        './tests/acceptance',
        './tests/functional',
        './tests/unit',
    ];

    private string $testsJsonFolder = 'tests_resources/';

    public function init(): void
    {
        parent::init();
    }

    /**
     * @throws Exception
     */
    public function actionVendorPublish(): void
    {
        if(file_exists('./tests')){
            $this->outputError('tests folder already exists');
            return;
        }

        if(!file_exists($this->testsFolder) || !file_exists($this->ymlFolder) || !file_exists($this->testsCaseFolder)
        ) {
            $this->outputError('tests folder not found');
            return;
        }

        if(file_exists($this->testsFolder)){
            FileHelper::copyDirectory($this->testsFolder, './tests');
            FileHelper::copyDirectory($this->testsCaseFolder, './tests_resources');

            $this->outputSuccess('tests folder is published');
        }

        if(file_exists($this->ymlFolder)){
            copy($this->ymlFolder, 'tmp-codeception.txt'); rename('tmp-codeception.txt', 'codeception.yml');

            $this->outputSuccess('codeception.yml is published');
        }

        foreach ($this->testExecFiles as $testExecFile) {
            FileHelper::createDirectory($testExecFile);
        }

        FileHelper::createDirectory($this->testsJsonFolder);

        $this->outputSuccess('All tests are created');
    }
}
