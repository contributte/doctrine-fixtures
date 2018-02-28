<?php declare(strict_types = 1);

namespace Nettrine\Fixtures;

use Nette\DI\Container;

interface ContainerAwareInterface
{

	/**
	 * @param Container $container
	 * @return void
	 */
	public function setContainer(Container $container): void;

}
