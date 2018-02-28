<?php declare(strict_types = 1);

namespace Tests\Nettrine\Fixtures\Fixture;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nette\DI\Container;
use Nettrine\Fixtures\ContainerAwareInterface;

class ContainerFixture implements ContainerAwareInterface, FixtureInterface
{

	/** @var Container|NULL */
	private $container;

	/**
	 * @param Container $container
	 * @return void
	 */
	public function setContainer(Container $container): void
	{
		$this->container = $container;
	}

	/**
	 * @return Container|NULL
	 */
	public function getContainer(): ?Container
	{
		return $this->container;
	}

	/**
	 * @param ObjectManager $manager
	 * @return void
	 */
	public function load(ObjectManager $manager): void
	{
	}

}
