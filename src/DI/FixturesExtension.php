<?php declare(strict_types = 1);

namespace Nettrine\Fixtures\DI;

use Nette\DI\CompilerExtension;
use Nettrine\Fixtures\Command\LoadDataFixturesCommand;
use Nettrine\Fixtures\Loader\FixturesLoader;

class FixturesExtension extends CompilerExtension
{

	/** @var mixed[] */
	private $defaults = [
		'paths' => [],
	];

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$builder->addDefinition($this->prefix('fixturesLoader'))
			->setClass(FixturesLoader::class, [$config['paths']]);

		$builder->addDefinition($this->prefix('loadDataFixturesCommand'))
			->setClass(LoadDataFixturesCommand::class)
			->setInject(false);
	}

}
