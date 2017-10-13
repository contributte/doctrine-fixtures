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

	/** @var LoadDataFixturesCommand $command */
	$command = $container->getByType(LoadDataFixturesCommand::class);
	Assert::type(LoadDataFixturesCommand::class, $command);
});
