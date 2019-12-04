<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Tests\Sample;

use NS8\ProtectSDK\Sample\Demo;
use PHPUnit\Framework\TestCase;

/**
 * A demo test class for illustrative purposes.
 *
 * Feel free to delete this once there's some actual code worth testing.
 *
 * @coversDefaultClass NS8\ProtectSDK\Sample\Demo
 */
class DemoTest extends TestCase
{
    /**
     * Test the constructor.
     *
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor() : void
    {
        $this->assertInstanceOf(Demo::class, new Demo(3));
    }

    /**
     * Test the isGreaterThanTwo() method.
     *
     * @return void
     *
     * @covers ::__construct
     * @covers ::isGreaterThanTwo
     */
    public function testIsGreaterThanTwo() : void
    {
        $demo = new Demo(1);
        $this->assertFalse($demo->isGreaterThanTwo());

        $demo = new Demo(2);
        $this->assertFalse($demo->isGreaterThanTwo());

        $demo = new Demo(3);
        $this->assertTrue($demo->isGreaterThanTwo());
    }
}
