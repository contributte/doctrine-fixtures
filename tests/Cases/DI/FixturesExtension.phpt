<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mockery;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\Definitions\Statement;
use Nettrine\Fixtures\Command\LoadDataFixturesCommand;
use Nettrine\Fixtures\DI\FixturesExtension;
use Nettrine\Fixtures\Loader\FixturesLoader;
use Tester\Assert;
use Tests\Mocks\ContainerFixture;
use Tests\Toolkit\Tests;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->getContainerBuilder()
				->addDefinition('managerRegistry')
				->setType(ManagerRegistry::class)
				->setFactory(new Statement(Mockery::class . '::mock', [ManagerRegistry::class]));

			$compiler->addExtension('fixtures', new FixturesExtension());
			$compiler->addConfig([
				'parameters' => [
					'fixturesPath' => Tests::FIXTURES_PATH,
				],
				'fixtures' => [
					'paths' => [
						'%fixturesPath%',
					],
				],
			]);
		})
		->build();

	/** @var FixturesLoader $loader */
	$loader = $container->getByType(FixturesLoader::class);

	// Load fixtures
	Assert::count(0, $loader->getFixtures());
	$loader->load();
	Assert::count(1, $loader->getFixtures());

	/** @var ContainerFixture $containerFixture */
	$containerFixture = $loader->getFixture(ContainerFixture::class);
	Assert::type(Container::class, $containerFixture->getContainer());

	/** @var LoadDataFixturesCommand $command */
	$command = $container->getByType(LoadDataFixturesCommand::class);
	Assert::type(LoadDataFixturesCommand::class, $command);
});

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->getContainerBuilder()
				->addDefinition('managerRegistry')
				->setType(ManagerRegistry::class)
				->setFactory(new Statement(Mockery::class . '::mock', [ManagerRegistry::class]));

			$compiler->addExtension('fixtures', new FixturesExtension());
		})->build();

	/** @var FixturesLoader $loader */
	$loader = $container->getByType(FixturesLoader::class);

	// Load fixtures manually with given paths
	Assert::count(0, $loader->getFixtures());
	$loader->loadPaths([Tests::FIXTURES_PATH]);
	Assert::count(1, $loader->getFixtures());

	/** @var ContainerFixture $containerFixture */
	$containerFixture = $loader->getFixture(ContainerFixture::class);
	Assert::type(Container::class, $containerFixture->getContainer());
});
