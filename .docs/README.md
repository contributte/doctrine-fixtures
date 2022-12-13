# Contributte Doctrine Fixtures

[Doctrine/DataFixtures](https://github.com/doctrine/data-fixtures) for Nette Framework


## Content

- [Setup](#usage)
- [Relying](#relying)
- [Configuration](#configuration)
- [Usage](#usage)
- [Examples](#examples)


## Setup

Install package

```bash
composer require nettrine/fixtures
```

Register extension

```yaml
extensions:
  nettrine.fixtures: Nettrine\Fixtures\DI\FixturesExtension
```


## Relying

Take advantage of enpowering this package with 2 extra packages:

- `doctrine/orm`
- `symfony/console`


### `doctrine/orm`

This package relies on `doctrine/orm`, use prepared [nettrine/orm](https://github.com/contributte/doctrine-orm) integration.
Doctrine ORM depends on Doctrine DBAL, use prepared [nettrine/dbal](https://github.com/contributte/doctrine-dbal) integration

```bash
composer require nettrine/dbal
composer require nettrine/orm
```

Without these packages the fixtures can't be processed, because they need a database connection and entities information.


### `symfony/console`

This package relies on `symfony/console`, use prepared [contributte/console](https://github.com/contributte/console) integration.

```bash
composer require contributte/console
```

```yaml
extensions:
  console: Contributte\Console\DI\ConsoleExtension(%consoleMode%)
```


## Configuration

**Schema definition**

```yaml
nettrine.fixtures:
  paths: <string[]>
```

**Under the hood**

You should define paths where the fixture classes are stored.

```yaml
nettrine.fixtures:
  paths:
    - %appDir%/fixtures
```


## Usage

Type `bin/console` in your terminal and there should be a `doctrine:fixtures` command group.

The **doctrine:fixtures:load** command loads data fixtures from your configuration by default:

```
bin/console doctrine:fixtures:load
```

You can also optionally specify the path to the fixtures with the **--fixtures** option:

```
bin/console doctrine:fixtures:load --fixtures=/path/to/fixtures1 --fixtures=/path/to/fixtures2
```

If you want to append the fixtures instead of first flushing the database you can use the **--append** option:

```
bin/console doctrine:fixtures:load --append
```

By default `Doctrine Fixtures` uses `DELETE` statements to drop the existing rows from
the database. If you want to use a `TRUNCATE` statement instead, you can use the **--purge-with-truncate** flag:

```
bin/console doctrine:fixtures:load --purge-with-truncate
```

![Console Commands](https://raw.githubusercontent.com/nettrine/fixtures/master/.docs/assets/console.png)


### Fixture

The simplest fixture just implements **Doctrine\Common\DataFixtures\FixtureInterface**

```php
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class Foo1Fixture implements FixtureInterface
{

  /**
   * Load data fixtures with the passed ObjectManager
   */
  public function load(ObjectManager $manager): void
  {
  }

}
```

If you need to run the fixtures in a fixed succession, implement **Doctrine\Common\DataFixtures\OrderedFixtureInterface**


```php
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class Foo2Fixture implements FixtureInterface, OrderedFixtureInterface
{

  /**
   * Load data fixtures with the passed ObjectManager
   */
  public function load(ObjectManager $manager): void
  {
  }

  /**
   * Get the order of this fixture
   */
  public function getOrder(): int
  {
    return 1;
  }

}
```

If you need to use referencing, extend **Doctrine\Common\DataFixtures\AbstractFixture**

```php
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class Foo3Fixture extends AbstractFixture
{

  /**
   * Load data fixtures with the passed ObjectManager
   */
  public function load(ObjectManager $manager): void
  {
    $this->addReference('user', new User());
    $this->getReference('user');
  }

}
```

If you need to use the Container, implement **Nettrine\Fixtures\ContainerAwareInterface**


```php

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nette\DI\Container;

class Foo4Fixture implements FixtureInterface, ContainerAwareInterface
{

  /** @var Container */
  private $container;

  public function setContainer(Container $container)
  {
    $this->container = $container;
  }

  /**
   * Load data fixtures with the passed ObjectManager
   */
  public function load(ObjectManager $manager): void
  {
    $this->container->getService('foo');
  }

}
```


## Examples

- https://github.com/contributte/playground (playground)
- https://contributte.org/examples.html (more examples)
