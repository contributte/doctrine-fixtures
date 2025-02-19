<?php declare(strict_types = 1);

namespace Nettrine\Fixtures\DI;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nettrine\Fixtures\Command\LoadDataFixturesCommand;
use Nettrine\Fixtures\Loader\FixturesLoader;
use stdClass;

/**
 * @property-read stdClass $config
 */
class FixturesExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'paths' => Expect::listOf('string'),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$builder->addDefinition($this->prefix('fixturesLoader'))
			->setFactory(FixturesLoader::class, [$config->paths]);

		$builder->addDefinition($this->prefix('loadDataFixturesCommand'))
			->setFactory(LoadDataFixturesCommand::class)
			->addTag('console.command', 'doctrine:fixtures:load');
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		$fixtures = $builder->findByType(FixtureInterface::class);

		/** @var ServiceDefinition $fixtureLoader */
		$fixtureLoader = $builder->getDefinitionByType(FixturesLoader::class);

		foreach ($fixtures as $fixture) {
			$fixtureLoader->addSetup('addFixture', [$fixture]);
		}
	}

}
