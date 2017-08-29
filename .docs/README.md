# Fixtures

## Content

- [Usage - how to register](#usage)
- [Extension - how to configure](#configuration)
- [Command - how to use command](#command)
- [Fixture - how to write fixtures](#fixture)

## Usage

Use Symfony/Console integration [Contributte/Console](https://github.com/contributte/console).

Register extension.

```yaml
extensions:
    fixtures: Nettrine\Fixtures\DI\FixturesExtension
```

## Configuration

Optional configuration.

```yaml
fixtures:
    paths:
        - app/model/Fixtures
        - ...
```

## Command

The **doctrine:fixtures:load** command loads data fixtures from your configuration by default:
  
    doctrine:fixtures:load
  
You can also optionally specify the path to fixtures with the **--fixtures** option:
  
    doctrine:fixtures:load --fixtures=/path/to/fixtures1 --fixtures=/path/to/fixtures2
  
If you want to append the fixtures instead of flushing the database first you can use the **--append** option:
  
    doctrine:fixtures:load --append
  
By default Doctrine Data Fixtures uses DELETE statements to drop the existing rows from
the database. If you want to use a TRUNCATE statement instead you can use the **--purge-with-truncate** flag:
  
    doctrine:fixtures:load --purge-with-truncate
  

## Fixture

Simplest use of fixture is implement **Doctrine\Common\DataFixtures\FixtureInterface**

`TODO example`

If you need use ordering, implement **Doctrine\Common\DataFixtures\OrderedFixtureInterface**

`TODO example`

If you need use referencing, extend **Doctrine\Common\DataFixtures\AbstractFixture**

`TODO example`

If you need use container, implement **Nettrine\Fixtures\ContainerAwareInterface**

`TODO example`