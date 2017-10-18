<?php

/**
 * Test: DI\FixturesExtension
 */

use Doctrine\Common\Persistence\ManagerRegistry;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Fixtures\Command\LoadDataFixturesCommand;
use Nettrine\Fixtures\DI\FixturesExtension;
use Nettrine\Fixtures\Loader\FixturesLoader;
use Tester\Assert;
use Tests\Fixture\ContainerFixture;

require_once __DIR__ . '/../../bootstrap.php';

test(function () {
	$managerRegistry = Mockery::mock(ManagerRegistry::class);

	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) use ($managerRegistry) {
		//Fixtures
		$compiler->getContainerBuilder()
			->addDefinition('managerRegistry')
			->setClass(ManagerRegistry::class)
			->setDynamic(TRUE);
		$compiler->addExtension('fixtures', new FixturesExtension());
	}, '1a');

	/** @var Container $container */
	$container = new $class;
	$container->addService('managerRegistry', $managerRegistry);

	/** @var FixturesLoader $loader */
	$loader = $container->getByType(FixturesLoader::class);
	Assert::type(FixturesLoader::class, $loader);

	$loader->loadPaths([__DIR__ . '/../Fixture']);
	/** @var ContainerFixture $containerFixture */
	$containerFixture = $loader->getFixture(ContainerFixture::class);
	Assert::type(ContainerFixture::class, $containerFixture);
	Assert::type(Container::class, $containerFixture->getContainer());

	/** @var LoadDataFixturesCommand $command */
	$command = $container->getByType(LoadDataFixturesCommand::class);
	Assert::type(LoadDataFixturesCommand::class, $command);
});
