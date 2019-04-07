# Nettrine Fixtures

[Doctrine\DataFixtures](https://github.com/doctrine/data-fixtures) for Nette Framework

## Content

- [Setup](#usage)
- [Configuration](#configuration)
- [Usage](#command)
- [Fixture - create own fixtures](#fixture)

## Setup

Install package

```bash
composer require nettrine/fixtures
```

Register extension

```yaml
extensions:
    fixtures: Nettrine\Fixtures\DI\FixturesExtension
```

This extension is highly depending on `Symfony\Console`, it does not make sence to use it without `Console`. Take
a look at simple [Contributte/Console](https://github.com/contributte/console) integration.

```
composer require contributte/console
```

```yaml
extensions:
    console: Contributte\Console\DI\ConsoleExtension
```

## Configuration

You should define paths where the fixture classes are stored.

```yaml
fixtures:
    paths:
        - app/model/Fixtures
        - ...
```

## Usage

The **doctrine:fixtures:load** command loads data fixtures from your configuration by default:

```
doctrine:fixtures:load
```

You can also optionally specify the path to the fixtures with the **--fixtures** option:

```
doctrine:fixtures:load --fixtures=/path/to/fixtures1 --fixtures=/path/to/fixtures2
```

If you want to append the fixtures instead of flushing the database first you can use the **--append** option:

```
doctrine:fixtures:load --append
```

By default `Doctrine Fixtures` uses `DELETE` statements to drop the existing rows from
the database. If you want to use a `TRUNCATE` statement instead, you can use the **--purge-with-truncate** flag:

```
doctrine:fixtures:load --purge-with-truncate
```

## Fixture

Simpliest fixture implements just **Doctrine\Common\DataFixtures\FixtureInterface**

```php
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class Foo1Fixture implements FixtureInterface
{

	/**
	 * Load data fixtures with the passed ObjectManager
	 *
	 * @param ObjectManager $manager
	 * @return void
	 */
	public function load(ObjectManager $manager)
	{
		// TODO: Implement load() method.
	}

}
```

If you need use ordering, implement **Doctrine\Common\DataFixtures\OrderedFixtureInterface**


```php
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class Foo2Fixture implements FixtureInterface, OrderedFixtureInterface
{

	/**
	 * Load data fixtures with the passed ObjectManager
	 *
	 * @param ObjectManager $manager
	 * @return void
	 */
	public function load(ObjectManager $manager)
	{
		// TODO: Implement load() method.
	}

	/**
	 * Get the order of this fixture
	 *
	 * @return int
	 */
	public function getOrder()
	{
		return 1;
	}

}
```

If you need use referencing, extend **Doctrine\Common\DataFixtures\AbstractFixture**

```php
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class Foo3Fixture extends AbstractFixture
{

	/**
	 * Load data fixtures with the passed ObjectManager
	 *
	 * @param ObjectManager $manager
	 */
	public function load(ObjectManager $manager)
	{
		// TODO: Implement load() method.
		$this->addReference('user', new User());
		$this->getReference('user');
	}

}
```

If you need use container, implement **Nettrine\Fixtures\ContainerAwareInterface**


```php

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nette\DI\Container;

class Foo4Fixture implements FixtureInterface, ContainerAwareInterface
{

	/** @var Container */
	private $container;

	/**
	 * @param Container $container
	 * @return void
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Load data fixtures with the passed ObjectManager
	 *
	 * @param ObjectManager $manager
	 */
	public function load(ObjectManager $manager)
	{
		// TODO: Implement load() method.
		$this->container->getService('foo');
	}

}
```
