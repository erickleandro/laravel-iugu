<?php

namespace Potelo\GuPayment\Tests;

use Potelo\GuPayment\GuPaymentServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[WithFaker::class])) {
            $this->setUpFaker();
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            GuPaymentServiceProvider::class,
        ];
    }
}
