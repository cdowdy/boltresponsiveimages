<?php

namespace Bolt\Extension\cdowdy\boltresponsiveimages\Tests;

use Bolt\Tests\BoltUnitTest;
use Bolt\Extension\cdowdy\boltresponsiveimages\BoltResponsiveImagesExtension;

/**
 * Ensure that the ExtensionName extension loads correctly.
 *
 */
class ExtensionTest extends BoltUnitTest
{
    public function testExtensionRegister()
    {
        $app = $this->getApp();
        $extension = new Extension($app);
        $app['extensions']->register( $extension );
        $name = $extension->getName();
        $this->assertSame($name, 'ExtensionName');
        $this->assertSame($extension, $app["extensions.$name"]);
    }
}
