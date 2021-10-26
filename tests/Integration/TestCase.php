<?php

namespace leinonen\Yii2Monolog\Tests\Integration;

use yii\di\Container;
use yii\console\Application;
use yii\helpers\ArrayHelper;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     *
     * @param array $config The application configuration, if needed
     *
     * @return Application
     */
    protected function mockApplication($config = [])
    {
        return new Application(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
        ], $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        if (\Yii::$app && \Yii::$app->has('session', true)) {
            \Yii::$app->session->close();
        }
        \Yii::$app = null;
        \Yii::$container = new Container();
    }
}
