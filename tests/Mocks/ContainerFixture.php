<?php declare(strict_types = 1);

namespace Tests\Mocks;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nette\DI\Container;
use Nettrine\Fixtures\Fixture\ContainerAwareInterface;

class ContainerFixture implements ContainerAwareInterface, FixtureInterface
{

	private Container|null $container = null;

	public function setContainer(Container $container): void
	{
		$this->container = $container;
	}

	public function getContainer(): ?Container
	{
		return $this->container;
	}

	public function load(ObjectManager $manager): void
	{
		// Implement in child
	}

}
