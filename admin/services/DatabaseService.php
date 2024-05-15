<?php

namespace sitis\tests\admin\services;

use Yii;
use yii\base\NotSupportedException;
use yii\db\TableSchema;
use \yii\helpers\Console;

class DatabaseService
{
    public array $tables = [];

    public function __construct(private readonly Yii $yii)
    {
    }

    /**
     * @throws NotSupportedException
     */
    public function getTables(): ?array
    {
        $output = [];

        $db = $this->yii::$app->db;

        $tables = $db->getSchema()->getTableNames();

        if (!empty($tables)) {
            foreach ($tables as $tableName) {
                $output[] = $tableName;
            }
        } else {
            Console::error('В базе данных нет таблиц.');

            return null;
        }

        return $output;
    }

    public function makeSortTablesByForeignKey(array $tables, $i = 0): void
    {
        foreach ($tables as $key => $table) {
            $tableInfo = $this->getTableInfo($table);

            if (count($tableInfo->foreignKeys) == 0) {
                $this->tables[] = $tableInfo->name;
                unset($tables[$key]);
            } elseif ($i != 0 && count($tableInfo->foreignKeys) > 0) {
                $findTables = array_diff(array_column($tableInfo->foreignKeys, 0), [$tableInfo->name]);
                if (empty(array_diff($findTables, $this->tables))) {
                    $this->tables[] = $tableInfo->name;
                    unset($tables[$key]);
                }
            }
        }

        $tablesHasForeign = array_values($tables);

        if (!empty($tablesHasForeign)) {
            $this->makeSortTablesByForeignKey($tablesHasForeign, ++$i);
        }
    }

    public function getTableInfo(string $tableName): ?TableSchema
    {
        return $this->yii::$app->db->getTableSchema($tableName);
    }
}