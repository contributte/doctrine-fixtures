<?php

/**
 * Test: DI\FixturesExtension
 */

use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Fixtures\Command\LoadDataFixturesCommand;
use Nettrine\Fixtures\DI\FixturesExtension;
use Nettrine\Fixtures\Loader\FixturesLoader;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

test(function () {
	//TODO mock Doctrine\Common\Persistence\ManagerRegistry
	$loader = new ContainerLoader(TEMP_DIR, TRUE);
	$class = $loader->load(function (Compiler $compiler) {
		//Fixtures
		$compiler->addExtension('fixtures', new FixturesExtension());
	}, '1a');

	/** @var Container $container */
	$container = new $class;

	/** @var FixturesLoader $loader */
	$loader = $container->getByType(FixturesLoader::class);
	Assert::type(FixturesLoader::class, $loader);

	/** @var LoadDataFixturesCommand $command */
	$command = $container->getByType(LoadDataFixturesCommand::class);
	Assert::type(LoadDataFixturesCommand::class, $command);
});
