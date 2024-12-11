<?php declare(strict_types = 1);

namespace Nettrine\Fixtures\Command;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Nettrine\Fixtures\Exceptions\LogicalException;
use Nettrine\Fixtures\Loader\FixturesLoader;
use Nettrine\Fixtures\Utils\ConsoleHelper;
use Psr\Log\AbstractLogger;
use Stringable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Load data fixtures from bundles.
 *
 * @see https://github.com/doctrine/DoctrineFixturesBundle
 */
#[AsCommand(name: 'doctrine:fixtures:load')]
final class LoadDataFixturesCommand extends Command
{

	public function __construct(
		private FixturesLoader $fixturesLoader,
		private ManagerRegistry $managerRegistry
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->setDescription('Load data fixtures to your database')
			->addOption('append', null, InputOption::VALUE_NONE, 'Append the data fixtures instead of deleting all data from the database first.')
			->addOption('fixtures', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'File or directory to load data fixtures from.')
			->addOption('em', null, InputOption::VALUE_REQUIRED, 'The entity manager to use for this command.')
			->addOption('purge-with-truncate', null, InputOption::VALUE_NONE, 'Purge data by using a database-level TRUNCATE statement')
			->setHelp(<<<'EOT'
				The <info>%command.name%</info> command loads data fixtures from your application:

				  <info>php %command.full_name%</info>

				You can also optionally specify the path to fixtures with the <info>--fixtures</info> option:

				<info>%command.name% --fixtures=db/fixtures/development --fixtures=db/fixtures/staging</info>

				If you want to append the fixtures instead of flushing the database first you can use the <comment>--append</comment> option:

				  <info>php %command.full_name%</info> <comment>--append</comment>

				By default Doctrine Data Fixtures uses DELETE statements to drop the existing rows from the database.
				If you want to use a TRUNCATE statement instead you can use the <comment>--purge-with-truncate</comment> flag:

				  <info>php %command.full_name%</info> <comment>--purge-with-truncate</comment>
				EOT
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$ui = new SymfonyStyle($input, $output);

		$inputAppend = ConsoleHelper::bool($input->getOption('append'));
		$inputEm = ConsoleHelper::stringNull($input->getOption('em'));
		$inputTruncate = ConsoleHelper::bool($input->getOption('purge-with-truncate'));
		$inputFixtures = ConsoleHelper::arrayString($input->getOption('fixtures'));

		$em = $this->managerRegistry->getManager($inputEm);
		assert($em instanceof EntityManagerInterface);

		// Ask user to confirm purging database
		if (!$inputAppend) {
			if (!$ui->confirm(sprintf('Careful, database "%s" will be purged. Do you want to continue?', $em->getConnection()->getDatabase()), !$input->isInteractive())) {
				return 0;
			}
		}

		// Load fixtures from given paths
		if ($inputFixtures === []) {
			$this->fixturesLoader->load();
		} else {
			$this->fixturesLoader->loadPaths($inputFixtures);
		}

		$fixtures = $this->fixturesLoader->getFixtures();
		if ($fixtures === []) {
			$paths = $this->fixturesLoader->getPaths();

			if ($paths === []) {
				throw new LogicalException('Could not find any fixtures to load.');
			} else {
				throw new LogicalException(sprintf('Could not find any fixtures to load in paths: %s', implode(', ', $paths)));
			}
		}

		$purger = new ORMPurger($em);
		$purger->setPurgeMode($inputTruncate !== false ? ORMPurger::PURGE_MODE_TRUNCATE : ORMPurger::PURGE_MODE_DELETE);

		$executor = new ORMExecutor($em, $purger);
		$executor->setLogger(new class ($ui) extends AbstractLogger {

			public function __construct(private SymfonyStyle $ui)
			{
			}

			/**
			 * {@inheritDoc}
			 */
			public function log(mixed $level, string|Stringable $message, array $context = []): void
			{
				$this->ui->text(sprintf('  <comment>></comment> <info>%s</info>', $message));
			}

		});

		$executor->execute($fixtures, $inputAppend);

		return self::SUCCESS;
	}

}
