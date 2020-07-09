<?php
/**
 * Fractionalize plugin for Craft CMS 3.x
 *
 * Provides a twig filter to display decimal values as fractions.
 *
 * @link      https://github.com/extensibleseth/
 * @copyright Copyright (c) 2020 Seth Hendrick
 */

namespace ponies\fractionalizetests\unit;

use Codeception\Test\Unit;
use UnitTester;
use Craft;
use ponies\fractionalize\Fractionalize;

/**
 * ExampleUnitTest
 *
 *
 * @author    Seth Hendrick
 * @package   Fractionalize
 * @since     0.1.0
 */
class ExampleUnitTest extends Unit
{
    // Properties
    // =========================================================================

    /**
     * @var UnitTester
     */
    protected $tester;

    // Public methods
    // =========================================================================

    // Tests
    // =========================================================================

    /**
     *
     */
    public function testPluginInstance()
    {
        $this->assertInstanceOf(
            Fractionalize::class,
            Fractionalize::$plugin
        );
    }

    /**
     *
     */
    public function testCraftEdition()
    {
        Craft::$app->setEdition(Craft::Pro);

        $this->assertSame(
            Craft::Pro,
            Craft::$app->getEdition()
        );
    }
}
