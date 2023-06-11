<?php declare(strict_types = 1);

namespace Nettrine\Fixtures\Loader;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Nette\DI\Container;
use Nettrine\Fixtures\ContainerAwareInterface;

class FixturesLoader extends Loader
{

	private Container $container;

	/** @var string[] */
	private array $paths = [];

	/**
	 * @param string[] $paths
	 */
	public function __construct(array $paths, Container $container)
	{
		$this->paths = $paths;
		$this->container = $container;
	}

	/**
	 * @param string[] $paths
	 */
	public function loadPaths(array $paths): void
	{
		foreach ($paths as $path) {
			if (is_dir($path)) {
				$this->loadFromDirectory($path);
			} elseif (is_file($path)) {
				$this->loadFromFile($path);
			}
		}
	}

	public function load(): void
	{
		$this->loadPaths($this->paths);
	}

	public function addFixture(FixtureInterface $fixture): void
	{
		if ($fixture instanceof ContainerAwareInterface) {
			$fixture->setContainer($this->container);
		}

		parent::addFixture($fixture);
	}

	/**
	 * @return string[]
	 */
	public function getPaths(): array
	{
		return $this->paths;
	}

}
