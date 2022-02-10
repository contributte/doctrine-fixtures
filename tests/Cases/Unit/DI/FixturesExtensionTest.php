<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Doctrine\Persistence\ManagerRegistry;
use Mockery;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Fixtures\Command\LoadDataFixturesCommand;
use Nettrine\Fixtures\DI\FixturesExtension;
use Nettrine\Fixtures\Loader\FixturesLoader;
use Tester\Assert;
use Tester\TestCase;
use Tests\Fixtures\ContainerFixture;

require_once __DIR__ . '/../../../bootstrap.php';

final class FixturesExtensionTest extends TestCase
{

	public function testLoad(): void
	{
		$loader = new ContainerLoader(TMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
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
		}, __METHOD__ . '1');

		/** @var Container $container */
		$container = new $class();
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
	}

	public function testLoadPaths(): void
	{
		$loader = new ContainerLoader(TMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			// Manager registry is needed for console command
			// It's also provided by nettrine/orm package
			$compiler->getContainerBuilder()
				->addImportedDefinition('managerRegistry')
				->setType(ManagerRegistry::class);

			$compiler->addExtension('fixtures', new FixturesExtension());
		}, __FILE__ . '2');

		/** @var Container $container */
		$container = new $class();
		$container->addService('managerRegistry', Mockery::mock(ManagerRegistry::class));

		/** @var FixturesLoader $loader */
		$loader = $container->getByType(FixturesLoader::class);

		// Load fixtures manually with given paths
		$loader->loadPaths([__DIR__ . '/../../../Fixtures']);

		/** @var ContainerFixture $containerFixture */
		$containerFixture = $loader->getFixture(ContainerFixture::class);
		Assert::type(ContainerFixture::class, $containerFixture);
		Assert::type(Container::class, $containerFixture->getContainer());
	}

}

(new FixturesExtensionTest())->run();
