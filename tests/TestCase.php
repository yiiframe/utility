<?php declare(strict_types=1);

namespace Casoa\Yii\Tests;

use ReflectionClass;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Application as ConsoleApplication;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->destroyApplication();

        parent::tearDown();
    }

    protected function mockApplication(): void
    {
        $config = require __DIR__ . '/config/test.php';

        try {
            new ConsoleApplication($config);
        } catch (InvalidConfigException $e) {
            echo $e->getMessage() . "\n";
            exit;
        }
    }

    protected function destroyApplication(): void
    {
        Yii::$app = null;
    }

    /**
     * Invokes object method, even if it is private or protected.
     * @param object $object object.
     * @param string $method method name.
     * @param array $args method arguments
     * @return mixed method result
     * @throws \ReflectionException
     */
    protected function invoke($object, string $method, array $args = [])
    {
        $classReflection = new ReflectionClass(get_class($object));
        $methodReflection = $classReflection->getMethod($method);
        $methodReflection->setAccessible(true);
        $result = $methodReflection->invokeArgs($object, $args);
        $methodReflection->setAccessible(false);
        return $result;
    }
}
