<?php declare(strict_types = 1);

namespace Nettrine\Fixtures\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Nette\DI\Container;

abstract class AbstractContainerAwareFixture extends AbstractFixture implements ContainerAwareInterface
{

	protected Container|null $container = null;

	public function setContainer(Container $container): void
	{
		$this->container = $container;
	}

}
