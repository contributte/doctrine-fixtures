<?php

namespace Nettrine\Fixtures;

use Nette\DI\Container;

interface ContainerAwareInterface
{

	/**
	 * @param Container $container
	 * @return void
	 */
	public function setContainer(Container $container);

}
