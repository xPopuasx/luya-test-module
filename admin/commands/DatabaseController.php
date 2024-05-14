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

    public array $foreignIds = [];

    public array $hasForeignKeyTables = [];

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

        if(count($tableInfo->foreignKeys) > 0) {
            foreach ($tableInfo->foreignKeys as $foreignKey) {
                $tableName = $foreignKey[0];

                if(in_array($tableName, $this->hasForeignKeyTables)){
                    continue;
                }

                $this->hasForeignKeyTables[] = $tableName;
                $this->foreignIds[$tableName][array_keys($foreignKey)[1]] = $this->seedService->getForeignIds($tableName, $foreignKey[array_keys($foreignKey)[1]]);
                $this->seed($tableName);
            }
        } else {
            $this->seedService->seed($tableInfo, $this->foreignIds);
        }

        Console::output('Seeded ' . $tableName . ' table');
    }


}
