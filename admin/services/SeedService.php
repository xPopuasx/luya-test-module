<?php

namespace sitis\tests\admin\services;

use DateTime;
use Faker\Factory;
use Yii;
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
    public function seed(TableSchema $tableSchema, int $count = 10): void
    {

        $fakeData = $foreignIdsInserts = [];

        if(!empty($tableSchema->foreignKeys)){
            foreach ($tableSchema->foreignKeys as $foreignKey){
                $findIds = $this->getForeignIds($foreignKey[0], array_values($foreignKey)[1]);
                $foreignIdsInserts[array_keys($foreignKey)[1]] =
                    array_values(
                        array_diff($findIds,
                            $this->unique($tableSchema->name, array_keys($foreignKey)[1])
                        )
                    );

            }
        }

        while ($count-- > 0) {
            /** @var ColumnSchema $column */
            foreach ($tableSchema->columns as $column) {
                if ($column->autoIncrement) {
                    continue;
                }

                $fakeData[$count][$column->name] =
                    $this->getFakeByType(
                        $column,
                        array_diff(
                            $foreignIdsInserts[$column->name] ?? [],
                            array_column($fakeData, $column->name)
                        ),
                        array_keys($foreignIdsInserts)
                    );
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
        return (new Query())->select($column)->from($tableName)->orderBy([$column => SORT_DESC])->column();
    }

    public function unique(string $tableName, string $column): array
    {
        return (new Query())->select($column)->from($tableName)->column();
    }

    /**
     * @throws \yii\base\Exception
     */
    private function getFakeByType(ColumnSchema $columnSchema, array $foreignIds = [], array $foreignKeys = []): float|DateTime|string|null
    {
        $instance = $this->faker->create();

        if (method_exists($instance, $columnSchema->name)) {
            return $instance->{$columnSchema->name}();
        }

        if (in_array($columnSchema->name , ['password', 'password_hash'])) {
            return Yii::$app->security->generatePasswordHash('password');
        }

        if ($columnSchema->name == 'phone') {
            return $instance->phoneNumber();
        }
        if (in_array($columnSchema->name , ['email', 'mail'])) {
            return $instance->email();
        }

        if (in_array($columnSchema->name, $foreignKeys)) {
            if(count($foreignIds) == 0){
                return null;
            }

            return $foreignIds[array_rand($foreignIds)];
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