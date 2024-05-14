<?php

namespace sitis\tests\admin\services;

use Yii;
use yii\base\NotSupportedException;
use yii\db\TableSchema;
use \yii\helpers\Console;

class DatabaseService
{
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

    public function getTableInfo(string $tableName): ?TableSchema
    {
        return $this->yii::$app->db->getTableSchema($tableName);
    }
}