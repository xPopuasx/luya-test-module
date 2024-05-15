<?php

namespace sitis\tests\admin\commands;

use luya\console\Command;
use sitis\tests\admin\services\DatabaseService;
use sitis\tests\admin\services\SeedService;
use yii\base\NotSupportedException;
use yii\db\Exception;
use yii\helpers\Console;

class DatabaseController extends Command
{
    public function __construct($id, $module, private readonly DatabaseService $databaseService, private readonly SeedService $seedService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * @throws NotSupportedException
     * @throws Exception
     */
    public function actionSeed(): void
    {
        $tables = $this->databaseService->getTables();

        $this->databaseService->makeSortTablesByForeignKey($tables);

        $tables = $this->databaseService->tables;

        if (!$tables) {
            return;
        }

        foreach ($tables as $table) {
            $this->seed($table);
        }
    }

    /**
     * @throws Exception
     */
    private function seed(string $tableName): void
    {
        $tableInfo = $this->databaseService->getTableInfo($tableName);

        Console::output('Seeding ' . $tableName . ' table...');

        $this->seedService->seed($tableInfo);

        Console::output('Seeded ' . $tableName . ' table');
    }

}
