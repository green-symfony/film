<?php

namespace App\Command;

use Symfony\Component\Uid\Uuid;
use function Symfony\Component\String\u;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\{
	Path,
	Filesystem
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Helper\{
    ProgressBar,
    Table
};
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\{
    Constraints,
    Validation
};
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\{
    TableSeparator
};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Completion\{
    CompletionSuggestions,
    CompletionInput
};
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Carbon\Carbon;
use App\Entity\Guest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\{
    AsCommand
};
use Symfony\Component\Console\Input\{
    InputArgument,
    InputOption,
    InputInterface
};
use Symfony\Component\Console\Output\{
    OutputInterface
};

// bin/console <command>
/*
#[AsCommand(
    name: 'app:add-guest',
    description: 'Adds new guests.',
    hidden: false,
)]
*/
abstract class AbstractCommand extends Command implements SignalableCommandInterface, ServiceSubscriberInterface
{
	public const DOP_WIDTH_FOR_STR_PAD		= 1;
	
	public const EMOJI_START_RANGE			= 0x1F400;
	public const EMOJI_END_RANGE			= 0x1F43C;
	
    protected const WIDTH_PROGRESS_BAR = 40;
    protected const EMPTY_COLOR_PROGRESS_BAR = 'black';
    protected const PROGRESS_COLOR_PROGRESS_BAR = 'cyan';

    protected SymfonyStyle $style;
    protected $formatter;
    protected $progressBar;
    protected $table;

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
		return Command::SUCCESS;
    }

    public function __construct(
		protected $env,
		protected $arrayService,
		protected $t,
		protected $devLogger,
	) {
        parent::__construct();
        // >>> Style >>>
        // >>> ProgressBar >>>
        ProgressBar::setPlaceholderFormatterDefinition(
            'spin',
            static function (
                ProgressBar $progressBar,
                OutputInterface $output,
            ) {
                static $i = 0;
                //https://raw.githubusercontent.com/sindresorhus/cli-spinners/master/spinners.json
                $spin = [
                    "🕐 ",
                    "🕑 ",
                    "🕒 ",
                    "🕓 ",
                    "🕔 ",
                    "🕕 ",
                    "🕖 ",
                    "🕗 ",
                    "🕘 ",
                    "🕙 ",
                    "🕚 ",
                    "🕛 ",
                ];
                if ($i >= \count($spin)) {
                    $i = 0;
                }
                return $spin[$i++];
            }
        );
        ProgressBar::setFormatDefinition('normal', '%bar% %percent:2s%% %spin%');
        ProgressBar::setFormatDefinition('normal_nomax', '%bar% progress: %current% %spin%');
    }

    protected function configure(): void
    {
		//\pcntl_signal(\SIGINT, $this->shutdown(...));
		//\register_shutdown_function($this->shutdown(...));
    }

    protected function initialize(
        InputInterface $input,
        OutputInterface $output,
    ) {
        // >>> Locale/Charset >>>
        //\ini_set('mbstring.internal_encoding', 'UTF-8');
        \setlocale(LC_ALL, 'Russian');
        // >>> Objects >>>
        $this->io = new SymfonyStyle($input, $output);
        // >>> Style >>>
        $this->setFormatter();
        $this->setProgressBar();
        $this->setTable();
    }

    private function setFormatter()
    {
        $this->formatter = $this->getHelper('formatter');
    }

    /*
        protected const WIDTH_PROGRESS_BAR = 20;

        $this->progressBar->setMaxSteps(<int>);
        $this->progressBar->start();
    */
    private function setProgressBar()
    {
        $this->progressBar = $this->io->createProgressBar();
        $this->progressBar->setEmptyBarCharacter("<bg=" . static::EMPTY_COLOR_PROGRESS_BAR . "> </>");
        $this->progressBar->setProgressCharacter("<bg=" . static::EMPTY_COLOR_PROGRESS_BAR . ";fg=" . static::EMPTY_COLOR_PROGRESS_BAR . "> </>");
        $this->progressBar->setBarCharacter("<bg=" . static::PROGRESS_COLOR_PROGRESS_BAR . "> </>");
        $this->progressBar->setBarWidth(static::WIDTH_PROGRESS_BAR);
    }

    private function setTable()
    {
        $this->table = $this->io->createTable();
        $this->table->setStyle(
            (new TableStyle())
            ->setHorizontalBorderChars('-')
            ->setVerticalBorderChars('|')
            ->setDefaultCrossingChar('+')
        );
        $this->table->setStyle('box-double');
    }

    protected function interact(
        InputInterface $input,
        OutputInterface $output,
    ) {
        // get missed options/arguments
    }

	public function getSubscribedSignals(): array
    {
        return [
			/*
            \SIGINT,
            \SIGTERM,
			*/
        ];
    }
	
    public function handleSignal(int $signal): void
    {
		/*
		if ($signal == 2 || $signal == 255) {
            $this->shutdown();
        }
		*/
    }


    /* ServiceSubscriberInterface */
    public static function getSubscribedServices(): array
    {
        return [
            'logger' => '?Psr\Log\LoggerInterface',
        ];
    }
	
	//###> HELPER ###
	
	protected function isOk(
		?string $message			= null,
		string $default				= null,
		bool $exitWhenDisagree		= true,
	) {
		$message ??= $this->t->trans('Right') . '?';
		
		$agree = $this->io->askQuestion(
			($default !== null ? new ConfirmationQuestion($message, $default) : new ConfirmationQuestion($message))
		);
		
		if ($exitWhenDisagree && !$agree) {
			$this->io->warning($this->t->trans('EXIT'));
			exit(Command::INVALID);
		}

		return $agree;
	}
	
	protected function getEscapedForRegex(string $string): string
	{
		$string = \strtr(
			$string,
			[
				'|'		=> '[|]',
				'+'		=> '[+]',
				'*'		=> '[*]',
				'?'		=> '[?]',
				'['		=> '[[]',
				']'		=> '[]]',
				'\\'	=> '(?:\\\\|\/)',
				'/'		=> '(?:\\|\/)',
				'.'		=> '[.]',
				'-'		=> '[-]',
				')'		=> '[)]',
				'('		=> '[(]',
				'{'		=> '[{]',
				'}'		=> '[}]',
			]
		);

		return $string;
	}
	
	protected function getEmoji(): string {
		[$max, $min] = [
			self::EMOJI_START_RANGE,
			self::EMOJI_END_RANGE,
		];
		if ($min > $max) [$max, $min] = [$min, $max];
		return \IntlChar::chr(\random_int($min, $max));
	}
	
	protected function getPath(
		string...$parts,
	): string {
		$NDS				= Path::normalize(\DIRECTORY_SEPARATOR);
		
		\array_walk($parts, static fn(&$path) => $path = \rtrim(\trim($path), "/\\"));
		
		$resultPath		 = Path::normalize(\implode($NDS, $parts));

		return $resultPath;
	}
	
	protected function getOptimalWidthForStrPad($inputString, array $all): int {
		// const part
		$maxLen			= $this->arrayService->getMaxLen($all);
		$const			= $maxLen + self::DOP_WIDTH_FOR_STR_PAD;
		// dynamic part
		$geLengthWithoutMbLetters = static fn($string) => \strlen(
			\preg_replace('~[а-я]~ui', '', (string) $string)
		);
		$currentLen		= \mb_strlen((string) $inputString) - $geLengthWithoutMbLetters($inputString);
		
		// for \str_pad
		return $currentLen + $const;
	}
	
	protected function flushOb(): void {
		while(\ob_get_level() > 0) \ob_end_flush();
	}
	
	protected function shutdown(): void {
		$this->devLogger->info(__METHOD__);
		$this->io->warning(
			$this->t->trans(
				'Команда %command% остановлена',
				parameters: [
					'%command%'		=> $this->getName(),
				],
			)
		);
		$this->flushOb();
		exit(Command::INVALID);
	}
}
