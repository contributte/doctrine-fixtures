<?php declare(strict_types = 1);

namespace Tests\Nettrine\Fixtures\Cases\Fixture;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nette\DI\Container;
use Nettrine\Fixtures\ContainerAwareInterface;

class ContainerFixture implements ContainerAwareInterface, FixtureInterface
{

	/** @var Container|NULL */
	private $container;

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
	}

}
