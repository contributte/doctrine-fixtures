# Contributte Doctrine Fixtures

Integration of [Doctrine DataFixtures](https://www.doctrine-project.org/projects/data-fixtures.html) for Nette Framework.

## Content


## Installation

Install package using composer.

```bash
composer require nettrine/annotations
```

Register prepared [compiler extension](https://doc.nette.org/en/dependency-injection/nette-container) in your `config.neon` file.

```neon
extensions:
  nettrine.fixtures: Nettrine\Fixtures\DI\FixturesExtension
```

> [!NOTE]
> This is just **Fixtures**, for **ORM** use [nettrine/orm](https://github.com/contributte/doctrine-orm) or **DBAL** use [nettrine/dbal](https://github.com/contributte/doctrine-dbal).

## Configuration

### Minimal configuration

```neon
nettrine.fixtures:
  paths:
    - %appDir%/fixtures
```

### Advanced configuration

Here is the list of all available options with their types.

```yaml
nettrine.fixtures:
  paths: <string[]>
```

## Usage

Type `bin/console` in your terminal and there should be a `doctrine:fixtures` command group.

```sh
bin/console doctrine:fixtures:load
bin/console doctrine:fixtures:load --fixtures=db/fixtures/development
```

By default, the fixtures are appended to the database. If you want to delete all data before loading fixtures, use `--purge` option.

```sh
bin/console doctrine:fixtures:load --purge=truncate
bin/console doctrine:fixtures:load --purge=delete
```

![Console Commands](https://raw.githubusercontent.com/nettrine/fixtures/master/.docs/assets/console.png)

## Fixture

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

If you need to run the fixtures in a fixed order after some other fixture, implement **Doctrine\Common\DataFixtures\DependentFixtureInterface**


```php
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class Foo2Fixture implements FixtureInterface, DependentFixtureInterface
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
  public function getDependencies(): int
  {
    return [Foo1Fixture::class];
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

### Services

To autoload your fixtures, register them as services in your `config.neon` file.

```neon
services:
  - App\Fixtures\Foo1Fixture
  - App\Fixtures\Foo2Fixture
  - App\Fixtures\Foo3Fixture
  - App\Fixtures\Foo4Fixture
```

## DBAL & ORM

> [!TIP]
> Doctrine Migrations needs a database connection and entities information.
> Take a look at [nettrine/dbal](https://github.com/contributte/doctrine-dbal) and [nettrine/orm](https://github.com/contributte/doctrine-orm).

```bash
composer require nettrine/dbal
composer require nettrine/orm
```

### Console

> [!TIP]
> Doctrine DBAL needs Symfony Console to work. You can use `symfony/console` or [contributte/console](https://github.com/contributte/console).

```bash
composer require contributte/console
```

```neon
extensions:
  console: Contributte\Console\DI\ConsoleExtension(%consoleMode%)

  nettrine.dbal: Nettrine\DBAL\DI\DbalExtension
```

Since this moment when you type `bin/console`, there'll be registered commands from Doctrine DBAL.

![Console Commands](https://raw.githubusercontent.com/nettrine/dbal/master/.docs/assets/console.png)

## Examples

> [!TIP]
> Take a look at more examples in [contributte/doctrine](https://github.com/contributte/doctrine/tree/master/.docs).
