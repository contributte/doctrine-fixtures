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
			->addOption('fixtures', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'File or directory to load data fixtures from.')
			->addOption('em', null, InputOption::VALUE_REQUIRED, 'The entity manager to use for this command.')
			->addOption('purge', null, InputOption::VALUE_REQUIRED, 'Purge data from database using TRUNCATE or DELETE. Default no purging, data will be appended.')
			->setHelp(<<<'EOT'
				The <info>%command.name%</info> command loads data fixtures from your application:

					<info>php %command.full_name%</info>

				You can also optionally specify the path to fixtures with the <info>--fixtures</info> option:

					<info>%command.name% --fixtures=db/fixtures/development --fixtures=db/fixtures/staging</info>

				You provide entity manager name <info>--em</info> option:

					<info>%command.name% --em=second/info>

				You can append, truncate or delete data using <comment>--append</comment> option (append by default):

					<info>php %command.full_name%</info> <comment>--purge=truncate</comment>
					<info>php %command.full_name%</info> <comment>--purge=delete</comment>
				EOT
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$ui = new SymfonyStyle($input, $output);

		$inputEm = ConsoleHelper::stringNull($input->getOption('em'));
		$inputPurge = ConsoleHelper::stringNull($input->getOption('purge'));
		$inputFixtures = ConsoleHelper::arrayString($input->getOption('fixtures'));

		if ($inputPurge !== null && !in_array($inputPurge, ['truncate', 'delete'], true)) {
			throw new LogicalException(sprintf('Invalid value for --purge option. Use "truncate", "delete" or no value.'));
		}

		$em = $this->managerRegistry->getManager($inputEm);
		assert($em instanceof EntityManagerInterface);

		// Ask user to confirm purging database
		if (in_array($inputPurge, ['truncate', 'delete'], true)) {
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
		$purger->setPurgeMode($inputPurge === 'truncate' ? ORMPurger::PURGE_MODE_TRUNCATE : ORMPurger::PURGE_MODE_DELETE);

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

		$executor->execute($fixtures, $inputPurge === null);

		return self::SUCCESS;
	}

}
