<?php declare(strict_types=1);

namespace Casoa\Yii\Utility;

use Yii;
use yii\db\ColumnSchema;
use yii\db\Exception;
use yii\db\mysql\Schema;
use yii\helpers\StringHelper;

class DatabaseUtility extends Utility
{
    private static function quoteTableName(string $tableName): string
    {
        if (!StringHelper::startsWith($tableName, '{{%')) {
            $tableName = "{{%$tableName}}";
        }

        return $tableName;
    }

    /**
     * 获取表是否存在
     * @param string $tableName
     * @return bool
     * @throws \yii\base\NotSupportedException
     */
    public static function isTableExist(string $tableName): bool
    {
        $db = Yii::$app->getDb();

        $rawTableName = $db->getSchema()->getRawTableName(self::quoteTableName($tableName));

        return in_array($rawTableName, $db->getSchema()->getTableNames(), true);
    }

    /**
     * 获取表的字段是否存在
     * @param string $tableName
     * @param string $fieldName
     * @return bool
     * @throws \yii\base\NotSupportedException
     */
    public static function isFieldExist(string $tableName, string $fieldName): bool
    {
        $db = Yii::$app->getDb();

        $rawTableName = $db->getSchema()->getRawTableName(self::quoteTableName($tableName));

        $sql = "SHOW COLUMNS FROM $rawTableName LIKE '$fieldName'";

        try {
            $queryAll = $db->createCommand($sql)->queryAll();
        } catch (Exception $exception) {
            return false;
        }

        return count($queryAll) > 0;
    }

    /**
     * 获取表状态（表结构或字段）.
     * @param string|null $tableName 表名称
     * @return array 如果$tableName为null则返回值是一个多维数组，数组可能为空；否则，则返回值是一个一维数组
     * @throws \yii\db\Exception
     * @throws \yii\base\NotSupportedException
     */
    public static function getTableStatus(string $tableName = null): array
    {
        $db = Yii::$app->getDb();

        $sql = 'SHOW TABLE STATUS';

        if ($tableName !== null) {
            $rawTableName = $db->getSchema()->getRawTableName(self::quoteTableName($tableName));

            $sql .= " WHERE Name = '$rawTableName'";
        } else {
            $sql .= ' WHERE Name NOT IN ("' . implode(
                    '","',
                    array_map(static function (string $name): string {
                        return Yii::$app->getDb()->getSchema()->getRawTableName(self::quoteTableName($name));
                    }, [
                        'auth_assignment',
                        'auth_item',
                        'auth_item_child',
                        'auth_rule',
                        'message',
                        'source_message',
                        'migration',
                        'queue',
                        'errlog',
                    ])
                ) . '")';
        }

        $status = $db->createCommand($sql)->queryAll();

        return !empty($status) && $tableName !== null ? $status[0] : $status;
    }

    /**
     * 获取表（唯一）索引.
     * @param string $tableName 表名称
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getTableIndexes(string $tableName): array
    {
        $db = Yii::$app->db;

        $sql = 'SHOW INDEX FROM ' . $db->schema->getRawTableName(self::quoteTableName($tableName));

        return $db->createCommand($sql)->queryAll();
    }

    /**
     * 简单类型推断.
     * @param string $dbType
     * @return string
     */
    public static function castDataType(string $dbType): string
    {
        if (strpos($dbType, '(') === false) {
            return $dbType;
        }

        return explode('(', $dbType)[0];
    }

    /**
     * 获取列的最大值（只适用于数字类型）
     * @param \yii\db\ColumnSchema $column
     * @return int
     */
    public static function getColumnMaxValue(ColumnSchema $column): ?int
    {
        $dbType = self::castDataType($column->dbType);
        switch ($dbType) {
            case Schema::TYPE_TINYINT:
                $max = $column->size >= 3 ? 127 : str_repeat('9', $column->size);
                break;
            case Schema::TYPE_SMALLINT:
                $max = $column->size >= 5 ? 32767 : str_repeat('9', $column->size);
                break;
            case 'mediumint':
                $max = $column->size >= 7 ? 8388607 : str_repeat('9', $column->size);
                break;
            case 'int':
            case Schema::TYPE_INTEGER:
                $max = $column->size >= 10 ? 2147483647 : str_repeat('9', $column->size);
                break;
            case Schema::TYPE_BIGINT:
                $max = $column->size >= 19 ? 9223372036854775807 : str_repeat('9', $column->size);
                break;
            default:
                $max = null;
                break;
        }

        return (int)$max;
    }
}
