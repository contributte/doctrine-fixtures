<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mockery;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nettrine\Fixtures\Command\LoadDataFixturesCommand;
use Nettrine\Fixtures\DI\FixturesExtension;
use Nettrine\Fixtures\Loader\FixturesLoader;
use Tester\Assert;
use Tests\Fixtures\ContainerFixture;

require_once __DIR__ . '/../../../bootstrap.php';

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			// Manager registry is needed for console command
			// It's also provided by nettrine/orm package
			$compiler->getContainerBuilder()
				->addImportedDefinition('managerRegistry')
				->setType(ManagerRegistry::class);

			$compiler->addExtension('fixtures', new FixturesExtension());
			$compiler->addConfig([
				'parameters' => [
					'rootPath' => realpath(__DIR__ . '/../../../../'),
				],
				'fixtures' => [
					'paths' => [
						'%rootPath%/tests/Fixtures',
					],
				],
			]);
		})
		->build();

	$container->addService('managerRegistry', Mockery::mock(ManagerRegistry::class));

	/** @var FixturesLoader $loader */
	$loader = $container->getByType(FixturesLoader::class);
	Assert::type(FixturesLoader::class, $loader);

	// Load fixtures
	$loader->load();

	/** @var ContainerFixture $containerFixture */
	$containerFixture = $loader->getFixture(ContainerFixture::class);
	Assert::type(ContainerFixture::class, $containerFixture);
	Assert::type(Container::class, $containerFixture->getContainer());

	/** @var LoadDataFixturesCommand $command */
	$command = $container->getByType(LoadDataFixturesCommand::class);
	Assert::type(LoadDataFixturesCommand::class, $command);
});

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			// Manager registry is needed for console command
			// It's also provided by nettrine/orm package
			$compiler->getContainerBuilder()
				->addImportedDefinition('managerRegistry')
				->setType(ManagerRegistry::class);

			$compiler->addExtension('fixtures', new FixturesExtension());
		})->build();

	$container->addService('managerRegistry', Mockery::mock(ManagerRegistry::class));

	/** @var FixturesLoader $loader */
	$loader = $container->getByType(FixturesLoader::class);

	// Load fixtures manually with given paths
	$loader->loadPaths([__DIR__ . '/../../../Fixtures']);

	/** @var ContainerFixture $containerFixture */
	$containerFixture = $loader->getFixture(ContainerFixture::class);
	Assert::type(ContainerFixture::class, $containerFixture);
	Assert::type(Container::class, $containerFixture->getContainer());
});
