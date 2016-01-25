<?php
/**
 * Date: 1/25/2016
 * Time: 9:39 AM
 */

namespace Bolt\Extension\cdowdy\boltresponsiveimages\tests;

use Bolt\Tests\BoltUnitTest;
use Bolt\Extension\cdowdy\boltresponsiveimages\Extension;



class RespImgTest extends BoltUnitTest
{
	public function testExtensionLoads()
	{
		$app = $this->getApp();
		$extension = new Extension($app);
		$app['extensions']->register( $extension );
		$name = $extension->getName();
		$this->assertSame($name, 'boltresponsiveimages');
		$this->assertSame($extension, $app["extensions.$name"]);
	}

	public function testAddSnippet()
	{
		$app = $this->getApp();
		$extension = $this->getMockForAbstractClass('Bolt\BaseExtension', array($app));
		$handler = $this->getMock('Bolt\Extensions', array('insertSnippet'), array($app));

		$handler->expects($this->once())
			->method('insertSnippet');

		$app['extensions'] = $handler;

		$extension->addSnippet('test', array($this, 'testAddSnippet'));
	}
}