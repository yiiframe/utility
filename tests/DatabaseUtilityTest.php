<?php declare(strict_types=1);

namespace Casoa\Yii\Tests;

use Yii;
use Casoa\Yii\DatabaseUtility;
use yii\db\mysql\ColumnSchema;

class DatabaseUtilityTest extends TestCase
{
    private $_tableName = CC_APPCODE . '_database_utility_test_table';
    private $_tableNameWithoutPrefix = 'database_utility_test_table';

    /**
     * @throws \yii\db\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();

        $sqls = [
            "DROP TABLE IF EXISTS `{$this->_tableName}`;",

            <<<SQL
CREATE TABLE `{$this->_tableName}` (
   `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
   `pid` int(11) NULL COMMENT 'Pid',
   `title` varchar(191) NOT NULL COMMENT 'Title',
   `tinyint1` tinyint(1) NULL COMMENT 'Tinyint1',
   `tinyint2` tinyint(2) NULL COMMENT 'Tinyint2',
   `tinyint3` tinyint(3) NULL COMMENT 'Tinyint3',
   `smallint4` smallint(4) NULL COMMENT 'Smallint4',
   `smallint5` smallint(5) NULL COMMENT 'Smallint5',
   `int9` int(9) NULL COMMENT 'Int9',
   `int10` int(10) NULL COMMENT 'Int10',
   `bigint15` bigint(15) NULL COMMENT 'Bigint15'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci COMMENT='Database Utility Test Table';
SQL,
            "CREATE INDEX idx_pid ON `{$this->_tableName}` (`pid`);"
        ];

       foreach ($sqls as $sql) {
           Yii::$app->db->createCommand($sql)->execute();
       }
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function tearDown(): void
    {
        $sql = <<<SQL
DROP TABLE IF EXISTS `{$this->_tableName}`;
SQL;
        Yii::$app->db->createCommand($sql)->execute();

        parent::tearDown();
    }

    /**
     * @throws \yii\base\NotSupportedException
     */
    public function testIsTableExist(): void
    {
        $this->assertTrue(DatabaseUtility::isTableExist($this->_tableNameWithoutPrefix));
        $this->assertFalse(DatabaseUtility::isTableExist($this->_tableNameWithoutPrefix . 'sajdh'));
    }

    /**
     * @throws \yii\base\NotSupportedException
     */
    public function testIsFieldExist(): void
    {
        $this->assertTrue(DatabaseUtility::isFieldExist($this->_tableNameWithoutPrefix, 'title'));
        $this->assertFalse(DatabaseUtility::isFieldExist($this->_tableNameWithoutPrefix, 'titleasdas'));
    }

    /**
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\Exception
     */
    public function testGetTableStatus(): void
    {
        $status = DatabaseUtility::getTableStatus();

        $pos = array_search($this->_tableName, array_column($status, 'Name'), true);
        $this->assertNotFalse($pos);

        $this->assertEquals('Database Utility Test Table', $status[$pos]['Comment']);

        $status = DatabaseUtility::getTableStatus($this->_tableNameWithoutPrefix);
        $this->assertEquals('Database Utility Test Table', $status['Comment']);

        $status = DatabaseUtility::getTableStatus($this->_tableNameWithoutPrefix . 'asdsad');
        $this->assertEquals([], $status);

        $status = DatabaseUtility::getTableStatus('auth_assignment');
        $this->assertEquals([], $status);
    }

    /**
     * @throws \yii\db\Exception
     */
    public function testGetTableIndexes(): void
    {
        $status = DatabaseUtility::getTableIndexes($this->_tableNameWithoutPrefix);

        $pos = array_search('pid', array_column($status, 'Column_name'), true);
        $this->assertNotFalse($pos);

        $this->assertEquals($this->_tableName, $status[$pos]['Table']);
        $this->assertEquals('idx_pid', $status[$pos]['Key_name']);
        $this->assertEquals('1', $status[$pos]['Non_unique']);
    }

    public function testCastDataType(): void
    {
        $this->assertEquals('int', DatabaseUtility::castDataType('int(11)'));
        $this->assertEquals('string', DatabaseUtility::castDataType('string(254)'));
        $this->assertEquals('date', DatabaseUtility::castDataType('date'));
    }

    public function testGetColumnMaxValue(): void
    {
        $columns = Yii::$app->db->schema->getTableSchema($this->_tableName, true)->columns;
        foreach ($columns as $column) {
            if ($column->name === 'tinyint1') {
                $this->assertEquals(9, DatabaseUtility::getColumnMaxValue($column));
            }
            if ($column->name === 'tinyint2') {
                $this->assertEquals(99, DatabaseUtility::getColumnMaxValue($column));
            }
            if ($column->name === 'tinyint3') {
                $this->assertEquals(127, DatabaseUtility::getColumnMaxValue($column));
            }
            if ($column->name === 'smallint4') {
                $this->assertEquals(9999, DatabaseUtility::getColumnMaxValue($column));
            }
            if ($column->name === 'smallint5') {
                $this->assertEquals(32767, DatabaseUtility::getColumnMaxValue($column));
            }
            if ($column->name === 'int9') {
                $this->assertEquals(999999999, DatabaseUtility::getColumnMaxValue($column));
            }
            if ($column->name === 'int10') {
                $this->assertEquals(2147483647, DatabaseUtility::getColumnMaxValue($column));
            }
            if ($column->name === 'bigint15') {
                $this->assertEquals(999999999999999, DatabaseUtility::getColumnMaxValue($column));
            }
            if ($column->name === 'pid') {
                $this->assertEquals(2147483647, DatabaseUtility::getColumnMaxValue($column));
            }
        }
    }
}
