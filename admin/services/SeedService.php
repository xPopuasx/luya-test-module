<?php

namespace sitis\tests\admin\services;

use DateTime;
use Faker\Factory;
use yii\db\Exception;
use yii\db\mysql\ColumnSchema;
use yii\db\Query;
use yii\db\TableSchema;
use YooKassa\Helpers\UUID;

class SeedService
{
    public function __construct(private readonly Factory $faker)
    {

    }


    /**
     * @throws Exception
     */
    public function seed(TableSchema $tableSchema, array $foreignIds = [], int $count = 10): void
    {
        $fakeData = $foreignIdsInserts = [];

        if ($foreignIds) {
            foreach ($tableSchema->foreignKeys as $foreignKey) {
                $foreignIdsInserts[array_keys($foreignIds[$foreignKey[0]])[0]] = array_values($foreignIds[$foreignKey[0]])[0];
            }
        }

        while ($count-- > 0) {
            /** @var ColumnSchema $column */
            foreach ($tableSchema->columns as $column) {
                if ($column->autoIncrement) {
                    continue;
                }

                $fakeData[$count][$column->name] = $this->getFakeByType($column, $foreignIdsInserts, array_merge(array_column($fakeData, $column->name), $this->getForeignIds($tableSchema->name, $column->name)));
            }
        }

        $columnNames = [];

        foreach ($tableSchema->columns as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            $columnNames[] = $column->name;
        }

        $fakeData = array_values($fakeData);

        \Yii::$app->db->createCommand()
            ->batchInsert($tableSchema->name, array_values($columnNames), array_map(fn($column) => array_values($column), $fakeData))
            ->execute();

    }

    public function getForeignIds(string $tableName, string $column): array
    {
        return (new Query())->select($column)->from($tableName)->column();
    }

    private function getFakeByType(ColumnSchema $columnSchema, array $foreignIds = [], array $existingValues = []): float|DateTime|string
    {
        $instance = $this->faker->create();

        if (method_exists($instance, $columnSchema->name)) {
            return $instance->{$columnSchema->name}();
        }

        if (in_array($columnSchema->name, array_keys($foreignIds))) {
            $id = array_rand(array_diff($foreignIds[$columnSchema->name], $existingValues));

            return $foreignIds[$columnSchema->name][$id];
        }

        return match ($columnSchema->type) {
            'string', 'char' => $instance->lexify(str_repeat('?', $columnSchema->size)),
            'text' => $instance->text(100),
            'integer', 'bigint' => $instance->numberBetween(),
            'tinyint' => $instance->numberBetween(0, 1),
            'smallint' => $instance->numberBetween(0, 32767),
            'mediumint' => $instance->numberBetween(0, 8388607),
            'boolean' => $instance->boolean(),
            'binary' => UUID::v4(),
            'float', 'double' => $instance->randomFloat(),
            'decimal' => $columnSchema->precision / $columnSchema->scale,
            'datetime', 'timestamp' => $instance->dateTime()->format('Y-m-d H:i:s'),
            'date' => $instance->dateTime()->format('Y-m-d'),
        };
    }


}