<?php declare(strict_types = 1);

namespace Nettrine\Fixtures\Command;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Nettrine\Fixtures\Loader\FixturesLoader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Load data fixtures from bundles.
 *
 * @see https://github.com/doctrine/DoctrineFixturesBundle/blob/3.5.x/Command/LoadDataFixturesDoctrineCommand.php
 */
#[AsCommand(name: 'doctrine:fixtures:load')]
class LoadDataFixturesCommand extends Command
{

	private FixturesLoader $fixturesLoader;

	private ManagerRegistry $managerRegistry;

	public function __construct(FixturesLoader $fixturesLoader, ManagerRegistry $managerRegistry)
	{
		parent::__construct();

		$this->fixturesLoader = $fixturesLoader;
		$this->managerRegistry = $managerRegistry;
	}

	protected function configure(): void
	{
		$this
			->setDescription('Load data fixtures to your database')
			->addOption('append', null, InputOption::VALUE_NONE, 'Append the data fixtures instead of deleting all data from the database first.')
			->addOption('em', null, InputOption::VALUE_REQUIRED, 'The entity manager to use for this command.')
			->addOption('purge-with-truncate', null, InputOption::VALUE_NONE, 'Purge data by using a database-level TRUNCATE statement')
			->setHelp(<<<'EOT'
The <info>%command.name%</info> command loads data fixtures from your application:

  <info>php %command.full_name%</info>

Fixtures are services that are tagged with <comment>doctrine.fixture.orm</comment>.

If you want to append the fixtures instead of flushing the database first you can use the <comment>--append</comment> option:

  <info>php %command.full_name%</info> <comment>--append</comment>

By default Doctrine Data Fixtures uses DELETE statements to drop the existing rows from the database.
If you want to use a TRUNCATE statement instead you can use the <comment>--purge-with-truncate</comment> flag:

  <info>php %command.full_name%</info> <comment>--purge-with-truncate</comment>

To execute only fixtures that live in a certain group, use:

  <info>php %command.full_name%</info> <comment>--group=group1</comment>

EOT
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$ui = new SymfonyStyle($input, $output);

		$em = $this->managerRegistry->getManager(is_string($input->getOption('em')) ? $input->getOption('em') : null);
		assert($em instanceof EntityManagerInterface);

		if ($input->getOption('append') === false) {
			if (!$ui->confirm(sprintf('Careful, database "%s" will be purged. Do you want to continue?', $em->getConnection()->getDatabase()), !$input->isInteractive())) {
				return 0;
			}
		}

		$this->fixturesLoader->load();
		$fixtures = $this->fixturesLoader->getFixtures();
		if ($fixtures === []) {
			$ui->error('Could not find any fixture services to load.');

			return 1;
		}

		$purgeTruncate = $input->getOption('purge-with-truncate');
		$purger = new ORMPurger($em);
		$purger->setPurgeMode($purgeTruncate !== false ? ORMPurger::PURGE_MODE_TRUNCATE : ORMPurger::PURGE_MODE_DELETE);

		$executor = new ORMExecutor($em, $purger);
		$executor->setLogger(static function ($message) use ($ui): void {
			$ui->text(sprintf('  <comment>></comment> <info>%s</info>', $message));
		});
		$executor->execute($fixtures, $input->getOption('append') !== false);

		return 0;
	}

}
