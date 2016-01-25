<?php
/**
 * Date: 10/29/2015
 * Time: 10:23 AM
 */

namespace Bolt\Extension\cdowdy\boltresponsiveimages;

if (isset($app)) {
	$app['extensions']->register(new Extension($app));
}
