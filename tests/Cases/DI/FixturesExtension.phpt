<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Mockery;
use Mockery\MockInterface;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\Definitions\Statement;
use Nettrine\Fixtures\Command\LoadDataFixturesCommand;
use Nettrine\Fixtures\DI\FixturesExtension;
use Nettrine\Fixtures\Exceptions\RuntimeException;
use Nettrine\Fixtures\Loader\FixturesLoader;
use Symfony\Component\Console\Tester\CommandTester;
use Tester\Assert;
use Tests\Mocks\ContainerFixture;
use Tests\Toolkit\Tests;

require_once __DIR__ . '/../../bootstrap.php';

// Minimal configuration
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

// Load by paths
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

// Load by paths
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
	Assert::count(0, $loader->getFixtures());

	$em = Mockery::mock(EntityManagerInterface::class);
	$em->shouldReceive('getEventManager')->andThrows(new RuntimeException('Not implemented', 999));

	/** @var MockInterface $managerRegistry */
	$managerRegistry = $container->getByType(ManagerRegistry::class);
	$managerRegistry->shouldReceive('getManager')->andReturn($em);

	/** @var LoadDataFixturesCommand $loadDataFixtureCommand */
	$loadDataFixtureCommand = $container->getByType(LoadDataFixturesCommand::class);

	try {
		$commandTester = new CommandTester($loadDataFixtureCommand);
		$commandTester->execute([
			'--fixtures' => [Tests::FIXTURES_PATH],
		]);
	} catch (RuntimeException $e) {
		Assert::equal(999, $e->getCode());
		Assert::count(1, $loader->getFixtures());
	}
});
